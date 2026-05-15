<?php

function simple_theme_setup() {

    add_theme_support('title-tag');

    add_theme_support('post-thumbnails');

    register_nav_menus(array(
        'primary' => __('Primary Menu')
    ));
}

add_action('after_setup_theme', 'simple_theme_setup');


function simple_theme_assets() {

    wp_enqueue_style(
        'simple-style',
        get_stylesheet_uri(),
        array(),
        '1.0'
    );

}

add_action('wp_enqueue_scripts', 'simple_theme_assets');
