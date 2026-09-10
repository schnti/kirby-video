// Filename kept as youtube.js on purpose: renaming it would break the
// <script> tag of every existing installation.
//
// Selectors accept both the new generic class names and the old youtube-*
// ones, so a project that overrides the snippet with the previous markup
// keeps working.
//
// The consent click releases the embed of the clicked container: the poster is
// hidden and the iframe URL moves from data-src to src. Embeds that are not
// iframes — a map that loads a provider script, for instance — need their own
// handler; the notice snippet can still be reused for them.

document.addEventListener('click', function (event) {

    var button = event.target.closest('.video-hint-button, .youtube-hint-button');

    if (!button) {
        return;
    }

    var container = button.closest('.video-container, .youtube-container');

    if (!container) {
        return;
    }

    container.classList.remove('disabled');

    // The poster is optional – it is missing when the thumbnail could not be
    // fetched, so do not assume it is there.
    var poster = container.getElementsByTagName('img')[0];

    if (poster) {
        poster.style.display = 'none';
    }

    var embed = container.getElementsByClassName('embed-container')[0];

    if (!embed) {
        return;
    }

    embed.style.display = 'block';

    var frame = embed.querySelector('iframe[data-src]');

    if (frame) {
        frame.src = frame.dataset.src;
        frame.removeAttribute('data-src');
    }
});
