<?php
/** Page template. @package Faculty_Theme */
get_header();
while (have_posts()) : the_post();
?>
<?php faculty_theme_page_header(get_the_title()); ?>
<div class="container content-layout content-layout--full">
    <main id="primary" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <?php if (has_post_thumbnail()) : ?><div class="post-thumbnail"><?php the_post_thumbnail('large'); ?></div><?php endif; ?>
            <div class="entry-content"><?php the_content(); wp_link_pages(); ?></div>
        </article>
        <?php if (comments_open() || get_comments_number()) : comments_template(); endif; ?>
    </main>
</div>
<?php if (is_active_sidebar('page-bottom')) : ?><div class="page-bottom-region"><div class="container"><?php dynamic_sidebar('page-bottom'); ?></div></div><?php endif; ?>
<?php endwhile; get_footer(); ?>
