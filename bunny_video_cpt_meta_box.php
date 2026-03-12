<?php
// Add Bunny Video meta box
function add_bunny_video_meta_box() {
    add_meta_box(
        'bunny_video_meta_box',
        'Bunny Video Settings',
        'render_bunny_video_meta_box',
        'lesson', // Custom Post Type (change it to your own CPT)
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_bunny_video_meta_box');

// Render fields
function render_bunny_video_meta_box($post) {
    wp_nonce_field('save_bunny_video_meta', 'bunny_video_nonce');

    $video_id = get_post_meta($post->ID, 'bunny_video_id', true);
    $library_id = get_post_meta($post->ID, 'bunny_video_library_id', true);

    echo '<p>';
    echo '<label for="bunny_video_id"><strong>Video ID</strong></label>';
    echo '<input type="text" id="bunny_video_id" name="bunny_video_id" value="' . esc_attr($video_id) . '" style="width:100%;" />';
    echo '</p>';

    echo '<p>';
    echo '<label for="bunny_video_library_id"><strong>Library ID</strong></label>';
    echo '<input type="text" id="bunny_video_library_id" name="bunny_video_library_id" value="' . esc_attr($library_id) . '" style="width:100%;" />';
    echo '</p>';
}

// Save fields
function save_bunny_video_meta($post_id) {

    if (!isset($_POST['bunny_video_nonce'])) return;

    if (!wp_verify_nonce($_POST['bunny_video_nonce'], 'save_bunny_video_meta')) return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['bunny_video_id'])) {
        update_post_meta(
            $post_id,
            'bunny_video_id',
            sanitize_text_field($_POST['bunny_video_id'])
        );
    }

    if (isset($_POST['bunny_video_library_id'])) {
        update_post_meta(
            $post_id,
            'bunny_video_library_id',
            sanitize_text_field($_POST['bunny_video_library_id'])
        );
    }
}
add_action('save_post_lesson', 'save_bunny_video_meta');