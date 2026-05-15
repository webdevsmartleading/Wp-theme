<?php
/**
 * Lightweight Shopify-style section registry, renderer, and REST output.
 */

if (! defined('ABSPATH')) {
    exit;
}

const SLS_SECTION_META_KEY = '_sls_page_sections';

function sls_get_section_registry() {
    static $registry = null;

    if (null !== $registry) {
        return $registry;
    }

    $registry = array();
    $schema_files = glob(SLS_THEME_PATH . '/sections/*/section.json');

    if (! is_array($schema_files)) {
        return $registry;
    }

    foreach ($schema_files as $schema_file) {
        $slug = basename(dirname($schema_file));
        $schema = json_decode((string) file_get_contents($schema_file), true);

        if (! is_array($schema)) {
            continue;
        }

        $schema['slug'] = sanitize_key($schema['slug'] ?? $slug);
        $schema['name'] = sanitize_text_field($schema['name'] ?? ucfirst($slug));
        $schema['description'] = sanitize_text_field($schema['description'] ?? '');
        $schema['fields'] = is_array($schema['fields'] ?? null) ? $schema['fields'] : array();
        $schema['fields']['custom_css'] = array(
            'label'       => __('Custom CSS for this section', 'sls-theme'),
            'type'        => 'css',
            'default'     => '',
            'placeholder' => '.section-title { color: #2458ff; }',
        );
        $schema['assets'] = is_array($schema['assets'] ?? null) ? $schema['assets'] : array();
        $schema['order'] = absint($schema['order'] ?? 100);
        $schema['path'] = dirname($schema_file);
        $schema['uri'] = SLS_THEME_URI . '/sections/' . $schema['slug'];

        $registry[$schema['slug']] = $schema;
    }

    uasort(
        $registry,
        function ($first, $second) {
            if ($first['order'] === $second['order']) {
                return strcmp($first['name'], $second['name']);
            }

            return $first['order'] <=> $second['order'];
        }
    );

    return $registry;
}

function sls_get_field_default($field) {
    if (! is_array($field) || ! array_key_exists('default', $field)) {
        return '';
    }

    return $field['default'];
}

function sls_get_section_defaults($type) {
    $registry = sls_get_section_registry();

    if (! isset($registry[$type])) {
        return array();
    }

    $defaults = array();

    foreach ($registry[$type]['fields'] as $field_key => $field) {
        $defaults[sanitize_key($field_key)] = sls_get_field_default($field);
    }

    return $defaults;
}

function sls_get_default_sections() {
    return array(
        array(
            'id'       => 'hero-demo',
            'type'     => 'hero',
            'settings' => sls_get_section_defaults('hero'),
        ),
        array(
            'id'       => 'usb-demo',
            'type'     => 'usb',
            'settings' => sls_get_section_defaults('usb'),
        ),
        array(
            'id'       => 'results-demo',
            'type'     => 'results',
            'settings' => sls_get_section_defaults('results'),
        ),
        array(
            'id'       => 'about-demo',
            'type'     => 'about',
            'settings' => sls_get_section_defaults('about'),
        ),
    );
}

function sls_sanitize_section_css($css) {
    $css = is_scalar($css) ? (string) $css : '';
    $css = preg_replace('#</?style[^>]*>#i', '', $css);

    return trim(wp_strip_all_tags($css));
}

function sls_sanitize_section_setting($value, $field) {
    $type = isset($field['type']) ? sanitize_key($field['type']) : 'text';
    $default = sls_get_field_default($field);

    switch ($type) {
        case 'color':
            $value = is_scalar($value) ? (string) $value : '';
            return sanitize_hex_color($value) ?: $default;

        case 'image':
            return absint($value);

        case 'url':
            $value = is_scalar($value) ? (string) $value : '';
            return esc_url_raw($value);

        case 'textarea':
            $value = is_scalar($value) ? (string) $value : '';
            return wp_kses_post($value);

        case 'css':
            return sls_sanitize_section_css($value);

        case 'plain_text':
            $value = is_scalar($value) ? (string) $value : '';
            return sanitize_text_field($value);

        case 'select':
            $options = is_array($field['options'] ?? null) ? $field['options'] : array();
            $value = is_scalar($value) ? (string) $value : '';
            $key = sanitize_key($value);
            return array_key_exists($key, $options) ? $key : $default;

        case 'number':
            return is_numeric($value) ? (float) $value : $default;

        case 'text':
        default:
            $value = is_scalar($value) ? (string) $value : '';
            return wp_kses_post($value);
    }
}

