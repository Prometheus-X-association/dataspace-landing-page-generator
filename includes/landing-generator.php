<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('wp_ajax_ptxdala_generate_landing_page', 'ptxdala_generate_landing_page');

function ptxdala_generate_landing_page() {
    // Check nonce for security
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ptxdala_generate_landing_page_nonce' ) ) {
        wp_send_json_error('Invalid nonce');
    }

    if (! current_user_can('edit_posts')) {
        wp_send_json_error('Access denied');
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (! $post_id) {
        wp_send_json_error('Missing post ID');
    }

    update_post_meta($post_id, 'is_dataspace_generated', '1');

    // Retrieve data from API
    $project_data = json_decode(stripslashes($_POST['project_data'] ?? ''), true);

    $title   = sanitize_text_field( $project_data['title'] ?? 'No title' );
    $content = wp_kses_post( $project_data['description'] ?? '' );
    
    // Determine short description logic
    $api_short_description = isset($project_data['short_description']) ? trim(sanitize_textarea_field($project_data['short_description'])) : '';

    if ( ! empty( $api_short_description ) ) {
        // Limit to 40 words to avoid overly long descriptions
        $final_short_description = wp_trim_words( wp_strip_all_tags( $api_short_description ), 40, '...' );
    } else {
        $final_short_description = wp_trim_words( wp_strip_all_tags( $project_data['description'] ?? '' ), 40, '...' );
    }

    // Ensure content is properly formatted for the editor
    $content = wpautop($content);
    $content = str_replace(["\r\n", "\r", "\n"], '', $content);

    $cover_url         = $project_data['image'] ?? '';
    $proposed_logo_url = $project_data['orchestrator_logo'] ?? '';
    $participants      = $project_data['participants'] ?? [];

    // At this point we create the page WITHOUT downloading images yet
    // to avoid blocking the user. We store raw URLs for background processing.

    $cover_id    = 0;
    $proposed_id = 0;

    // Build partners array (without attachment IDs yet)
    $trusted_partners = [];
    $participants_raw = [];
    foreach ( $participants as $idx => $item ) {
        if ( $idx === 0 ) {
            continue; // skip orchestrator (index 0)
        }
        $logo_url = esc_url_raw( $item['logo'] ?? '' );
        $partner_name = sanitize_text_field( $item['name'] ?? '' );
        if ( $logo_url ) {
            $trusted_partners[] = [
                'id'   => 0,
                'url'  => $logo_url,
                'name' => $partner_name,
            ];
            $participants_raw[] = [
                'logo' => $logo_url,
                'name' => $partner_name,
            ];
        }
    }

    // Save full meta information (temporary id = 0)
    update_post_meta( $post_id, 'trusted_actors_full_data', wp_json_encode( $trusted_partners ) );
    update_post_meta( $post_id, 'trusted_actors_entries',     wp_json_encode( $trusted_partners ) );

    // Reset old list of IDs (none yet)
    update_post_meta( $post_id, 'trusted_actors_logo_ids', wp_json_encode( [] ) );

    // Store raw URLs for background processing
    $raw_urls = [
        'cover'       => esc_url_raw( $cover_url ),
        'proposed'    => esc_url_raw( $proposed_logo_url ),
        'participants'=> $participants_raw,
    ];
    update_post_meta( $post_id, '_dataspace_raw_urls', $raw_urls );

    // Schedule background job that will download images and update meta
    if ( ! wp_next_scheduled( 'ptxdala_process_images', [ $post_id ] ) ) {
        wp_schedule_single_event( time() + 5, 'ptxdala_process_images', [ $post_id ] );
    }

    // CTA buttons
    $cta_buttons_raw = sanitize_textarea_field( wp_unslash( $_POST['cta_buttons'] ?? '[]' ) );
    $buttons_data = json_decode( stripslashes( $cta_buttons_raw ), true );
    $buttons = []; // Array to hold sanitised, valid buttons
    if (is_array($buttons_data)) {
        foreach ($buttons_data as $btn) {
            if ( !empty($btn['text']) && !empty($btn['url']) && isset($btn['tooltip']) ) {
                $buttons[] = [
                    'text'    => sanitize_text_field($btn['text']),
                    'url'     => esc_url_raw($btn['url']),
                    'tooltip' => sanitize_textarea_field($btn['tooltip'])
                ];
            }
        }
    }
    // $buttons now contains only valid entries (or empty array)

    // Update the post itself
    wp_update_post( [
        'ID'           => $post_id,
        'post_title'   => $title,
        'post_excerpt' => $final_short_description,
        'post_content' => $content,
    ] );

    // Save meta fields
    update_post_meta( $post_id, 'short_description', $final_short_description );
    update_post_meta( $post_id, 'cover_image_id',          $cover_id );
    update_post_meta( $post_id, 'proposed_by_logo_id',     $proposed_id );
    update_post_meta( $post_id, 'proposed_by_name', sanitize_text_field( $project_data['orchestrator_name'] ?? '' ));
    // Keep the legacy field for backward compatibility
    // trusted_actors_logo_ids will be updated in the background job
    update_post_meta( $post_id, 'cta_buttons',             wp_json_encode( $buttons, JSON_UNESCAPED_UNICODE ) );

    // Page template
    $template = sanitize_text_field( wp_unslash( $_POST['template'] ?? '' ) );
    if ( in_array( $template, ['layout-1','layout-2'], true ) ) {
        update_post_meta( $post_id, '_wp_page_template', $template );
    }

    // Build a list of images that still need to be downloaded
    $pending_images = [];
    if ( ! $cover_id && $cover_url ) {
        $pending_images[] = [ 'key' => 'cover', 'url' => esc_url_raw( $cover_url ) ];
    }
    if ( ! $proposed_id && $proposed_logo_url ) {
        $pending_images[] = [ 'key' => 'proposed', 'url' => esc_url_raw( $proposed_logo_url ) ];
    }
    foreach ( $trusted_partners as $idx => $tp ) {
        if ( empty( $tp['id'] ) && ! empty( $tp['url'] ) ) {
            $pending_images[] = [ 'key' => 'trusted_' . $idx, 'url' => esc_url_raw( $tp['url'] ), 'name' => $tp['name'] ];
        }
    }

    // Return data to JS
    wp_send_json_success( [
        'title'               => $title,
        'excerpt'             => $final_short_description,
        'content'             => $content,
        'short_description'   => $final_short_description,
        'cover_image_id'      => $cover_id,
        'cover_image_url'     => $cover_id    ? wp_get_attachment_image_url( $cover_id, 'medium' ) : esc_url_raw( $cover_url ),
        'proposed_by_logo_id' => $proposed_id,
        'proposed_logo_url'   => $proposed_id ? wp_get_attachment_image_url( $proposed_id, 'medium' ) : esc_url_raw( $proposed_logo_url ),
        'proposed_by_name'       => sanitize_text_field( $project_data['orchestrator_name'] ?? '' ),
        'trusted_logos'       => array_map( function( $item ) {
            $url = '';
            if ( ! empty( $item['id'] ) ) {
                $url = wp_get_attachment_image_url( $item['id'], 'medium' );
            }
            if ( ! $url && ! empty( $item['url'] ) ) {
                $url = esc_url( $item['url'] );
            }
            return [
                'id'   => $item['id'],
                'url'  => $url,
                'name' => $item['name'] ?? '',
            ];
        }, $trusted_partners ),

        // Legacy logic preserved for backward compatibility
        /*'trusted_logos'       => array_map( function( $id ) {
            return [
                'id'  => $id,
                'url' => wp_get_attachment_image_url( $id, 'medium' ),
            ];
        }, $trusted_ids ),*/
        'cta_buttons'         => $buttons,

        'pending_images'      => $pending_images,
    ] );
}

