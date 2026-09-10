<?php

/**
 * YouTube embed. Delegates to the shared video snippet.
 *
 * This file only exists for backwards compatibility: `snippet('youtube', …)`
 * is the documented entry point of 1.x, and projects may call or override it.
 * The `??` defaults therefore accept the old 1.x parameter set as well.
 *
 * `youtube-container` is passed on as an additional class so stylesheets
 * written against earlier versions keep matching.
 */

snippet('video', [
    'service'     => $service ?? 'YouTube',
    'src'         => $src,
    'link'        => $link ?? ('https://www.youtube.com/watch?v=' . $id),
    'id'          => $id,
    'image'       => $image ?? null,
    'class'       => $class ?? '',
    'legacyClass' => 'youtube-container',
]);
