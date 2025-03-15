# Video Plugin

A plugin for [Kirby CMS](http://getkirby.com) to embed content from YouTube without compromising privacy.

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

## How to use it

Use this kirbytag

```
(youtube: g5BEXgNHZJU)
```
