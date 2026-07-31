{{-- Scroll-spy, smooth scrolling and the mobile TOC drawer for the shared
     SIBSTR detail view. No form JS is needed: the page renders values, not
     inputs, so there is nothing to disable. --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    var links    = Array.prototype.slice.call(document.querySelectorAll('.sd-toc-link'));
    var sections = Array.prototype.slice.call(document.querySelectorAll('.sd-card'));
    var toc      = document.getElementById('sd-toc');
    var overlay  = document.getElementById('sd-toc-overlay');
    var fab      = document.getElementById('sd-toc-fab');
    var topBtn   = document.getElementById('sd-top');

    function closeToc() {
        if (toc) toc.classList.remove('open');
        if (overlay) overlay.classList.remove('active');
    }

    function syncActive() {
        var pos = window.scrollY + 120;
        var current = sections.length ? sections[0].id : '';
        sections.forEach(function (s) {
            if (s.offsetTop <= pos) current = s.id;
        });
        links.forEach(function (l) {
            l.classList.toggle('active', l.getAttribute('data-section') === current);
        });
        if (topBtn) topBtn.classList.toggle('show', window.scrollY > 600);
    }

    var ticking = false;
    window.addEventListener('scroll', function () {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(function () {
            syncActive();
            ticking = false;
        });
    });
    syncActive();

    links.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var target = document.getElementById(this.getAttribute('data-section'));
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            closeToc();
        });
    });

    if (fab) {
        fab.addEventListener('click', function () {
            if (toc) toc.classList.toggle('open');
            if (overlay) overlay.classList.toggle('active');
        });
    }
    if (overlay) overlay.addEventListener('click', closeToc);

    if (topBtn) {
        topBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});
</script>
