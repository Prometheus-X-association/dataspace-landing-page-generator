<?php
// Vision Trust API handler

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('wp_ajax_ptxdala_get_project', 'ptxdala_get_project');
add_action('wp_ajax_nopriv_ptxdala_get_project', 'ptxdala_get_project');

function ptxdala_get_project() {
    // Check nonce for security
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ptxdala_get_project_nonce' ) ) {
        wp_send_json_error(['message' => 'Invalid nonce']);
    }

    // Check user capabilities
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error(['message' => 'Access denied']);
    }

    if (empty($_POST['project_id']) || strlen(sanitize_text_field( wp_unslash( $_POST['project_id'] ) )) !== 24) {
        wp_send_json_error(['message' => 'Invalid Project ID']);
    }

    $project_id = sanitize_text_field( wp_unslash( $_POST['project_id'] ) );
    $url = 'https://api.visionstrust.com/v1/ecosystems/' . $project_id;

    $response = wp_remote_get($url, [
        'headers' => [
            'Accept' => 'application/json',
        ],
        'timeout' => 20,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error([
            'message' => 'Request failed',
            'error'   => $response->get_error_message()
        ]);
    }
    
    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        wp_send_json_error(['message' => 'Project not found']);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // removed empty check block — always return whatever is provided

    $image_url = $data['logo'] ?? '';
    if ($image_url === 'ecosystem_default.jpg') {
        $image_url = plugin_dir_url(__DIR__) . 'public/images/ecosystem_default.jpg';
    }

    $project = [
        'title'             => $data['name'] ?? '',
        'short_description' => $data['description'] ?? '',
        'description'       => $data['detailedDescription'] ?? '',
        'image'             => $image_url,
        'orchestrator_name' => $data['orchestrator']['legalName'] ?? '',
        'orchestrator_logo' => $data['orchestrator']['associatedOrganisation']['logo'] ?? '',
        'participants'      => [],
    ];

    if (!empty($data['participants'])) {
        $seen = [];
        foreach ($data['participants'] as $p) {
            $name = $p['participant']['legalName'] ?? '';
            $logo = $p['participant']['logo'] ?? '';
            if (! $name || isset($seen[$name])) {
                continue;
            }
            $seen[$name] = true;
            $project['participants'][] = [
                'name' => $name,
                'logo' => $logo,
            ];
        }
    }

    wp_send_json_success($project);
}

// ───────────────────────────────
//  FETCH SINGLE IMAGE (progressive)
// ───────────────────────────────
add_action('wp_ajax_ptxdala_fetch_image', 'ptxdala_fetch_single_image');
add_action('wp_ajax_nopriv_ptxdala_fetch_image', 'ptxdala_fetch_single_image');

function ptxdala_fetch_single_image() {
    // Check nonce for security
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ptxdala_fetch_image_nonce' ) ) {
        wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
    }

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( [ 'message' => 'Access denied' ] );
    }

    $post_id = intval( $_POST['post_id'] ?? 0 );
    $url     = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
    $key     = sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) );

    if ( ! $post_id || ! $url || ! $key ) {
        wp_send_json_error( [ 'message' => 'Missing data' ] );
    }

    // use existing helper
    if ( ! function_exists( 'ptxdala_ensure_attachment' ) ) {
        require_once plugin_dir_path( __DIR__ ) . 'includes/landing-generator.php';
    }

    $id = ptxdala_ensure_attachment( $url, $post_id );
    if ( ! $id ) {
        wp_send_json_error( [ 'message' => 'Failed to save image' ] );
    }

    $local = wp_get_attachment_image_url( $id, 'medium' );

    wp_send_json_success( [
        'key'       => $key,
        'id'        => $id,
        'local_url' => $local,
        'remote'    => $url,
    ] );
}
