<?php
/** Sidebar. @package Faculty_Theme */
if (!is_active_sidebar('sidebar-1')) { return; }
?>
<aside id="secondary" class="sidebar" aria-label="<?php esc_attr_e('Sidebar', 'faculty-theme'); ?>"><?php dynamic_sidebar('sidebar-1'); ?></aside>
