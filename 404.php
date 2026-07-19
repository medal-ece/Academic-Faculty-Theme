<?php
/** 404 template. @package Faculty_Theme */
get_header();
?>
<?php faculty_theme_page_header(__('Page not found', 'faculty-theme')); ?>
<div class="container content-layout content-layout--full"><main id="primary" class="site-main"><div class="entry-content"><p><?php esc_html_e('The page may have moved or no longer exists. Try searching the site.', 'faculty-theme'); ?></p><?php get_search_form(); ?></div></main></div>
<?php get_footer(); ?>
