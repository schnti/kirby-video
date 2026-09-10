<?php

/**
 * Consent notice shown on top of an embedded third-party element.
 *
 * Deliberately knows nothing about YouTube or Vimeo, so it can be reused for
 * any embed that must not load before the visitor agrees – maps, calendars,
 * whatever.
 *
 * $service     Name of the provider, filled into the texts (e.g. "Vimeo")
 * $link        Optional direct link to the content at the provider
 * $note        Optional small line below the link (e.g. the video id)
 * $icon        Optional image URL shown above the headline
 * $buttonClass Optional extra classes for the button, so a project can reuse
 *              its own button style instead of restyling this one. Defaults to
 *              the `schnti.video.buttonClass` option, which sets it for every
 *              notice at once.
 *
 * The texts default to the plugin's own `schnti.video.*` translations, with
 * `{{ service }}` filled in; the `%s` placeholder of 1.x still works too.
 * Pass $headline, $text, $button or $linkText to override them – needed when
 * the embed is not a video and "Activate video" would be wrong.
 *
 * Every element carries the old youtube-* class as well, so stylesheets and
 * scripts from earlier versions keep matching.
 */

$replace = ['service' => $service ?? ''];

$headline = $headline ?? schntiVideoText('schnti.video.headline', $replace);
$text     = $text     ?? schntiVideoText('schnti.video.text', $replace);
$button   = $button   ?? schntiVideoText('schnti.video.buttonText', $replace);
$linkText = $linkText ?? schntiVideoText('schnti.video.linkText', $replace);

$buttonClass = trim('video-hint-button youtube-hint-button ' . ($buttonClass ?? option('schnti.video.buttonClass', '')));

?>
<div class="video-hint youtube-hint">
    <div class="video-hint-text youtube-hint-text">
        <div>

            <?php if (empty($icon) === false) : ?>
                <img class="video-hint-icon" src="<?= $icon; ?>" alt="" />
            <?php endif; ?>

            <h3><?= $headline; ?></h3>
            <p><?= $text; ?></p>
            <button type="button" class="<?= $buttonClass; ?>"><?= $button; ?></button>

            <?php if (empty($link) === false) : ?>
                <div class="video-hint-link-container youtube-hint-link-container">
                    <small><a href="<?= $link; ?>" class="video-hint-link youtube-hint-link" target="_blank" rel="noopener"><?= $linkText; ?></a></small>
                </div>
            <?php endif; ?>

            <?php if (empty($note) === false) : ?>
                <div class="video-id youtube-id"><?= schntiVideoText('schnti.video.id', $replace); ?> <?= $note; ?></div>
            <?php endif; ?>

        </div>
    </div>
</div>
