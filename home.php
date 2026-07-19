<?php
/**
 * Posts page used as the site News page.
 *
 * @package Faculty_Theme
 */

get_header();
$posts_page_id = (int) get_option('page_for_posts');
$title = $posts_page_id ? get_the_title($posts_page_id) : __('News', 'faculty-theme');
?>
<?php faculty_theme_page_header($title); ?>
<main id="primary" class="site-main faculty-news-page">
    <div class="container">
        <?php faculty_theme_render_news_filter_form(); ?>
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
<?php get_footer(); ?>
