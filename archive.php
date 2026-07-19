<?php
/** Archive template. @package Faculty_Theme */
get_header();
$archive_title = get_the_archive_title();
if (is_category()) {
    $archive_title = single_cat_title('', false);
} elseif (is_tag()) {
    $archive_title = single_tag_title('', false);
} elseif (is_author()) {
    $archive_title = __('Author Archive', 'faculty-theme');
} elseif (is_post_type_archive()) {
    $archive_title = post_type_archive_title('', false);
}
?>
<?php faculty_theme_page_header($archive_title, get_the_archive_description()); ?>
<?php if (is_category() || is_tag() || is_date()) : ?>
    <main id="primary" class="site-main faculty-news-page">
        <div class="container">
            <?php if (have_posts()) : ?>
                <div class="faculty-news-index">
                    <?php while (have_posts()) : the_post(); ?>
                        <article <?php post_class('faculty-news-index-item'); ?>>
                            <a class="faculty-news-index-link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(sprintf(__('Read %s', 'faculty-theme'), get_the_title())); ?>"></a>
                            <div class="faculty-news-index-image" aria-hidden="true">
                                <?php if (has_post_thumbnail()) : the_post_thumbnail('medium_large'); else : ?><span><?php esc_html_e('News', 'faculty-theme'); ?></span><?php endif; ?>
                            </div>
                            <div class="faculty-news-index-body">
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                                <h2><?php the_title(); ?></h2>
                                <div class="faculty-news-categories"><?php the_category(' '); ?></div>
                                <div class="faculty-news-excerpt"><?php the_excerpt(); ?></div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                <?php the_posts_pagination(); ?>
            <?php else : ?>
                <?php get_template_part('template-parts/content', 'none'); ?>
            <?php endif; ?>
        </div>
    </main>
<?php else : ?>
    <div class="container content-layout"><main id="primary" class="site-main">
    <?php if (have_posts()) : while (have_posts()) : the_post(); get_template_part('template-parts/content', get_post_type()); endwhile; the_posts_pagination(); else : get_template_part('template-parts/content', 'none'); endif; ?>
    </main><?php get_sidebar(); ?></div>
<?php endif; ?>
<?php get_footer(); ?>
