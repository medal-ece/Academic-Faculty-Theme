<?php
/**
 * Template Name: Faculty Research
 *
 * @package Faculty_Theme
 */

get_header();
$options = faculty_theme_get_options();
$areas = array_values((array) $options['research_areas']);
$projects = array_values((array) $options['research_projects']);
$sponsors = array_values((array) $options['research_sponsors']);
$project_statuses = array(
    'active' => __('Active', 'faculty-theme'),
    'completed' => __('Completed', 'faculty-theme'),
    'paused' => __('Paused', 'faculty-theme'),
    'pending' => __('Pending', 'faculty-theme'),
);
?>
<?php faculty_theme_page_header(get_the_title()); ?>
<main id="primary" class="site-main faculty-page-template faculty-research-page">
    <?php if ($options['research_intro']) : ?>
        <section class="faculty-research-intro">
            <div class="container">
                <div class="faculty-research-intro-card entry-content"><?php echo wp_kses_post(wpautop($options['research_intro'])); ?></div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($areas) : ?>
        <section class="faculty-research-areas"><div class="container">
            <h2><?php esc_html_e('Research Areas', 'faculty-theme'); ?></h2>
            <div class="faculty-research-grid">
                <?php foreach ($areas as $area) : ?>
                    <article class="faculty-research-card">
                        <?php if (!empty($area['image'])) : ?><img src="<?php echo esc_url($area['image']); ?>" alt="<?php echo esc_attr(!empty($area['title']) ? $area['title'] : __('Research area image', 'faculty-theme')); ?>" loading="lazy" decoding="async"><?php endif; ?>
                        <div class="faculty-research-card-body">
                            <?php if (!empty($area['title'])) : ?><h3><?php echo esc_html($area['title']); ?></h3><?php endif; ?>
                            <?php if (!empty($area['text'])) : ?><p><?php echo esc_html($area['text']); ?></p><?php endif; ?>
                            <?php if (!empty($area['url'])) : ?><a href="<?php echo esc_url($area['url']); ?>"><?php esc_html_e('Learn more', 'faculty-theme'); ?></a><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div></section>
    <?php endif; ?>

    <?php if ($projects) : ?>
        <section class="faculty-funded-projects"><div class="container">
            <h2><?php esc_html_e('Funded Research Projects', 'faculty-theme'); ?></h2>
            <div class="faculty-filter-bar faculty-project-filters" data-project-filters>
                <label>
                    <span><?php esc_html_e('Search projects', 'faculty-theme'); ?></span>
                    <input type="search" data-project-search placeholder="<?php esc_attr_e('Search title, sponsor, or years', 'faculty-theme'); ?>">
                </label>
                <label>
                    <span><?php esc_html_e('Status', 'faculty-theme'); ?></span>
                    <select data-project-status>
                        <option value=""><?php esc_html_e('All statuses', 'faculty-theme'); ?></option>
                        <?php foreach ($project_statuses as $project_status_key => $project_status_label) : ?>
                            <option value="<?php echo esc_attr($project_status_key); ?>"><?php echo esc_html($project_status_label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <p class="faculty-filter-empty" data-project-empty hidden><?php esc_html_e('No projects match your filters.', 'faculty-theme'); ?></p>
            <ul class="faculty-project-list" data-project-list>
                <?php foreach ($projects as $project) : ?>
                    <?php
                    $status = !empty($project['status']) && isset($project_statuses[$project['status']]) ? $project['status'] : 'active';
                    $project_search = trim(
                        (!empty($project['title']) ? $project['title'] : '') . ' ' .
                        (!empty($project['sponsor']) ? $project['sponsor'] : '') . ' ' .
                        (!empty($project['years']) ? $project['years'] : '') . ' ' .
                        $project_statuses[$status]
                    );
                    ?>
                    <li data-project-item data-project-status="<?php echo esc_attr($status); ?>" data-project-search="<?php echo esc_attr(wp_strip_all_tags($project_search)); ?>">
                        <?php if (!empty($project['url'])) : ?><a href="<?php echo esc_url($project['url']); ?>"><?php endif; ?>
                        <span class="faculty-project-title"><?php echo esc_html($project['title']); ?></span>
                        <?php if (!empty($project['url'])) : ?></a><?php endif; ?>
                        <?php if (!empty($project['sponsor']) || !empty($project['years']) || !empty($status)) : ?>
                            <span class="faculty-project-meta">
                                <span class="faculty-project-status faculty-project-status-<?php echo esc_attr($status); ?>"><?php echo esc_html($project_statuses[$status]); ?></span>
                                <?php if (!empty($project['sponsor'])) : ?><span class="faculty-project-sponsor"><?php echo esc_html($project['sponsor']); ?></span><?php endif; ?>
                                <?php if (!empty($project['years'])) : ?><span class="faculty-project-years"><?php echo esc_html($project['years']); ?></span><?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div></section>
    <?php endif; ?>

    <?php if ($sponsors) : ?>
        <section class="faculty-research-sponsors"><div class="container">
            <h2><?php esc_html_e('Sponsors', 'faculty-theme'); ?></h2>
            <ul class="faculty-sponsor-list">
                <?php foreach ($sponsors as $sponsor) : ?>
                    <li>
                        <?php if (!empty($sponsor['url'])) : ?><a href="<?php echo esc_url($sponsor['url']); ?>"><?php endif; ?>
                        <?php if (!empty($sponsor['image'])) : ?><img src="<?php echo esc_url($sponsor['image']); ?>" alt="<?php echo esc_attr($sponsor['name']); ?>" loading="lazy" decoding="async"><?php else : ?><span><?php echo esc_html($sponsor['name']); ?></span><?php endif; ?>
                        <?php if (!empty($sponsor['url'])) : ?></a><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div></section>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
