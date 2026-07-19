<?php
/**
 * Focused homepage for the MEDAL research group.
 *
 * @package Faculty_Theme
 */

get_header();

$options = faculty_theme_get_options();
$slides = array_values(array_filter((array) $options['slides'], function ($slide) {
    return !empty($slide['image']) || !empty($slide['title']) || !empty($slide['text']);
}));
$slide_font_class = 'faculty-slide-font-' . sanitize_html_class($options['slide_font']);
$parallax_bands = array_values(array_filter((array) $options['parallax_bands'], function ($band) {
    return !empty($band['image']);
}));
$logo_items = array_values(array_filter((array) $options['logo_items'], function ($logo) {
    return !empty($logo['image']);
}));

if (!function_exists('faculty_theme_home_parallax_band')) {
    function faculty_theme_home_parallax_band($band, $index) {
        if (empty($band['image'])) {
            return;
        }
        $label = !empty($band['label']) ? $band['label'] : sprintf(__('University image %d', 'faculty-theme'), $index + 1);
        ?>
        <section class="home-photo-band" aria-label="<?php echo esc_attr($label); ?>" style="background-image:url('<?php echo esc_url(faculty_theme_normalize_media_url($band['image'])); ?>')">
            <span class="screen-reader-text"><?php echo esc_html($label); ?></span>
        </section>
        <?php
    }
}
?>

<?php if ('1' === $options['show_intro']) : ?>
<section class="medal-intro" aria-labelledby="medal-intro-title">
    <div class="container medal-intro-grid">
        <div class="medal-intro-copy">
            <?php if ($options['intro_eyebrow']) : ?><p class="section-kicker"><?php echo esc_html($options['intro_eyebrow']); ?></p><?php endif; ?>
            <?php if ($options['intro_title']) : ?><h1 id="medal-intro-title"><?php echo esc_html($options['intro_title']); ?></h1><?php endif; ?>
            <?php if ($options['intro_subtitle']) : ?><p class="medal-intro-subtitle"><?php echo esc_html($options['intro_subtitle']); ?></p><?php endif; ?>
            <?php if ($options['intro_text']) : ?><div class="medal-intro-text"><?php echo wp_kses_post(wpautop($options['intro_text'])); ?></div><?php endif; ?>
            <?php if ($options['intro_button_text'] && $options['intro_button_url']) : ?><a class="faculty-button" href="<?php echo esc_url($options['intro_button_url']); ?>"><?php echo esc_html($options['intro_button_text']); ?></a><?php endif; ?>
        </div>
        <div class="medal-intro-visual" aria-hidden="<?php echo $options['intro_image'] ? 'false' : 'true'; ?>">
            <?php if ($options['intro_image']) : ?>
                <img src="<?php echo esc_url(faculty_theme_normalize_media_url($options['intro_image'])); ?>" alt="<?php echo esc_attr($options['intro_title'] ? $options['intro_title'] : get_bloginfo('name')); ?>">
            <?php else : ?>
                <div class="medal-monogram">MEDAL</div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($parallax_bands[0])) : faculty_theme_home_parallax_band($parallax_bands[0], 0); endif; ?>

