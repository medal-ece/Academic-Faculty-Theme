<?php
/**
 * Site footer.
 *
 * @package Faculty_Theme
 */
?>
    </div>
    <footer id="colophon" class="site-footer">
        <?php if (is_active_sidebar('footer-1') || is_active_sidebar('footer-2') || is_active_sidebar('footer-3')) : ?>
            <div class="container footer-widgets">
                <?php for ($column = 1; $column <= 3; $column++) : ?>
                    <div class="footer-column"><?php dynamic_sidebar('footer-' . $column); ?></div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
        <div class="footer-bottom">
            <div class="container footer-bottom-inner">
                <div class="footer-left">
                    <?php $footer_lab_info = faculty_theme_get_option('footer_lab_info', ''); ?>
                    <?php if ($footer_lab_info) : ?><div class="footer-lab-info"><?php echo wp_kses_post(wpautop($footer_lab_info)); ?></div><?php endif; ?>
                    <?php $footer_text = faculty_theme_get_option('footer_text', ''); ?>
                    <?php if ($footer_text) : ?><div class="footer-custom"><?php echo wp_kses_post(wpautop($footer_text)); ?></div><?php endif; ?>
                    <p class="footer-copyright">&copy; <?php echo esc_html(wp_date('Y')); ?> <?php bloginfo('name'); ?></p>
                </div>
                <div class="footer-right">
                    <?php wp_nav_menu(array('theme_location' => 'footer', 'container' => 'nav', 'container_aria_label' => __('Footer navigation', 'faculty-theme'), 'depth' => 1, 'fallback_cb' => false)); ?>
                </div>
            </div>
        </div>
    </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
