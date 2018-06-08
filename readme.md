# Video Plugin - FIRST DRAFT!!

A Tag for [Kirby 2 CMS](http://getkirby.com) to embed content from YouTube without compromising privacy.

![Video Plugin Example](./youtube.png)

## Installation

Copy or link the `video` directory to `site/plugins/` **or** use the [Kirby CLI](https://github.com/getkirby/cli):

```
kirby plugin:install schnti/kirby-video
```

### JavaScript
```html

<script src="jquery.min.js"></script>

<script>
    $('.youtube-hint-button').click(function (e) {
        e.stopPropagation();

        var youtubeContainer = $(this).closest('.youtube-container');
        youtubeContainer.removeClass('disabled');

        var img = youtubeContainer.find('img');
        img.hide();

        var frame = youtubeContainer.find('iframe');
        frame.attr('src', frame.data('src'));
        frame.show();
    });
</script>
```

### SCSS

```SCSS
@import "../website/site/plugins/video/video.scss";
```

or

```scss
.youtube-container {
  position: relative;
  
  img {
    width: 100%;
  }

  &.disabled:after {
    position: absolute;
    content: '';
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #ffffff;
    opacity: 0.7;
    z-index: 1000;
  }

  .youtube-hint {
    display: none;
  }

  &.disabled .youtube-hint {

    display: inline-block;

    position: absolute;

    width: 400px;
    height: 130px;

    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    margin: auto;

    max-width: 100%;
    max-height: 100%;

    z-index: 1100;
    background: #ffffff;

    font-size: 0.9em;

    .youtube-hint-text {

      padding: 10px;
    }
  }

}
```

## How to use it

Use this simple tag

```
(youtube: g5BEXgNHZJU class: image-left width: 350 height: 220)
```

## Demo
https://abbruchwalter.de/leistungen