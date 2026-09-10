<?php

use Kirby\Toolkit\Str;

/**
 * Shared markup for an embedded video behind a consent notice.
 *
 * $service     Provider name, used in the texts (e.g. "Vimeo")
 * $src         Player URL, only set on the iframe after consent
 * $link        Direct link to the video at the provider
 * $id          Video id, shown as a small note unless switched off
 * $image       Poster image, or null when none could be fetched
 * $class       Extra classes from the kirbytag
 * $legacyClass Old class name kept so existing stylesheets keep matching
 *
 * Every element carries both the new generic class and the old youtube-*
 * one. Stylesheets and scripts written against earlier versions of this
 * plugin therefore keep working unchanged.
 */

// Without a poster we cannot know the aspect ratio, so fall back to 16:9.
// Read it from the original: the resized version would have to be generated
// first, which pulls image processing into the HTML request for no reason.
$ratio = $image ? ($image->original()->height() / $image->original()->width() * 100) : 56.25;

$classes = implode(' ', array_filter([
    'video-container',
    $legacyClass ?? '',
    'disabled',
    $class ?? '',
]));

?>
<div class="<?= $classes; ?>">

    <?= $image; ?>

    <div class="embed-container" style="display: none; padding-bottom: <?= Str::float($ratio); ?>%">
        <iframe data-src="<?= $src; ?>"
                frameborder="0"
                allow="autoplay; encrypted-media"
                allowfullscreen></iframe>
    </div>

    <?php snippet('video-consent', [
        'service' => $service,
        'link'    => $link,
        'note'    => option('schnti.video.showId') ? $id : null,
    ]); ?>

</div>
