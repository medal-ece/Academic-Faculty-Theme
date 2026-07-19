(function () {
    'use strict';

    var modal = document.querySelector('[data-gallery-modal]');
    var image = document.querySelector('[data-gallery-modal-image]');
    var title = document.querySelector('[data-gallery-modal-title]');
    var count = document.querySelector('[data-gallery-modal-count]');
    var prev = document.querySelector('[data-gallery-prev]');
    var next = document.querySelector('[data-gallery-next]');
    var closeButtons = Array.prototype.slice.call(document.querySelectorAll('[data-gallery-close]'));
    var loadMoreButton = document.querySelector('[data-gallery-load-more]');
    var galleryTimeline = document.querySelector('[data-gallery-batch-size]');
    var gallerySearch = document.querySelector('[data-gallery-search]');
    var galleryYear = document.querySelector('[data-gallery-year]');
    var galleryEmpty = document.querySelector('[data-gallery-empty]');
    var projectSearch = document.querySelector('[data-project-search]');
    var projectStatus = document.querySelector('[data-project-status]');
    var projectEmpty = document.querySelector('[data-project-empty]');
    var activeImages = [];
    var activeIndex = 0;
    var lastTrigger = null;

    function normalizeText(value) {
        return (value || '').toString().toLowerCase().trim();
    }

    function getFocusableElements() {
        return Array.prototype.slice.call(modal.querySelectorAll('button:not([disabled]), [href], input, select, textarea, [tabindex]:not([tabindex="-1"])')).filter(function (element) {
            return !element.hidden && element.offsetParent !== null;
        });
    }

    function render() {
        if (!activeImages.length) return;
        activeIndex = (activeIndex + activeImages.length) % activeImages.length;
        var current = activeImages[activeIndex];
        var src = typeof current === 'string' ? current : current.src;
        var alt = typeof current === 'string' ? '' : current.alt;
        image.src = src;
        image.alt = alt || (title.textContent ? title.textContent + ' photo ' + (activeIndex + 1) : 'Gallery photo ' + (activeIndex + 1));
        count.textContent = (activeIndex + 1) + ' / ' + activeImages.length;
        prev.disabled = activeImages.length < 2;
        next.disabled = activeImages.length < 2;
    }

    function openGallery(button) {
        var images;
        try {
            images = JSON.parse(button.getAttribute('data-gallery-images') || '[]');
        } catch (error) {
            images = [];
        }
        activeImages = images.filter(function (item) {
            return typeof item === 'string' ? item : item && item.src;
        });
        if (!activeImages.length) return;
        activeIndex = 0;
        lastTrigger = button;
        title.textContent = button.getAttribute('data-gallery-title') || 'Gallery';
        modal.hidden = false;
        document.documentElement.classList.add('faculty-gallery-modal-open');
        render();
        (next.disabled ? modal.querySelector('[data-gallery-close]') : next).focus();
    }

    function closeGallery() {
        modal.hidden = true;
        document.documentElement.classList.remove('faculty-gallery-modal-open');
        image.removeAttribute('src');
        image.removeAttribute('srcset');
        if (lastTrigger) lastTrigger.focus();
    }

    if (modal && image && title && count && prev && next) {
        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-gallery-images]');
            if (trigger) {
                event.preventDefault();
                openGallery(trigger);
            }
        });

        prev.addEventListener('click', function () {
            activeIndex -= 1;
            render();
        });

        next.addEventListener('click', function () {
            activeIndex += 1;
            render();
        });

        closeButtons.forEach(function (button) {
            button.addEventListener('click', closeGallery);
        });

        document.addEventListener('keydown', function (event) {
            if (modal.hidden) return;
            if (event.key === 'Escape') {
                closeGallery();
                return;
            }
            if (event.key === 'ArrowLeft') {
                activeIndex -= 1;
                render();
            }
            if (event.key === 'ArrowRight') {
                activeIndex += 1;
                render();
            }
            if (event.key === 'Tab') {
                var focusable = getFocusableElements();
                var first = focusable[0];
                var last = focusable[focusable.length - 1];

                if (!first || !last) {
                    event.preventDefault();
                    return;
                }

                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });
    }

    function galleryEventMatches(event) {
        var query = normalizeText(gallerySearch ? gallerySearch.value : '');
        var year = galleryYear ? galleryYear.value : '';
        var haystack = normalizeText(event.getAttribute('data-gallery-search'));
        var eventYear = event.getAttribute('data-gallery-year') || '';

        return (!query || haystack.indexOf(query) !== -1) && (!year || eventYear === year);
    }

    function applyGalleryFilters() {
        if (!galleryTimeline) return;
        var events = Array.prototype.slice.call(galleryTimeline.querySelectorAll('[data-gallery-event]'));
        var anyMatches = false;
        var anyVisible = false;
        var hasMoreMatching = false;

        events.forEach(function (event) {
            var matches = galleryEventMatches(event);
            var loaded = event.getAttribute('data-gallery-loaded') === '1';
            anyMatches = anyMatches || matches;
            hasMoreMatching = hasMoreMatching || (matches && !loaded);
            event.hidden = !(matches && loaded);
            anyVisible = anyVisible || (matches && loaded);
        });

        if (galleryEmpty) {
            galleryEmpty.hidden = anyMatches;
        }
        if (loadMoreButton) {
            loadMoreButton.hidden = !hasMoreMatching;
        }
        if (!anyVisible && anyMatches && hasMoreMatching && loadMoreButton) {
            loadMoreButton.click();
        }
    }

    if (loadMoreButton && galleryTimeline) {
        loadMoreButton.addEventListener('click', function () {
            var batchSize = parseInt(galleryTimeline.getAttribute('data-gallery-batch-size') || '6', 10);
            var unloadedEvents = Array.prototype.slice.call(galleryTimeline.querySelectorAll('[data-gallery-event]')).filter(function (event) {
                return event.getAttribute('data-gallery-loaded') !== '1' && galleryEventMatches(event);
            });

            unloadedEvents.slice(0, Math.max(1, batchSize)).forEach(function (event) {
                event.setAttribute('data-gallery-loaded', '1');
            });

            applyGalleryFilters();
        });
    }

    if (gallerySearch) {
        gallerySearch.addEventListener('input', applyGalleryFilters);
    }
    if (galleryYear) {
        galleryYear.addEventListener('change', applyGalleryFilters);
    }
    applyGalleryFilters();

    function applyProjectFilters() {
        var list = document.querySelector('[data-project-list]');
        if (!list) return;
        var query = normalizeText(projectSearch ? projectSearch.value : '');
        var status = projectStatus ? projectStatus.value : '';
        var items = Array.prototype.slice.call(list.querySelectorAll('[data-project-item]'));
        var visibleCount = 0;

        items.forEach(function (item) {
            var matchesQuery = !query || normalizeText(item.getAttribute('data-project-search')).indexOf(query) !== -1;
            var matchesStatus = !status || item.getAttribute('data-project-status') === status;
            var visible = matchesQuery && matchesStatus;
            item.hidden = !visible;
            if (visible) visibleCount += 1;
        });

        if (projectEmpty) {
            projectEmpty.hidden = visibleCount > 0;
        }
    }

    if (projectSearch) {
        projectSearch.addEventListener('input', applyProjectFilters);
    }
    if (projectStatus) {
        projectStatus.addEventListener('change', applyProjectFilters);
    }
    applyProjectFilters();
}());
