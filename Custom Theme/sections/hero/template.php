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
    '--sls-section-bg:%s;--sls-section-text:%s;--sls-section-accent:%s;',
    esc_attr($settings['background_color']),
    esc_attr($settings['text_color']),
    esc_attr($settings['accent_color'])
);
$has_image = ! empty($settings['image']);
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="sls-section sls-hero <?php echo $has_image ? 'sls-hero--has-media' : 'sls-hero--has-placeholder'; ?>"
    style="<?php echo esc_attr($style); ?>"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="sls-container sls-hero__grid">
        <div class="sls-hero__content">
            <?php if (sls_has_text_value($settings['eyebrow'])) : ?>
                <p class="sls-section__eyebrow"><?php sls_render_html_setting($settings['eyebrow']); ?></p>
            <?php endif; ?>

            <?php if (sls_has_text_value($settings['heading'])) : ?>
                <h1 id="<?php echo esc_attr($section_id); ?>-heading"><?php sls_render_html_setting($settings['heading']); ?></h1>
            <?php endif; ?>

            <?php if (sls_has_text_value($settings['content'])) : ?>
                <div class="sls-hero__text">
                    <?php sls_render_html_setting($settings['content'], true); ?>
                </div>
            <?php endif; ?>

            <?php if (sls_has_text_value($settings['button_label']) && ! empty($settings['button_url'])) : ?>
                <a class="sls-button" href="<?php echo esc_url($settings['button_url']); ?>">
                    <?php sls_render_html_setting($settings['button_label']); ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="sls-hero__visual">
            <?php if ($has_image) : ?>
                <?php sls_render_attachment_image($settings['image'], $settings['image_alt'], 'large', 'sls-hero__image', 'eager'); ?>
            <?php else : ?>
                <?php sls_render_placeholder_visual(__('Hero image placeholder', 'sls-theme')); ?>
            <?php endif; ?>
        </div>
    </div>
</section>
