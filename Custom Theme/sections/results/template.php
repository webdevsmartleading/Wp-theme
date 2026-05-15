<?php
/**
 * Results section template.
 *
 * Available variables: $section_id, $section_type, $settings.
 */

if (! defined('ABSPATH')) {
    exit;
}

$results = array(
    array(
        'value' => $settings['result_one_value'] ?? '',
        'label' => $settings['result_one_label'] ?? '',
    ),
    array(
        'value' => $settings['result_two_value'] ?? '',
        'label' => $settings['result_two_label'] ?? '',
    ),
    array(
        'value' => $settings['result_three_value'] ?? '',
        'label' => $settings['result_three_label'] ?? '',
    ),
);
$style = sprintf(
    '--sls-section-bg:%s;--sls-section-text:%s;--sls-section-accent:%s;',
    esc_attr($settings['background_color']),
    esc_attr($settings['text_color']),
    esc_attr($settings['accent_color'])
);
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="sls-section sls-results"
    style="<?php echo esc_attr($style); ?>"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="sls-container sls-results__grid">
        <div class="sls-results__content">
            <?php if (sls_has_text_value($settings['eyebrow'])) : ?>
                <p class="sls-section__eyebrow"><?php sls_render_html_setting($settings['eyebrow']); ?></p>
            <?php endif; ?>

            <?php if (sls_has_text_value($settings['heading'])) : ?>
                <h2 id="<?php echo esc_attr($section_id); ?>-heading"><?php sls_render_html_setting($settings['heading']); ?></h2>
            <?php endif; ?>

            <?php if (sls_has_text_value($settings['content'])) : ?>
                <div class="sls-results__text">
                    <?php sls_render_html_setting($settings['content'], true); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="sls-results__metrics">
            <?php foreach ($results as $result) : ?>
                <?php if (! sls_has_text_value($result['value']) && ! sls_has_text_value($result['label'])) : ?>
                    <?php continue; ?>
                <?php endif; ?>

                <article class="sls-results__metric">
                    <?php if (sls_has_text_value($result['value'])) : ?>
                        <strong><?php sls_render_html_setting($result['value']); ?></strong>
                    <?php endif; ?>

                    <?php if (sls_has_text_value($result['label'])) : ?>
                        <span><?php sls_render_html_setting($result['label']); ?></span>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