function sls_normalize_sections($sections) {
    $registry = sls_get_section_registry();
    $normalized = array();

    if (! is_array($sections)) {
        return $normalized;
    }

    foreach ($sections as $index => $section) {
        if (! is_array($section)) {
            continue;
        }

        $type = sanitize_key($section['type'] ?? '');

        if (! isset($registry[$type])) {
            continue;
        }

        $raw_settings = is_array($section['settings'] ?? null) ? $section['settings'] : array();
        $settings = sls_get_section_defaults($type);

        foreach ($registry[$type]['fields'] as $field_key => $field) {
            $safe_key = sanitize_key($field_key);

            if (array_key_exists($safe_key, $raw_settings)) {
                $settings[$safe_key] = sls_sanitize_section_setting(wp_unslash($raw_settings[$safe_key]), $field);
            }
        }

        $id = sanitize_key($section['id'] ?? '');

        if ('' === $id) {
            $id = $type . '-' . ($index + 1);
        }

        $normalized[] = array(
            'id'       => $id,
            'type'     => $type,
            'settings' => $settings,
        );
    }

    return $normalized;
}

function sls_decode_sections_json($json) {
    if (is_array($json)) {
        return $json;
    }

    if (! is_string($json) || '' === trim($json)) {
        return array();
    }

    $decoded = json_decode(wp_unslash($json), true);

    return is_array($decoded) ? $decoded : array();
}

function sls_get_page_sections($post_id = null) {
    $post_id = $post_id ? absint($post_id) : absint(get_queried_object_id());

    if ($post_id && metadata_exists('post', $post_id, SLS_SECTION_META_KEY)) {
        return sls_normalize_sections(sls_decode_sections_json(get_post_meta($post_id, SLS_SECTION_META_KEY, true)));
    }

    return array();
}

function sls_get_active_sections() {
    if (is_singular('page') || is_front_page()) {
        return sls_get_page_sections();
    }

    return array();
}

function sls_enqueue_section_assets() {
    $registry = sls_get_section_registry();
    $active_types = array();

    foreach (sls_get_active_sections() as $section) {
        $type = $section['type'];

        if (! isset($registry[$type]) || ! sls_section_has_visible_content($section, $registry[$type])) {
            continue;
        }

        $active_types[$type] = true;
    }

    foreach (array_keys($active_types) as $type) {
        if (! isset($registry[$type])) {
            continue;
        }

        $schema = $registry[$type];
        $style = $schema['assets']['style'] ?? '';
        $script = $schema['assets']['script'] ?? '';

        if ($style && file_exists($schema['path'] . '/' . $style)) {
            wp_enqueue_style(
                'sls-section-' . $type,
                $schema['uri'] . '/' . $style,
                array('sls-global'),
                filemtime($schema['path'] . '/' . $style)
            );
        }

        if ($script && file_exists($schema['path'] . '/' . $script)) {
            wp_enqueue_script(
                'sls-section-' . $type,
                $schema['uri'] . '/' . $script,
                array(),
                filemtime($schema['path'] . '/' . $script),
                true
            );
            wp_script_add_data('sls-section-' . $type, 'defer', true);
        }
    }
}
add_action('wp_enqueue_scripts', 'sls_enqueue_section_assets', 20);

function sls_render_attachment_image($attachment_id, $alt = '', $size = 'large', $class = '', $loading = 'lazy') {
    $attachment_id = absint($attachment_id);

    if (! $attachment_id) {
        return;
    }

    echo wp_get_attachment_image(
        $attachment_id,
        $size,
        false,
        array(
            'class'    => $class,
            'alt'      => $alt,
            'loading'  => $loading,
            'decoding' => 'async',
        )
    );
}

