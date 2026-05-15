<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="sls-container site-header__inner">
        <a class="site-branding" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <span><?php bloginfo('name'); ?></span>
            <?php endif; ?>
        </a>

        <nav class="primary-navigation" aria-label="<?php esc_attr_e('Primary menu', 'sls-theme'); ?>">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container'      => false,
                'fallback_cb'    => false,
                'depth'          => 1,
            ));
            ?>
        </nav>
    </div>
</header>
