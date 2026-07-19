<?php
/**
 * Comments template.
 *
 * @package Faculty_Theme
 */

if (post_password_required()) {
    return;
}
?>

<section id="comments" class="faculty-comments">
    <?php if (have_comments()) : ?>
        <h2 class="faculty-comments-title">
            <?php
            printf(
                esc_html(_n('%d comment', '%d comments', get_comments_number(), 'faculty-theme')),
                (int) get_comments_number()
            );
            ?>
        </h2>

        <ol class="faculty-comment-list">
            <?php
            wp_list_comments(array(
                'style'      => 'ol',
                'short_ping' => true,
                'avatar_size' => 48,
            ));
            ?>
        </ol>

        <?php the_comments_navigation(); ?>
    <?php endif; ?>

    <?php if (!comments_open() && get_comments_number()) : ?>
        <p class="faculty-no-comments"><?php esc_html_e('Comments are closed.', 'faculty-theme'); ?></p>
    <?php endif; ?>

    <?php
    comment_form(array(
        'class_form' => 'faculty-comment-form',
        'title_reply_before' => '<h2 id="reply-title" class="comment-reply-title">',
        'title_reply_after' => '</h2>',
        'submit_button' => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
    ));
    ?>
</section>
