<?php
/**
 * About section template.
 *
 * Available variables: $section_id, $section_type, $settings.
 */

if (! defined('ABSPATH')) {
    exit;
}

$features = array_filter(array(
    $settings['feature_one'] ?? '',
    $settings['feature_two'] ?? '',
    $settings['feature_three'] ?? '',
), 'sls_has_text_value');
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
    class="sls-section sls-about <?php echo $has_image ? 'sls-about--has-media' : 'sls-about--has-placeholder'; ?>"
    style="<?php echo esc_attr($style); ?>"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="sls-container sls-about__grid">
        <div class="sls-about__media">
            <?php if ($has_image) : ?>
                <?php sls_render_attachment_image($settings['image'], $settings['image_alt'], 'large', 'sls-about__image'); ?>
            <?php else : ?>
                <?php sls_render_placeholder_visual(__('About image placeholder', 'sls-theme')); ?>
            <?php endif; ?>
        </div>

        <div class="sls-about__content">
            <?php if (sls_has_text_value($settings['eyebrow'])) : ?>
                <p class="sls-section__eyebrow"><?php sls_render_html_setting($settings['eyebrow']); ?></p>
            <?php endif; ?>

            <?php if (sls_has_text_value($settings['heading'])) : ?>
                <h2 id="<?php echo esc_attr($section_id); ?>-heading"><?php sls_render_html_setting($settings['heading']); ?></h2>
            <?php endif; ?>

            <?php if (sls_has_text_value($settings['content'])) : ?>
                <div class="sls-about__text">
                    <?php sls_render_html_setting($settings['content'], true); ?>
                </div>
            <?php endif; ?>

            <?php if ($features) : ?>
                <ul class="sls-about__features" aria-label="<?php esc_attr_e('Framework benefits', 'sls-theme'); ?>">
                    <?php foreach ($features as $feature) : ?>
                        <li><?php sls_render_html_setting($feature); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</section>
