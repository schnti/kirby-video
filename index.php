<?php

use Kirby\Cms\File;
use Kirby\Filesystem\F;
use Kirby\Http\Remote;

function parseYoutube(string $url)
{
    $data = [];

    # Extract YouTube video ID
    # See http://stackoverflow.com/questions/2936467/parse-youtube-video-id-using-preg-match
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[\w\-?&!#=,;]+/[\w\-?&!#=/,;]+/|(?:v|e(?:mbed)?)/|[\w\-?&!#=,;]*[?&]v=)|youtu\.be/)([\w-]{11})(?:[^\w-]|\Z)%i', $url, $match);
    $data['id'] = $match[1] ?? $url;

    # Extract YouTube playlist ID
    # See https://stackoverflow.com/questions/5115233/fetching-youtube-playlist-id-with-php-regex
    $parts = parse_url($url);

    if (isset($parts['query'])) {
        parse_str($parts['query'], $query);

        if (isset($query['list'])) {
            $data['playlist'] = $query['list'];
        }
    }

    # Build embed URL
    $data['src'] = 'https://www.youtube-nocookie.com/embed/' . $data['id'] . '?autoplay=1';

    if (isset($data['playlist'])) {
        $data['src'] = 'https://www.youtube-nocookie.com/embed/videoseries?list=' . $data['playlist'] . '&autoplay=1';
    }

    $data['link'] = 'https://www.youtube.com/watch?v=' . $data['id'];

    return $data;
}

/**
 * Extracts the video id from a Vimeo URL.
 *
 * Handles public links (vimeo.com/<id>) as well as unlisted ones carrying a
 * privacy hash (vimeo.com/<id>/<hash>). The hash has to be passed to the
 * player as the h parameter, otherwise the video stays inaccessible.
 *
 * Returns null when no id could be found. Does no network access.
 */
function parseVimeo(string $url)
{
    if (preg_match('#vimeo\.com/(?:video/)?(\d+)(?:/([a-zA-Z0-9]+))?#i', $url, $match) !== 1) {
        return null;
    }

    $id   = $match[1];
    $hash = $match[2] ?? null;

    return [
        'id'   => $id,
        'src'  => 'https://player.vimeo.com/video/' . $id . '?' . ($hash ? 'h=' . $hash . '&' : '') . 'autoplay=1',
        'link' => 'https://vimeo.com/' . $id . ($hash ? '/' . $hash : ''),
    ];
}

/**
 * Fetches a URL and returns its body, or null on any failure.
 */
function schntiVideoFetch(string $url, int $timeout)
{
    try {
        $response = Remote::get($url, ['timeout' => $timeout]);

        if ($response->code() !== 200) {
            return null;
        }

        return $response->content() ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

/**
 * Looks up the thumbnail URL of a Vimeo video via the public oEmbed endpoint.
 *
 * The width matters: without it Vimeo returns its smallest thumbnail (295px),
 * which is too small for a poster and makes the later resize() a no-op.
 *
 * Returns null when the lookup fails, so a missing thumbnail never takes the
 * page down with it.
 */
function schntiVimeoPosterUrl(string $link, int $width)
{
    $url  = 'https://vimeo.com/api/oembed.json?url=' . urlencode($link) . '&width=' . $width;
    $body = schntiVideoFetch($url, 3);

    if ($body === null) {
        return null;
    }

    return json_decode($body, true)['thumbnail_url'] ?? null;
}

/**
 * Downloads the poster image and stores it next to the page, so the preview is
 * served from your own server and no request reaches the video platform before
 * the visitor consents.
 *
 * Two guards keep this out of the critical path:
 *
 * - A failed attempt is remembered in the plugin cache for a day. Without it
 *   every single page view would run into the same timeouts again — for a
 *   deleted video that would be forever.
 * - At most one poster is fetched per request. A page with five new videos
 *   would otherwise do ten HTTP requests in a row while the visitor waits; it
 *   now fills the gap over the next few page views instead.
 *
 * Returns true when the poster is on disk afterwards.
 */
function schntiVideoFetchPoster(string $service, string $id, string $path, callable $posterUrl)
{
    static $budget = 1;

    $cache = kirby()->cache('schnti.video');
    $key   = 'failed/' . $service . '/' . $id;

    if ($cache->get($key) !== null || $budget < 1) {
        return false;
    }

    $budget--;

    foreach (array_filter((array)$posterUrl()) as $candidate) {
        if ($image = schntiVideoFetch($candidate, 5)) {
            F::write($path, $image);

            return true;
        }
    }

    $cache->set($key, true, 60 * 24);

    return false;
}

/**
 * Returns the poster as a resized File, or null when there is none. The
 * embed renders without it and falls back to a 16:9 box.
 */
function schntiVideoPoster($tag, string $service, string $id, callable $posterUrl)
{
    $filename = F::safeName($service . '_' . $id . '.jpg');
    $path     = $tag->parent()->root() . '/' . $filename;

    if (file_exists($path) === false && schntiVideoFetchPoster($service, $id, $path, $posterUrl) === false) {
        return null;
    }

    // Kirbys File-Objekt liest die Datei selbst über parent + filename ein;
    // ein 'source' würde ignoriert.
    $file = new File([
        'filename' => $filename,
        'parent'   => $tag->parent(),
    ]);

    // resize() verändert $file nicht, sondern gibt eine neue Version zurück.
    // Ohne die Zuweisung würde der Thumb erzeugt, aber das Original in voller
    // Auflösung ausgeliefert.
    return $file->resize(schntiVideoWidth($tag));
}

/**
 * Poster width for a tag.
 */
function schntiVideoWidth($tag)
{
    return $tag->width ? (int)$tag->width : 1000;
}

/**
 * Text with the provider name filled in.
 *
 * tt() handles the documented {{ service }} placeholder. The str_replace is
 * only a compatibility shim for 1.x, where the placeholder was %s — an
 * override written back then keeps working instead of showing a literal %s.
 */
function schntiVideoText(string $key, array $replace)
{
    return str_replace('%s', $replace['service'], tt($key, null, $replace));
}

/**
 * Shared body of the video tags: parse, fetch the poster, render.
 *
 * snippet() without the third parameter prints instead of returning. That was
 * the behaviour before and stays: printing bypasses the Markdown parser, which
 * would otherwise read the indented snippet lines as a code block.
 */
function schntiVideoEmbed($tag, string $service, ?array $data, callable $posterUrl, string $snippet)
{
    if ($data === null) {
        return $service . ' video not found';
    }

    $image = schntiVideoPoster($tag, strtolower($service), $data['id'], $posterUrl);

    snippet($snippet, [
        'class'   => $tag->class,
        'service' => $service,
        'id'      => $data['id'],
        'src'     => $data['src'],
        'link'    => $data['link'],
        'image'   => $image,
        // Wird vom mitgelieferten Snippet nicht gebraucht, gehoerte aber zum
        // Vertrag von 1.x – ein ueberschriebenes youtube.php darf es nutzen.
        'width'   => schntiVideoWidth($tag),
    ]);

    return '';
}


Kirby::plugin('schnti/video', [
	'options'      => [
		// Merkt fehlgeschlagene Poster-Abrufe. Ohne aktiven Cache laeuft jeder
		// Seitenaufruf erneut in dieselben Timeouts.
		'cache'  => true,
		// Die Video-ID unter dem Hinweis anzeigen.
		'showId' => true,
		// Zusaetzliche Klassen fuer den Einwilligungs-Button, damit ein Projekt
		// seinen eigenen Button-Stil nutzen kann statt diesen umzustylen.
		'buttonClass' => '',
	],
	'translations' => [
		'de' => [
			'schnti.video.headline'   => 'Wir respektieren deinen Datenschutz!',
			'schnti.video.text'       => 'Klicke zum Aktivieren des Videos auf den Knopf. Wir möchten darauf hinweisen, dass nach der Aktivierung deine Daten an {{ service }} übermittelt werden.',
			'schnti.video.buttonText' => 'Video aktivieren',
			'schnti.video.linkText'   => 'oder bei {{ service }} anschauen',
			'schnti.video.id'         => 'Video-ID:',
		],
		'en' => [
			'schnti.video.headline'   => 'We respect your privacy!',
			'schnti.video.text'       => 'Click the button to activate the video. Then a connection to {{ service }} is established.',
			'schnti.video.buttonText' => 'Activate video',
			'schnti.video.linkText'   => 'or watch on {{ service }}',
			'schnti.video.id'         => 'Video ID:',
		]
	],
	'snippets'     => [
		// video-consent kennt weder YouTube noch Vimeo und laesst sich damit
		// auch fuer andere Einbettungen wiederverwenden (Karten, Kalender, ...).
		'video-consent' => __DIR__ . '/snippets/video-consent.php',
		'video'         => __DIR__ . '/snippets/video.php',
		'youtube'       => __DIR__ . '/snippets/youtube.php',
	],
	'tags'         => [
		'youtube' => [
			'attr' => array(
				'class',
				'width',
			),
			'html' => function ($tag) {

				$data = parseYoutube((string)$tag->value);

				// maxresdefault gibt es nicht fuer jedes Video, deshalb mit
				// hqdefault als Rueckfalloption.
				return schntiVideoEmbed($tag, 'YouTube', $data, fn () => [
					'https://i.ytimg.com/vi/' . $data['id'] . '/maxresdefault.jpg',
					'https://i.ytimg.com/vi/' . $data['id'] . '/hqdefault.jpg',
				], 'youtube');
			}
		],
		'vimeo' => [
			'attr' => array(
				'class',
				'width',
			),
			'html' => function ($tag) {

				$data = parseVimeo((string)$tag->value);

				// Vimeo hat keine vorhersagbare Thumbnail-URL, sie muss per
				// oEmbed nachgeschlagen werden – deshalb als Callback, damit
				// das nur beim ersten Mal passiert.
				return schntiVideoEmbed($tag, 'Vimeo', $data, fn () => $data
					? schntiVimeoPosterUrl($data['link'], schntiVideoWidth($tag))
					: null, 'video');
			}
		]
	]
]);
