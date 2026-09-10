# Video Plugin

A plugin for [Kirby CMS](http://getkirby.com) to embed content from YouTube and
Vimeo without compromising privacy.

Requires Kirby 3.6 or newer and PHP 7.4 or newer. Tested against Kirby 5.5 and
6.0-alpha.

## Commercial Usage

This plugin is free but if you use it in a commercial project please consider

- [making a donation](https://www.paypal.me/schnti/5) or
- [buying a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/48194?link=1170)

## Installation

### Download

[Download the files](https://github.com/schnti/kirby-video/archive/master.zip) and place them inside `site/plugins/video`.

### Composer

```
composer require schnti/video
```

### Git Submodule
You can add the plugin as a Git submodule.

    $ cd your/project/root
    $ git submodule add https://github.com/schnti/kirby-video.git site/plugins/video
    $ git submodule update --init --recursive
    $ git commit -am "Add Kirby video plugin"

Run these commands to update the plugin:

    $ cd your/project/root
    $ git submodule foreach git checkout master
    $ git submodule foreach git pull
    $ git commit -am "Update submodules"
    $ git submodule update --init --recursive
      

## CSS (SCSS)

```HTML
<link rel="stylesheet" href="plugins/video/dist/youtube.css">
```

or

```SCSS
@import "site/plugins/video/src/youtube";
```

## JS

```HTML
<script src="site/plugins/video/src/youtube.js"></script>
```

or

```JS
require('site/plugins/video/src/youtube');
```

## Development

The stylesheet in `dist/` is built from `src/youtube.scss` with Dart Sass —
no build pipeline beyond that:

```
npm install
npm run build      # writes dist/youtube.css and dist/youtube.min.css
npm run watch      # rebuilds on change
```

## How to use it

Use one of these kirbytags:

```
(youtube: g5BEXgNHZJU)
(vimeo: https://vimeo.com/820820654)
```

Vimeo links that carry a privacy hash for unlisted videos work as well:

```
(vimeo: https://vimeo.com/473379353/0f607890d1)
```

Both tags accept `class` and `width`.

### How the privacy part works

Nothing is loaded from YouTube or Vimeo until the visitor clicks the button:

- The poster image is downloaded **once, server side**, and stored next to the
  page, so the preview is served from your own domain.
- The player URL sits in `data-src` and is only moved to `src` on click.

For Vimeo the thumbnail is looked up via the public oEmbed endpoint. That
lookup only happens when the poster is not on disk yet, so a normal page view
never talks to Vimeo.

If the lookup fails — for instance because the video was deleted — the embed
still renders, just without a poster and with a 16:9 box. The failure is
remembered for a day in the plugin cache, so a dead video does not cost a
network timeout on every page view. Clearing the Kirby cache retries earlier.

At most **one** poster is fetched per request. A page with five new videos
would otherwise do ten HTTP requests in a row while the visitor waits; it
fills the gap over the next few page views instead.

### Reusing the consent notice

`video-consent` knows nothing about YouTube or Vimeo and can be used for any
embed that must not load before consent.

```php
<div class="video-container disabled">
    <div class="embed-container" style="display: none">
        <iframe data-src="https://example.com/embed/…"></iframe>
    </div>
    <?php snippet('video-consent', [
        'service'     => 'Example',
        'text'        => 'Click the button to load the embed. …',
        'button'      => 'Load embed',
        'icon'        => url('assets/images/icon.svg'),
        'buttonClass' => 'btn btn-primary',
        'link'        => 'https://example.com/…',
    ]) ?>
</div>
```

On consent the script hides the poster, reveals `.embed-container` and moves
the iframe URL from `data-src` to `src`.

Pass `text`, `button`, `headline` and `linkText` for anything that is not a
video; the defaults talk about videos. `buttonClass` adds classes to the
button so you can reuse your own button style instead of restyling
`.video-hint-button`, and `icon` shows an image above the headline.

**Embeds that are not an iframe** — a map that loads a provider script, for
instance — need their own click handler. Reuse the notice for the markup and
do the loading yourself; the plugin deliberately stays out of that.

### Texts

The texts take the provider name as `{{ service }}`:

| Key | Default (en) |
| --- | --- |
| `schnti.video.headline` | We respect your privacy! |
| `schnti.video.text` | Click the button to activate the video. Then a connection to {{ service }} is established. |
| `schnti.video.buttonText` | Activate video |
| `schnti.video.linkText` | or watch on {{ service }} |
| `schnti.video.id` | Video ID: |

Texts without a placeholder pass through unchanged, and the old `%s`
placeholder from 1.x is still filled in — overrides written back then keep
working.

### Options

```php
return [
  'schnti.video.cache'  => true, // remember failed poster lookups (default)
  'schnti.video.showId' => true, // show the video id below the notice
];
```

Leave `cache` on — it is what keeps a deleted video from costing a network
timeout on every page view.

### Upgrading from 1.x

Backwards compatible. The markup now carries both the new generic class names
(`video-container`, `video-hint`, `video-hint-button`, …) and the previous
`youtube-*` ones, and stylesheet and script match either. The filenames of the
built assets are unchanged.
