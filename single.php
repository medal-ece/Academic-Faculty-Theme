<?php
/** Single post template. @package Faculty_Theme */
get_header();
while (have_posts()) : the_post();
?>
<?php faculty_theme_page_header(get_the_title()); ?>
<div class="container faculty-single-layout">
    <main id="primary" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class('faculty-single-post'); ?>>
            <header class="faculty-single-meta">
                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                <?php if (has_category()) : ?>
                    <div class="faculty-news-categories"><?php the_category(' '); ?></div>
                <?php endif; ?>
            </header>
            <?php if (has_post_thumbnail()) : ?>
                <div class="faculty-single-thumbnail"><?php the_post_thumbnail('large'); ?></div>
            <?php endif; ?>
            <div class="entry-content"><?php the_content(); wp_link_pages(); ?></div>
            <?php if (has_tag()) : ?>
                <footer class="faculty-single-tags"><?php the_tags('', ' ', ''); ?></footer>
            <?php endif; ?>
        </article>
        <div class="faculty-single-actions">
            <?php
            $posts_page_id = (int) get_option('page_for_posts');
            if ($posts_page_id) :
                ?>
                <a class="faculty-back-to-news" href="<?php echo esc_url(get_permalink($posts_page_id)); ?>"><?php esc_html_e('Back to News', 'faculty-theme'); ?></a>
            <?php endif; ?>
            <?php
            the_post_navigation(array(
                'prev_text' => '<span>' . esc_html__('Previous', 'faculty-theme') . '</span>%title',
                'next_text' => '<span>' . esc_html__('Next', 'faculty-theme') . '</span>%title',
            ));
            ?>
        </div>
        <?php if (comments_open() || get_comments_number()) : comments_template(); endif; ?>
    </main>
</div>
<?php endwhile; get_footer(); ?>
