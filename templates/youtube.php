<div class="youtube-container disabled <?= $class; ?>">
	<?php if (isset($id)) : ?>
		<?= $image; ?>
        <iframe style="display: none;" width="<?= $width; ?>" height="<?= $height; ?>" data-src="https://www.youtube-nocookie.com/embed/<?= $id; ?>" frameborder="0"
                allow="autoplay; encrypted-media"
                allowfullscreen></iframe>
        <div class="youtube-hint">
            <div class="youtube-hint-text">
                <div>
                    <p>Zum Aktivieren des Videos musst du auf den Link unten klicken. Wir möchten dich darauf hinweisen, dass nach der Aktivierung Daten an den jeweiligen Anbieter
                        übermittelt werden.</p>
                    <p>
                        <button class="btn btn-success youtube-hint-button">Video aktivieren</button>
                    </p>
                </div>
            </div>
        </div>
	<?php endif; ?>
</div>
