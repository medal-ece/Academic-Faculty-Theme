<?php
/**
 * Main template.
 *
 * @package Faculty_Theme
 */
if (is_home() && is_front_page()) {
    require get_template_directory() . '/front-page.php';
    return;
}

get_header();
?>
<?php faculty_theme_page_header(is_home() && !is_front_page() ? get_the_title(get_option('page_for_posts')) : __('News & Updates', 'faculty-theme')); ?>
<div class="container content-layout">
    <main id="primary" class="site-main">
        <?php if (have_posts()) : while (have_posts()) : the_post(); get_template_part('template-parts/content', get_post_type()); endwhile; the_posts_pagination(); else : get_template_part('template-parts/content', 'none'); endif; ?>
    </main>
    <?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>
