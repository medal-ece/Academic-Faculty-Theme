<?php
/** Post summary. @package Faculty_Theme */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?>>
    <?php if (has_post_thumbnail()) : ?><a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1"><?php the_post_thumbnail('large'); ?></a><?php endif; ?>
    <header class="entry-header"><h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><div class="entry-meta"><?php echo esc_html(get_the_date()); ?></div></header>
    <div class="entry-summary"><?php the_excerpt(); ?></div>
</article>