function sls_has_text_value($value) {
    if (! is_scalar($value)) {
        return false;
    }

    return '' !== trim(wp_kses_post((string) $value));
}

function sls_section_has_visible_content($section, $schema) {
    $settings = is_array($section['settings'] ?? null) ? $section['settings'] : array();

    foreach ($schema['fields'] as $field_key => $field) {
        $field_type = isset($field['type']) ? sanitize_key($field['type']) : 'text';
        $safe_key = sanitize_key($field_key);
        $value = $settings[$safe_key] ?? '';

        if (in_array($safe_key, array('custom_css', 'image_alt'), true)) {
            continue;
        }

        if ('button_label' === $safe_key && empty($settings['button_url'])) {
            continue;
        }

        if (in_array($field_type, array('color', 'css', 'plain_text', 'select', 'number', 'url'), true)) {
            continue;
        }

        if ('image' === $field_type && absint($value)) {
            return true;
        }

        if (in_array($field_type, array('text', 'textarea'), true) && sls_has_text_value($value)) {
            return true;
        }
    }

    return false;
}

function sls_render_html_setting($value, $autop = false) {
    if (! sls_has_text_value($value)) {
        return;
    }

    $html = wp_kses_post((string) $value);

    echo $autop ? wpautop($html) : $html;
}

function sls_scope_css_selectors($selectors, $scope) {
    $scoped = array();

    foreach (explode(',', $selectors) as $selector) {
        $selector = trim($selector);

        if ('' === $selector) {
            continue;
        }

        if (0 === strpos($selector, '&')) {
            $scoped[] = $scope . substr($selector, 1);
            continue;
        }

        if (0 === strpos($selector, $scope)) {
            $scoped[] = $selector;
            continue;
        }

        $scoped[] = $scope . ' ' . $selector;
    }

    return implode(', ', $scoped);
}

function sls_scope_custom_css($css, $section_id) {
    $css = sls_sanitize_section_css($css);

    if ('' === $css) {
        return '';
    }

    $scope = '#' . sanitize_html_class($section_id);

    if (false === strpos($css, '{')) {
        return $scope . " {\n" . $css . "\n}";
    }

    return preg_replace_callback(
        '/(^|[{}])\s*([^@{}][^{}]*)\s*\{/',
        function ($matches) use ($scope) {
            return $matches[1] . ' ' . sls_scope_css_selectors($matches[2], $scope) . ' {';
        },
        $css
    );
}

function sls_render_scoped_custom_css($section_id, $settings) {
    $custom_css = sls_scope_custom_css($settings['custom_css'] ?? '', $section_id);

    if ('' === $custom_css) {
        return;
    }

    printf(
        '<style id="%1$s">%2$s</style>',
        esc_attr('sls-custom-css-' . $section_id),
        $custom_css
    );
}

function sls_render_page_sections($sections = null) {
    $sections = null === $sections ? sls_get_page_sections() : sls_normalize_sections($sections);
    $registry = sls_get_section_registry();

    foreach ($sections as $section) {
        $section_type = $section['type'];
        $section_id = $section['id'];
        $settings = $section['settings'];

        if (! isset($registry[$section_type])) {
            continue;
        }

        if (! sls_section_has_visible_content($section, $registry[$section_type])) {
            continue;
        }

        $template = $registry[$section_type]['path'] . '/template.php';

        if (file_exists($template)) {
            sls_render_scoped_custom_css($section_id, $settings);
            include $template;
        }
    }
}

function sls_register_sections_rest_field() {
    register_rest_field(
        'page',
        'sls_sections',
        array(
            'get_callback' => function ($post) {
                return sls_get_page_sections($post['id']);
            },
            'schema'       => array(
                'description' => __('Modular page sections for headless frontends.', 'sls-theme'),
                'type'        => 'array',
                'context'     => array('view', 'edit'),
            ),
        )
    );
}
add_action('rest_api_init', 'sls_register_sections_rest_field');
