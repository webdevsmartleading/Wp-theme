<?php
/**
 * Hero section template.
 *
 * Available variables: $section_id, $section_type, $settings.
 */

if (! defined('ABSPATH')) {
    exit;
}

$style = sprintf(
    '--sls-section-bg:%s;--sls-section-text:%s;',
    esc_attr($settings['background_color']),
    esc_attr($settings['text_color'])
);
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="sls-section sls-hero"
    style="<?php echo esc_attr($style); ?>"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="sls-container sls-hero__grid">
        <div class="sls-hero__content">
            <?php if (! empty($settings['eyebrow'])) : ?>
                <p class="sls-section__eyebrow"><?php echo esc_html($settings['eyebrow']); ?></p>
            <?php endif; ?>

            <?php if (! empty($settings['heading'])) : ?>
                <h1 id="<?php echo esc_attr($section_id); ?>-heading"><?php echo esc_html($settings['heading']); ?></h1>
            <?php endif; ?>

            <?php if (! empty($settings['content'])) : ?>
                <div class="sls-hero__text">
                    <?php echo wpautop(wp_kses_post($settings['content'])); ?>
                </div>
            <?php endif; ?>

            <?php if (! empty($settings['button_label']) && ! empty($settings['button_url'])) : ?>
                <a class="sls-button" href="<?php echo esc_url($settings['button_url']); ?>">
                    <?php echo esc_html($settings['button_label']); ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="sls-hero__visual" aria-hidden="<?php echo empty($settings['image']) ? 'true' : 'false'; ?>">
            <?php if (! empty($settings['image'])) : ?>
                <?php sls_render_attachment_image($settings['image'], $settings['image_alt'], 'large', 'sls-hero__image', 'eager'); ?>
            <?php else : ?>
                <div class="sls-hero__placeholder">
                    <span>JSON</span>
                    <span>PHP</span>
                    <span>CSS</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
