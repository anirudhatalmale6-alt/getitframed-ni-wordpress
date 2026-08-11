<?php
/**
 * Get It Framed NI -- theme bootstrap.
 *
 * Converted from the approved static prototype. Deliberately dependency-free:
 * no page builder, no premium plugin, nothing with a licence key to lapse.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GIF_VERSION', '1.0.0' );
define( 'GIF_DIR', get_template_directory() );
define( 'GIF_URI', get_template_directory_uri() );

require_once GIF_DIR . '/inc/helpers.php';
require_once GIF_DIR . '/inc/post-types.php';
require_once GIF_DIR . '/inc/customizer.php';
require_once GIF_DIR . '/inc/meta-boxes.php';
require_once GIF_DIR . '/inc/contact-form.php';
require_once GIF_DIR . '/inc/seo.php';

/**
 * Theme supports.
 */
function gif_setup() {
	load_theme_textdomain( 'getitframed', GIF_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	// The card image is a fixed 4:3-ish crop in the design; the service hero is wide.
	add_image_size( 'gif-card', 600, 450, true );
	add_image_size( 'gif-service-hero', 1200, 640, true );
	add_image_size( 'gif-gallery', 800, 800, true );

	register_nav_menus(
		array(
			'footer_quick' => __( 'Footer -- Quick Links', 'getitframed' ),
		)
	);
}
add_action( 'after_setup_theme', 'gif_setup' );

/**
 * Front-end assets.
 *
 * Fonts are self-hosted rather than pulled from Google, so the site does not
 * hand a visitor's IP to a third party on every page load (GDPR) and does not
 * depend on an external host being up.
 */
function gif_assets() {
	wp_enqueue_style( 'gif-fonts', GIF_URI . '/assets/fonts/fonts.css', array(), GIF_VERSION );
	wp_enqueue_style( 'gif-style', get_stylesheet_uri(), array( 'gif-fonts' ), GIF_VERSION );
	wp_enqueue_script( 'gif-script', GIF_URI . '/assets/js/site.js', array(), GIF_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'gif_assets' );

/**
 * Editor styles so the back end roughly resembles the front end.
 */
function gif_editor_assets() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );
}
add_action( 'after_setup_theme', 'gif_editor_assets' );

/**
 * Palette exposed to the block editor, so any colour Darren picks is one of ours.
 */
function gif_editor_palette() {
	add_theme_support(
		'editor-color-palette',
		array(
			array(
				'name'  => __( 'Teal', 'getitframed' ),
				'slug'  => 'teal',
				'color' => '#048A81',
			),
			array(
				'name'  => __( 'Rose', 'getitframed' ),
				'slug'  => 'rose',
				'color' => '#B5838D',
			),
			array(
				'name'  => __( 'Violet', 'getitframed' ),
				'slug'  => 'violet',
				'color' => '#6D6875',
			),
			array(
				'name'  => __( 'Ink', 'getitframed' ),
				'slug'  => 'ink',
				'color' => '#454851',
			),
			array(
				'name'  => __( 'Paper', 'getitframed' ),
				'slug'  => 'paper',
				'color' => '#F1F2F6',
			),
			array(
				'name'  => __( 'White', 'getitframed' ),
				'slug'  => 'white',
				'color' => '#FFFFFF',
			),
		)
	);
	add_theme_support( 'disable-custom-colors' );
}
add_action( 'after_setup_theme', 'gif_editor_palette' );

/**
 * Housekeeping: strip output nobody here needs and that only widens the
 * surface area. None of this changes how the site looks.
 */
function gif_cleanup() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'gif_cleanup' );

/**
 * The XML-RPC endpoint is not used by this site and is a standing brute-force
 * target. Disabled in code so it cannot be switched back on by accident.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Never advertise which user IDs exist via /?author=1 redirects.
 */
function gif_block_author_enumeration() {
	if ( is_admin() ) {
		return;
	}
	if ( isset( $_GET['author'] ) && ! is_user_logged_in() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'gif_block_author_enumeration' );

/**
 * Comments are not part of this build. Turn them off everywhere rather than
 * leaving a spam target open on a site nobody moderates.
 */
function gif_disable_comments_support() {
	foreach ( get_post_types() as $type ) {
		if ( post_type_supports( $type, 'comments' ) ) {
			remove_post_type_support( $type, 'comments' );
			remove_post_type_support( $type, 'trackbacks' );
		}
	}
}
add_action( 'admin_init', 'gif_disable_comments_support' );
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );

/**
 * Tidy the admin menu for a non-technical owner: fewer things to click,
 * fewer things to break.
 */
function gif_admin_menu_tidy() {
	remove_menu_page( 'edit-comments.php' );
	if ( ! current_user_can( 'manage_options' ) ) {
		remove_menu_page( 'tools.php' );
	}
}
add_action( 'admin_menu', 'gif_admin_menu_tidy', 999 );

/**
 * Dashboard note so whoever logs in knows where things live.
 */
function gif_dashboard_widget() {
	wp_add_dashboard_widget(
		'gif_help',
		__( 'Editing this website', 'getitframed' ),
		function () {
			echo '<ul style="list-style:disc;padding-left:1.2em;line-height:1.7">';
			echo '<li><strong>Services</strong> &mdash; one entry per service. The homepage grid and the menu dropdown are built from this list, in the order set by <em>Order</em>.</li>';
			echo '<li><strong>Gallery</strong> &mdash; one entry per photo. Assign a category and it appears under that filter button.</li>';
			echo '<li><strong>Enquiries</strong> &mdash; every contact form submission is saved here as well as emailed, so nothing is lost if an email goes astray.</li>';
			echo '<li><strong>Pages</strong> &mdash; About, Trade, Contact and so on.</li>';
			echo '<li><strong>Appearance &rarr; Customise</strong> &mdash; phone numbers, address, email, opening hours and the homepage wording.</li>';
			echo '</ul>';
			echo '<p style="margin-bottom:0"><em>Anything left as a draft stays off the site and out of the menu.</em></p>';
		}
	);
}
add_action( 'wp_dashboard_setup', 'gif_dashboard_widget' );

/**
 * Body classes used by the stylesheet.
 */
function gif_body_class( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'is-front';
	}
	return $classes;
}
add_filter( 'body_class', 'gif_body_class' );
