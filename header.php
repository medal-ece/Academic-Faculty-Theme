<?php
/**
 * Site header.
 *
 * @package Faculty_Theme
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'faculty-theme'); ?></a>
    <header id="masthead" class="site-header">
        <div class="branding-bar">
            <div class="container branding-inner">
                <div class="site-branding">
                    <?php $brand_logo = faculty_theme_normalize_media_url(faculty_theme_get_option('brand_logo', '')); ?>
                    <?php if ($brand_logo) : ?>
                        <a class="custom-logo-link" href="<?php echo esc_url(home_url('/')); ?>" rel="home"><img class="custom-logo" src="<?php echo esc_url($brand_logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>"></a>
                    <?php elseif (has_custom_logo()) : the_custom_logo(); endif; ?>
                    <div class="site-identity">
                        <?php $eyebrow = faculty_theme_get_option('eyebrow', __('The University of Utah', 'faculty-theme')); ?>
                        <?php if ($eyebrow) : ?><p class="brand-eyebrow"><?php echo esc_html($eyebrow); ?></p><?php endif; ?>
                        <?php if (is_front_page() && is_home()) : ?>
                            <h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></h1>
                        <?php else : ?>
                            <p class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></p>
                        <?php endif; ?>
                        <?php $description = get_bloginfo('description', 'display'); ?>
                        <?php if ($description) : ?><p class="site-description"><?php echo esc_html($description); ?></p><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="nav-bar">
            <div class="container nav-inner">
                <button class="menu-toggle" type="button" aria-controls="primary-menu" aria-expanded="false">
                    <span aria-hidden="true">☰</span> <?php esc_html_e('Menu', 'faculty-theme'); ?>
                </button>
                <nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e('Primary navigation', 'faculty-theme'); ?>">
                    <?php wp_nav_menu(array('theme_location' => 'primary', 'menu_id' => 'primary-menu', 'container' => false, 'fallback_cb' => 'faculty_theme_fallback_menu')); ?>
                </nav>
            </div>
        </div>
    </header>
    <div id="content" class="site-content">