/**
 * Downloads an external image into the media library and returns its ID.
 */
function ptxdala_ensure_attachment( $url, $parent_post_id ) {
    if ( empty( $url ) ) {
        return 0;
    }

    $existing = attachment_url_to_postid( $url );
    if ( $existing ) {
        return $existing;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // Disable intermediate image sizes for this sideload to avoid unnecessary resized images
    $disable_sizes = function () { return []; };
    add_filter( 'intermediate_image_sizes_advanced', $disable_sizes, 99 );

    try {
        $tmp = download_url( $url );
        if ( is_wp_error( $tmp ) ) {
            // Reactivate image size generation
            remove_filter( 'intermediate_image_sizes_advanced', $disable_sizes, 99 );
            return 0;
        }

        $file = [
            'name'     => basename( wp_parse_url( $url, PHP_URL_PATH ) ),
            'tmp_name' => $tmp,
        ];

        $id = media_handle_sideload( $file, $parent_post_id );
        if ( is_wp_error( $id ) ) {
            wp_delete_file( $tmp );
            return 0;
        }
    } finally {
        // Always remove the filter to avoid side-effects in other uploads
        remove_filter( 'intermediate_image_sizes_advanced', $disable_sizes, 99 );
    }

    return $id;
}

add_action('add_meta_boxes', function() {
    add_meta_box(
        'ptxdala_landing_meta_box',
        'Landing Page Details',
        'ptxdala_render_landing_meta_box',
        'page',
        'normal',
        'high'
    );
});

add_action( 'admin_enqueue_scripts', function() {
    global $post;
    if ( get_current_screen()->post_type === 'page'
      && get_current_screen()->base === 'post' ) {

        $is_new  = $post && $post->post_status === 'auto-draft';
        $not_gen = $post && ! get_post_meta( $post->ID, 'is_dataspace_generated', true );

        // Now hide the meta box only if the page is NEW and not yet generated
        if ( $is_new && $not_gen ) {
            wp_add_inline_style( 'wp-admin', '#ptxdala_landing_meta_box{display:none;}' );
        }
    }
});

add_action( 'admin_enqueue_scripts', function (): void {
    // Enqueue trusted logos media script
    wp_enqueue_script(
        'dataspace-trusted-logos-media',
        plugin_dir_url( dirname( __FILE__ ) ) . 'admin/trusted-logos-media.js',
        ['jquery', 'media-editor', 'media-views'],
        filemtime( plugin_dir_path( dirname( __FILE__ ) ) . 'admin/trusted-logos-media.js' ),
        true
    );
});

function ptxdala_render_landing_meta_box( $post ) {
    $short   = get_post_meta( $post->ID, 'short_description', true );
    $cover   = get_post_meta( $post->ID, 'cover_image_id', true );
    $prop    = get_post_meta( $post->ID, 'proposed_by_logo_id', true );
    $buttons = json_decode( get_post_meta( $post->ID, 'cta_buttons', true ), true ) ?: [];

    // Short Description
    echo '<p><label>Short Description:<br>';
    echo '<textarea name="short_description" rows="4" style="width:100%;">' . esc_textarea( $short ) . '</textarea>';
    echo '</label></p>';

    // Cover Image
    echo '<p><strong>Cover Image:</strong><br>';
    ptxdala_render_media_field( $cover, 'cover_image_id' );
    $raw = get_post_meta( $post->ID, '_dataspace_raw_urls', true );
    $ext = is_array( $raw ) ? $raw['cover'] ?? '' : '';
    if ( ! $cover && $ext ) {
        echo '<img src="' . esc_url( $ext ) . '" class="media-preview" style="max-width:150px;display:block;margin-top:5px;" alt="Cover (remote)" />';
    }
    echo '</p>';

    // Proposed by Logo
    echo '<p><strong>Proposed by Logo:</strong><br>';
    ptxdala_render_media_field( $prop, 'proposed_by_logo_id' );
    $ext2 = is_array( $raw ) ? $raw['proposed'] ?? '' : '';
    if ( ! $prop && $ext2 ) {
        echo '<img src="' . esc_url( $ext2 ) . '" class="media-preview" style="max-width:150px;display:block;margin-top:5px;" alt="Logo (remote)" />';
    }
    echo '</p>';

    // Proposed by Name
    $orchestrator_name = get_post_meta( $post->ID, 'proposed_by_name', true );
    echo '<p><label><strong>Proposed by Name:</strong><br>';
    if (isset($orchestrator_name)) {
        echo '<input type="text" name="proposed_by_name" value="' . esc_attr($orchestrator_name) . '" style="width:100%;" maxlength="120" />';
    }
    echo '</label></p>';

    // Trusted Logos (with names)
    echo '<p><strong>Trusted Logos & Names:</strong></p>';
    echo '<div id="trusted-logos-wrapper">';
    $trusted_entries = json_decode(get_post_meta($post->ID, 'trusted_actors_entries', true), true) ?: [];
    foreach ($trusted_entries as $index => $entry) {
        $image_id  = intval($entry['id'] ?? 0);
        $thumb_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
        $ext_url   = empty($image_id) && ! empty( $entry['url'] ) ? $entry['url'] : '';

        echo '<div class="trusted-entry" style="margin-bottom:10px;padding:10px;border:1px dashed #ccc;">';
        if ( $image_id ) {
            echo '<div class="dataspace-media-wrapper" data-field="trusted_logo_' . esc_attr($index) . '">';
            echo '<img src="' . esc_url($thumb_url) . '" class="media-preview" style="max-width:80px;display:block;margin-bottom:5px;" />';
            echo '<input type="hidden" name="trusted_actors_logo_ids[' . esc_attr($index) . ']" value="' . esc_attr($image_id) . '" />';
            echo '<button class="button dataspace-upload">Select Image</button> <button class="button dataspace-remove">Remove</button>';
            echo '</div>';
        } elseif ( $ext_url ) {
            echo '<img src="' . esc_url( $ext_url ) . '" style="max-width:80px;display:block;margin-bottom:5px;" alt="Logo scheduled" />';
            echo '<p style="color:#666;">Scheduled for download…</p>';
        }
        echo '<p style="margin-top:5px;">Name: <input type="text" name="trusted_actors_names[' . esc_attr($index) . ']" value="' . esc_attr($entry['name'] ?? '') . '" style="width:300px;" maxlength="120" /></p>';
        echo '<button type="button" class="button remove-trusted-entry" style="margin-top:5px;">Delete partner</button>';
        echo '</div>';
    }
    echo '</div>';

    echo '<button type="button" class="button" id="add-trusted-entry">Add Trusted Partner</button>';


    // CTA Buttons
    echo '<p><strong>CTA Buttons:</strong></p>';
    for ( $i = 0; $i < 2; $i++ ) {
        $t = $buttons[$i]['text'] ?? '';
        $u = $buttons[$i]['url'] ?? '';
        $tooltip = $buttons[$i]['tooltip'] ?? '';

        echo "<div style='margin-bottom: 15px; padding: 10px; border: 1px solid #eee;'>";
        echo "<h4>Button " . ( esc_attr($i + 1 ) ) . "</h4>";
        echo "<p>Text: <input type='text' name='cta_button_text_" . esc_attr($i) . "' value='" . esc_attr($t) . "' style='width:100%;' maxlength='120' /></p>";
        echo "<p>URL: <input type='url' name='cta_button_url_" . esc_attr($i) . "' value='" . esc_url($u) . "' style='width:100%;' /></p>";
        echo "<p>Accessibility Description (Text visible on hover...):<br />";
        echo "<textarea name='cta_button_tooltip_" . esc_attr($i) . "' rows='3' maxlength='400' style='width:100%;'>" . esc_textarea($tooltip) . "</textarea></p>";
        echo "</div>";
    }
}

// Output one media field
function ptxdala_render_media_field( $id, $field ) {
    $src = $id ? wp_get_attachment_image_src( $id, 'medium' )[0] : '';
    echo "<div class='dataspace-media-wrapper' data-field='".esc_attr($field)."'>";
    if ( $src ) {
        echo "<img src='".esc_url($src)."' class='media-preview' style='max-width:150px;display:block;' />";
    }
    echo "<input type='hidden' name='".esc_attr($field)."' value='".esc_attr( $id )."' />";
    echo "<button class='button dataspace-upload'>Select Image</button> ";
    echo "<button class='button dataspace-remove'>Remove</button>";
    echo "</div>";
}

add_action('save_post', function( $post_id ) {
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

    // Check nonce for security
    if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-post_' . $post_id ) ) {
        return;
    }

    // Check user capabilities
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Short description
    $short_description = sanitize_textarea_field( wp_unslash( $_POST['short_description'] ?? '' ) );
    $short_description = wp_trim_words( $short_description, 30, '...' );
    update_post_meta( $post_id, 'short_description', $short_description );

    // Cover & Proposed by Logo (avoid overwriting 0 over existing)
    $new_cover_id    = intval( $_POST['cover_image_id'] ?? 0 );
    if ( $new_cover_id > 0 ) {
        update_post_meta( $post_id, 'cover_image_id', $new_cover_id );
    }
    $new_prop_id     = intval( $_POST['proposed_by_logo_id'] ?? 0 );
    if ( $new_prop_id > 0 ) {
        update_post_meta( $post_id, 'proposed_by_logo_id', $new_prop_id );
    }

    // Orchestrator name
    if ( isset( $_POST['proposed_by_name'] ) ) {
        update_post_meta( $post_id, 'proposed_by_name', sanitize_text_field( wp_unslash( $_POST['proposed_by_name'] ) ) );
    }

    // CTA Buttons
    $btns = [];
    for ( $i = 0; $i < 2; $i++ ) {
        $text = sanitize_text_field( wp_unslash( $_POST["cta_button_text_{$i}"] ?? '' ) );
        $url = esc_url_raw( wp_unslash( $_POST["cta_button_url_{$i}"] ?? '' ) );
        $tooltip = sanitize_textarea_field( wp_unslash( $_POST["cta_button_tooltip_{$i}"] ?? '' ) );

        // Always add a record for each button to maintain array structure,
        // even if text/URL/tooltip is empty.
        $btns[] = [
            'text' => $text,
            'url' => $url,
            'tooltip' => $tooltip
        ];
    }
    update_post_meta( $post_id, 'cta_buttons', wp_json_encode( $btns, JSON_UNESCAPED_UNICODE ) );


    // Trusted Logos (table)
    if ( isset( $_POST['trusted_actors_names'] ) ) {
        $names = sanitize_text_field( wp_unslash( $_POST['trusted_actors_names'] ) );
        update_post_meta( $post_id, 'trusted_actors_names', $names );
    }

    $trusted_ids   = map_deep( wp_unslash( $_POST['trusted_actors_logo_ids'] ?? [] ), 'sanitize_text_field' );
    $trusted_names = map_deep( wp_unslash( $_POST['trusted_actors_names'] ?? [] ), 'sanitize_text_field' );

    $prev_entries_map = [];
    $prev_entries = json_decode( get_post_meta( $post_id, 'trusted_actors_entries', true ), true ) ?: [];
    foreach ( $prev_entries as $pe ) {
        $prev_entries_map[ sanitize_text_field( $pe['name'] ?? '' ) ] = $pe;
    }

    $trusted_entries = [];
    foreach ( $trusted_ids as $i => $id ) {
        $name = sanitize_text_field( $trusted_names[ $i ] ?? '' );
        $id_int = intval( $id );
        $entry = [ 'id' => $id_int, 'name' => $name ];
        // If id = 0, try to take url from previous record
        if ( $id_int === 0 && isset( $prev_entries_map[ $name ]['url'] ) ) {
            $entry['url'] = esc_url_raw( $prev_entries_map[ $name ]['url'] );
        }
        $trusted_entries[] = $entry;
    }
    if ( ! empty( $trusted_entries ) ) {
        update_post_meta( $post_id, 'trusted_actors_entries', wp_json_encode( $trusted_entries ) );
        $ids_only = array_filter( wp_list_pluck( $trusted_entries, 'id' ) );
        if ( ! empty( $ids_only ) ) {
            update_post_meta( $post_id, 'trusted_actors_logo_ids', wp_json_encode( $ids_only ) );
        }
    }
});

