<?php
/*
 * Plugin Name: Prometheus-X Dataspace Landing Page Generator
 * Description: Create dynamic project landing pages using data from the Prometheus-X Dataspace Catalog API, with customizable call-to-action buttons and templates.
 * Version: 1.0.0
 * Author: inokufu
 * Requires PHP: 8.2
 * Requires at least: 6.8
 * License: MIT
 */
defined( 'ABSPATH' ) || exit;

// ───────────────────────────────
//  LOAD CORE MODULES
// ───────────────────────────────
require_once plugin_dir_path( __FILE__ ) . 'includes/api-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/landing-generator.php';

// ───────────────────────────────
//  ADMIN ASSETS
// ───────────────────────────────
add_action( 'admin_enqueue_scripts', function ( $hook ) {

	if ( $hook !== 'post-new.php' && $hook !== 'post.php' ) {
		return;
	}

	global $post;
	$post_id      = $post ? $post->ID : 0;
	$is_generated = $post_id ? get_post_meta( $post_id, 'is_dataspace_generated', true ) : false;

	// main admin UI script
	wp_enqueue_script(
		'dataspace-admin',
		plugin_dir_url( __FILE__ ) . 'admin/admin-ui.js',
		[ 'jquery' ],
		null,
		true
	);

	// patch for Trusted Logos (load AFTER admin-ui.js)
	wp_enqueue_script(
		'dataspace-trusted-fix',
		plugin_dir_url( __FILE__ ) . 'admin/trusted-logos-fix.js',
		[ 'jquery', 'media-editor', 'media-views' ],
		null,
		true
	);

	wp_localize_script(
		'dataspace-admin',
		'dataspace_data',
		[
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			'is_generated' => $is_generated,
			'plugin_url' => plugin_dir_url(__FILE__),
			'nonces' => [
				'get_project' => wp_create_nonce( 'ptxdala_get_project_nonce' ),
				'generate_landing_page' => wp_create_nonce( 'ptxdala_generate_landing_page_nonce' ),
				'fetch_image' => wp_create_nonce( 'ptxdala_fetch_image_nonce' ),
			],
		]
	);

	wp_enqueue_style(
		'dataspace-admin-style',
		plugin_dir_url( __FILE__ ) . 'admin/admin-style.css',
		[],
		null
	);
} );

// ───────────────────────────────
//  FRONT-END
// ───────────────────────────────
add_action( 'wp_enqueue_scripts', function () {
	if ( is_page() ) {
		wp_enqueue_style(
			'dataspace-landing-style',
			plugin_dir_url( __FILE__ ) . 'public/landing-style.css',
			[],
			null
		);
		wp_enqueue_style('swiper', plugin_dir_url( __FILE__ ) . 'public/swiper-bundle.min.css', [], null);
		wp_enqueue_script('swiper', plugin_dir_url( __FILE__ ) . 'public/swiper-bundle.min.js', [], null, true);
		wp_enqueue_script(
			'partners-slider', 
			plugin_dir_url( __FILE__ ) . '/public/partners-slider.js',
			['jquery','swiper'],
			'1.0',
			true
		);
		
		// Enqueue CTA accessibility script for dataspace templates
		global $post;
		if ( $post && in_array( get_post_meta( $post->ID, '_wp_page_template', true ), ['layout-1', 'layout-2'] ) ) {
			wp_enqueue_script(
				'dataspace-cta-accessibility',
				plugin_dir_url( __FILE__ ) . 'public/cta-accessibility.js',
				[],
				filemtime( plugin_dir_path( __FILE__ ) . 'public/cta-accessibility.js' ),
				true
			);
		}
	}
} );

// ───────────────────────────────
//  ADMIN (for preview in admin interface)
// ───────────────────────────────
add_action( 'admin_enqueue_scripts', function () {
    // Preview styles, only in admin
    wp_enqueue_style(
        'dataspace-landing-style-admin',
        plugin_dir_url( __FILE__ ) . 'public/landing-style.css',
        [],
        filemtime( plugin_dir_path( __FILE__ ) . 'public/landing-style.css' )
    );
    wp_enqueue_style('swiper', plugin_dir_url( __FILE__ ) . 'public/swiper-bundle.min.css', [], null);
    wp_enqueue_script('swiper', plugin_dir_url( __FILE__ ) . 'public/swiper-bundle.min.js', [], null, true);
} );

// ───────────────────────────────
//  ADMIN POP-UP / TEMPLATE SELECTOR
// ───────────────────────────────
add_action( 'edit_form_after_title', function () {

	global $post;
	if ( ! $post || get_post_type( $post ) !== 'page' ) {
		return;
	}

	$is_new      = $post->post_status === 'auto-draft';
	$is_generated = get_post_meta( $post->ID, 'is_dataspace_generated', true );

	    // show button only for NEW, not yet generated page
	if ( ! $is_new || $is_generated ) {
		return;
	}

	echo '<div id="dataspace-button" class="dataspace-btn"><img src="' . esc_url( plugin_dir_url(__FILE__) . 'public/images/icone-PTX.png' ) . '" alt="Logo" style="height: 28px; vertical-align: middle; margin-right: 8px;">Dataspace landing page</div>';
	echo '<div id="dataspace-popup" style="display:none;">
		<div id="dataspace-popup-wrapper">
        <div id="close-popup-button" role="button" style="cursor:pointer;">X</div>
	';
	include plugin_dir_path( __FILE__ ) . 'admin/popup-content.php';
	include plugin_dir_path( __FILE__ ) . 'admin/template-selector.php';
	include plugin_dir_path( __FILE__ ) . 'admin/cta-buttons-form.php';
	echo '</div>';
	echo '</div>';
} );

