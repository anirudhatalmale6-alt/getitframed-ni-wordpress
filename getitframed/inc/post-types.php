<?php
/**
 * Content types.
 *
 * Services and gallery items are real content, not hard-coded markup, which is
 * what lets the menu, the homepage grid and the gallery filters build
 * themselves. Enquiries are stored so a form submission is never lost to a
 * bounced email.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the content types.
 */
function gif_register_post_types() {

	register_post_type(
		'gif_service',
		array(
			'labels'        => array(
				'name'               => __( 'Services', 'getitframed' ),
				'singular_name'      => __( 'Service', 'getitframed' ),
				'add_new_item'       => __( 'Add New Service', 'getitframed' ),
				'edit_item'          => __( 'Edit Service', 'getitframed' ),
				'new_item'           => __( 'New Service', 'getitframed' ),
				'view_item'          => __( 'View Service', 'getitframed' ),
				'search_items'       => __( 'Search Services', 'getitframed' ),
				'not_found'          => __( 'No services yet', 'getitframed' ),
				'not_found_in_trash' => __( 'No services in the bin', 'getitframed' ),
				'menu_name'          => __( 'Services', 'getitframed' ),
			),
			'public'        => true,
			'has_archive'   => 'services',
			'menu_icon'     => 'dashicons-format-gallery',
			'menu_position' => 20,
			'rewrite'       => array(
				'slug'       => 'services',
				'with_front' => false,
			),
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
			'show_in_rest'  => true,
		)
	);

	register_post_type(
		'gif_gallery',
		array(
			'labels'        => array(
				'name'          => __( 'Gallery', 'getitframed' ),
				'singular_name' => __( 'Gallery Item', 'getitframed' ),
				'add_new_item'  => __( 'Add New Gallery Item', 'getitframed' ),
				'edit_item'     => __( 'Edit Gallery Item', 'getitframed' ),
				'menu_name'     => __( 'Gallery', 'getitframed' ),
			),
			'public'        => true,
			'has_archive'   => 'gallery',
			'menu_icon'     => 'dashicons-images-alt2',
			'menu_position' => 21,
			'rewrite'       => array(
				'slug'       => 'gallery',
				'with_front' => false,
			),
			'supports'      => array( 'title', 'excerpt', 'thumbnail', 'page-attributes' ),
			'show_in_rest'  => true,
		)
	);

	register_taxonomy(
		'gif_gallery_cat',
		'gif_gallery',
		array(
			'labels'            => array(
				'name'          => __( 'Gallery Categories', 'getitframed' ),
				'singular_name' => __( 'Gallery Category', 'getitframed' ),
				'menu_name'     => __( 'Categories', 'getitframed' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'gallery-category',
				'with_front' => false,
			),
		)
	);

	// Enquiries: private, never public, never indexed. A safety net behind email.
	register_post_type(
		'gif_enquiry',
		array(
			'labels'              => array(
				'name'          => __( 'Enquiries', 'getitframed' ),
				'singular_name' => __( 'Enquiry', 'getitframed' ),
				'menu_name'     => __( 'Enquiries', 'getitframed' ),
				'not_found'     => __( 'No enquiries yet', 'getitframed' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'menu_icon'           => 'dashicons-email-alt',
			'menu_position'       => 22,
			'supports'            => array( 'title' ),
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
			),
			'map_meta_cap'        => true,
		)
	);
}
add_action( 'init', 'gif_register_post_types' );

/**
 * Rewrite rules only need flushing when the registered slugs change, so this
 * runs on theme switch and never on a normal page load.
 */
function gif_flush_rewrites() {
	gif_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'gif_flush_rewrites' );

/**
 * Services and gallery items are ordered by hand, so default to that in admin.
 *
 * @param WP_Query $query Query object.
 */
function gif_admin_ordering( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( in_array( $query->get( 'post_type' ), array( 'gif_service', 'gif_gallery' ), true ) && ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', 'menu_order title' );
		$query->set( 'order', 'ASC' );
	}
}
add_action( 'pre_get_posts', 'gif_admin_ordering' );

/**
 * Front-end ordering for the services and gallery archives.
 *
 * @param WP_Query $query Query object.
 */
function gif_front_ordering( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( array( 'gif_service', 'gif_gallery' ) ) || $query->is_tax( 'gif_gallery_cat' ) ) {
		$query->set( 'orderby', 'menu_order title' );
		$query->set( 'order', 'ASC' );
		$query->set( 'posts_per_page', $query->is_post_type_archive( 'gif_service' ) ? 24 : 36 );
	}
}
add_action( 'pre_get_posts', 'gif_front_ordering' );

/**
 * Admin column showing the card colour, so the grid can be balanced at a glance.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function gif_service_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['gif_colour'] = __( 'Card colour', 'getitframed' );
			$new['gif_image']  = __( 'Image', 'getitframed' );
		}
	}
	return $new;
}
add_filter( 'manage_gif_service_posts_columns', 'gif_service_columns' );

/**
 * Render those columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function gif_service_column_content( $column, $post_id ) {
	if ( 'gif_colour' === $column ) {
		$choices = gif_card_colour_choices();
		$colour  = gif_card_colour( $post_id );
		echo esc_html( isset( $choices[ $colour ] ) ? $choices[ $colour ] : $colour );
	}
	if ( 'gif_image' === $column ) {
		echo has_post_thumbnail( $post_id )
			? esc_html__( 'Yes', 'getitframed' )
			: '<span style="color:#b32d2e">' . esc_html__( 'Missing', 'getitframed' ) . '</span>';
	}
}
add_action( 'manage_gif_service_posts_custom_column', 'gif_service_column_content', 10, 2 );
