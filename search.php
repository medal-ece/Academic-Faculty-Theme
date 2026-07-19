<?php
/** Search results template. @package Faculty_Theme */
get_header();
$is_news_search = isset($_GET['post_type']) && 'post' === sanitize_key(wp_unslash($_GET['post_type']));
$title = $is_news_search ? __('News Search', 'faculty-theme') : sprintf(__('Search results for: %s', 'faculty-theme'), get_search_query());
?>
<?php faculty_theme_page_header($title); ?>
<?php if ($is_news_search) : ?>
    <main id="primary" class="site-main faculty-news-page">
        <div class="container">
            <?php faculty_theme_render_news_filter_form(home_url('/')); ?>
            <?php if (have_posts()) : ?>
                <p class="faculty-search-summary">
                    <?php echo esc_html(sprintf(_n('%d news result found.', '%d news results found.', (int) $wp_query->found_posts, 'faculty-theme'), (int) $wp_query->found_posts)); ?>
                </p>
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
                <section class="entry-content faculty-filter-empty">
                    <p><?php esc_html_e('No news posts match your search. Try another keyword or category.', 'faculty-theme'); ?></p>
                </section>
            <?php endif; ?>
        </div>
    </main>
<?php else : ?>
    <div class="container content-layout"><main id="primary" class="site-main">
    <?php if (have_posts()) : while (have_posts()) : the_post(); get_template_part('template-parts/content', 'search'); endwhile; the_posts_pagination(); else : get_template_part('template-parts/content', 'none'); endif; ?>
    </main><?php get_sidebar(); ?></div>
<?php endif; ?>
<?php get_footer(); ?>
