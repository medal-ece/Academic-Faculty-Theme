(function ($) {
    'use strict';

    function appendTemplate(templateSelector, listSelector) {
        var template = $(templateSelector).html();
        var index = 'new_' + Date.now();

        if (!template || !$(listSelector).length) {
            return;
        }

        $(listSelector).append(template.replace(/__INDEX__/g, index));
        refreshAdminEnhancements();
    }

    function escapeRegExp(text) {
        return String(text).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function reindexList(list) {
        var option = $(list).data('repeat-list');
        if (!option) {
            return;
        }

        $(list).children('[data-repeat-item]').each(function (index) {
            $(this).find('[name]').each(function () {
                var name = $(this).attr('name');
                var pattern = new RegExp('faculty_theme_options\\[' + escapeRegExp(option) + '\\]\\[[^\\]]+\\]');
                $(this).attr('name', name.replace(pattern, 'faculty_theme_options[' + option + '][' + index + ']'));
            });
        });
    }

    function updateMediaPreview(input) {
        var field = $(input);
        var url = $.trim(field.val() || '');
        var anchor = field.closest('p');
        var preview;

        if (!anchor.length) {
            anchor = field;
        }

        preview = anchor.next('.faculty-media-preview');

        if (!preview.length) {
            preview = $('<div class="faculty-media-preview" aria-hidden="true"><img alt=""></div>');
            anchor.after(preview);
        }

        if (url && /\.(png|jpe?g|gif|webp|svg)(\?.*)?$/i.test(url)) {
            preview.find('img').attr('src', url);
            preview.show();
        } else {
            preview.hide();
        }
    }

    function updateGalleryPreview(textarea) {
        var field = $(textarea);
        var urls = (field.val() || '').split(/\r\n|\r|\n/).map(function (line) {
            return $.trim(line);
        }).filter(Boolean).slice(0, 6);
        var preview = field.closest('p').next('.faculty-gallery-preview');

        if (!preview.length) {
            preview = $('<div class="faculty-gallery-preview" aria-hidden="true"></div>');
            field.closest('p').after(preview);
        }

        preview.empty();
        urls.forEach(function (url) {
            if (/\.(png|jpe?g|gif|webp|svg)(\?.*)?$/i.test(url)) {
                $('<img alt="">').attr('src', url).appendTo(preview);
            }
        });
        preview.toggle(preview.children().length > 0);
    }

    function updateColorValue(input) {
        var field = $(input);
        field.closest('.faculty-color-card').find('[data-color-value]').text(field.val());
    }

    function updateSlidePreview(item) {
        var panel = $(item).closest('.faculty-slide-settings');
        var image = $.trim(panel.find('.faculty-media-url').first().val() || '');
        var title = $.trim(panel.find('[name$="[title]"]').val() || 'Slide heading');
        var text = $.trim(panel.find('[name$="[text]"]').val() || '');
        var preview = panel.find('.faculty-slide-admin-preview');

        if (!preview.length) {
            preview = $('<div class="faculty-preview-panel faculty-slide-admin-preview" aria-label="Slide preview"><img alt=""><div class="faculty-slide-admin-preview-content"><span class="faculty-slide-admin-preview-title"></span><span class="faculty-slide-admin-preview-text"></span></div></div>');
            panel.find('.faculty-collapsible-body').append(preview);
        }

        preview.find('img').attr('src', image);
        preview.find('img').toggle(!!image);
        preview.find('.faculty-slide-admin-preview-title').text(title);
        preview.find('.faculty-slide-admin-preview-text').text(text);
    }

    function updateGalleryDeckPreview(textarea) {
        var field = $(textarea);
        var urls = (field.val() || '').split(/\r\n|\r|\n/).map(function (line) {
            return $.trim(line);
        }).filter(Boolean).slice(0, 4);
        var panel = field.closest('.faculty-gallery-admin-set');
        var preview = panel.find('.faculty-gallery-deck-admin-preview');

        if (!preview.length) {
            preview = $('<div class="faculty-preview-panel faculty-gallery-deck-admin-preview" aria-label="Gallery deck preview"></div>');
            panel.find('.faculty-collapsible-body').append(preview);
        }

        preview.empty();
        urls.forEach(function (url) {
            if (/\.(png|jpe?g|gif|webp|svg)(\?.*)?$/i.test(url)) {
                $('<img alt="">').attr('src', url).prependTo(preview);
            }
        });
        preview.toggle(preview.children().length > 0);
    }

    function refreshAdminEnhancements() {
        $('[data-repeat-list]').each(function () {
            var list = $(this);
            if (!list.data('ui-sortable')) {
                list.sortable({
                    items: '> [data-repeat-item]',
                    handle: '.faculty-sort-handle',
                    placeholder: 'faculty-sort-placeholder',
                    forcePlaceholderSize: true,
                    update: function () {
                        reindexList(this);
                    }
                });
            }
            reindexList(list);
        });

        $('.faculty-media-url').each(function () {
            updateMediaPreview(this);
        });
        $('.faculty-gallery-image-list').each(function () {
            updateGalleryPreview(this);
            updateGalleryDeckPreview(this);
        });
        $('.faculty-slide-settings').each(function () {
            updateSlidePreview(this);
        });
        $('.faculty-color-card input[type="color"]').each(function () {
            updateColorValue(this);
        });
    }

    function updateGallerySetSummary(item) {
        var panel = $(item).closest('.faculty-gallery-admin-set');
        var title = $.trim(panel.find('[data-gallery-title-input]').val() || 'New gallery event');
        var date = $.trim(panel.find('[data-gallery-date-input]').val() || '');
        var imageText = panel.find('[data-gallery-images-input]').val() || '';
        var imageCount = imageText.split(/\r\n|\r|\n/).filter(function (line) {
            return $.trim(line).length > 0;
        }).length;
        var meta = [];

        if (date) {
            meta.push(date);
        }
        meta.push(imageCount + (imageCount === 1 ? ' photo' : ' photos'));

        panel.find('[data-gallery-summary-title]').text(title);
        panel.find('[data-gallery-summary-meta]').text(meta.join(' · '));
    }

    function activateTab(target) {
        var panelSelector;
        var legacyTargets = {
            '#faculty-tab-general': '#faculty-group-general',
            '#faculty-tab-front-page': '#faculty-group-homepage',
            '#faculty-tab-intro': '#faculty-group-homepage',
            '#faculty-tab-slider': '#faculty-group-homepage',
            '#faculty-tab-news': '#faculty-group-homepage',
            '#faculty-tab-visuals': '#faculty-group-homepage',
            '#faculty-tab-contact': '#faculty-group-pages',
            '#faculty-tab-research': '#faculty-group-research',
            '#faculty-tab-gallery': '#faculty-group-gallery',
            '#faculty-tab-colors': '#faculty-group-design',
            '#faculty-tab-footer': '#faculty-group-footer',
            '#faculty-tab-accessibility': '#faculty-group-maintenance',
            '#faculty-tab-import-export': '#faculty-group-maintenance',
            '#faculty-tab-help': '#faculty-group-help'
        };

        if (legacyTargets[target]) {
            target = legacyTargets[target];
        }

        if (!target || (!$('[data-faculty-panel-group="' + target + '"]').length && !$(target).length)) {
            target = '#faculty-group-general';
        }

        panelSelector = $('[data-faculty-panel-group="' + target + '"]').length ? '[data-faculty-panel-group="' + target + '"]' : target;

        $('[data-faculty-tab]').removeClass('nav-tab-active').attr('aria-selected', 'false');
        $('[data-faculty-tab][href="' + target + '"]').addClass('nav-tab-active').attr('aria-selected', 'true');
        $('.faculty-theme-tab-panel').removeClass('is-active');
        $(panelSelector).addClass('is-active');
    }

    $('[data-faculty-tab]').on('click', function (event) {
        event.preventDefault();
        var target = $(this).attr('href');
        activateTab(target);
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', target);
        }
    });

    activateTab(window.location.hash);

    $(document).on('click', '.faculty-select-media', function (event) {
        event.preventDefault();
        var input = $($(this).data('target'));
        var frame = wp.media({ title: 'Choose slide image', button: { text: 'Use this image' }, multiple: false });
        frame.on('select', function () {
            var image = frame.state().get('selection').first().toJSON();
            if (input.is('textarea.faculty-gallery-image-list')) {
                input.val($.trim(input.val() + '\n' + image.url)).trigger('change');
            } else {
                input.val(image.url).trigger('change');
            }
        });
        frame.open();
    });

    $(document).on('click', '#faculty-add-slide', function (event) {
        event.preventDefault();
        appendTemplate('#faculty-slide-template', '#faculty-slides-list');
    });

    $(document).on('click', '#faculty-add-parallax', function (event) {
        event.preventDefault();
        appendTemplate('#faculty-parallax-template', '#faculty-parallax-list');
    });

    $(document).on('click', '#faculty-add-logo', function (event) {
        event.preventDefault();
        appendTemplate('#faculty-logo-template', '#faculty-logo-list');
    });

    $(document).on('click', '#faculty-add-research-area', function (event) {
        event.preventDefault();
        appendTemplate('#faculty-research-area-template', '#faculty-research-area-list');
    });

    $(document).on('click', '#faculty-add-research-project', function (event) {
        event.preventDefault();
        appendTemplate('#faculty-research-project-template', '#faculty-research-project-list');
    });

    $(document).on('click', '#faculty-add-research-sponsor', function (event) {
        event.preventDefault();
        appendTemplate('#faculty-research-sponsor-template', '#faculty-research-sponsor-list');
    });

    $(document).on('click', '#faculty-add-gallery-item', function (event) {
        event.preventDefault();
        appendTemplate('#faculty-gallery-template', '#faculty-gallery-list');
    });

    $(document).on('click', '#faculty-add-gallery-set', function (event) {
        event.preventDefault();
        appendTemplate('#faculty-gallery-set-template', '#faculty-gallery-set-list');
    });

    $(document).on('input change', '.faculty-gallery-admin-set [data-gallery-title-input], .faculty-gallery-admin-set [data-gallery-date-input], .faculty-gallery-admin-set [data-gallery-images-input]', function () {
        updateGallerySetSummary(this);
    });

    $(document).on('input change', '.faculty-media-url', function () {
        updateMediaPreview(this);
        updateSlidePreview(this);
    });

    $(document).on('input change', '.faculty-gallery-image-list', function () {
        updateGalleryPreview(this);
        updateGalleryDeckPreview(this);
    });

    $(document).on('input change', '.faculty-slide-settings [name$="[title]"], .faculty-slide-settings [name$="[text]"]', function () {
        updateSlidePreview(this);
    });

    $(document).on('input change', '.faculty-color-card input[type="color"]', function () {
        updateColorValue(this);
    });

    $(document).on('click', '.faculty-reset-color', function (event) {
        event.preventDefault();
        var field = $(this).closest('.faculty-color-card').find('input[type="color"]');
        field.val(field.data('color-default')).trigger('change');
    });

    $(document).on('click', '[data-collapse-list]', function (event) {
        event.preventDefault();
        var target = $($(this).data('collapse-list'));
        var shouldOpen = $(this).data('collapse-action') === 'expand';
        target.find('details[data-repeat-item]').prop('open', shouldOpen);
    });

    $(document).on('click', '.faculty-remove-dynamic-item', function () {
        var list = $(this).closest('[data-repeat-list]');
        $(this).closest('[data-repeat-item], .faculty-dynamic-item').remove();
        reindexList(list);
    });

    $(document).on('click', '.faculty-remove-slide', function () {
        var list = $(this).closest('[data-repeat-list]');
        $(this).closest('[data-repeat-item], .faculty-slide-settings').remove();
        reindexList(list);
    });

    refreshAdminEnhancements();
}(jQuery));
