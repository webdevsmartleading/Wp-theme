<?php
/**
 * Theme bootstrap.
 *
 * Keeps WordPress hooks small and delegates framework behavior to focused files.
 */

if (! defined('ABSPATH')) {
    exit;
}

define('SLS_THEME_VERSION', '1.1.0');
define('SLS_THEME_PATH', get_template_directory());
define('SLS_THEME_URI', get_template_directory_uri());

require_once SLS_THEME_PATH . '/inc/setup.php';
require_once SLS_THEME_PATH . '/inc/section-engine.php';
require_once SLS_THEME_PATH . '/inc/admin-section-builder.php';
