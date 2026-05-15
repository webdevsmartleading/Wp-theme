<?php
/**
 * Low-code page section editor for the WordPress admin.
 */

if (! defined('ABSPATH')) {
    exit;
}

function sls_add_section_builder_metabox() {
    add_meta_box(
        'sls-section-builder',
        __('Page Sections', 'sls-theme'),
        'sls_render_section_builder_metabox',
        'page',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'sls_add_section_builder_metabox');

function sls_prepare_registry_for_admin() {
    $registry = sls_get_section_registry();
    $prepared = array();

    foreach ($registry as $slug => $schema) {
        $fields = array();

        foreach ($schema['fields'] as $field_key => $field) {
            $safe_key = sanitize_key($field_key);
            $fields[$safe_key] = array(
                'label'       => sanitize_text_field($field['label'] ?? ucfirst($safe_key)),
                'type'        => sanitize_key($field['type'] ?? 'text'),
                'default'     => sls_get_field_default($field),
                'placeholder' => sanitize_text_field($field['placeholder'] ?? ''),
                'options'     => is_array($field['options'] ?? null) ? $field['options'] : array(),
            );
        }

        $prepared[$slug] = array(
            'slug'        => $slug,
            'name'        => $schema['name'],
            'description' => $schema['description'],
            'fields'      => $fields,
        );
    }

    return $prepared;
}

function sls_enqueue_admin_section_builder($hook) {
    if (! in_array($hook, array('post.php', 'post-new.php'), true)) {
        return;
    }

    $screen = get_current_screen();

    if (! $screen || 'page' !== $screen->post_type) {
        return;
    }

    wp_enqueue_media();

    wp_enqueue_style(
        'sls-admin-section-builder',
        SLS_THEME_URI . '/assets/css/admin-section-builder.css',
        array(),
        filemtime(SLS_THEME_PATH . '/assets/css/admin-section-builder.css')
    );

    wp_enqueue_script(
        'sls-admin-section-builder',
        SLS_THEME_URI . '/assets/js/admin-section-builder.js',
        array(),
        filemtime(SLS_THEME_PATH . '/assets/js/admin-section-builder.js'),
        true
    );

    $post_id = isset($_GET['post']) ? absint(wp_unslash($_GET['post'])) : 0;

    wp_localize_script(
        'sls-admin-section-builder',
        'SLSSectionBuilder',
        array(
            'schemas'     => sls_prepare_registry_for_admin(),
            'sections'    => sls_get_page_sections($post_id),
            'mediaTitle'  => __('Select image', 'sls-theme'),
            'mediaButton' => __('Use this image', 'sls-theme'),
            'i18n'        => array(
                'addSection' => __('Add section', 'sls-theme'),
                'empty'      => __('No sections added yet. Add Hero or About to build this page.', 'sls-theme'),
                'remove'     => __('Remove', 'sls-theme'),
                'moveUp'     => __('Move up', 'sls-theme'),
                'moveDown'   => __('Move down', 'sls-theme'),
                'choose'     => __('Choose image', 'sls-theme'),
                'clear'      => __('Clear', 'sls-theme'),
            ),
        )
    );
}
add_action('admin_enqueue_scripts', 'sls_enqueue_admin_section_builder');

function sls_render_section_builder_metabox($post) {
    wp_nonce_field('sls_save_sections', 'sls_sections_nonce');

    $sections = sls_get_page_sections($post->ID);
    ?>
    <p class="sls-section-builder-help">
        <?php esc_html_e('Add, reorder, and edit lightweight reusable page sections. Each section keeps its own template and assets for faster frontend output.', 'sls-theme'); ?>
    </p>
    <input
        type="hidden"
        id="sls-page-sections"
        name="sls_page_sections"
        value="<?php echo esc_attr(wp_json_encode($sections)); ?>"
    >
    <div id="sls-section-builder" class="sls-section-builder" data-input-id="sls-page-sections"></div>
    <?php
}

function sls_save_page_sections($post_id) {
    if (! isset($_POST['sls_sections_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sls_sections_nonce'])), 'sls_save_sections')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    $raw_sections = isset($_POST['sls_page_sections']) ? wp_unslash($_POST['sls_page_sections']) : '[]';
    $sections = sls_normalize_sections(sls_decode_sections_json($raw_sections));

    update_post_meta($post_id, SLS_SECTION_META_KEY, wp_json_encode($sections));
}
add_action('save_post_page', 'sls_save_page_sections');
