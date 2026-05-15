<?php
/**
 * Global theme setup.
 */

if (! defined('ABSPATH')) {
    exit;
}

function sls_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('responsive-embeds');
    add_theme_support('custom-logo', array(
        'height'      => 80,
        'width'       => 240,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'sls-theme'),
    ));
}
add_action('after_setup_theme', 'sls_theme_setup');

function sls_enqueue_global_assets() {
    wp_enqueue_style(
        'sls-global',
        SLS_THEME_URI . '/assets/css/global.css',
        array(),
        filemtime(SLS_THEME_PATH . '/assets/css/global.css')
    );
}
add_action('wp_enqueue_scripts', 'sls_enqueue_global_assets', 5);
