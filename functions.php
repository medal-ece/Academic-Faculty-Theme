<?php
/**
 * Faculty Theme setup and integrations.
 *
 * @package Faculty_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('FACULTY_THEME_VERSION')) {
    define('FACULTY_THEME_VERSION', '1.4.6');
}

function faculty_theme_setup() {
    load_theme_textdomain('faculty-theme', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_editor_style('style.css');
    add_theme_support('custom-logo', array('height' => 120, 'width' => 360, 'flex-height' => true, 'flex-width' => true));
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
    register_nav_menus(array(
        'primary' => __('Primary Navigation', 'faculty-theme'),
        'footer'  => __('Footer Navigation', 'faculty-theme'),
    ));
}
add_action('after_setup_theme', 'faculty_theme_setup');

function faculty_theme_hide_courses_menu_item($items, $args) {
    return array_values(array_filter((array) $items, function ($item) {
        $title = isset($item->title) ? trim(wp_strip_all_tags($item->title)) : '';
        $url = isset($item->url) ? trim((string) $item->url) : '';

        if (strcasecmp($title, 'Courses') === 0) {
            return false;
        }

        return !preg_match('~/courses/?($|[?#])~i', $url);
    }));
}
add_filter('wp_nav_menu_objects', 'faculty_theme_hide_courses_menu_item', 10, 2);

function faculty_theme_assets() {
    $version = FACULTY_THEME_VERSION;
    wp_enqueue_style('faculty-theme-style', get_stylesheet_uri(), array(), $version);
    wp_enqueue_script('faculty-theme-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array(), $version, true);
    wp_enqueue_script('faculty-theme-gallery', get_template_directory_uri() . '/assets/js/gallery.js', array(), $version, true);
}
add_action('wp_enqueue_scripts', 'faculty_theme_assets');

function faculty_theme_get_update_info() {
    if (!defined('FACULTY_THEME_UPDATE_JSON') || !FACULTY_THEME_UPDATE_JSON) {
        return false;
    }

    $cache_key = 'faculty_theme_update_info';
    $cached = get_site_transient($cache_key);
    if (is_array($cached)) {
        return $cached;
    }

    $response = wp_remote_get(FACULTY_THEME_UPDATE_JSON, array(
        'timeout' => 5,
        'headers' => array('Accept' => 'application/json'),
    ));

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        set_site_transient($cache_key, array(), HOUR_IN_SECONDS);
        return false;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($data) || empty($data['version']) || empty($data['download_url'])) {
        set_site_transient($cache_key, array(), HOUR_IN_SECONDS);
        return false;
    }

    set_site_transient($cache_key, $data, 6 * HOUR_IN_SECONDS);

    return $data;
}

function faculty_theme_check_for_updates($transient) {
    if (empty($transient->checked['faculty-theme'])) {
        return $transient;
    }

    $info = faculty_theme_get_update_info();
    if (!$info || empty($info['version']) || version_compare($info['version'], FACULTY_THEME_VERSION, '<=')) {
        return $transient;
    }

    $transient->response['faculty-theme'] = array(
        'theme' => 'faculty-theme',
        'new_version' => sanitize_text_field($info['version']),
        'url' => !empty($info['details_url']) ? esc_url_raw($info['details_url']) : '',
        'package' => esc_url_raw($info['download_url']),
        'requires' => !empty($info['requires']) ? sanitize_text_field($info['requires']) : '',
        'requires_php' => !empty($info['requires_php']) ? sanitize_text_field($info['requires_php']) : '',
    );

    return $transient;
}
add_filter('pre_set_site_transient_update_themes', 'faculty_theme_check_for_updates');

function faculty_theme_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'faculty-theme'),
        'id'            => 'sidebar-1',
        'description'   => __('Widgets shown beside posts and archives.', 'faculty-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
    for ($column = 1; $column <= 3; $column++) {
        register_sidebar(array(
            'name'          => sprintf(__('Footer Column %d', 'faculty-theme'), $column),
            'id'            => 'footer-' . $column,
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
    }

    for ($region = 1; $region <= 3; $region++) {
        register_sidebar(array(
            'name'          => sprintf(__('Homepage Gadget %d', 'faculty-theme'), $region),
            'id'            => 'home-gadget-' . $region,
            'description'   => __('A flexible homepage region for text, links, media, or plugin widgets.', 'faculty-theme'),
            'before_widget' => '<section id="%1$s" class="widget home-gadget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
    }

    register_sidebar(array(
        'name'          => __('Page Bottom', 'faculty-theme'),
        'id'            => 'page-bottom',
        'description'   => __('Optional full-width widgets shown below regular page content.', 'faculty-theme'),
        'before_widget' => '<section id="%1$s" class="widget page-bottom-widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'faculty_theme_widgets_init');

function faculty_theme_default_options() {
    return array(
        'eyebrow'          => __('The University of Utah', 'faculty-theme'),
        'brand_logo'       => '',
        'accent'           => '#BE0000',
        'accent_dark'      => '#890000',
        'ink'              => '#000000',
        'body_text'        => '#242424',
        'muted'            => '#707271',
        'line'             => '#E2E6E6',
        'soft'             => '#F7F9FB',
        'surface'          => '#FFFFFF',
        'nav_bg'           => '#101010',
        'nav_bg_dark'      => '#050505',
        'footer_bg'        => '#000000',
        'gallery_timeline' => '#BE0000',
        'gallery_card_accent' => '#3F4A54',
        'vacancy_accent'   => '#007A86',
        'page_hero_image'  => '',
        'page_hero_title_size' => '4rem',
        'footer_lab_info'  => '',
        'footer_text'      => '',
        'show_intro'       => '1',
        'intro_eyebrow'    => __('Research Group', 'faculty-theme'),
        'intro_title'      => __('MEDAL', 'faculty-theme'),
        'intro_subtitle'   => __('Microelectronics, Embedded Devices, and Applied Learning Research Group', 'faculty-theme'),
        'intro_text'       => '',
        'intro_button_text' => __('Learn more', 'faculty-theme'),
        'intro_button_url' => '',
        'intro_image'      => '',
        'show_slideshow'   => '0',
        'slide_font'       => 'default',
        'slide_title_size' => '4.2rem',
        'slide_default_duration' => 7000,
        'show_gadgets'      => '0',
        'show_news'        => '1',
        'news_title'       => __('Latest News', 'faculty-theme'),
        'news_category'    => 0,
        'news_count'       => 3,
        'news_archive_url' => '',
        'slides'           => array(),
        'parallax_bands'   => array(),
        'show_logo_strip'  => '1',
        'logo_strip_title' => __('Affiliations and Partners', 'faculty-theme'),
        'logo_items'       => array(),
        'contact_intro'    => '',
        'contact_address'  => '',
        'contact_email'    => '',
        'contact_phone'    => '',
        'contact_map_embed' => '',
        'research_intro'   => '',
        'research_areas'   => array(),
        'research_projects' => array(),
        'research_sponsors' => array(),
        'gallery_intro'    => '',
        'gallery_batch_size' => 6,
        'gallery_items'    => array(),
        'gallery_sets'     => array(),
        'last_saved'       => '',
    );
}

function faculty_theme_color_fields() {
    return array(
        'accent' => array('label' => __('Primary accent', 'faculty-theme'), 'default' => '#BE0000', 'description' => __('Main Utah red accent used for buttons, active states, and section accents.', 'faculty-theme')),
        'accent_dark' => array('label' => __('Dark accent', 'faculty-theme'), 'default' => '#890000', 'description' => __('Hover and dark-gradient companion to the primary accent.', 'faculty-theme')),
        'ink' => array('label' => __('Heading / ink', 'faculty-theme'), 'default' => '#000000', 'description' => __('Main heading and strong text color.', 'faculty-theme')),
        'body_text' => array('label' => __('Body text', 'faculty-theme'), 'default' => '#242424', 'description' => __('Default paragraph/body copy color.', 'faculty-theme')),
        'muted' => array('label' => __('Muted text', 'faculty-theme'), 'default' => '#707271', 'description' => __('Subtitles, dates, metadata, and secondary text.', 'faculty-theme')),
        'line' => array('label' => __('Border / divider', 'faculty-theme'), 'default' => '#E2E6E6', 'description' => __('Subtle borders, rules, and dividers.', 'faculty-theme')),
        'soft' => array('label' => __('Soft background', 'faculty-theme'), 'default' => '#F7F9FB', 'description' => __('Light background panels and labels.', 'faculty-theme')),
        'surface' => array('label' => __('Card surface', 'faculty-theme'), 'default' => '#FFFFFF', 'description' => __('Cards, forms, and content panels.', 'faculty-theme')),
        'nav_bg' => array('label' => __('Navigation top', 'faculty-theme'), 'default' => '#101010', 'description' => __('Top color of the navigation gradient.', 'faculty-theme')),
        'nav_bg_dark' => array('label' => __('Navigation bottom', 'faculty-theme'), 'default' => '#050505', 'description' => __('Bottom color of the navigation gradient.', 'faculty-theme')),
        'footer_bg' => array('label' => __('Footer background', 'faculty-theme'), 'default' => '#000000', 'description' => __('Footer background color.', 'faculty-theme')),
        'gallery_timeline' => array('label' => __('Gallery timeline', 'faculty-theme'), 'default' => '#BE0000', 'description' => __('Vertical timeline line and dots on the gallery page.', 'faculty-theme')),
        'gallery_card_accent' => array('label' => __('Gallery card accent', 'faculty-theme'), 'default' => '#3F4A54', 'description' => __('Left accent line on gallery event text cards.', 'faculty-theme')),
        'vacancy_accent' => array('label' => __('Vacancy accent', 'faculty-theme'), 'default' => '#007A86', 'description' => __('Open-positions callout accent color.', 'faculty-theme')),
    );
}

function faculty_theme_sanitize_css_size($value, $default = '') {
    $value = trim(sanitize_text_field((string) $value));

    if ($value === '') {
        return $default;
    }

    if (preg_match('/^\d+(\.\d+)?$/', $value)) {
        $value .= 'rem';
    }

    if (!preg_match('/^\d+(\.\d+)?(px|rem|em|vw|vh|%)$/', $value)) {
        return $default;
    }

    return $value;
}

function faculty_theme_get_options() {
    return wp_parse_args((array) get_option('faculty_theme_options', array()), faculty_theme_default_options());
}

function faculty_theme_get_option($key, $default = '') {
    $options = faculty_theme_get_options();
    return isset($options[$key]) ? $options[$key] : $default;
}

function faculty_theme_normalize_media_url($url) {
    $url = trim((string) $url);

    if ($url === '') {
        return '';
    }

    if (ctype_digit($url)) {
        $attachment_url = wp_get_attachment_image_url((int) $url, 'full');
        if ($attachment_url) {
            return $attachment_url;
        }
    }

    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $attachment_id = attachment_url_to_postid($url);
        if ($attachment_id) {
            $attachment_url = wp_get_attachment_image_url($attachment_id, 'full');
            if ($attachment_url) {
                return $attachment_url;
            }
        }
    }

    $uploads_marker = '/wp-content/uploads/';
    $marker_position = strpos($url, $uploads_marker);

    if ($marker_position !== false) {
        $relative_upload_path = substr($url, $marker_position + strlen('/wp-content/'));
        return content_url($relative_upload_path);
    }

    $path = filter_var($url, FILTER_VALIDATE_URL) ? (string) wp_parse_url($url, PHP_URL_PATH) : $url;
    $basename = wp_basename($path);

    if ($basename !== '') {
        global $wpdb;
        $name_without_extension = pathinfo($basename, PATHINFO_FILENAME);
        $attachment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT pm.post_id FROM $wpdb->postmeta pm
             INNER JOIN $wpdb->posts p ON p.ID = pm.post_id
             WHERE pm.meta_key = '_wp_attached_file'
             AND (pm.meta_value LIKE %s OR pm.meta_value LIKE %s)
             AND p.post_type = 'attachment'
             AND p.post_mime_type LIKE 'image/%%'
             LIMIT 1",
            '%' . $wpdb->esc_like($basename),
            '%' . $wpdb->esc_like($name_without_extension) . '%'
        ));

        if ($attachment_id) {
            $attachment_url = wp_get_attachment_image_url((int) $attachment_id, 'full');
            if ($attachment_url) {
                return $attachment_url;
            }
        }
    }

    return $url;
}

function faculty_theme_get_gallery_image_data($image_url, $fallback_alt = '') {
    $image_url = esc_url_raw(faculty_theme_normalize_media_url($image_url));
    $data = array(
        'full'    => $image_url,
        'preview' => $image_url,
        'srcset'  => '',
        'sizes'   => '',
        'alt'     => $fallback_alt,
    );

    if (!$image_url) {
        return $data;
    }

    $attachment_id = attachment_url_to_postid($image_url);
    if ($attachment_id) {
        $full = wp_get_attachment_image_url($attachment_id, 'full');
        $preview = wp_get_attachment_image_url($attachment_id, 'medium_large');
        $srcset = wp_get_attachment_image_srcset($attachment_id, 'medium_large');
        $sizes = wp_get_attachment_image_sizes($attachment_id, 'medium_large');
        $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

        $data['full'] = $full ? $full : $data['full'];
        $data['preview'] = $preview ? $preview : $data['preview'];
        $data['srcset'] = $srcset ? $srcset : '';
        $data['sizes'] = $sizes ? $sizes : '(max-width: 720px) 90vw, 640px';
        $data['alt'] = $alt ? $alt : (get_the_title($attachment_id) ? get_the_title($attachment_id) : $fallback_alt);
    }

    return $data;
}

function faculty_theme_render_news_filter_form($action_url = '') {
    $posts_page_id = (int) get_option('page_for_posts');
    $action_url = $action_url ? $action_url : ($posts_page_id ? get_permalink($posts_page_id) : home_url('/'));
    $selected_category = isset($_GET['cat']) ? absint(wp_unslash($_GET['cat'])) : 0;
    $search_query = get_search_query();
    $categories = get_categories(array(
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ));
    ?>
    <form class="faculty-news-filter-form" method="get" action="<?php echo esc_url($action_url); ?>" role="search">
        <input type="hidden" name="post_type" value="post">
        <label>
            <span><?php esc_html_e('Search news', 'faculty-theme'); ?></span>
            <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="<?php esc_attr_e('Search news posts', 'faculty-theme'); ?>">
        </label>
        <label>
            <span><?php esc_html_e('Category', 'faculty-theme'); ?></span>
            <select name="cat">
                <option value="0"><?php esc_html_e('All categories', 'faculty-theme'); ?></option>
                <?php foreach ($categories as $category) : ?>
                    <option value="<?php echo esc_attr($category->term_id); ?>" <?php selected($selected_category, (int) $category->term_id); ?>><?php echo esc_html($category->name); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit"><?php esc_html_e('Search', 'faculty-theme'); ?></button>
        <?php if ($search_query || $selected_category) : ?>
            <a class="faculty-news-filter-reset" href="<?php echo esc_url($action_url); ?>"><?php esc_html_e('Reset', 'faculty-theme'); ?></a>
        <?php endif; ?>
    </form>
    <?php
}

function faculty_theme_sanitize_options($input) {
    $defaults = faculty_theme_default_options();
    $output = array();
    $output['eyebrow'] = sanitize_text_field(isset($input['eyebrow']) ? $input['eyebrow'] : $defaults['eyebrow']);
    $output['brand_logo'] = esc_url_raw(isset($input['brand_logo']) ? $input['brand_logo'] : '');
    foreach (faculty_theme_color_fields() as $color_key => $color_settings) {
        $fallback = isset($defaults[$color_key]) ? $defaults[$color_key] : $color_settings['default'];
        $output[$color_key] = sanitize_hex_color(isset($input[$color_key]) ? $input[$color_key] : $fallback);
        $output[$color_key] = $output[$color_key] ? $output[$color_key] : $fallback;
    }
    $output['page_hero_image'] = esc_url_raw(isset($input['page_hero_image']) ? $input['page_hero_image'] : '');
    $output['page_hero_title_size'] = faculty_theme_sanitize_css_size(isset($input['page_hero_title_size']) ? $input['page_hero_title_size'] : '', $defaults['page_hero_title_size']);
    $output['footer_lab_info'] = wp_kses_post(isset($input['footer_lab_info']) ? $input['footer_lab_info'] : '');
    $output['footer_text'] = wp_kses_post(isset($input['footer_text']) ? $input['footer_text'] : '');
    $output['show_intro'] = !empty($input['show_intro']) ? '1' : '0';
    $output['intro_eyebrow'] = sanitize_text_field(isset($input['intro_eyebrow']) ? $input['intro_eyebrow'] : $defaults['intro_eyebrow']);
    $output['intro_title'] = sanitize_text_field(isset($input['intro_title']) ? $input['intro_title'] : $defaults['intro_title']);
    $output['intro_subtitle'] = sanitize_text_field(isset($input['intro_subtitle']) ? $input['intro_subtitle'] : $defaults['intro_subtitle']);
    $output['intro_text'] = wp_kses_post(isset($input['intro_text']) ? $input['intro_text'] : '');
    $output['intro_button_text'] = sanitize_text_field(isset($input['intro_button_text']) ? $input['intro_button_text'] : $defaults['intro_button_text']);
    $output['intro_button_url'] = esc_url_raw(isset($input['intro_button_url']) ? $input['intro_button_url'] : '');
    $output['intro_image'] = esc_url_raw(isset($input['intro_image']) ? $input['intro_image'] : '');
    $output['show_slideshow'] = !empty($input['show_slideshow']) ? '1' : '0';
    $allowed_fonts = array('default', 'classic', 'serif', 'condensed', 'mono');
    $output['slide_font'] = isset($input['slide_font']) && in_array($input['slide_font'], $allowed_fonts, true) ? $input['slide_font'] : $defaults['slide_font'];
    $output['slide_title_size'] = faculty_theme_sanitize_css_size(isset($input['slide_title_size']) ? $input['slide_title_size'] : '', $defaults['slide_title_size']);
    $output['slide_default_duration'] = isset($input['slide_default_duration']) ? min(30000, max(2000, absint($input['slide_default_duration']))) : $defaults['slide_default_duration'];
    $output['show_gadgets'] = !empty($input['show_gadgets']) ? '1' : '0';
    $output['show_news'] = !empty($input['show_news']) ? '1' : '0';
    $output['news_title'] = sanitize_text_field(isset($input['news_title']) ? $input['news_title'] : $defaults['news_title']);
    $output['news_category'] = isset($input['news_category']) ? absint($input['news_category']) : 0;
    $output['news_count'] = isset($input['news_count']) ? min(12, max(1, absint($input['news_count']))) : 3;
    $output['news_archive_url'] = esc_url_raw(isset($input['news_archive_url']) ? $input['news_archive_url'] : '');
    $output['slides'] = array();

    if (!empty($input['slides']) && is_array($input['slides'])) {
        foreach ($input['slides'] as $slide) {
            $clean = array(
                'image'       => esc_url_raw(isset($slide['image']) ? $slide['image'] : ''),
                'title'       => sanitize_text_field(isset($slide['title']) ? $slide['title'] : ''),
                'text'        => sanitize_textarea_field(isset($slide['text']) ? $slide['text'] : ''),
                'button_text' => sanitize_text_field(isset($slide['button_text']) ? $slide['button_text'] : ''),
                'button_url'  => esc_url_raw(isset($slide['button_url']) ? $slide['button_url'] : ''),
                'duration'    => isset($slide['duration']) ? min(30000, max(2000, absint($slide['duration']))) : $output['slide_default_duration'],
                'transition'  => isset($slide['transition']) && in_array($slide['transition'], array('fade', 'slide', 'zoom'), true) ? $slide['transition'] : 'fade',
                'image_fit'   => isset($slide['image_fit']) && in_array($slide['image_fit'], array('contain', 'cover', 'stretch'), true) ? $slide['image_fit'] : 'contain',
                'title_size'  => faculty_theme_sanitize_css_size(isset($slide['title_size']) ? $slide['title_size'] : ''),
            );
            if ($clean['image'] || $clean['title'] || $clean['text']) {
                $output['slides'][] = $clean;
            }
        }
    }

    $output['parallax_bands'] = array();
    if (!empty($input['parallax_bands']) && is_array($input['parallax_bands'])) {
        foreach ($input['parallax_bands'] as $band) {
            $clean = array(
                'image' => esc_url_raw(isset($band['image']) ? $band['image'] : ''),
                'label' => sanitize_text_field(isset($band['label']) ? $band['label'] : ''),
            );
            if ($clean['image']) {
                $output['parallax_bands'][] = $clean;
            }
        }
    }

    $output['show_logo_strip'] = !empty($input['show_logo_strip']) ? '1' : '0';
    $output['logo_strip_title'] = sanitize_text_field(isset($input['logo_strip_title']) ? $input['logo_strip_title'] : $defaults['logo_strip_title']);
    $output['logo_items'] = array();
    if (!empty($input['logo_items']) && is_array($input['logo_items'])) {
        foreach ($input['logo_items'] as $item) {
            $clean = array(
                'image' => esc_url_raw(isset($item['image']) ? $item['image'] : ''),
                'name'  => sanitize_text_field(isset($item['name']) ? $item['name'] : ''),
                'url'   => esc_url_raw(isset($item['url']) ? $item['url'] : ''),
            );
            if ($clean['image']) {
                $output['logo_items'][] = $clean;
            }
        }
    }

    $output['contact_intro'] = wp_kses_post(isset($input['contact_intro']) ? $input['contact_intro'] : '');
    $output['contact_address'] = wp_kses_post(isset($input['contact_address']) ? $input['contact_address'] : '');
    $output['contact_email'] = sanitize_email(isset($input['contact_email']) ? $input['contact_email'] : '');
    $output['contact_phone'] = sanitize_text_field(isset($input['contact_phone']) ? $input['contact_phone'] : '');
    $output['contact_map_embed'] = isset($input['contact_map_embed']) ? wp_kses($input['contact_map_embed'], array(
        'iframe' => array(
            'src' => true,
            'width' => true,
            'height' => true,
            'style' => true,
            'allowfullscreen' => true,
            'loading' => true,
            'referrerpolicy' => true,
            'title' => true,
        ),
    )) : '';

    $output['research_intro'] = wp_kses_post(isset($input['research_intro']) ? $input['research_intro'] : '');
    $output['research_areas'] = array();
    if (!empty($input['research_areas']) && is_array($input['research_areas'])) {
        foreach ($input['research_areas'] as $area) {
            $clean = array(
                'title' => sanitize_text_field(isset($area['title']) ? $area['title'] : ''),
                'image' => esc_url_raw(isset($area['image']) ? $area['image'] : ''),
                'text'  => sanitize_textarea_field(isset($area['text']) ? $area['text'] : ''),
                'url'   => esc_url_raw(isset($area['url']) ? $area['url'] : ''),
            );
            if ($clean['title'] || $clean['image'] || $clean['text']) {
                $output['research_areas'][] = $clean;
            }
        }
    }

    $output['research_projects'] = array();
    if (!empty($input['research_projects']) && is_array($input['research_projects'])) {
        foreach ($input['research_projects'] as $project) {
            $clean = array(
                'title' => sanitize_text_field(isset($project['title']) ? $project['title'] : ''),
                'sponsor' => sanitize_text_field(isset($project['sponsor']) ? $project['sponsor'] : ''),
                'years' => sanitize_text_field(isset($project['years']) ? $project['years'] : ''),
                'status' => isset($project['status']) && in_array($project['status'], array('active', 'completed', 'paused', 'pending'), true) ? $project['status'] : 'active',
                'url' => esc_url_raw(isset($project['url']) ? $project['url'] : ''),
            );
            if ($clean['title']) {
                $output['research_projects'][] = $clean;
            }
        }
    }

    $output['research_sponsors'] = array();
    if (!empty($input['research_sponsors']) && is_array($input['research_sponsors'])) {
        foreach ($input['research_sponsors'] as $sponsor) {
            $clean = array(
                'name' => sanitize_text_field(isset($sponsor['name']) ? $sponsor['name'] : ''),
                'image' => esc_url_raw(isset($sponsor['image']) ? $sponsor['image'] : ''),
                'url' => esc_url_raw(isset($sponsor['url']) ? $sponsor['url'] : ''),
            );
            if ($clean['name'] || $clean['image']) {
                $output['research_sponsors'][] = $clean;
            }
        }
    }

    $output['gallery_intro'] = wp_kses_post(isset($input['gallery_intro']) ? $input['gallery_intro'] : '');
    $output['gallery_batch_size'] = isset($input['gallery_batch_size']) ? min(50, max(1, absint($input['gallery_batch_size']))) : $defaults['gallery_batch_size'];
    $output['gallery_items'] = array();
    if (!empty($input['gallery_items']) && is_array($input['gallery_items'])) {
        foreach ($input['gallery_items'] as $item) {
            $clean = array(
                'image' => esc_url_raw(isset($item['image']) ? $item['image'] : ''),
                'title' => sanitize_text_field(isset($item['title']) ? $item['title'] : ''),
                'caption' => sanitize_textarea_field(isset($item['caption']) ? $item['caption'] : ''),
                'category' => sanitize_text_field(isset($item['category']) ? $item['category'] : ''),
            );
            if ($clean['image']) {
                $output['gallery_items'][] = $clean;
            }
        }
    }
    $output['gallery_sets'] = array();
    if (!empty($input['gallery_sets']) && is_array($input['gallery_sets'])) {
        foreach ($input['gallery_sets'] as $set) {
            $images_raw = isset($set['images']) ? (string) $set['images'] : '';
            $images = array();
            foreach (preg_split('/\r\n|\r|\n/', $images_raw) as $image_url) {
                $image_url = esc_url_raw(trim($image_url));
                if ($image_url) {
                    $images[] = $image_url;
                }
            }

            $date_raw = sanitize_text_field(isset($set['date']) ? $set['date'] : '');
            $date_for_sort = '';
            if ($date_raw && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_raw)) {
                $date_for_sort = $date_raw;
            }

            $clean = array(
                'title' => sanitize_text_field(isset($set['title']) ? $set['title'] : ''),
                'date' => $date_for_sort,
                'description' => sanitize_textarea_field(isset($set['description']) ? $set['description'] : ''),
                'images' => $images,
            );

            if ($clean['title'] || $clean['date'] || $clean['description'] || $clean['images']) {
                $output['gallery_sets'][] = $clean;
            }
        }
    }

    $output['last_saved'] = current_time('mysql');

    return $output;
}

function faculty_theme_register_settings() {
    register_setting('faculty_theme_settings', 'faculty_theme_options', array(
        'sanitize_callback' => 'faculty_theme_sanitize_options',
        'default'           => faculty_theme_default_options(),
    ));
}
add_action('admin_init', 'faculty_theme_register_settings');

function faculty_theme_add_settings_page() {
    add_menu_page(
        __('Faculty Theme Settings', 'faculty-theme'),
        __('Faculty Theme', 'faculty-theme'),
        'edit_theme_options',
        'faculty-theme-settings',
        'faculty_theme_render_settings_page',
        'dashicons-admin-appearance',
        58
    );

    add_submenu_page(
        'faculty-theme-settings',
        __('Faculty Theme Settings', 'faculty-theme'),
        __('Settings', 'faculty-theme'),
        'edit_theme_options',
        'faculty-theme-settings',
        'faculty_theme_render_settings_page'
    );

    add_submenu_page(
        'faculty-theme-settings',
        __('Faculty Theme Setup Guide', 'faculty-theme'),
        __('Setup Guide', 'faculty-theme'),
        'edit_theme_options',
        'faculty-theme-setup-guide',
        'faculty_theme_render_setup_guide_page'
    );
}
add_action('admin_menu', 'faculty_theme_add_settings_page');

function faculty_theme_admin_post_actions() {
    add_action('admin_post_faculty_theme_export_settings', 'faculty_theme_export_settings');
    add_action('admin_post_faculty_theme_import_settings', 'faculty_theme_import_settings');
}
add_action('admin_init', 'faculty_theme_admin_post_actions');

function faculty_theme_admin_assets($hook) {
    if ('toplevel_page_faculty-theme-settings' !== $hook) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('faculty-theme-admin', get_template_directory_uri() . '/assets/js/theme-admin.js', array('jquery', 'jquery-ui-sortable'), wp_get_theme()->get('Version'), true);
}
add_action('admin_enqueue_scripts', 'faculty_theme_admin_assets');

function faculty_theme_export_settings() {
    if (!current_user_can('edit_theme_options')) {
        wp_die(__('You do not have permission to export theme settings.', 'faculty-theme'));
    }

    check_admin_referer('faculty_theme_export_settings', 'faculty_theme_export_nonce');

    $payload = array(
        'theme' => 'faculty-theme',
        'version' => wp_get_theme()->get('Version'),
        'exported_at' => gmdate('c'),
        'options' => faculty_theme_get_options(),
    );

    nocache_headers();
    header('Content-Type: application/json; charset=' . get_option('blog_charset'));
    header('Content-Disposition: attachment; filename=faculty-theme-settings-' . gmdate('Ymd-His') . '.json');
    echo wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

function faculty_theme_import_settings() {
    if (!current_user_can('edit_theme_options')) {
        wp_die(__('You do not have permission to import theme settings.', 'faculty-theme'));
    }

    check_admin_referer('faculty_theme_import_settings', 'faculty_theme_import_nonce');

    $raw_json = isset($_POST['faculty_theme_settings_json']) ? wp_unslash($_POST['faculty_theme_settings_json']) : '';
    $decoded = json_decode((string) $raw_json, true);
    $imported = false;

    if (is_array($decoded)) {
        $options = isset($decoded['options']) && is_array($decoded['options']) ? $decoded['options'] : $decoded;
        if (is_array($options)) {
            update_option('faculty_theme_options', faculty_theme_sanitize_options($options));
            $imported = true;
        }
    }

    wp_safe_redirect(admin_url('admin.php?page=faculty-theme-settings&theme_imported=' . ($imported ? '1' : '0') . '#faculty-group-maintenance'));
    exit;
}

function faculty_theme_get_page_hero_image() {
    $options = faculty_theme_get_options();

    if (!empty($options['page_hero_image'])) {
        return faculty_theme_normalize_media_url($options['page_hero_image']);
    }

    if (!empty($options['parallax_bands']) && is_array($options['parallax_bands'])) {
        foreach ($options['parallax_bands'] as $band) {
            if (!empty($band['image'])) {
                return faculty_theme_normalize_media_url($band['image']);
            }
        }
    }

    if (!empty($options['slides']) && is_array($options['slides'])) {
        foreach ($options['slides'] as $slide) {
            if (!empty($slide['image'])) {
                return faculty_theme_normalize_media_url($slide['image']);
            }
        }
    }

    if (!empty($options['intro_image'])) {
        return faculty_theme_normalize_media_url($options['intro_image']);
    }

    $legacy_profile_settings = get_option('academic_directory_profile_page_settings', array());
    if (is_array($legacy_profile_settings) && !empty($legacy_profile_settings['hero_image'])) {
        return faculty_theme_normalize_media_url($legacy_profile_settings['hero_image']);
    }

    return '';
}

function faculty_theme_page_header($title = '', $description = '') {
    $title = '' !== $title ? $title : get_the_title();
    $image = faculty_theme_get_page_hero_image();
    $title_size = faculty_theme_get_option('page_hero_title_size', '4rem');
    $style = '--faculty-page-title-size:' . esc_attr($title_size) . ';';

    if ($image) {
        $style .= 'background-image:url(' . esc_url($image) . ');';
    }
    ?>
    <header class="page-header<?php echo $image ? ' page-header--image' : ''; ?>" style="<?php echo esc_attr($style); ?>">
        <div class="page-header-overlay"></div>
        <div class="container page-header-inner">
            <h1 class="page-title"><?php echo esc_html(wp_strip_all_tags($title)); ?></h1>
            <?php if ($description) : ?><div class="archive-description"><?php echo wp_kses_post($description); ?></div><?php endif; ?>
        </div>
    </header>
    <?php
}

function faculty_theme_get_accessibility_warnings($options) {
    $warnings = array();

    foreach (array_values((array) $options['logo_items']) as $index => $logo) {
        if (!empty($logo['image']) && empty($logo['name'])) {
            $warnings[] = sprintf(__('Logo strip item %d has an image but no logo name / alt text.', 'faculty-theme'), $index + 1);
        }
    }

    foreach (array_values((array) $options['research_areas']) as $index => $area) {
        if (!empty($area['image']) && empty($area['title'])) {
            $warnings[] = sprintf(__('Research area %d has an image but no title to use as accessible text.', 'faculty-theme'), $index + 1);
        }
    }

    foreach (array_values((array) $options['research_sponsors']) as $index => $sponsor) {
        if (!empty($sponsor['image']) && empty($sponsor['name'])) {
            $warnings[] = sprintf(__('Research sponsor %d has a logo but no sponsor name / alt text.', 'faculty-theme'), $index + 1);
        }
    }

    foreach (array_values((array) $options['gallery_sets']) as $index => $set) {
        $images = !empty($set['images']) && is_array($set['images']) ? array_filter($set['images']) : array();
        if ($images && empty($set['title'])) {
            $warnings[] = sprintf(__('Gallery event %d has photos but no event title.', 'faculty-theme'), $index + 1);
        }
        if ($images && empty($set['description'])) {
            $warnings[] = sprintf(__('Gallery event %d has photos but no short description.', 'faculty-theme'), $index + 1);
        }
    }

    return $warnings;
}

function faculty_theme_render_settings_page_legacy() {
    if (!current_user_can('edit_theme_options')) {
        return;
    }
    $options = faculty_theme_get_options();
    $slides = array_values((array) $options['slides']);
    while (count($slides) < 5) {
        $slides[] = array('image' => '', 'title' => '', 'text' => '', 'button_text' => '', 'button_url' => '');
    }
    ?>
    <div class="wrap faculty-theme-settings">
        <h1><?php esc_html_e('Faculty Theme Settings', 'faculty-theme'); ?></h1>
        <p><?php esc_html_e('Manage site branding, homepage slides, news, and footer content from one screen.', 'faculty-theme'); ?></p>
        <?php settings_errors(); ?>
        <form method="post" action="options.php">
            <?php settings_fields('faculty_theme_settings'); ?>
            <h2><?php esc_html_e('Branding', 'faculty-theme'); ?></h2>
            <table class="form-table" role="presentation">
                <tr><th scope="row"><label for="faculty-eyebrow"><?php esc_html_e('Institution line', 'faculty-theme'); ?></label></th><td><input class="regular-text" id="faculty-eyebrow" name="faculty_theme_options[eyebrow]" value="<?php echo esc_attr($options['eyebrow']); ?>"></td></tr>
                <tr><th scope="row"><label for="faculty-brand-logo"><?php esc_html_e('Logo', 'faculty-theme'); ?></label></th><td><input class="regular-text faculty-media-url" id="faculty-brand-logo" name="faculty_theme_options[brand_logo]" value="<?php echo esc_url($options['brand_logo']); ?>"> <button type="button" class="button faculty-select-media" data-target="#faculty-brand-logo"><?php esc_html_e('Choose logo', 'faculty-theme'); ?></button><p class="description"><?php esc_html_e('Use an approved University or department logo. If empty, the WordPress custom logo or site title is used.', 'faculty-theme'); ?></p></td></tr>
                <tr><th scope="row"><label for="faculty-accent"><?php esc_html_e('Primary accent', 'faculty-theme'); ?></label></th><td><input id="faculty-accent" name="faculty_theme_options[accent]" type="color" value="<?php echo esc_attr($options['accent']); ?>"> <code>#BE0000</code> <?php esc_html_e('is official Utah Red.', 'faculty-theme'); ?></td></tr>
            </table>

            <h2><?php esc_html_e('Homepage Hero Slides', 'faculty-theme'); ?></h2>
            <p><?php esc_html_e('Complete up to five slides. Empty slides are ignored. Use wide, consistently sized images; 1920 × 900 pixels is a practical target.', 'faculty-theme'); ?></p>
            <?php foreach ($slides as $index => $slide) : ?>
                <fieldset class="faculty-slide-settings" style="padding:1rem;margin:0 0 1rem;border:1px solid #ccd0d4;background:#fff;">
                    <legend><strong><?php printf(esc_html__('Slide %d', 'faculty-theme'), $index + 1); ?></strong></legend>
                    <p><label><?php esc_html_e('Image URL', 'faculty-theme'); ?><br><input id="faculty-slide-<?php echo esc_attr($index); ?>" class="large-text faculty-media-url" name="faculty_theme_options[slides][<?php echo esc_attr($index); ?>][image]" value="<?php echo esc_url($slide['image']); ?>"></label> <button type="button" class="button faculty-select-media" data-target="#faculty-slide-<?php echo esc_attr($index); ?>"><?php esc_html_e('Choose image', 'faculty-theme'); ?></button></p>
                    <p><label><?php esc_html_e('Heading', 'faculty-theme'); ?><br><input class="large-text" name="faculty_theme_options[slides][<?php echo esc_attr($index); ?>][title]" value="<?php echo esc_attr($slide['title']); ?>"></label></p>
                    <p><label><?php esc_html_e('Summary', 'faculty-theme'); ?><br><textarea class="large-text" rows="2" name="faculty_theme_options[slides][<?php echo esc_attr($index); ?>][text]"><?php echo esc_textarea($slide['text']); ?></textarea></label></p>
                    <p><label><?php esc_html_e('Button label', 'faculty-theme'); ?> <input name="faculty_theme_options[slides][<?php echo esc_attr($index); ?>][button_text]" value="<?php echo esc_attr($slide['button_text']); ?>"></label> <label><?php esc_html_e('Button URL', 'faculty-theme'); ?> <input class="regular-text" type="url" name="faculty_theme_options[slides][<?php echo esc_attr($index); ?>][button_url]" value="<?php echo esc_url($slide['button_url']); ?>"></label></p>
                </fieldset>
            <?php endforeach; ?>

            <h2><?php esc_html_e('Homepage News', 'faculty-theme'); ?></h2>
            <table class="form-table" role="presentation">
                <tr><th scope="row"><?php esc_html_e('Display', 'faculty-theme'); ?></th><td><label><input type="checkbox" name="faculty_theme_options[show_news]" value="1" <?php checked($options['show_news'], '1'); ?>> <?php esc_html_e('Show the latest posts section', 'faculty-theme'); ?></label></td></tr>
                <tr><th scope="row"><label for="faculty-news-title"><?php esc_html_e('Section title', 'faculty-theme'); ?></label></th><td><input class="regular-text" id="faculty-news-title" name="faculty_theme_options[news_title]" value="<?php echo esc_attr($options['news_title']); ?>"></td></tr>
                <tr><th scope="row"><label for="faculty-news-category"><?php esc_html_e('Category', 'faculty-theme'); ?></label></th><td><?php wp_dropdown_categories(array('show_option_all' => __('All categories', 'faculty-theme'), 'hide_empty' => false, 'id' => 'faculty-news-category', 'name' => 'faculty_theme_options[news_category]', 'selected' => $options['news_category'])); ?></td></tr>
                <tr><th scope="row"><label for="faculty-news-count"><?php esc_html_e('Number of posts', 'faculty-theme'); ?></label></th><td><input id="faculty-news-count" type="number" min="1" max="12" name="faculty_theme_options[news_count]" value="<?php echo esc_attr($options['news_count']); ?>"></td></tr>
                <tr><th scope="row"><label for="faculty-news-url"><?php esc_html_e('More news URL', 'faculty-theme'); ?></label></th><td><input class="regular-text" id="faculty-news-url" type="url" name="faculty_theme_options[news_archive_url]" value="<?php echo esc_url($options['news_archive_url']); ?>"></td></tr>
            </table>

            <h2><?php esc_html_e('Footer', 'faculty-theme'); ?></h2>
            <table class="form-table" role="presentation"><tr><th scope="row"><label for="faculty-footer-text"><?php esc_html_e('Footer text', 'faculty-theme'); ?></label></th><td><textarea class="large-text" rows="4" id="faculty-footer-text" name="faculty_theme_options[footer_text]"><?php echo esc_textarea($options['footer_text']); ?></textarea><p class="description"><?php esc_html_e('Basic HTML links are allowed.', 'faculty-theme'); ?></p></td></tr></table>
            <?php submit_button(); ?>
        </form>
        <hr>
        <h2><?php esc_html_e('Homepage Gadgets', 'faculty-theme'); ?></h2>
        <p><?php printf(wp_kses_post(__('Add blocks or plugin widgets to the three homepage regions under <a href="%s">Appearance → Widgets</a>. The Page Bottom region is available on regular pages.', 'faculty-theme')), esc_url(admin_url('widgets.php'))); ?></p>
    </div>
    <?php
}

function faculty_theme_render_setup_guide_page() {
    if (!current_user_can('edit_theme_options')) {
        return;
    }

    $theme = wp_get_theme();
    ?>
    <div class="wrap faculty-theme-help">
        <h1><?php esc_html_e('Faculty Theme Setup Guide', 'faculty-theme'); ?></h1>
        <p class="description"><?php esc_html_e('A quick in-admin guide for maintaining the public website without reading code or README files.', 'faculty-theme'); ?></p>

        <style>
            .faculty-theme-help-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-top: 1.25rem; }
            .faculty-theme-help-card { padding: 1rem 1.15rem; border: 1px solid #dcdcde; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
            .faculty-theme-help-card h2 { margin-top: 0; }
            .faculty-theme-help-card ol, .faculty-theme-help-card ul { padding-left: 1.3rem; }
            .faculty-theme-help-callout { margin-top: 1rem; padding: 1rem 1.15rem; border-left: 4px solid #BE0000; background: #fff; }
            .faculty-theme-help-code { display: inline-block; padding: .1rem .35rem; background: #f0f0f1; font-family: Consolas, Monaco, monospace; }
        </style>

        <div class="faculty-theme-help-callout">
            <h2><?php esc_html_e('What this menu controls', 'faculty-theme'); ?></h2>
            <p><?php esc_html_e('Use Faculty Theme for the visual website: homepage, headers, navigation, page templates, gallery, contact page, research page, footer, logos, colors, and slideshow.', 'faculty-theme'); ?></p>
            <p><?php esc_html_e('Use Faculty Toolkit for people data: PI profile, students, education records, private edit links, and the automatic Research Group directory.', 'faculty-theme'); ?></p>
        </div>

        <div class="faculty-theme-help-grid">
            <section class="faculty-theme-help-card">
                <h2><?php esc_html_e('1. Required pages', 'faculty-theme'); ?></h2>
                <ol>
                    <li><?php esc_html_e('Create Home with the default template.', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Create NEWS with the default template.', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Create Research and select the Faculty Research template.', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Create Gallery and select the Faculty Gallery template.', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Create Contact and select the Faculty Contact template.', 'faculty-theme'); ?></li>
                </ol>
                <p><strong><?php esc_html_e('Do not create a WordPress page named Research Group.', 'faculty-theme'); ?></strong> <?php esc_html_e('The plugin automatically owns /research-group/.', 'faculty-theme'); ?></p>
            </section>

            <section class="faculty-theme-help-card">
                <h2><?php esc_html_e('2. Reading settings', 'faculty-theme'); ?></h2>
                <ol>
                    <li><?php esc_html_e('Go to Settings > Reading.', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Choose "A static page".', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Set Homepage to Home.', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Set Posts page to NEWS.', 'faculty-theme'); ?></li>
                </ol>
                <p><?php esc_html_e('If Homepage is set to a static page but the dropdown is empty, the front page can appear missing.', 'faculty-theme'); ?></p>
            </section>

            <section class="faculty-theme-help-card">
                <h2><?php esc_html_e('3. Main menu', 'faculty-theme'); ?></h2>
                <p><?php esc_html_e('Go to Appearance > Menus and add:', 'faculty-theme'); ?></p>
                <ul>
                    <li><?php esc_html_e('Home', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('NEWS', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Research', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Research Group as a custom link to /research-group/', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Gallery', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Contact', 'faculty-theme'); ?></li>
                </ul>
            </section>

            <section class="faculty-theme-help-card">
                <h2><?php esc_html_e('4. Theme tabs', 'faculty-theme'); ?></h2>
                <ul>
                    <li><?php esc_html_e('General: logo and shared page hero image.', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Colors: editable theme palette variables for accents, navigation, footer, gallery timeline, and vacancy callouts.', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Front Page and MEDAL Intro: homepage structure and introduction.', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Slider: collapsible, drag-and-drop homepage slides with timing, transitions, and image fitting.', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('News: homepage news preview settings.', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Contact, Research, Gallery: content for those page templates; repeatable items are collapsible for easier ordering.', 'faculty-theme'); ?></li>
                    <li><?php esc_html_e('Visuals / Logos and Footer: collapsible background bands, logos, address, footer note.', 'faculty-theme'); ?></li>
                </ul>
            </section>

            <section class="faculty-theme-help-card">
                <h2><?php esc_html_e('5. Quick tests', 'faculty-theme'); ?></h2>
                <ul>
                    <li><span class="faculty-theme-help-code">/</span></li>
                    <li><span class="faculty-theme-help-code">/news/</span></li>
                    <li><span class="faculty-theme-help-code">/research/</span></li>
                    <li><span class="faculty-theme-help-code">/research-group/</span></li>
                    <li><span class="faculty-theme-help-code">/research-group/PI/</span></li>
                    <li><span class="faculty-theme-help-code">/gallery/</span></li>
                    <li><span class="faculty-theme-help-code">/contact/</span></li>
                </ul>
                <p><?php esc_html_e('If Research Group URLs show 404, go to Settings > Permalinks and click Save Changes.', 'faculty-theme'); ?></p>
            </section>

            <section class="faculty-theme-help-card">
                <h2><?php esc_html_e('Producer / maintenance info', 'faculty-theme'); ?></h2>
                <p><strong><?php esc_html_e('Theme:', 'faculty-theme'); ?></strong> <?php echo esc_html($theme->get('Name')); ?> <?php echo esc_html($theme->get('Version')); ?></p>
                <p><strong><?php esc_html_e('Designed for:', 'faculty-theme'); ?></strong> <?php esc_html_e('MEDAL Research Group academic website workflows.', 'faculty-theme'); ?></p>
                <p><strong><?php esc_html_e('Producer:', 'faculty-theme'); ?></strong> <?php esc_html_e('Soroosh Noorzad / MEDAL website project.', 'faculty-theme'); ?></p>
                <p><?php esc_html_e('For long-term maintenance, keep people data in Faculty Toolkit and visual/page content in Faculty Theme.', 'faculty-theme'); ?></p>
            </section>
        </div>
    </div>
    <?php
}

function faculty_theme_render_settings_page() {
    if (!current_user_can('edit_theme_options')) {
        return;
    }

    $options = faculty_theme_get_options();
    $slides = array_values((array) $options['slides']);
    $parallax_bands = array_values((array) $options['parallax_bands']);
    $logo_items = array_values((array) $options['logo_items']);
    $research_areas = array_values((array) $options['research_areas']);
    $research_projects = array_values((array) $options['research_projects']);
    $research_sponsors = array_values((array) $options['research_sponsors']);
    $gallery_items = array_values((array) $options['gallery_items']);
    $gallery_sets = array_values((array) $options['gallery_sets']);
    $accessibility_warnings = faculty_theme_get_accessibility_warnings($options);
    $slide_fonts = array(
        'default'   => __('Theme default', 'faculty-theme'),
        'classic'   => __('Clean academic sans', 'faculty-theme'),
        'serif'     => __('Editorial serif', 'faculty-theme'),
        'condensed' => __('Bold condensed', 'faculty-theme'),
        'mono'      => __('Technical mono accent', 'faculty-theme'),
    );
    ?>
    <div class="wrap faculty-theme-settings">
        <h1><?php esc_html_e('Faculty Theme Settings', 'faculty-theme'); ?></h1>
        <p><?php esc_html_e('Manage site branding, homepage behavior, slides, news, and footer content from one place.', 'faculty-theme'); ?></p>
        <?php if (!empty($options['last_saved'])) : ?>
            <p class="description"><?php printf(esc_html__('Last saved: %s', 'faculty-theme'), esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($options['last_saved'])))); ?></p>
        <?php endif; ?>
        <?php settings_errors(); ?>

        <style>
            .faculty-theme-tabs { margin-top: 1rem; }
            .faculty-theme-tab-panel { display: none; max-width: 1120px; padding: 1.25rem; border: 1px solid #c3c4c7; border-top: 0; background: #fff; }
            .faculty-theme-tab-panel.is-active { display: block; }
            .faculty-slide-settings { position: relative; padding: 1rem; margin: 0 0 1rem; border: 1px solid #ccd0d4; background: #fff; }
            .faculty-sort-handle { display: inline-flex; align-items: center; gap: .35rem; margin-right: .5rem; color: #50575e; cursor: move; font-weight: 700; }
            .faculty-sort-placeholder { min-height: 4rem; margin: 0 0 1rem; border: 2px dashed #8c8f94; background: #f6f7f7; }
            .faculty-media-preview { display: none; width: 10rem; max-width: 100%; margin-top: .6rem; padding: .35rem; border: 1px solid #dcdcde; background: #fff; }
            .faculty-media-preview img { display: block; width: 100%; height: 5rem; object-fit: cover; }
            .faculty-gallery-preview { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .6rem; }
            .faculty-gallery-preview img { width: 4rem; height: 3rem; object-fit: cover; border: 1px solid #dcdcde; background: #fff; }
            .faculty-slide-settings legend { padding: 0 .35rem; }
            .faculty-slide-actions { display: flex; gap: .5rem; align-items: center; margin-top: .5rem; }
            .faculty-settings-note { max-width: 760px; color: #646970; }
            .faculty-dynamic-item { padding: 1rem; margin: 0 0 1rem; border: 1px solid #ccd0d4; background: #fff; }
            .faculty-collapsible-item { padding: 0; }
            .faculty-collapsible-item summary { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .85rem 1rem; cursor: pointer; background: #f6f7f7; }
            .faculty-collapsible-item summary:hover { background: #f0f0f1; }
            .faculty-collapsible-item summary strong { color: #1d2327; }
            .faculty-collapsible-item summary span { color: #646970; font-size: .88rem; }
            .faculty-collapsible-item[open] summary { border-bottom: 1px solid #ccd0d4; }
            .faculty-collapsible-body { padding: 1rem; }
            .faculty-color-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
            .faculty-color-card { padding: 1rem; border: 1px solid #dcdcde; background: #fff; }
            .faculty-color-card label { display: flex; align-items: center; justify-content: space-between; gap: 1rem; font-weight: 700; }
            .faculty-color-card input[type=color] { width: 4.5rem; height: 2.4rem; padding: .15rem; }
            .faculty-color-card code { display: inline-block; margin-top: .5rem; }
            .faculty-color-actions { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; margin-top: .65rem; }
            .faculty-preview-panel { margin-top: .85rem; padding: .75rem; border: 1px solid #dcdcde; background: #f6f7f7; }
            .faculty-slide-admin-preview { position: relative; min-height: 11rem; overflow: hidden; color: #fff; background: #111; }
            .faculty-slide-admin-preview img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: .72; }
            .faculty-slide-admin-preview-content { position: relative; z-index: 1; max-width: 28rem; padding: 1.2rem; text-shadow: 0 2px 8px rgba(0,0,0,.4); }
            .faculty-slide-admin-preview-title { display: block; font-size: 1.35rem; font-weight: 800; line-height: 1.1; }
            .faculty-slide-admin-preview-text { display: block; margin-top: .45rem; font-size: .92rem; }
            .faculty-gallery-deck-admin-preview { position: relative; min-height: 11rem; overflow: hidden; }
            .faculty-gallery-deck-admin-preview img { position: absolute; top: 50%; left: 50%; width: auto; max-width: 78%; max-height: 8rem; padding: .35rem; border: 1px solid #dcdcde; background: #fff; box-shadow: 0 6px 16px rgba(0,0,0,.15); transform: translate(-50%, -50%); }
            .faculty-gallery-deck-admin-preview img:nth-child(2) { transform: translate(calc(-50% + 1.2rem), calc(-50% + .2rem)) rotate(4deg); }
            .faculty-gallery-deck-admin-preview img:nth-child(3) { transform: translate(calc(-50% - 1.2rem), calc(-50% + .2rem)) rotate(-4deg); }
            .faculty-gallery-deck-admin-preview img:nth-child(4) { transform: translate(calc(-50% + 2rem), calc(-50% + .4rem)) rotate(7deg); }
            .faculty-import-export-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; }
            .faculty-import-export-card { padding: 1rem; border: 1px solid #dcdcde; background: #fff; }
            .faculty-theme-settings { --ft-admin-accent: #2f4858; --ft-admin-accent-dark: #1d2f3a; --ft-admin-warm: #8b6f3d; --ft-admin-ink: #18222b; --ft-admin-muted: #66717b; --ft-admin-line: #d7dde2; --ft-admin-soft: #f7f8f8; --ft-admin-card: #fff; }
            .faculty-theme-settings > h1 { margin-top: 1.05rem; color: var(--ft-admin-ink); font-family: Georgia, "Times New Roman", serif; font-size: 1.75rem; font-weight: 700; letter-spacing: -.018em; }
            .faculty-theme-settings > p { max-width: 58rem; color: var(--ft-admin-muted); font-size: .94rem; }
            .faculty-theme-tabs.nav-tab-wrapper { display: flex; flex-wrap: wrap; gap: .2rem; margin: 1rem 0 0; padding: .35rem; border: 1px solid var(--ft-admin-line); border-bottom-color: #c8d0d6; border-radius: 4px 4px 0 0; background: #f2f4f5; box-shadow: none; }
            .faculty-theme-tabs .nav-tab { margin: 0; padding: .46rem .72rem; border: 1px solid transparent; border-radius: 3px; background: transparent; color: var(--ft-admin-ink); font-size: .88rem; font-weight: 700; transition: background-color .12s ease, color .12s ease, border-color .12s ease; }
            .faculty-theme-tabs .nav-tab:hover { border-color: #c9d1d7; background: #fff; color: var(--ft-admin-accent-dark); }
            .faculty-theme-tabs .nav-tab-active,
            .faculty-theme-tabs .nav-tab-active:hover { border-color: var(--ft-admin-accent); background: var(--ft-admin-accent); color: #fff; box-shadow: none; }
            .faculty-theme-tab-panel { max-width: 1120px; margin: 0; padding: 1.05rem 1.1rem; border: 1px solid var(--ft-admin-line); border-top: 0; border-radius: 0; background: #fff; box-shadow: none; }
            .faculty-theme-tab-panel + .faculty-theme-tab-panel.is-active { border-top: 1px solid var(--ft-admin-line); margin-top: .9rem; }
            .faculty-theme-tab-panel h2 { margin: 0 0 .75rem; color: var(--ft-admin-ink); font-family: Georgia, "Times New Roman", serif; font-size: 1.28rem; letter-spacing: -.01em; }
            .faculty-theme-tab-panel h3 { margin: 1.25rem 0 .6rem; color: var(--ft-admin-ink); font-size: .98rem; letter-spacing: .01em; text-transform: uppercase; }
            .faculty-theme-tab-panel .form-table { margin-top: .55rem; border-collapse: separate; border-spacing: 0 .52rem; }
            .faculty-theme-tab-panel .form-table th { width: 215px; padding: .68rem .9rem .68rem 0; color: var(--ft-admin-ink); font-weight: 700; vertical-align: top; }
            .faculty-theme-tab-panel .form-table td { padding: .6rem .72rem; border: 1px solid var(--ft-admin-line); border-radius: 3px; background: var(--ft-admin-card); box-shadow: none; }
            .faculty-theme-settings label { color: var(--ft-admin-ink); font-weight: 650; }
            .faculty-theme-settings input[type="text"],
            .faculty-theme-settings input[type="url"],
            .faculty-theme-settings input[type="email"],
            .faculty-theme-settings input[type="number"],
            .faculty-theme-settings input[type="date"],
            .faculty-theme-settings input:not([type]),
            .faculty-theme-settings select,
            .faculty-theme-settings textarea { min-height: 36px; border: 1px solid #c6cdd3; border-radius: 3px; background: #fff; box-shadow: none; color: var(--ft-admin-ink); transition: border-color .12s ease, box-shadow .12s ease, background-color .12s ease; }
            .faculty-theme-settings textarea { padding: .55rem .62rem; line-height: 1.45; }
            .faculty-theme-settings input:focus,
            .faculty-theme-settings select:focus,
            .faculty-theme-settings textarea:focus { border-color: var(--ft-admin-accent); box-shadow: 0 0 0 2px rgba(47,72,88,.13); outline: none; }
            .faculty-theme-settings input[type="color"] { width: 4.2rem; min-height: 2.3rem; padding: .16rem; border-radius: 3px; cursor: pointer; }
            .faculty-theme-settings input[type="checkbox"] { width: 1rem; height: 1rem; border-radius: 2px; box-shadow: none; vertical-align: middle; }
            .faculty-theme-settings .description,
            .faculty-settings-note { color: var(--ft-admin-muted); line-height: 1.55; }
            .faculty-theme-settings .button { min-height: 34px; padding: .2rem .7rem; border-radius: 3px; border-color: #b8c3cc; font-weight: 650; transition: background-color .12s ease, border-color .12s ease; }
            .faculty-theme-settings .button:hover { border-color: #8f9ba5; box-shadow: none; }
            .faculty-theme-settings .button-primary { border-color: var(--ft-admin-accent); background: var(--ft-admin-accent); }
            .faculty-theme-settings .faculty-slide-settings,
            .faculty-theme-settings .faculty-dynamic-item,
            .faculty-theme-settings .faculty-color-card,
            .faculty-theme-settings .faculty-import-export-card { border: 1px solid var(--ft-admin-line); border-radius: 3px; background: var(--ft-admin-card); box-shadow: none; overflow: hidden; }
            .faculty-theme-settings .faculty-collapsible-item summary { padding: .68rem .78rem; border-left: 3px solid var(--ft-admin-warm); background: #f7f8f8; }
            .faculty-theme-settings .faculty-collapsible-item summary strong { font-size: .94rem; letter-spacing: 0; }
            .faculty-theme-settings .faculty-collapsible-item summary span:last-child { margin-left: auto; padding: .18rem .45rem; border-radius: 2px; background: #e9edf0; color: var(--ft-admin-muted); font-size: .73rem; font-weight: 650; }
            .faculty-theme-settings .faculty-collapsible-body { display: grid; gap: .65rem; padding: .8rem; }
            .faculty-theme-settings .faculty-collapsible-body p { margin: 0; }
            .faculty-theme-settings .faculty-slide-actions { margin: 0; }
            .faculty-theme-settings .faculty-media-preview { width: 10rem; padding: .35rem; border-color: var(--ft-admin-line); border-radius: 3px; background: #f8fafb; box-shadow: none; }
            .faculty-theme-settings .faculty-media-preview img { height: 5.5rem; border-radius: 2px; }
            .faculty-theme-settings .faculty-preview-panel { border-color: var(--ft-admin-line); border-radius: 3px; background: #f3f6f8; }
            .faculty-theme-settings code { border-radius: 2px; background: #eef2f5; color: #28343d; }
            .faculty-theme-settings .wp-editor-wrap { border-radius: 3px; overflow: hidden; box-shadow: none; }
            @media (max-width: 782px) {
                .faculty-theme-tab-panel .form-table th,
                .faculty-theme-tab-panel .form-table td { display: block; width: auto; padding: .85rem; }
                .faculty-theme-tab-panel .form-table th { padding-bottom: .2rem; }
            }
        </style>

        <nav class="nav-tab-wrapper faculty-theme-tabs" aria-label="<?php esc_attr_e('Faculty Theme settings sections', 'faculty-theme'); ?>">
            <a href="#faculty-group-general" class="nav-tab nav-tab-active" data-faculty-tab><?php esc_html_e('General', 'faculty-theme'); ?></a>
            <a href="#faculty-group-homepage" class="nav-tab" data-faculty-tab><?php esc_html_e('Homepage', 'faculty-theme'); ?></a>
            <a href="#faculty-group-pages" class="nav-tab" data-faculty-tab><?php esc_html_e('Pages', 'faculty-theme'); ?></a>
            <a href="#faculty-group-research" class="nav-tab" data-faculty-tab><?php esc_html_e('Research', 'faculty-theme'); ?></a>
            <a href="#faculty-group-gallery" class="nav-tab" data-faculty-tab><?php esc_html_e('Gallery', 'faculty-theme'); ?></a>
            <a href="#faculty-group-design" class="nav-tab" data-faculty-tab><?php esc_html_e('Visual Design', 'faculty-theme'); ?></a>
            <a href="#faculty-group-footer" class="nav-tab" data-faculty-tab><?php esc_html_e('Footer', 'faculty-theme'); ?></a>
            <a href="#faculty-group-maintenance" class="nav-tab" data-faculty-tab><?php esc_html_e('Maintenance', 'faculty-theme'); ?></a>
            <a href="#faculty-group-help" class="nav-tab" data-faculty-tab><?php esc_html_e('Help', 'faculty-theme'); ?></a>
        </nav>

        <?php if (isset($_GET['theme_imported'])) : ?>
            <?php $theme_imported = sanitize_key(wp_unslash($_GET['theme_imported'])); ?>
            <?php if ($theme_imported === '1') : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Faculty Theme settings imported successfully.', 'faculty-theme'); ?></p></div>
            <?php else : ?>
                <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Could not import those Faculty Theme settings. Please paste valid JSON exported from this theme.', 'faculty-theme'); ?></p></div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields('faculty_theme_settings'); ?>

            <section id="faculty-tab-general" class="faculty-theme-tab-panel is-active" data-faculty-panel-group="#faculty-group-general">
                <h2><?php esc_html_e('General Theme Settings', 'faculty-theme'); ?></h2>
                <table class="form-table" role="presentation">
                    <tr><th scope="row"><label for="faculty-eyebrow"><?php esc_html_e('Institution line', 'faculty-theme'); ?></label></th><td><input class="regular-text" id="faculty-eyebrow" name="faculty_theme_options[eyebrow]" value="<?php echo esc_attr($options['eyebrow']); ?>"></td></tr>
                    <tr><th scope="row"><label for="faculty-brand-logo"><?php esc_html_e('Logo', 'faculty-theme'); ?></label></th><td><input class="regular-text faculty-media-url" id="faculty-brand-logo" name="faculty_theme_options[brand_logo]" value="<?php echo esc_url($options['brand_logo']); ?>"> <button type="button" class="button faculty-select-media" data-target="#faculty-brand-logo"><?php esc_html_e('Choose logo', 'faculty-theme'); ?></button><p class="description"><?php esc_html_e('Use an approved University or department logo. If empty, the WordPress custom logo or site title is used.', 'faculty-theme'); ?></p></td></tr>
                    <tr><th scope="row"><label for="faculty-page-hero-image"><?php esc_html_e('Shared page hero image', 'faculty-theme'); ?></label></th><td><input class="regular-text faculty-media-url" id="faculty-page-hero-image" name="faculty_theme_options[page_hero_image]" value="<?php echo esc_url($options['page_hero_image']); ?>"> <button type="button" class="button faculty-select-media" data-target="#faculty-page-hero-image"><?php esc_html_e('Choose image', 'faculty-theme'); ?></button><p class="description"><?php esc_html_e('Used behind page titles on normal theme pages. If empty, the theme reuses a visual divider, slide, intro image, or existing Academic Directory hero image when available.', 'faculty-theme'); ?></p></td></tr>
                    <tr><th scope="row"><label for="faculty-page-hero-title-size"><?php esc_html_e('Shared page hero title size', 'faculty-theme'); ?></label></th><td><input class="small-text" id="faculty-page-hero-title-size" name="faculty_theme_options[page_hero_title_size]" value="<?php echo esc_attr($options['page_hero_title_size']); ?>"><p class="description"><?php esc_html_e('Use a CSS size such as 4rem, 64px, or 3.5em. A plain number is saved as rem.', 'faculty-theme'); ?></p></td></tr>
                </table>
            </section>

            <section id="faculty-tab-front-page" class="faculty-theme-tab-panel" data-faculty-panel-group="#faculty-group-homepage">
                <h2><?php esc_html_e('Front Page Settings', 'faculty-theme'); ?></h2>
                <p class="faculty-settings-note"><?php esc_html_e('These switches control the modular homepage shell. Content for the page itself still comes from the WordPress page editor, and widgets/gadgets are managed under Appearance > Widgets.', 'faculty-theme'); ?></p>
                <table class="form-table" role="presentation">
                    <tr><th scope="row"><?php esc_html_e('Homepage modules', 'faculty-theme'); ?></th><td>
                        <p><label><input type="checkbox" name="faculty_theme_options[show_intro]" value="1" <?php checked($options['show_intro'], '1'); ?>> <?php esc_html_e('Display the MEDAL introduction at the top of the first page', 'faculty-theme'); ?></label></p>
                        <p><label><input type="checkbox" name="faculty_theme_options[show_slideshow]" value="1" <?php checked($options['show_slideshow'], '1'); ?>> <?php esc_html_e('Display the slideshow on the first page', 'faculty-theme'); ?></label></p>
                        <p><label><input type="checkbox" name="faculty_theme_options[show_gadgets]" value="1" <?php checked($options['show_gadgets'], '1'); ?>> <?php esc_html_e('Display homepage gadget/widget regions', 'faculty-theme'); ?></label></p>
                        <p><label><input type="checkbox" name="faculty_theme_options[show_logo_strip]" value="1" <?php checked($options['show_logo_strip'], '1'); ?>> <?php esc_html_e('Display the logo strip near the bottom', 'faculty-theme'); ?></label></p>
                    </td></tr>
                </table>
            </section>

            <section id="faculty-tab-intro" class="faculty-theme-tab-panel" data-faculty-panel-group="#faculty-group-homepage">
                <h2><?php esc_html_e('MEDAL Group Introduction', 'faculty-theme'); ?></h2>
                <p class="faculty-settings-note"><?php esc_html_e('Use this for the concise top-of-page introduction. Keep it short: name, one strong sentence, a short paragraph, and one optional call-to-action.', 'faculty-theme'); ?></p>
                <table class="form-table" role="presentation">
                    <tr><th scope="row"><label for="faculty-intro-eyebrow"><?php esc_html_e('Small label', 'faculty-theme'); ?></label></th><td><input class="regular-text" id="faculty-intro-eyebrow" name="faculty_theme_options[intro_eyebrow]" value="<?php echo esc_attr($options['intro_eyebrow']); ?>"></td></tr>
                    <tr><th scope="row"><label for="faculty-intro-title"><?php esc_html_e('Main title', 'faculty-theme'); ?></label></th><td><input class="regular-text" id="faculty-intro-title" name="faculty_theme_options[intro_title]" value="<?php echo esc_attr($options['intro_title']); ?>"></td></tr>
                    <tr><th scope="row"><label for="faculty-intro-subtitle"><?php esc_html_e('Subtitle', 'faculty-theme'); ?></label></th><td><input class="large-text" id="faculty-intro-subtitle" name="faculty_theme_options[intro_subtitle]" value="<?php echo esc_attr($options['intro_subtitle']); ?>"></td></tr>
                    <tr><th scope="row"><label for="faculty-intro-text"><?php esc_html_e('Introduction text', 'faculty-theme'); ?></label></th><td><textarea class="large-text" rows="5" id="faculty-intro-text" name="faculty_theme_options[intro_text]"><?php echo esc_textarea($options['intro_text']); ?></textarea><p class="description"><?php esc_html_e('Basic HTML is allowed for emphasis and links.', 'faculty-theme'); ?></p></td></tr>
                    <tr><th scope="row"><label for="faculty-intro-image"><?php esc_html_e('Intro image / group graphic', 'faculty-theme'); ?></label></th><td><input class="regular-text faculty-media-url" id="faculty-intro-image" name="faculty_theme_options[intro_image]" value="<?php echo esc_url($options['intro_image']); ?>"> <button type="button" class="button faculty-select-media" data-target="#faculty-intro-image"><?php esc_html_e('Choose image', 'faculty-theme'); ?></button></td></tr>
                    <tr><th scope="row"><?php esc_html_e('Button', 'faculty-theme'); ?></th><td><label><?php esc_html_e('Label', 'faculty-theme'); ?> <input name="faculty_theme_options[intro_button_text]" value="<?php echo esc_attr($options['intro_button_text']); ?>"></label> <label><?php esc_html_e('URL', 'faculty-theme'); ?> <input class="regular-text" type="url" name="faculty_theme_options[intro_button_url]" value="<?php echo esc_url($options['intro_button_url']); ?>"></label></td></tr>
                </table>
            </section>

            <section id="faculty-tab-slider" class="faculty-theme-tab-panel" data-faculty-panel-group="#faculty-group-homepage">
                <h2><?php esc_html_e('Slider Settings', 'faculty-theme'); ?></h2>
                <table class="form-table" role="presentation">
                    <tr><th scope="row"><label for="faculty-slide-font"><?php esc_html_e('Slide typography', 'faculty-theme'); ?></label></th><td><select id="faculty-slide-font" name="faculty_theme_options[slide_font]"><?php foreach ($slide_fonts as $font_key => $font_label) : ?><option value="<?php echo esc_attr($font_key); ?>" <?php selected($options['slide_font'], $font_key); ?>><?php echo esc_html($font_label); ?></option><?php endforeach; ?></select></td></tr>
                    <tr><th scope="row"><label for="faculty-slide-title-size"><?php esc_html_e('Default slide title size', 'faculty-theme'); ?></label></th><td><input id="faculty-slide-title-size" class="small-text" name="faculty_theme_options[slide_title_size]" value="<?php echo esc_attr($options['slide_title_size']); ?>"> <span class="description"><?php esc_html_e('Use a CSS size such as 3rem, 56px, or 4vw. Individual slides can override this below.', 'faculty-theme'); ?></span></td></tr>
                    <tr><th scope="row"><label for="faculty-slide-default-duration"><?php esc_html_e('Default slide timing', 'faculty-theme'); ?></label></th><td><input id="faculty-slide-default-duration" type="number" min="2000" max="30000" step="500" name="faculty_theme_options[slide_default_duration]" value="<?php echo esc_attr($options['slide_default_duration']); ?>"> <span class="description"><?php esc_html_e('milliseconds; used when an individual slide timing is empty.', 'faculty-theme'); ?></span></td></tr>
                </table>
                <p class="faculty-settings-note"><?php esc_html_e('Add as many slides as you need. Empty slides are ignored when saved. Wide, consistently sized images around 1920 x 900 pixels work best.', 'faculty-theme'); ?></p>
                <div id="faculty-slides-list" data-repeat-list="slides">
                    <?php foreach ($slides as $index => $slide) : ?>
                        <?php faculty_theme_render_slide_fields($index, $slide); ?>
                    <?php endforeach; ?>
                </div>
                <p>
                    <button type="button" class="button button-secondary" id="faculty-add-slide"><?php esc_html_e('Add slide', 'faculty-theme'); ?></button>
                    <button type="button" class="button" data-collapse-list="#faculty-slides-list" data-collapse-action="expand"><?php esc_html_e('Expand all', 'faculty-theme'); ?></button>
                    <button type="button" class="button" data-collapse-list="#faculty-slides-list" data-collapse-action="collapse"><?php esc_html_e('Collapse all', 'faculty-theme'); ?></button>
                </p>
            </section>

            <section id="faculty-tab-colors" class="faculty-theme-tab-panel" data-faculty-panel-group="#faculty-group-design">
                <h2><?php esc_html_e('Theme Color Palette', 'faculty-theme'); ?></h2>
                <p class="faculty-settings-note"><?php esc_html_e('These values drive the CSS variables used by the theme. Change them here instead of editing CSS when the site needs a color refresh.', 'faculty-theme'); ?></p>
                <div class="faculty-color-grid">
                    <?php foreach (faculty_theme_color_fields() as $color_key => $color_settings) : ?>
                        <div class="faculty-color-card">
                            <label for="<?php echo esc_attr('faculty-color-' . $color_key); ?>">
                                <span><?php echo esc_html($color_settings['label']); ?></span>
                                <input id="<?php echo esc_attr('faculty-color-' . $color_key); ?>" name="<?php echo esc_attr('faculty_theme_options[' . $color_key . ']'); ?>" type="color" value="<?php echo esc_attr($options[$color_key]); ?>" data-color-default="<?php echo esc_attr($color_settings['default']); ?>">
                            </label>
                            <div class="faculty-color-actions">
                                <code data-color-value><?php echo esc_html($options[$color_key]); ?></code>
                                <button type="button" class="button button-small faculty-reset-color"><?php esc_html_e('Return to default', 'faculty-theme'); ?></button>
                            </div>
                            <p class="description"><?php echo esc_html($color_settings['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section id="faculty-tab-news" class="faculty-theme-tab-panel" data-faculty-panel-group="#faculty-group-homepage">
                <h2><?php esc_html_e('Homepage News', 'faculty-theme'); ?></h2>
                <table class="form-table" role="presentation">
                    <tr><th scope="row"><?php esc_html_e('Display', 'faculty-theme'); ?></th><td><label><input type="checkbox" name="faculty_theme_options[show_news]" value="1" <?php checked($options['show_news'], '1'); ?>> <?php esc_html_e('Show the latest posts section', 'faculty-theme'); ?></label></td></tr>
                    <tr><th scope="row"><label for="faculty-news-title"><?php esc_html_e('Section title', 'faculty-theme'); ?></label></th><td><input class="regular-text" id="faculty-news-title" name="faculty_theme_options[news_title]" value="<?php echo esc_attr($options['news_title']); ?>"></td></tr>
                    <tr><th scope="row"><label for="faculty-news-category"><?php esc_html_e('Category', 'faculty-theme'); ?></label></th><td><?php wp_dropdown_categories(array('show_option_all' => __('All categories', 'faculty-theme'), 'hide_empty' => false, 'id' => 'faculty-news-category', 'name' => 'faculty_theme_options[news_category]', 'selected' => $options['news_category'])); ?></td></tr>
                    <tr><th scope="row"><label for="faculty-news-count"><?php esc_html_e('Number of posts', 'faculty-theme'); ?></label></th><td><input id="faculty-news-count" type="number" min="1" max="12" name="faculty_theme_options[news_count]" value="<?php echo esc_attr($options['news_count']); ?>"></td></tr>
                    <tr><th scope="row"><label for="faculty-news-url"><?php esc_html_e('More news URL', 'faculty-theme'); ?></label></th><td><input class="regular-text" id="faculty-news-url" type="url" name="faculty_theme_options[news_archive_url]" value="<?php echo esc_url($options['news_archive_url']); ?>"></td></tr>
                </table>
            </section>

            <section id="faculty-tab-contact" class="faculty-theme-tab-panel" data-faculty-panel-group="#faculty-group-pages">
                <h2><?php esc_html_e('Contact Page', 'faculty-theme'); ?></h2>
                <p class="faculty-settings-note"><?php esc_html_e('Create a WordPress page and assign the Contact template. These fields populate that page.', 'faculty-theme'); ?></p>
                <table class="form-table" role="presentation">
                    <tr><th scope="row"><label for="faculty-contact-intro"><?php esc_html_e('Description', 'faculty-theme'); ?></label></th><td><textarea class="large-text" rows="5" id="faculty-contact-intro" name="faculty_theme_options[contact_intro]"><?php echo esc_textarea($options['contact_intro']); ?></textarea></td></tr>
                    <tr><th scope="row"><label for="faculty-contact-address"><?php esc_html_e('Lab address', 'faculty-theme'); ?></label></th><td><textarea class="large-text" rows="4" id="faculty-contact-address" name="faculty_theme_options[contact_address]"><?php echo esc_textarea($options['contact_address']); ?></textarea></td></tr>
                    <tr><th scope="row"><label for="faculty-contact-email"><?php esc_html_e('Email', 'faculty-theme'); ?></label></th><td><input class="regular-text" type="email" id="faculty-contact-email" name="faculty_theme_options[contact_email]" value="<?php echo esc_attr($options['contact_email']); ?>"></td></tr>
                    <tr><th scope="row"><label for="faculty-contact-phone"><?php esc_html_e('Phone', 'faculty-theme'); ?></label></th><td><input class="regular-text" id="faculty-contact-phone" name="faculty_theme_options[contact_phone]" value="<?php echo esc_attr($options['contact_phone']); ?>"></td></tr>
                    <tr><th scope="row"><label for="faculty-contact-map"><?php esc_html_e('Map embed', 'faculty-theme'); ?></label></th><td><textarea class="large-text code" rows="5" id="faculty-contact-map" name="faculty_theme_options[contact_map_embed]"><?php echo esc_textarea($options['contact_map_embed']); ?></textarea><p class="description"><?php esc_html_e('Paste a Google Maps iframe embed code.', 'faculty-theme'); ?></p></td></tr>
                </table>
            </section>

            <section id="faculty-tab-research" class="faculty-theme-tab-panel" data-faculty-panel-group="#faculty-group-research">
                <h2><?php esc_html_e('Research Page', 'faculty-theme'); ?></h2>
                <p class="faculty-settings-note"><?php esc_html_e('Create a WordPress page and assign the Research template. Use this tab for research topic images, funded projects, and sponsor logos.', 'faculty-theme'); ?></p>
                <table class="form-table" role="presentation">
                    <tr><th scope="row"><label for="faculty-research-intro"><?php esc_html_e('Research introduction', 'faculty-theme'); ?></label></th><td><textarea class="large-text" rows="5" id="faculty-research-intro" name="faculty_theme_options[research_intro]"><?php echo esc_textarea($options['research_intro']); ?></textarea></td></tr>
                </table>

                <h3><?php esc_html_e('Research areas', 'faculty-theme'); ?></h3>
                <div id="faculty-research-area-list" data-repeat-list="research_areas">
                    <?php foreach ($research_areas as $index => $area) : ?>
                        <?php faculty_theme_render_research_area_fields($index, $area); ?>
                    <?php endforeach; ?>
                </div>
                <p><button type="button" class="button button-secondary" id="faculty-add-research-area"><?php esc_html_e('Add research area', 'faculty-theme'); ?></button> <button type="button" class="button" data-collapse-list="#faculty-research-area-list" data-collapse-action="expand"><?php esc_html_e('Expand all', 'faculty-theme'); ?></button> <button type="button" class="button" data-collapse-list="#faculty-research-area-list" data-collapse-action="collapse"><?php esc_html_e('Collapse all', 'faculty-theme'); ?></button></p>

                <h3><?php esc_html_e('Funded projects', 'faculty-theme'); ?></h3>
                <div id="faculty-research-project-list" data-repeat-list="research_projects">
                    <?php foreach ($research_projects as $index => $project) : ?>
                        <?php faculty_theme_render_research_project_fields($index, $project); ?>
                    <?php endforeach; ?>
                </div>
                <p><button type="button" class="button button-secondary" id="faculty-add-research-project"><?php esc_html_e('Add funded project', 'faculty-theme'); ?></button> <button type="button" class="button" data-collapse-list="#faculty-research-project-list" data-collapse-action="expand"><?php esc_html_e('Expand all', 'faculty-theme'); ?></button> <button type="button" class="button" data-collapse-list="#faculty-research-project-list" data-collapse-action="collapse"><?php esc_html_e('Collapse all', 'faculty-theme'); ?></button></p>

                <h3><?php esc_html_e('Sponsors', 'faculty-theme'); ?></h3>
                <div id="faculty-research-sponsor-list" data-repeat-list="research_sponsors">
                    <?php foreach ($research_sponsors as $index => $sponsor) : ?>
                        <?php faculty_theme_render_research_sponsor_fields($index, $sponsor); ?>
                    <?php endforeach; ?>
                </div>
                <p><button type="button" class="button button-secondary" id="faculty-add-research-sponsor"><?php esc_html_e('Add sponsor', 'faculty-theme'); ?></button> <button type="button" class="button" data-collapse-list="#faculty-research-sponsor-list" data-collapse-action="expand"><?php esc_html_e('Expand all', 'faculty-theme'); ?></button> <button type="button" class="button" data-collapse-list="#faculty-research-sponsor-list" data-collapse-action="collapse"><?php esc_html_e('Collapse all', 'faculty-theme'); ?></button></p>
            </section>

            <section id="faculty-tab-gallery" class="faculty-theme-tab-panel" data-faculty-panel-group="#faculty-group-gallery">
                <h2><?php esc_html_e('Gallery Page', 'faculty-theme'); ?></h2>
                <p class="faculty-settings-note"><?php esc_html_e('Create a WordPress page and assign the Faculty Gallery template. Add timeline events here; each event becomes a stacked deck of overlapping photos.', 'faculty-theme'); ?></p>
                <table class="form-table" role="presentation">
                    <tr><th scope="row"><label for="faculty-gallery-intro"><?php esc_html_e('Gallery introduction', 'faculty-theme'); ?></label></th><td><textarea class="large-text" rows="4" id="faculty-gallery-intro" name="faculty_theme_options[gallery_intro]"><?php echo esc_textarea($options['gallery_intro']); ?></textarea></td></tr>
                    <tr><th scope="row"><label for="faculty-gallery-batch-size"><?php esc_html_e('Events per load', 'faculty-theme'); ?></label></th><td><input id="faculty-gallery-batch-size" class="small-text" type="number" min="1" max="50" name="faculty_theme_options[gallery_batch_size]" value="<?php echo esc_attr($options['gallery_batch_size']); ?>"> <span class="description"><?php esc_html_e('How many gallery events are shown before the Load more button reveals older events.', 'faculty-theme'); ?></span></td></tr>
                </table>
                <div id="faculty-gallery-set-list" data-repeat-list="gallery_sets">
                    <?php foreach ($gallery_sets as $index => $set) : ?>
                        <?php faculty_theme_render_gallery_set_fields($index, $set); ?>
                    <?php endforeach; ?>
                </div>
                <p>
                    <button type="button" class="button button-secondary" id="faculty-add-gallery-set"><?php esc_html_e('Add gallery event / deck', 'faculty-theme'); ?></button>
                    <button type="button" class="button" data-collapse-list="#faculty-gallery-set-list" data-collapse-action="expand"><?php esc_html_e('Expand all', 'faculty-theme'); ?></button>
                    <button type="button" class="button" data-collapse-list="#faculty-gallery-set-list" data-collapse-action="collapse"><?php esc_html_e('Collapse all', 'faculty-theme'); ?></button>
                </p>
                <?php if ($gallery_items) : ?>
                    <p class="description"><?php esc_html_e('Legacy single-image gallery entries are still preserved in the database, but the public Gallery page now displays timeline decks from the fields above.', 'faculty-theme'); ?></p>
                <?php endif; ?>
            </section>

            <section id="faculty-tab-visuals" class="faculty-theme-tab-panel" data-faculty-panel-group="#faculty-group-homepage">
                <h2><?php esc_html_e('Visual Dividers and Logos', 'faculty-theme'); ?></h2>
                <p class="faculty-settings-note"><?php esc_html_e('Use university or lab photos as quiet visual dividers between homepage sections. The fixed-background effect is disabled automatically on small screens for better mobile behavior.', 'faculty-theme'); ?></p>

                <h3><?php esc_html_e('Scrolling background picture bands', 'faculty-theme'); ?></h3>
                <div id="faculty-parallax-list" data-repeat-list="parallax_bands">
                    <?php foreach ($parallax_bands as $index => $band) : ?>
                        <?php faculty_theme_render_parallax_fields($index, $band); ?>
                    <?php endforeach; ?>
                </div>
                <p><button type="button" class="button button-secondary" id="faculty-add-parallax"><?php esc_html_e('Add background picture', 'faculty-theme'); ?></button> <button type="button" class="button" data-collapse-list="#faculty-parallax-list" data-collapse-action="expand"><?php esc_html_e('Expand all', 'faculty-theme'); ?></button> <button type="button" class="button" data-collapse-list="#faculty-parallax-list" data-collapse-action="collapse"><?php esc_html_e('Collapse all', 'faculty-theme'); ?></button></p>

                <hr>
                <h3><?php esc_html_e('Bottom logo strip', 'faculty-theme'); ?></h3>
                <table class="form-table" role="presentation">
                    <tr><th scope="row"><label for="faculty-logo-strip-title"><?php esc_html_e('Section title', 'faculty-theme'); ?></label></th><td><input class="regular-text" id="faculty-logo-strip-title" name="faculty_theme_options[logo_strip_title]" value="<?php echo esc_attr($options['logo_strip_title']); ?>"></td></tr>
                </table>
                <div id="faculty-logo-list" data-repeat-list="logo_items">
                    <?php foreach ($logo_items as $index => $logo) : ?>
                        <?php faculty_theme_render_logo_fields($index, $logo); ?>
                    <?php endforeach; ?>
                </div>
                <p><button type="button" class="button button-secondary" id="faculty-add-logo"><?php esc_html_e('Add logo', 'faculty-theme'); ?></button> <button type="button" class="button" data-collapse-list="#faculty-logo-list" data-collapse-action="expand"><?php esc_html_e('Expand all', 'faculty-theme'); ?></button> <button type="button" class="button" data-collapse-list="#faculty-logo-list" data-collapse-action="collapse"><?php esc_html_e('Collapse all', 'faculty-theme'); ?></button></p>
            </section>

            <section id="faculty-tab-footer" class="faculty-theme-tab-panel" data-faculty-panel-group="#faculty-group-footer">
                <h2><?php esc_html_e('Footer', 'faculty-theme'); ?></h2>
                <table class="form-table" role="presentation">
                    <tr><th scope="row"><label for="faculty-footer-lab-info"><?php esc_html_e('Lab address / info', 'faculty-theme'); ?></label></th><td><textarea class="large-text" rows="4" id="faculty-footer-lab-info" name="faculty_theme_options[footer_lab_info]"><?php echo esc_textarea($options['footer_lab_info']); ?></textarea><p class="description"><?php esc_html_e('Shown above the copyright in the left footer column. Basic HTML links are allowed.', 'faculty-theme'); ?></p></td></tr>
                    <tr><th scope="row"><label for="faculty-footer-text"><?php esc_html_e('Additional footer note', 'faculty-theme'); ?></label></th><td><textarea class="large-text" rows="3" id="faculty-footer-text" name="faculty_theme_options[footer_text]"><?php echo esc_textarea($options['footer_text']); ?></textarea><p class="description"><?php esc_html_e('Optional short note shown below the lab info and above the copyright.', 'faculty-theme'); ?></p></td></tr>
                </table>
            </section>

            <section id="faculty-tab-accessibility" class="faculty-theme-tab-panel" data-faculty-panel-group="#faculty-group-maintenance">
                <h2><?php esc_html_e('Accessibility Checks', 'faculty-theme'); ?></h2>
                <p class="faculty-settings-note"><?php esc_html_e('These checks look for images that may be missing meaningful text in the theme-managed areas.', 'faculty-theme'); ?></p>
                <?php if (empty($accessibility_warnings)) : ?>
                    <div class="notice notice-success inline"><p><?php esc_html_e('No missing image text warnings found in theme-managed logos, research images, sponsors, or gallery events.', 'faculty-theme'); ?></p></div>
                <?php else : ?>
                    <div class="notice notice-warning inline"><p><?php esc_html_e('Please review these items:', 'faculty-theme'); ?></p></div>
                    <ul style="list-style:disc;padding-left:1.5rem;">
                        <?php foreach ($accessibility_warnings as $warning) : ?>
                            <li><?php echo esc_html($warning); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <section id="faculty-tab-import-export" class="faculty-theme-tab-panel" data-faculty-panel-group="#faculty-group-maintenance">
                <h2><?php esc_html_e('Import / Export Settings', 'faculty-theme'); ?></h2>
                <p class="faculty-settings-note"><?php esc_html_e('Export a JSON copy before major design changes, or paste a previously exported JSON file to restore/move settings.', 'faculty-theme'); ?></p>
                <div class="faculty-import-export-grid">
                    <div class="faculty-import-export-card">
                        <h3><?php esc_html_e('Export current settings', 'faculty-theme'); ?></h3>
                        <p><?php esc_html_e('Downloads the full Faculty Theme settings payload, including colors, slides, gallery events, research page data, logos, and footer content.', 'faculty-theme'); ?></p>
                        <?php wp_nonce_field('faculty_theme_export_settings', 'faculty_theme_export_nonce'); ?>
                        <button
                            type="submit"
                            class="button button-secondary"
                            name="action"
                            value="faculty_theme_export_settings"
                            formaction="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                            formmethod="post"
                        ><?php esc_html_e('Download JSON export', 'faculty-theme'); ?></button>
                    </div>
                    <div class="faculty-import-export-card">
                        <h3><?php esc_html_e('Import settings JSON', 'faculty-theme'); ?></h3>
                        <p><?php esc_html_e('Paste JSON exported from Faculty Theme. Imported data is sanitized with the same rules as this settings screen.', 'faculty-theme'); ?></p>
                        <?php wp_nonce_field('faculty_theme_import_settings', 'faculty_theme_import_nonce'); ?>
                        <textarea class="large-text code" rows="9" name="faculty_theme_settings_json" placeholder="<?php esc_attr_e('Paste Faculty Theme JSON export here...', 'faculty-theme'); ?>"></textarea>
                        <p>
                            <button
                                type="submit"
                                class="button button-primary"
                                name="action"
                                value="faculty_theme_import_settings"
                                formaction="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                                formmethod="post"
                                onclick="return confirm('<?php echo esc_js(__('Import these settings? Current Faculty Theme settings will be replaced.', 'faculty-theme')); ?>');"
                            ><?php esc_html_e('Import JSON settings', 'faculty-theme'); ?></button>
                        </p>
                    </div>
                </div>
            </section>

            <section id="faculty-tab-help" class="faculty-theme-tab-panel" data-faculty-panel-group="#faculty-group-help">
                <h2><?php esc_html_e('Recommended content areas', 'faculty-theme'); ?></h2>
                <p><?php printf(wp_kses_post(__('Add blocks or plugin widgets to the three homepage regions under <a href="%s">Appearance > Widgets</a>. The Page Bottom region is available on regular pages.', 'faculty-theme')), esc_url(admin_url('widgets.php'))); ?></p>
                <p><?php esc_html_e('Recommended homepage pattern: concise MEDAL introduction, one optional university photo divider, a short news list, and a bottom logo strip. Keep extra widgets optional so the first page stays short and focused.', 'faculty-theme'); ?></p>
            </section>

            <?php submit_button(); ?>
        </form>

        <script type="text/html" id="faculty-slide-template">
            <?php faculty_theme_render_slide_fields('__INDEX__', array('image' => '', 'title' => '', 'text' => '', 'button_text' => '', 'button_url' => '', 'duration' => 7000, 'transition' => 'fade', 'image_fit' => 'contain', 'title_size' => '')); ?>
        </script>
        <script type="text/html" id="faculty-parallax-template">
            <?php faculty_theme_render_parallax_fields('__INDEX__', array('image' => '', 'label' => '')); ?>
        </script>
        <script type="text/html" id="faculty-logo-template">
            <?php faculty_theme_render_logo_fields('__INDEX__', array('image' => '', 'name' => '', 'url' => '')); ?>
        </script>
        <script type="text/html" id="faculty-research-area-template">
            <?php faculty_theme_render_research_area_fields('__INDEX__', array('title' => '', 'image' => '', 'text' => '', 'url' => '')); ?>
        </script>
        <script type="text/html" id="faculty-research-project-template">
            <?php faculty_theme_render_research_project_fields('__INDEX__', array('title' => '', 'sponsor' => '', 'years' => '', 'status' => 'active', 'url' => '')); ?>
        </script>
        <script type="text/html" id="faculty-research-sponsor-template">
            <?php faculty_theme_render_research_sponsor_fields('__INDEX__', array('name' => '', 'image' => '', 'url' => '')); ?>
        </script>
        <script type="text/html" id="faculty-gallery-set-template">
            <?php faculty_theme_render_gallery_set_fields('__INDEX__', array('title' => '', 'date' => '', 'description' => '', 'images' => array())); ?>
        </script>
    </div>
    <?php
}

function faculty_theme_render_slide_fields($index, $slide) {
    $slide = wp_parse_args((array) $slide, array('image' => '', 'title' => '', 'text' => '', 'button_text' => '', 'button_url' => '', 'duration' => 7000, 'transition' => 'fade', 'image_fit' => 'contain', 'title_size' => ''));
    $field_base = 'faculty_theme_options[slides][' . $index . ']';
    $image_id = 'faculty-slide-' . $index;
    $transitions = array(
        'fade'  => __('Fade', 'faculty-theme'),
        'slide' => __('Slide', 'faculty-theme'),
        'zoom'  => __('Soft zoom', 'faculty-theme'),
    );
    $image_fits = array(
        'contain' => __('Show full image', 'faculty-theme'),
        'cover'   => __('Fill slide / crop edges', 'faculty-theme'),
        'stretch' => __('Stretch to slide', 'faculty-theme'),
    );
    ?>
    <details class="faculty-slide-settings faculty-collapsible-item" data-repeat-item <?php echo is_numeric($index) ? '' : 'open'; ?>>
        <summary><span class="faculty-sort-handle" aria-hidden="true">↕</span><strong><?php echo esc_html($slide['title'] ? $slide['title'] : (is_numeric($index) ? sprintf(__('Slide %d', 'faculty-theme'), absint($index) + 1) : __('New slide', 'faculty-theme'))); ?></strong><span><?php echo esc_html($slide['image'] ? __('Image selected', 'faculty-theme') : __('No image yet', 'faculty-theme')); ?></span></summary>
        <div class="faculty-collapsible-body">
        <p><label><?php esc_html_e('Image URL', 'faculty-theme'); ?><br><input id="<?php echo esc_attr($image_id); ?>" class="large-text faculty-media-url" name="<?php echo esc_attr($field_base); ?>[image]" value="<?php echo esc_url($slide['image']); ?>"></label></p>
        <p class="faculty-slide-actions"><button type="button" class="button faculty-select-media" data-target="#<?php echo esc_attr($image_id); ?>"><?php esc_html_e('Choose image', 'faculty-theme'); ?></button> <button type="button" class="button faculty-remove-slide"><?php esc_html_e('Remove slide', 'faculty-theme'); ?></button></p>
        <p><label><?php esc_html_e('Heading', 'faculty-theme'); ?><br><input class="large-text" name="<?php echo esc_attr($field_base); ?>[title]" value="<?php echo esc_attr($slide['title']); ?>"></label></p>
        <p><label><?php esc_html_e('Summary', 'faculty-theme'); ?><br><textarea class="large-text" rows="2" name="<?php echo esc_attr($field_base); ?>[text]"><?php echo esc_textarea($slide['text']); ?></textarea></label></p>
        <p><label><?php esc_html_e('Button label', 'faculty-theme'); ?> <input name="<?php echo esc_attr($field_base); ?>[button_text]" value="<?php echo esc_attr($slide['button_text']); ?>"></label> <label><?php esc_html_e('Button URL', 'faculty-theme'); ?> <input class="regular-text" type="url" name="<?php echo esc_attr($field_base); ?>[button_url]" value="<?php echo esc_url($slide['button_url']); ?>"></label></p>
        <p>
            <label><?php esc_html_e('Title size override', 'faculty-theme'); ?> <input class="small-text" name="<?php echo esc_attr($field_base); ?>[title_size]" value="<?php echo esc_attr($slide['title_size']); ?>" placeholder="<?php esc_attr_e('Use default', 'faculty-theme'); ?>"></label>
            <label><?php esc_html_e('Timing', 'faculty-theme'); ?> <input type="number" min="2000" max="30000" step="500" name="<?php echo esc_attr($field_base); ?>[duration]" value="<?php echo esc_attr($slide['duration']); ?>"> <?php esc_html_e('ms', 'faculty-theme'); ?></label>
            <label><?php esc_html_e('Transition', 'faculty-theme'); ?> <select name="<?php echo esc_attr($field_base); ?>[transition]"><?php foreach ($transitions as $transition_key => $transition_label) : ?><option value="<?php echo esc_attr($transition_key); ?>" <?php selected($slide['transition'], $transition_key); ?>><?php echo esc_html($transition_label); ?></option><?php endforeach; ?></select></label>
            <label><?php esc_html_e('Image fit', 'faculty-theme'); ?> <select name="<?php echo esc_attr($field_base); ?>[image_fit]"><?php foreach ($image_fits as $fit_key => $fit_label) : ?><option value="<?php echo esc_attr($fit_key); ?>" <?php selected($slide['image_fit'], $fit_key); ?>><?php echo esc_html($fit_label); ?></option><?php endforeach; ?></select></label>
        </p>
        </div>
    </details>
    <?php
}

function faculty_theme_render_parallax_fields($index, $band) {
    $band = wp_parse_args((array) $band, array('image' => '', 'label' => ''));
    $field_base = 'faculty_theme_options[parallax_bands][' . $index . ']';
    $image_id = 'faculty-parallax-' . $index;
    ?>
    <details class="faculty-dynamic-item faculty-collapsible-item" data-repeat-item <?php echo is_numeric($index) ? '' : 'open'; ?>>
        <summary><span class="faculty-sort-handle" aria-hidden="true">↕</span><strong><?php echo esc_html($band['label'] ? $band['label'] : (is_numeric($index) ? sprintf(__('Background picture %d', 'faculty-theme'), absint($index) + 1) : __('New background picture', 'faculty-theme'))); ?></strong><span><?php echo esc_html($band['image'] ? __('Image selected', 'faculty-theme') : __('No image yet', 'faculty-theme')); ?></span></summary>
        <div class="faculty-collapsible-body">
        <p><label><?php esc_html_e('Image URL', 'faculty-theme'); ?><br><input id="<?php echo esc_attr($image_id); ?>" class="large-text faculty-media-url" name="<?php echo esc_attr($field_base); ?>[image]" value="<?php echo esc_url($band['image']); ?>"></label></p>
        <p class="faculty-slide-actions"><button type="button" class="button faculty-select-media" data-target="#<?php echo esc_attr($image_id); ?>"><?php esc_html_e('Choose image', 'faculty-theme'); ?></button> <button type="button" class="button faculty-remove-dynamic-item"><?php esc_html_e('Remove picture', 'faculty-theme'); ?></button></p>
        <p><label><?php esc_html_e('Accessible label / caption', 'faculty-theme'); ?><br><input class="large-text" name="<?php echo esc_attr($field_base); ?>[label]" value="<?php echo esc_attr($band['label']); ?>"></label></p>
        </div>
    </details>
    <?php
}

function faculty_theme_render_logo_fields($index, $logo) {
    $logo = wp_parse_args((array) $logo, array('image' => '', 'name' => '', 'url' => ''));
    $field_base = 'faculty_theme_options[logo_items][' . $index . ']';
    $image_id = 'faculty-logo-' . $index;
    ?>
    <details class="faculty-dynamic-item faculty-collapsible-item" data-repeat-item <?php echo is_numeric($index) ? '' : 'open'; ?>>
        <summary><span class="faculty-sort-handle" aria-hidden="true">↕</span><strong><?php echo esc_html($logo['name'] ? $logo['name'] : (is_numeric($index) ? sprintf(__('Logo %d', 'faculty-theme'), absint($index) + 1) : __('New logo', 'faculty-theme'))); ?></strong><span><?php echo esc_html($logo['image'] ? __('Logo selected', 'faculty-theme') : __('No logo yet', 'faculty-theme')); ?></span></summary>
        <div class="faculty-collapsible-body">
        <p><label><?php esc_html_e('Logo image URL', 'faculty-theme'); ?><br><input id="<?php echo esc_attr($image_id); ?>" class="large-text faculty-media-url" name="<?php echo esc_attr($field_base); ?>[image]" value="<?php echo esc_url($logo['image']); ?>"></label></p>
        <p class="faculty-slide-actions"><button type="button" class="button faculty-select-media" data-target="#<?php echo esc_attr($image_id); ?>"><?php esc_html_e('Choose logo', 'faculty-theme'); ?></button> <button type="button" class="button faculty-remove-dynamic-item"><?php esc_html_e('Remove logo', 'faculty-theme'); ?></button></p>
        <p><label><?php esc_html_e('Logo name / alt text', 'faculty-theme'); ?><br><input class="large-text" name="<?php echo esc_attr($field_base); ?>[name]" value="<?php echo esc_attr($logo['name']); ?>"></label></p>
        <p><label><?php esc_html_e('Optional link URL', 'faculty-theme'); ?><br><input class="large-text" type="url" name="<?php echo esc_attr($field_base); ?>[url]" value="<?php echo esc_url($logo['url']); ?>"></label></p>
        </div>
    </details>
    <?php
}

function faculty_theme_render_research_area_fields($index, $area) {
    $area = wp_parse_args((array) $area, array('title' => '', 'image' => '', 'text' => '', 'url' => ''));
    $field_base = 'faculty_theme_options[research_areas][' . $index . ']';
    $image_id = 'faculty-research-area-' . $index;
    ?>
    <details class="faculty-dynamic-item faculty-collapsible-item" data-repeat-item <?php echo is_numeric($index) ? '' : 'open'; ?>>
        <summary><span class="faculty-sort-handle" aria-hidden="true">↕</span><strong><?php echo esc_html($area['title'] ? $area['title'] : (is_numeric($index) ? sprintf(__('Research area %d', 'faculty-theme'), absint($index) + 1) : __('New research area', 'faculty-theme'))); ?></strong><span><?php echo esc_html($area['image'] ? __('Image selected', 'faculty-theme') : __('No image yet', 'faculty-theme')); ?></span></summary>
        <div class="faculty-collapsible-body">
        <p><label><?php esc_html_e('Title', 'faculty-theme'); ?><br><input class="large-text" name="<?php echo esc_attr($field_base); ?>[title]" value="<?php echo esc_attr($area['title']); ?>"></label></p>
        <p><label><?php esc_html_e('Image URL', 'faculty-theme'); ?><br><input id="<?php echo esc_attr($image_id); ?>" class="large-text faculty-media-url" name="<?php echo esc_attr($field_base); ?>[image]" value="<?php echo esc_url($area['image']); ?>"></label></p>
        <p class="faculty-slide-actions"><button type="button" class="button faculty-select-media" data-target="#<?php echo esc_attr($image_id); ?>"><?php esc_html_e('Choose image', 'faculty-theme'); ?></button> <button type="button" class="button faculty-remove-dynamic-item"><?php esc_html_e('Remove area', 'faculty-theme'); ?></button></p>
        <p><label><?php esc_html_e('Short description', 'faculty-theme'); ?><br><textarea class="large-text" rows="2" name="<?php echo esc_attr($field_base); ?>[text]"><?php echo esc_textarea($area['text']); ?></textarea></label></p>
        <p><label><?php esc_html_e('Optional URL', 'faculty-theme'); ?><br><input class="large-text" type="url" name="<?php echo esc_attr($field_base); ?>[url]" value="<?php echo esc_url($area['url']); ?>"></label></p>
        </div>
    </details>
    <?php
}

function faculty_theme_render_research_project_fields($index, $project) {
    $project = wp_parse_args((array) $project, array('title' => '', 'sponsor' => '', 'years' => '', 'status' => 'active', 'url' => ''));
    $field_base = 'faculty_theme_options[research_projects][' . $index . ']';
    $statuses = array(
        'active' => __('Active', 'faculty-theme'),
        'pending' => __('Pending / proposed', 'faculty-theme'),
        'paused' => __('Paused', 'faculty-theme'),
        'completed' => __('Completed', 'faculty-theme'),
    );
    ?>
    <details class="faculty-dynamic-item faculty-collapsible-item" data-repeat-item <?php echo is_numeric($index) ? '' : 'open'; ?>>
        <summary><span class="faculty-sort-handle" aria-hidden="true">↕</span><strong><?php echo esc_html($project['title'] ? $project['title'] : (is_numeric($index) ? sprintf(__('Funded project %d', 'faculty-theme'), absint($index) + 1) : __('New funded project', 'faculty-theme'))); ?></strong><span><?php echo esc_html(implode(' · ', array_filter(array($project['sponsor'], $project['years'])))); ?></span></summary>
        <div class="faculty-collapsible-body">
        <p><label><?php esc_html_e('Project title', 'faculty-theme'); ?><br><input class="large-text" name="<?php echo esc_attr($field_base); ?>[title]" value="<?php echo esc_attr($project['title']); ?>"></label></p>
        <p><label><?php esc_html_e('Sponsor', 'faculty-theme'); ?> <input name="<?php echo esc_attr($field_base); ?>[sponsor]" value="<?php echo esc_attr($project['sponsor']); ?>"></label> <label><?php esc_html_e('Years', 'faculty-theme'); ?> <input name="<?php echo esc_attr($field_base); ?>[years]" value="<?php echo esc_attr($project['years']); ?>"></label></p>
        <p><label><?php esc_html_e('Status', 'faculty-theme'); ?> <select name="<?php echo esc_attr($field_base); ?>[status]"><?php foreach ($statuses as $status_key => $status_label) : ?><option value="<?php echo esc_attr($status_key); ?>" <?php selected($project['status'], $status_key); ?>><?php echo esc_html($status_label); ?></option><?php endforeach; ?></select></label></p>
        <p><label><?php esc_html_e('Optional URL', 'faculty-theme'); ?><br><input class="large-text" type="url" name="<?php echo esc_attr($field_base); ?>[url]" value="<?php echo esc_url($project['url']); ?>"></label></p>
        <p><button type="button" class="button faculty-remove-dynamic-item"><?php esc_html_e('Remove project', 'faculty-theme'); ?></button></p>
        </div>
    </details>
    <?php
}

function faculty_theme_render_research_sponsor_fields($index, $sponsor) {
    $sponsor = wp_parse_args((array) $sponsor, array('name' => '', 'image' => '', 'url' => ''));
    $field_base = 'faculty_theme_options[research_sponsors][' . $index . ']';
    $image_id = 'faculty-research-sponsor-' . $index;
    ?>
    <details class="faculty-dynamic-item faculty-collapsible-item" data-repeat-item <?php echo is_numeric($index) ? '' : 'open'; ?>>
        <summary><span class="faculty-sort-handle" aria-hidden="true">↕</span><strong><?php echo esc_html($sponsor['name'] ? $sponsor['name'] : (is_numeric($index) ? sprintf(__('Sponsor %d', 'faculty-theme'), absint($index) + 1) : __('New sponsor', 'faculty-theme'))); ?></strong><span><?php echo esc_html($sponsor['image'] ? __('Logo selected', 'faculty-theme') : __('No logo yet', 'faculty-theme')); ?></span></summary>
        <div class="faculty-collapsible-body">
        <p><label><?php esc_html_e('Sponsor name', 'faculty-theme'); ?><br><input class="large-text" name="<?php echo esc_attr($field_base); ?>[name]" value="<?php echo esc_attr($sponsor['name']); ?>"></label></p>
        <p><label><?php esc_html_e('Logo URL', 'faculty-theme'); ?><br><input id="<?php echo esc_attr($image_id); ?>" class="large-text faculty-media-url" name="<?php echo esc_attr($field_base); ?>[image]" value="<?php echo esc_url($sponsor['image']); ?>"></label></p>
        <p class="faculty-slide-actions"><button type="button" class="button faculty-select-media" data-target="#<?php echo esc_attr($image_id); ?>"><?php esc_html_e('Choose logo', 'faculty-theme'); ?></button> <button type="button" class="button faculty-remove-dynamic-item"><?php esc_html_e('Remove sponsor', 'faculty-theme'); ?></button></p>
        <p><label><?php esc_html_e('Optional URL', 'faculty-theme'); ?><br><input class="large-text" type="url" name="<?php echo esc_attr($field_base); ?>[url]" value="<?php echo esc_url($sponsor['url']); ?>"></label></p>
        </div>
    </details>
    <?php
}

function faculty_theme_render_gallery_fields($index, $item) {
    $item = wp_parse_args((array) $item, array('image' => '', 'title' => '', 'caption' => '', 'category' => ''));
    $field_base = 'faculty_theme_options[gallery_items][' . $index . ']';
    $image_id = 'faculty-gallery-' . $index;
    ?>
    <details class="faculty-dynamic-item faculty-collapsible-item" data-repeat-item <?php echo is_numeric($index) ? '' : 'open'; ?>>
        <summary><span class="faculty-sort-handle" aria-hidden="true">↕</span><strong><?php echo esc_html($item['title'] ? $item['title'] : (is_numeric($index) ? sprintf(__('Gallery image %d', 'faculty-theme'), absint($index) + 1) : __('New gallery image', 'faculty-theme'))); ?></strong><span><?php echo esc_html($item['image'] ? __('Image selected', 'faculty-theme') : __('No image yet', 'faculty-theme')); ?></span></summary>
        <div class="faculty-collapsible-body">
        <p><label><?php esc_html_e('Image URL', 'faculty-theme'); ?><br><input id="<?php echo esc_attr($image_id); ?>" class="large-text faculty-media-url" name="<?php echo esc_attr($field_base); ?>[image]" value="<?php echo esc_url($item['image']); ?>"></label></p>
        <p class="faculty-slide-actions"><button type="button" class="button faculty-select-media" data-target="#<?php echo esc_attr($image_id); ?>"><?php esc_html_e('Choose image', 'faculty-theme'); ?></button> <button type="button" class="button faculty-remove-dynamic-item"><?php esc_html_e('Remove image', 'faculty-theme'); ?></button></p>
        <p><label><?php esc_html_e('Title', 'faculty-theme'); ?><br><input class="large-text" name="<?php echo esc_attr($field_base); ?>[title]" value="<?php echo esc_attr($item['title']); ?>"></label></p>
        <p><label><?php esc_html_e('Caption', 'faculty-theme'); ?><br><textarea class="large-text" rows="2" name="<?php echo esc_attr($field_base); ?>[caption]"><?php echo esc_textarea($item['caption']); ?></textarea></label></p>
        <p><label><?php esc_html_e('Optional category / label', 'faculty-theme'); ?><br><input class="regular-text" name="<?php echo esc_attr($field_base); ?>[category]" value="<?php echo esc_attr($item['category']); ?>"></label></p>
        </div>
    </details>
    <?php
}

function faculty_theme_render_gallery_set_fields($index, $set) {
    $set = wp_parse_args((array) $set, array('title' => '', 'date' => '', 'description' => '', 'images' => array()));
    $field_base = 'faculty_theme_options[gallery_sets][' . $index . ']';
    $images = is_array($set['images']) ? implode("\n", array_filter(array_map('esc_url', $set['images']))) : (string) $set['images'];
    $summary_title = $set['title'] ? $set['title'] : (is_numeric($index) ? sprintf(__('Gallery event %d', 'faculty-theme'), absint($index) + 1) : __('New gallery event', 'faculty-theme'));
    $summary_meta = array_filter(array($set['date'], sprintf(_n('%d photo', '%d photos', count(array_filter(preg_split('/\r\n|\r|\n/', $images))), 'faculty-theme'), count(array_filter(preg_split('/\r\n|\r|\n/', $images))))));
    ?>
    <details class="faculty-dynamic-item faculty-collapsible-item faculty-gallery-admin-set" data-repeat-item <?php echo is_numeric($index) ? '' : 'open'; ?>>
        <summary>
            <span class="faculty-sort-handle" aria-hidden="true">↕</span>
            <strong data-gallery-summary-title><?php echo esc_html($summary_title); ?></strong>
            <span data-gallery-summary-meta><?php echo esc_html(implode(' · ', $summary_meta)); ?></span>
        </summary>
        <div class="faculty-collapsible-body faculty-gallery-admin-set-body">
            <p><label><?php esc_html_e('Event / stack title', 'faculty-theme'); ?><br><input class="large-text" data-gallery-title-input name="<?php echo esc_attr($field_base); ?>[title]" value="<?php echo esc_attr($set['title']); ?>"></label></p>
            <p><label><?php esc_html_e('Event date', 'faculty-theme'); ?><br><input class="regular-text" type="date" data-gallery-date-input name="<?php echo esc_attr($field_base); ?>[date]" value="<?php echo esc_attr($set['date']); ?>"></label></p>
            <p><label><?php esc_html_e('Short description', 'faculty-theme'); ?><br><textarea class="large-text" rows="2" name="<?php echo esc_attr($field_base); ?>[description]"><?php echo esc_textarea($set['description']); ?></textarea></label></p>
            <p><label><?php esc_html_e('Photo URLs for this stack', 'faculty-theme'); ?><br><textarea id="<?php echo esc_attr('faculty-gallery-set-images-' . $index); ?>" class="large-text faculty-gallery-image-list" data-gallery-images-input rows="6" name="<?php echo esc_attr($field_base); ?>[images]" placeholder="<?php esc_attr_e('Paste one image URL per line. The first image is shown on top of the deck.', 'faculty-theme'); ?>"><?php echo esc_textarea($images); ?></textarea></label></p>
            <p><button type="button" class="button faculty-select-media" data-target="#<?php echo esc_attr('faculty-gallery-set-images-' . $index); ?>"><?php esc_html_e('Choose / add image URL', 'faculty-theme'); ?></button></p>
            <p class="description"><?php esc_html_e('Add one image URL per line. The first image is shown on top; the next few images become the visible stack underneath.', 'faculty-theme'); ?></p>
            <p><button type="button" class="button faculty-remove-dynamic-item"><?php esc_html_e('Remove gallery event', 'faculty-theme'); ?></button></p>
        </div>
    </details>
    <?php
}

function faculty_theme_customizer_css() {
    $options = faculty_theme_get_options();
    $css_vars = array();
    $var_map = array(
        'accent' => '--faculty-accent',
        'accent_dark' => '--faculty-accent-dark',
        'ink' => '--faculty-ink',
        'body_text' => '--faculty-body',
        'muted' => '--faculty-muted',
        'line' => '--faculty-line',
        'soft' => '--faculty-soft',
        'surface' => '--faculty-surface',
        'nav_bg' => '--faculty-nav-bg',
        'nav_bg_dark' => '--faculty-nav-bg-dark',
        'footer_bg' => '--faculty-footer-bg',
        'gallery_timeline' => '--faculty-gallery-timeline',
        'gallery_card_accent' => '--faculty-gallery-card-accent',
        'vacancy_accent' => '--faculty-vacancy-accent',
    );

    foreach ($var_map as $option_key => $css_var) {
        $value = sanitize_hex_color(isset($options[$option_key]) ? $options[$option_key] : '');
        if ($value) {
            $css_vars[] = $css_var . ':' . $value;
        }
    }

    $page_hero_image = faculty_theme_get_page_hero_image();
    $page_hero_title_size = faculty_theme_get_option('page_hero_title_size', '4rem');
    $hero_css = '';

    if ($page_hero_image) {
        $hero_css .= '.faculty-academic-route .academic-route-hero{background-image:url("' . esc_url($page_hero_image) . '") !important;--faculty-page-title-size:' . esc_html($page_hero_title_size) . ';}';
    }

    echo '<style id="faculty-theme-customizer-css">:root{' . esc_html(implode(';', $css_vars)) . ';}' . $hero_css . '</style>';
}
add_action('wp_head', 'faculty_theme_customizer_css');

function faculty_theme_body_classes($classes) {
    if (get_query_var('academic_directory_home') || get_query_var('academic_student_id') || get_query_var('academic_pi_profile') || get_query_var('academic_profile_edit')) {
        $classes[] = 'faculty-academic-route';
    }
    return $classes;
}
add_filter('body_class', 'faculty_theme_body_classes');

function faculty_theme_get_meta_description() {
    if (is_front_page()) {
        $intro = faculty_theme_get_option('intro_text', '');
        return $intro ? wp_trim_words(wp_strip_all_tags($intro), 32, '') : get_bloginfo('description');
    }

    if (is_singular()) {
        $post = get_post();
        if ($post) {
            $excerpt = has_excerpt($post) ? get_the_excerpt($post) : $post->post_content;
            return wp_trim_words(wp_strip_all_tags(strip_shortcodes($excerpt)), 32, '');
        }
    }

    if (is_archive()) {
        return wp_trim_words(wp_strip_all_tags(get_the_archive_description()), 32, '');
    }

    return get_bloginfo('description');
}

function faculty_theme_get_meta_image() {
    if (is_singular() && has_post_thumbnail()) {
        return get_the_post_thumbnail_url(get_the_ID(), 'large');
    }

    $hero = faculty_theme_get_page_hero_image();
    if ($hero) {
        return $hero;
    }

    $brand_logo = faculty_theme_get_option('brand_logo', '');
    return $brand_logo ? faculty_theme_normalize_media_url($brand_logo) : '';
}

function faculty_theme_render_seo_meta() {
    if (is_admin() || is_404() || get_query_var('academic_directory_home') || get_query_var('academic_student_id') || get_query_var('academic_pi_profile') || get_query_var('academic_profile_edit')) {
        return;
    }

    $title = wp_get_document_title();
    $description = faculty_theme_get_meta_description();
    $description = $description ? $description : get_bloginfo('description');
    $url = is_singular() ? get_permalink() : home_url(add_query_arg(array(), $GLOBALS['wp']->request ?? ''));
    $image = faculty_theme_get_meta_image();
    $type = is_singular('post') ? 'article' : 'website';
    ?>
    <meta name="description" content="<?php echo esc_attr($description); ?>">
    <meta property="og:type" content="<?php echo esc_attr($type); ?>">
    <meta property="og:title" content="<?php echo esc_attr($title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($description); ?>">
    <meta property="og:url" content="<?php echo esc_url($url); ?>">
    <meta name="twitter:card" content="<?php echo $image ? 'summary_large_image' : 'summary'; ?>">
    <meta name="twitter:title" content="<?php echo esc_attr($title); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($description); ?>">
    <?php if ($image) : ?>
        <meta property="og:image" content="<?php echo esc_url($image); ?>">
        <meta name="twitter:image" content="<?php echo esc_url($image); ?>">
    <?php endif; ?>
    <?php
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => get_bloginfo('name'),
        'url' => home_url('/'),
    );
    if ($image) {
        $schema['logo'] = $image;
    }
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}
add_action('wp_head', 'faculty_theme_render_seo_meta', 4);

function faculty_theme_fallback_menu() {
    echo '<ul class="menu">';
    wp_list_pages(array('title_li' => '', 'depth' => 2));
    echo '</ul>';
}
