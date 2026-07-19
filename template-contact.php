<?php
/**
 * Template Name: Faculty Contact
 *
 * @package Faculty_Theme
 */

get_header();
$options = faculty_theme_get_options();
?>
<?php faculty_theme_page_header(get_the_title()); ?>
<main id="primary" class="site-main faculty-page-template">
    <section class="faculty-contact-section">
        <div class="container faculty-contact-grid">
            <div class="faculty-contact-copy">
                <?php if ($options['contact_intro']) : ?><div class="entry-content"><?php echo wp_kses_post(wpautop($options['contact_intro'])); ?></div><?php endif; ?>
                <dl class="faculty-contact-list">
                    <?php if ($options['contact_address']) : ?><div><dt><?php esc_html_e('Address', 'faculty-theme'); ?></dt><dd><?php echo wp_kses_post(wpautop($options['contact_address'])); ?></dd></div><?php endif; ?>
                    <?php if ($options['contact_email']) : ?><div><dt><?php esc_html_e('Email', 'faculty-theme'); ?></dt><dd><a href="mailto:<?php echo esc_attr($options['contact_email']); ?>"><?php echo esc_html($options['contact_email']); ?></a></dd></div><?php endif; ?>
                    <?php if ($options['contact_phone']) : ?><div><dt><?php esc_html_e('Phone', 'faculty-theme'); ?></dt><dd><?php echo esc_html($options['contact_phone']); ?></dd></div><?php endif; ?>
                </dl>
            </div>
            <div class="faculty-contact-map">
                <?php if ($options['contact_map_embed']) : ?>
                    <?php echo $options['contact_map_embed']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php else : ?>
                    <p><?php esc_html_e('Add a map embed under Faculty Theme > Contact.', 'faculty-theme'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<?php get_footer(); ?>