add_action( 'ptxdala_process_images', 'ptxdala_process_images_cb', 10, 1 );
function ptxdala_process_images_cb( $post_id ) {
    // Get saved raw URLs
    $raw = get_post_meta( $post_id, '_dataspace_raw_urls', true );
    if ( ! is_array( $raw ) || empty( $raw ) ) {
        return;
    }

    // Cover & Proposed by
    $cover_id = ! empty( $raw['cover'] )    ? ptxdala_ensure_attachment( $raw['cover'],    $post_id ) : 0;
    $prop_id  = ! empty( $raw['proposed'] ) ? ptxdala_ensure_attachment( $raw['proposed'], $post_id ) : 0;

    // Trusted partners
    $trusted_entries = [];
    $trusted_ids     = [];
    if ( ! empty( $raw['participants'] ) && is_array( $raw['participants'] ) ) {
        foreach ( $raw['participants'] as $p ) {
            $url  = $p['logo'] ?? '';
            $name = $p['name'] ?? '';
            if ( ! $url ) {
                continue;
            }
            $id = ptxdala_ensure_attachment( $url, $post_id );
            if ( $id ) {
                $trusted_entries[] = [
                    'id'   => $id,
                    'name' => sanitize_text_field( $name ),
                ];
                $trusted_ids[] = $id;
            }
        }
    }

    // Update meta fields
    update_post_meta( $post_id, 'cover_image_id',          $cover_id );
    update_post_meta( $post_id, 'proposed_by_logo_id',     $prop_id );
    update_post_meta( $post_id, 'trusted_actors_logo_ids', wp_json_encode( $trusted_ids ) );
    update_post_meta( $post_id, 'trusted_actors_entries',  wp_json_encode( $trusted_entries ) );
    update_post_meta( $post_id, 'trusted_actors_full_data', wp_json_encode( $trusted_entries ) );

    // Remove temporary data
    delete_post_meta( $post_id, '_dataspace_raw_urls' );
}
