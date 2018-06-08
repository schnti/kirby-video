<?php

$kirby->set('tag', 'youtube', array(
	'attr' => array(
		'class',
		'width',
		'height'
	),
	'html' => function ($tag) {

		$id = $tag->attr('youtube');
		$class = $tag->attr('class');
		$width = $tag->attr('width', 350);
		$height = $tag->attr('height', 220);

		$imageUrl = 'https://i.ytimg.com/vi/' . $id . '/maxresdefault.jpg';

		$filename = f::safeName('youtube_' . $id . '.jpg');
		$path = $tag->page()->root() . '/' . $filename;

		if (!file_exists($path)) {
			file_put_contents($path, file_get_contents($imageUrl));
		}

		$image = thumb(new Media($path), [
			'width'  => $width,
			'height' => $height,
			'crop'   => true
		]);

		$data = [
			'width'  => $width,
			'height' => $height,
			'class'  => $class,
			'id'     => $id,
			'image'  => $image,
		];

		return tpl::load(__DIR__ . DS . 'templates/youtube.php', $data);
	}
));