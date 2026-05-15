<?php
/**
 * USB section template.
 *
 * Available variables: $section_id, $section_type, $settings.
 */

if (! defined('ABSPATH')) {
    exit;
}

$benefits = array(
    array(
        'title' => $settings['benefit_one_title'] ?? '',
        'text'  => $settings['benefit_one_text'] ?? '',
    ),
    array(
        'title' => $settings['benefit_two_title'] ?? '',
        'text'  => $settings['benefit_two_text'] ?? '',
    ),
    array(
        'title' => $settings['benefit_three_title'] ?? '',
        'text'  => $settings['benefit_three_text'] ?? '',
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
    class="sls-section sls-usb"
    style="<?php echo esc_attr($style); ?>"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="sls-container">
        <div class="sls-usb__header">
            <?php if (sls_has_text_value($settings['eyebrow'])) : ?>
                <p class="sls-section__eyebrow"><?php sls_render_html_setting($settings['eyebrow']); ?></p>
            <?php endif; ?>

            <?php if (sls_has_text_value($settings['heading'])) : ?>
                <h2 id="<?php echo esc_attr($section_id); ?>-heading"><?php sls_render_html_setting($settings['heading']); ?></h2>
            <?php endif; ?>

            <?php if (sls_has_text_value($settings['content'])) : ?>
                <div class="sls-usb__intro">
                    <?php sls_render_html_setting($settings['content'], true); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="sls-usb__grid">
            <?php foreach ($benefits as $index => $benefit) : ?>
                <?php if (! sls_has_text_value($benefit['title']) && ! sls_has_text_value($benefit['text'])) : ?>
                    <?php continue; ?>
                <?php endif; ?>

                <article class="sls-usb__card">
                    <span class="sls-usb__number"><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>

                    <?php if (sls_has_text_value($benefit['title'])) : ?>
                        <h3><?php sls_render_html_setting($benefit['title']); ?></h3>
                    <?php endif; ?>

                    <?php if (sls_has_text_value($benefit['text'])) : ?>
                        <div class="sls-usb__text">
                            <?php sls_render_html_setting($benefit['text'], true); ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