// ───────────────────────────────
//  REGISTER META
// ───────────────────────────────
register_activation_hook( __FILE__, function () {

	$fields = [
		'cover_image_id',
		'proposed_by_logo_id',
		'trusted_actors_logo_ids',
		'cta_buttons',
		'short_description',
		'is_dataspace_generated',
	];

	foreach ( $fields as $field ) {
		register_post_meta(
			'page',
			$field,
			[
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
				'auth_callback'=> function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);
	}
} );

// ───────────────────────────────
//  PAGE TEMPLATES
// ───────────────────────────────
add_filter( 'theme_page_templates', function ( $templates ) {

	$templates['layout-1'] = 'Layout 1 (Dataspace)';
	$templates['layout-2'] = 'Layout 2 (Dataspace)';
	return $templates;
} );

add_filter( 'page_template', function ( $template ) {

	global $post;
	if ( ! $post ) {
		return $template;
	}

	$custom          = get_post_meta( $post->ID, '_wp_page_template', true );
	$plugin_template = plugin_dir_path( __FILE__ ) . 'templates/' . $custom . '.php';

	if ( in_array( $custom, [ 'layout-1', 'layout-2' ], true ) && file_exists( $plugin_template ) ) {
		return $plugin_template;
	}

	return $template;
} );

// ───────────────────────────────
//  GUTENBERG SUPPORT
// ───────────────────────────────
add_action('enqueue_block_editor_assets', function() {
    // Connect admin styles
    wp_enqueue_style(
        'dataspace-admin-style',
        plugin_dir_url(__FILE__) . 'admin/admin-style.css',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'admin/admin-style.css')
    );

    // Connect main UI script
    wp_enqueue_script(
        'dataspace-admin',
        plugin_dir_url(__FILE__) . 'admin/admin-ui.js',
        ['jquery'],
        filemtime(plugin_dir_path(__FILE__) . 'admin/admin-ui.js'),
        true
    );

    // Connect patch for Trusted Logos
    wp_enqueue_script(
        'dataspace-trusted-fix',
        plugin_dir_url(__FILE__) . 'admin/trusted-logos-fix.js',
        ['jquery', 'media-editor', 'media-views'],
        filemtime(plugin_dir_path(__FILE__) . 'admin/trusted-logos-fix.js'),
        true
    );

    // Connect sidebar button script (only for pages)
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'page') {
        wp_enqueue_script(
            'dataspace-sidebar-button',
            plugin_dir_url(__FILE__) . 'admin/sidebar-button.js',
            ['jquery'],
            filemtime(plugin_dir_path(__FILE__) . 'admin/sidebar-button.js'),
            true
        );
    }

    // Pass data to JavaScript
    wp_localize_script(
        'dataspace-admin',
        'dataspace_data',
        [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'is_generated' => get_post_meta(get_the_ID(), 'is_dataspace_generated', true),
            'plugin_url' => plugin_dir_url(__FILE__),
            'nonces' => [
                'get_project' => wp_create_nonce( 'ptxdala_get_project_nonce' ),
                'generate_landing_page' => wp_create_nonce( 'ptxdala_generate_landing_page_nonce' ),
                'fetch_image' => wp_create_nonce( 'ptxdala_fetch_image_nonce' ),
            ],
        ]
    );
    
    // Pass data to sidebar button script (only for pages)
    if ($screen && $screen->post_type === 'page') {
        wp_localize_script(
            'dataspace-sidebar-button',
            'dataspaceData',
            [
                'pluginUrl' => plugin_dir_url(__FILE__),
            ]
        );
    }
});

// Gutenberg sidebar button: only for new pages that are not yet generated
add_action('admin_footer', function() {
    $screen = get_current_screen();
    if ( ! $screen || $screen->base !== 'post' || $screen->post_type !== 'page' ) {
        return; // Do not add button on other post types (e.g., articles)
    }

    global $post;
    if ( ! $post ) {
        return;
    }

    $is_new       = $post->post_status === 'auto-draft';
    $is_generated = get_post_meta( $post->ID, 'is_dataspace_generated', true );

    if ( ! $is_new || $is_generated ) {
        return; // Only for brand-new, not yet generated pages
    }

    // Existing popup + button code (moved into helper function for reuse)
    ?>
    <div id="dataspace-popup" style="display:none;">
        <div id="dataspace-popup-wrapper">
            <div id="close-popup-button" role="button" style="cursor:pointer;">X</div>
            <?php
            include plugin_dir_path(__FILE__) . 'admin/popup-content.php';
            include plugin_dir_path(__FILE__) . 'admin/template-selector.php';
            include plugin_dir_path(__FILE__) . 'admin/cta-buttons-form.php';
            ?>
        </div>
    </div>
    <?php
});
