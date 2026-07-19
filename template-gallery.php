<?php
/**
 * Template Name: Faculty Gallery
 *
 * @package Faculty_Theme
 */

get_header();
$options = faculty_theme_get_options();
$items = array_values((array) $options['gallery_items']);
$sets = array_values((array) $options['gallery_sets']);
$gallery_batch_size = !empty($options['gallery_batch_size']) ? max(1, absint($options['gallery_batch_size'])) : 6;

usort($sets, function ($a, $b) {
    $date_a = !empty($a['date']) ? strtotime($a['date']) : 0;
    $date_b = !empty($b['date']) ? strtotime($b['date']) : 0;

    if ($date_a === $date_b) {
        return 0;
    }

    return $date_a < $date_b ? 1 : -1;
});

if (!$sets && $items) {
    foreach ($items as $item) {
        $sets[] = array(
            'title' => !empty($item['title']) ? $item['title'] : (!empty($item['category']) ? $item['category'] : __('Gallery photos', 'faculty-theme')),
            'date' => !empty($item['category']) ? $item['category'] : '',
            'description' => !empty($item['caption']) ? $item['caption'] : '',
            'images' => !empty($item['image']) ? array($item['image']) : array(),
        );
    }
}

$gallery_years = array();
foreach ($sets as $set) {
    if (!empty($set['date'])) {
        $timestamp = strtotime($set['date']);
        if ($timestamp) {
            $gallery_years[] = date_i18n('Y', $timestamp);
        }
    }
}
$gallery_years = array_values(array_unique(array_filter($gallery_years)));
rsort($gallery_years);
?>
<?php faculty_theme_page_header(get_the_title()); ?>
<main id="primary" class="site-main faculty-page-template faculty-gallery-page">
    <div class="container">
        <?php if (!empty($options['gallery_intro'])) : ?>
            <section class="faculty-gallery-intro entry-content">
                <?php echo wp_kses_post(wpautop($options['gallery_intro'])); ?>
            </section>
        <?php endif; ?>

        <?php if ($sets) : ?>
            <div class="faculty-filter-bar faculty-gallery-filters" data-gallery-filters>
                <label>
                    <span><?php esc_html_e('Search gallery', 'faculty-theme'); ?></span>
                    <input type="search" data-gallery-search placeholder="<?php esc_attr_e('Search event titles or descriptions', 'faculty-theme'); ?>">
                </label>
                <?php if ($gallery_years) : ?>
                    <label>
                        <span><?php esc_html_e('Year', 'faculty-theme'); ?></span>
                        <select data-gallery-year>
                            <option value=""><?php esc_html_e('All years', 'faculty-theme'); ?></option>
                            <?php foreach ($gallery_years as $gallery_year) : ?>
                                <option value="<?php echo esc_attr($gallery_year); ?>"><?php echo esc_html($gallery_year); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                <?php endif; ?>
            </div>
            <p class="faculty-filter-empty" data-gallery-empty hidden><?php esc_html_e('No gallery events match your search.', 'faculty-theme'); ?></p>
            <section class="faculty-gallery-timeline" data-gallery-batch-size="<?php echo esc_attr($gallery_batch_size); ?>" aria-label="<?php esc_attr_e('Timeline gallery', 'faculty-theme'); ?>">
                <?php $rendered_index = 0; ?>
                <?php foreach ($sets as $set) : ?>
                    <?php
                    $images = !empty($set['images']) && is_array($set['images']) ? array_values(array_filter($set['images'])) : array();
                    if (!$images) {
                        continue;
                    }
                    $event_title = !empty($set['title']) ? $set['title'] : __('Gallery event', 'faculty-theme');
                    $gallery_images = array();
                    foreach ($images as $image_url) {
                        $gallery_images[] = faculty_theme_get_gallery_image_data($image_url, $event_title);
                    }
                    $visible_images = array_slice($gallery_images, 0, 5);
                    $modal_images = array_map(
                        function ($image_data) {
                            return array(
                                'src' => $image_data['full'],
                                'alt' => $image_data['alt'],
                            );
                        },
                        $gallery_images
                    );
                    $is_initially_hidden = $rendered_index >= $gallery_batch_size;
                    $event_year = '';
                    if (!empty($set['date']) && strtotime($set['date'])) {
                        $event_year = date_i18n('Y', strtotime($set['date']));
                    }
                    $event_search = trim($event_title . ' ' . (!empty($set['description']) ? $set['description'] : '') . ' ' . (!empty($set['date']) ? $set['date'] : ''));
                    ?>
                    <article class="faculty-gallery-event" data-gallery-event data-gallery-loaded="<?php echo $is_initially_hidden ? '0' : '1'; ?>" data-gallery-year="<?php echo esc_attr($event_year); ?>" data-gallery-search="<?php echo esc_attr(wp_strip_all_tags($event_search)); ?>" <?php echo $is_initially_hidden ? 'hidden' : ''; ?>>
                        <div class="faculty-gallery-event-meta">
                            <?php if (!empty($set['date'])) : ?><time datetime="<?php echo esc_attr($set['date']); ?>"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($set['date']))); ?></time><?php endif; ?>
                            <?php if (!empty($set['title'])) : ?><h2><?php echo esc_html($set['title']); ?></h2><?php endif; ?>
                            <?php if (!empty($set['description'])) : ?><p><?php echo esc_html($set['description']); ?></p><?php endif; ?>
                            <span class="faculty-gallery-count"><?php echo esc_html(sprintf(_n('%d photo', '%d photos', count($images), 'faculty-theme'), count($images))); ?></span>
                        </div>
                        <button type="button" class="faculty-gallery-deck" data-gallery-title="<?php echo esc_attr($event_title); ?>" data-gallery-images="<?php echo esc_attr(wp_json_encode(array_values($modal_images))); ?>" aria-label="<?php echo esc_attr(sprintf(__('Open photo stack: %s', 'faculty-theme'), $event_title)); ?>">
                            <?php foreach (array_reverse($visible_images, true) as $image_index => $image_data) : ?>
                                <span class="faculty-gallery-photo faculty-gallery-photo-<?php echo esc_attr($image_index); ?>" style="--photo-index: <?php echo esc_attr($image_index); ?>;">
                                    <img src="<?php echo esc_url($image_data['preview']); ?>" <?php echo !empty($image_data['srcset']) ? 'srcset="' . esc_attr($image_data['srcset']) . '"' : ''; ?> <?php echo !empty($image_data['sizes']) ? 'sizes="' . esc_attr($image_data['sizes']) . '"' : ''; ?> alt="<?php echo esc_attr($image_data['alt']); ?>" loading="lazy" decoding="async">
                                </span>
                            <?php endforeach; ?>
                        </button>
                    </article>
                    <?php $rendered_index++; ?>
                <?php endforeach; ?>
            </section>
            <?php if ($rendered_index > $gallery_batch_size) : ?>
                <p class="faculty-gallery-load-more-wrap">
                    <button type="button" class="faculty-gallery-load-more" data-gallery-load-more>
                        <?php esc_html_e('Load more gallery events', 'faculty-theme'); ?>
                    </button>
                </p>
            <?php endif; ?>
        <?php else : ?>
            <section class="entry-content">
                <p><?php esc_html_e('No gallery images have been added yet.', 'faculty-theme'); ?></p>
            </section>
        <?php endif; ?>
    </div>
    <div class="faculty-gallery-modal" data-gallery-modal hidden>
        <div class="faculty-gallery-modal-backdrop" data-gallery-close></div>
        <div class="faculty-gallery-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="faculty-gallery-modal-title">
            <button type="button" class="faculty-gallery-modal-close" data-gallery-close aria-label="<?php esc_attr_e('Close gallery viewer', 'faculty-theme'); ?>">×</button>
            <h2 id="faculty-gallery-modal-title" data-gallery-modal-title></h2>
            <div class="faculty-gallery-modal-stage">
                <button type="button" class="faculty-gallery-modal-nav faculty-gallery-modal-prev" data-gallery-prev aria-label="<?php esc_attr_e('Previous photo', 'faculty-theme'); ?>">←</button>
                <img src="" alt="" decoding="async" data-gallery-modal-image>
                <button type="button" class="faculty-gallery-modal-nav faculty-gallery-modal-next" data-gallery-next aria-label="<?php esc_attr_e('Next photo', 'faculty-theme'); ?>">→</button>
            </div>
            <p class="faculty-gallery-modal-count" data-gallery-modal-count></p>
        </div>
    </div>
</main>
<?php get_footer(); ?>
