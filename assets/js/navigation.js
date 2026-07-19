(function () {
    'use strict';
    var button = document.querySelector('.menu-toggle');
    var navigation = document.querySelector('.main-navigation');
    if (!button || !navigation) return;
    button.addEventListener('click', function () {
        var open = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', String(!open));
        navigation.classList.toggle('is-open', !open);
    });
}());

(function () {
    'use strict';
    var slider = document.querySelector('[data-slider]');
    if (!slider) return;
    var slides = Array.prototype.slice.call(slider.querySelectorAll('[data-slide]'));
    if (slides.length < 2) return;
    var current = 0;
    var paused = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var toggle = slider.querySelector('[data-slide-toggle]');
    var timer;
    slides.forEach(function (slide) {
        slide.hidden = false;
    });
    function durationFor(slide) {
        var duration = parseInt(slide.getAttribute('data-duration'), 10);
        if (isNaN(duration)) return 7000;
        return Math.max(2000, Math.min(30000, duration));
    }
    function show(index) {
        current = (index + slides.length) % slides.length;
        slider.setAttribute('data-transition', slides[current].getAttribute('data-transition') || 'fade');
        slides.forEach(function (slide, slideIndex) {
            var active = slideIndex === current;
            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', active ? 'false' : 'true');
        });
    }
    function start() {
        window.clearTimeout(timer);
        if (!paused) timer = window.setTimeout(function () { show(current + 1); start(); }, durationFor(slides[current]));
    }
    slider.querySelector('[data-slide-prev]').addEventListener('click', function () { show(current - 1); start(); });
    slider.querySelector('[data-slide-next]').addEventListener('click', function () { show(current + 1); start(); });
    toggle.addEventListener('click', function () {
        paused = !paused;
        toggle.setAttribute('aria-pressed', paused ? 'true' : 'false');
        toggle.textContent = paused ? 'Play' : 'Pause';
        start();
    });
    if (paused) { toggle.setAttribute('aria-pressed', 'true'); toggle.textContent = 'Play'; }
    start();
}());
