<?php
/**
 * Custom meta boxes for canonical URL and audio enclosure.
 *
 * @package asdo-blog
 */

// Register meta boxes
function asdo_register_meta_boxes() {
    add_meta_box(
        'asdo_canonical',
        'Canonical URL',
        'asdo_canonical_meta_box',
        'post',
        'normal',
        'default'
    );
    add_meta_box(
        'asdo_enclosure',
        'Audio Enclosure',
        'asdo_enclosure_meta_box',
        'post',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'asdo_register_meta_boxes');

// Canonical URL meta box
function asdo_canonical_meta_box($post) {
    wp_nonce_field('asdo_canonical_nonce', 'asdo_canonical_nonce_field');
    $href = get_post_meta($post->ID, 'canonical_href', true);
    $name = get_post_meta($post->ID, 'canonical_name', true);
    ?>
    <p>
        <label for="canonical_href"><strong>URL:</strong></label><br>
        <input type="url" id="canonical_href" name="canonical_href" value="<?php echo esc_attr($href); ?>" style="width: 100%;" placeholder="https://example.com/original-post">
    </p>
    <p>
        <label for="canonical_name"><strong>Source Name:</strong></label><br>
        <input type="text" id="canonical_name" name="canonical_name" value="<?php echo esc_attr($name); ?>" style="width: 100%;" placeholder="my Substack newsletter">
    </p>
    <?php
}

// Enclosure meta box
function asdo_enclosure_meta_box($post) {
    wp_nonce_field('asdo_enclosure_nonce', 'asdo_enclosure_nonce_field');
    $url = get_post_meta($post->ID, 'enclosure_url', true);
    $size = get_post_meta($post->ID, 'enclosure_size', true);
    $type = get_post_meta($post->ID, 'enclosure_type', true);
    ?>
    <p>
        <label for="enclosure_url"><strong>Audio URL:</strong></label><br>
        <input type="url" id="enclosure_url" name="enclosure_url" value="<?php echo esc_attr($url); ?>" style="width: 100%;" placeholder="https://example.com/audio.mp3">
    </p>
    <p>
        <label for="enclosure_size"><strong>File Size (bytes):</strong></label><br>
        <input type="text" id="enclosure_size" name="enclosure_size" value="<?php echo esc_attr($size); ?>" style="width: 50%;" placeholder="12345678">
    </p>
    <p>
        <label for="enclosure_type"><strong>MIME Type:</strong></label><br>
        <input type="text" id="enclosure_type" name="enclosure_type" value="<?php echo esc_attr($type); ?>" style="width: 50%;" placeholder="audio/mpeg">
    </p>
    <?php
}

// Save meta box data
function asdo_save_meta_boxes($post_id) {
    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save canonical fields
    if (isset($_POST['asdo_canonical_nonce_field']) &&
        wp_verify_nonce($_POST['asdo_canonical_nonce_field'], 'asdo_canonical_nonce')) {
        if (isset($_POST['canonical_href'])) {
            update_post_meta($post_id, 'canonical_href', esc_url_raw($_POST['canonical_href']));
        }
        if (isset($_POST['canonical_name'])) {
            update_post_meta($post_id, 'canonical_name', sanitize_text_field($_POST['canonical_name']));
        }
    }

    // Save enclosure fields
    if (isset($_POST['asdo_enclosure_nonce_field']) &&
        wp_verify_nonce($_POST['asdo_enclosure_nonce_field'], 'asdo_enclosure_nonce')) {
        if (isset($_POST['enclosure_url'])) {
            update_post_meta($post_id, 'enclosure_url', esc_url_raw($_POST['enclosure_url']));
        }
        if (isset($_POST['enclosure_size'])) {
            update_post_meta($post_id, 'enclosure_size', sanitize_text_field($_POST['enclosure_size']));
        }
        if (isset($_POST['enclosure_type'])) {
            update_post_meta($post_id, 'enclosure_type', sanitize_text_field($_POST['enclosure_type']));
        }
    }
}
add_action('save_post', 'asdo_save_meta_boxes');