<?php if ('1' === $options['show_slideshow'] && $slides) : ?>
<section class="faculty-hero <?php echo esc_attr($slide_font_class); ?>" style="--faculty-slide-title-size:<?php echo esc_attr(!empty($options['slide_title_size']) ? $options['slide_title_size'] : '4.2rem'); ?>;" aria-roledescription="carousel" aria-label="<?php esc_attr_e('Featured content', 'faculty-theme'); ?>" data-slider data-transition="<?php echo esc_attr(!empty($slides[0]['transition']) ? $slides[0]['transition'] : 'fade'); ?>">
    <div class="faculty-slides" aria-live="off">
        <?php foreach ($slides as $index => $slide) : ?>
            <article class="faculty-slide faculty-slide-fit-<?php echo esc_attr(!empty($slide['image_fit']) ? sanitize_html_class($slide['image_fit']) : 'contain'); ?><?php echo 0 === $index ? ' is-active' : ''; ?>" data-slide data-duration="<?php echo esc_attr(!empty($slide['duration']) ? absint($slide['duration']) : absint($options['slide_default_duration'])); ?>" data-transition="<?php echo esc_attr(!empty($slide['transition']) ? $slide['transition'] : 'fade'); ?>" aria-hidden="<?php echo 0 === $index ? 'false' : 'true'; ?>"<?php echo 0 === $index ? '' : ' hidden'; ?>>
                <?php if ($slide['image']) : ?><img class="faculty-slide-image" src="<?php echo esc_url(faculty_theme_normalize_media_url($slide['image'])); ?>" alt="<?php echo esc_attr(!empty($slide['title']) ? $slide['title'] : get_bloginfo('name')); ?>" loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>"><?php endif; ?>
                <div class="faculty-slide-shade"></div>
                <div class="container faculty-slide-content"<?php echo !empty($slide['title_size']) ? ' style="--faculty-slide-title-size:' . esc_attr($slide['title_size']) . ';"' : ''; ?>>
                    <?php if ($slide['title']) : ?><h2><?php echo esc_html($slide['title']); ?></h2><?php endif; ?>
                    <?php if ($slide['text']) : ?><p><?php echo esc_html($slide['text']); ?></p><?php endif; ?>
                    <?php if ($slide['button_text'] && $slide['button_url']) : ?><a class="faculty-button" href="<?php echo esc_url($slide['button_url']); ?>"><?php echo esc_html($slide['button_text']); ?></a><?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if (count($slides) > 1) : ?>
        <div class="container faculty-slider-controls">
            <button type="button" data-slide-prev aria-label="<?php esc_attr_e('Previous slide', 'faculty-theme'); ?>">&#8592;</button>
            <button type="button" data-slide-toggle aria-pressed="false"><?php esc_html_e('Pause', 'faculty-theme'); ?></button>
            <button type="button" data-slide-next aria-label="<?php esc_attr_e('Next slide', 'faculty-theme'); ?>">&#8594;</button>
        </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if ('1' === $options['show_news']) :
    $news_args = array('post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => $options['news_count'], 'ignore_sticky_posts' => true);
    if ($options['news_category']) {
        $news_args['cat'] = $options['news_category'];
    }
    $news = new WP_Query($news_args);
    if ($news->have_posts()) : ?>
    <section class="home-news home-news-list-section"><div class="container">
        <div class="home-section-heading"><h2><?php echo esc_html($options['news_title']); ?></h2><?php if ($options['news_archive_url']) : ?><a href="<?php echo esc_url($options['news_archive_url']); ?>"><?php esc_html_e('More news', 'faculty-theme'); ?> &#8594;</a><?php endif; ?></div>
        <ul class="home-news-list">
            <?php while ($news->have_posts()) : $news->the_post(); ?>
                <li <?php post_class('home-news-list-item'); ?>>
                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div></section>
    <?php endif; wp_reset_postdata(); ?>
<?php endif; ?>

<?php if (!empty($parallax_bands[1])) : faculty_theme_home_parallax_band($parallax_bands[1], 1); endif; ?>

<?php if ('1' === $options['show_gadgets'] && (is_active_sidebar('home-gadget-1') || is_active_sidebar('home-gadget-2') || is_active_sidebar('home-gadget-3'))) : ?>
<section class="home-gadgets" aria-label="<?php esc_attr_e('Featured resources', 'faculty-theme'); ?>"><div class="container home-gadget-grid">
    <?php for ($region = 1; $region <= 3; $region++) : ?><div class="home-gadget-column"><?php dynamic_sidebar('home-gadget-' . $region); ?></div><?php endfor; ?>
</div></section>
<?php endif; ?>

<?php if ('1' === $options['show_logo_strip'] && $logo_items) : ?>
<section class="home-logo-strip" aria-labelledby="home-logo-strip-title">
    <div class="container">
        <?php if ($options['logo_strip_title']) : ?><h2 id="home-logo-strip-title"><?php echo esc_html($options['logo_strip_title']); ?></h2><?php endif; ?>
        <ul class="home-logo-list">
            <?php foreach ($logo_items as $logo) : ?>
                <li>
                    <?php if (!empty($logo['url'])) : ?><a href="<?php echo esc_url($logo['url']); ?>"><?php endif; ?>
                    <img src="<?php echo esc_url(faculty_theme_normalize_media_url($logo['image'])); ?>" alt="<?php echo esc_attr($logo['name']); ?>" loading="lazy" decoding="async">
                    <?php if (!empty($logo['url'])) : ?></a><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($parallax_bands[2])) : faculty_theme_home_parallax_band($parallax_bands[2], 2); endif; ?>

<?php get_footer(); ?>
