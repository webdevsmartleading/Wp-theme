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
));
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="sls-section sls-about"
    style="<?php echo esc_attr('--sls-section-bg:' . $settings['background_color'] . ';'); ?>"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="sls-container sls-about__grid">
        <div class="sls-about__media">
            <?php if (! empty($settings['image'])) : ?>
                <?php sls_render_attachment_image($settings['image'], $settings['image_alt'], 'large', 'sls-about__image'); ?>
            <?php else : ?>
                <div class="sls-about__system-card" aria-label="<?php esc_attr_e('Framework architecture preview', 'sls-theme'); ?>">
                    <span>Global CSS</span>
                    <span>Section JSON</span>
                    <span>Template PHP</span>
                    <span>Headless REST</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="sls-about__content">
            <?php if (! empty($settings['eyebrow'])) : ?>
                <p class="sls-section__eyebrow"><?php echo esc_html($settings['eyebrow']); ?></p>
            <?php endif; ?>

            <?php if (! empty($settings['heading'])) : ?>
                <h2 id="<?php echo esc_attr($section_id); ?>-heading"><?php echo esc_html($settings['heading']); ?></h2>
            <?php endif; ?>

            <?php if (! empty($settings['content'])) : ?>
                <div class="sls-about__text">
                    <?php echo wpautop(wp_kses_post($settings['content'])); ?>
                </div>
            <?php endif; ?>

            <?php if ($features) : ?>
                <ul class="sls-about__features" aria-label="<?php esc_attr_e('Framework benefits', 'sls-theme'); ?>">
                    <?php foreach ($features as $feature) : ?>
                        <li><?php echo esc_html($feature); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</section>
