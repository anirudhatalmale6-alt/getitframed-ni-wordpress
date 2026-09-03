<?php
/**
 * Small helpers used across the templates.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read a Customizer value, falling back to the theme default.
 *
 * The fallback comes from gif_defaults(), which holds the wording from the
 * approved prototype. That matters: a fresh install, or a field the client
 * clears by accident, still shows the signed-off copy rather than a blank.
 *
 * @param string $key     Setting key, without the gif_ prefix.
 * @param string $default Explicit fallback, overriding the theme default.
 * @return string
 */
function gif_opt( $key, $default = null ) {
	static $defaults = null;

	if ( null === $defaults ) {
		$defaults = gif_defaults();
	}

	if ( null === $default ) {
		$default = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	}

	$value = get_theme_mod( 'gif_' . $key, $default );

	return ( '' === $value || null === $value ) ? $default : $value;
}

/**
 * Turn a phone number into something dialable.
 *
 * @param string $number Human-readable number.
 * @return string
 */
function gif_tel( $number ) {
	return preg_replace( '/[^0-9+]/', '', (string) $number );
}

/**
 * Permalink for a published page, by slug.
 *
 * Returns false when the page does not exist or is not published. Every
 * template checks this before printing a link, which is why this build cannot
 * produce the href="#" dead links the prototype was full of: a page that is
 * not ready simply does not appear in the menu.
 *
 * @param string $slug Page slug.
 * @return string|false
 */
function gif_page_url( $slug ) {
	static $cache = array();

	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}

	$page = get_page_by_path( $slug );
	$url  = ( $page && 'publish' === $page->post_status ) ? get_permalink( $page ) : false;

	$cache[ $slug ] = $url;
	return $url;
}

/**
 * All published services, in the order set in the admin.
 *
 * @param int $limit Maximum number to return.
 * @return WP_Post[]
 */
function gif_services( $limit = -1 ) {
	static $cache = array();

	if ( isset( $cache[ $limit ] ) ) {
		return $cache[ $limit ];
	}

	$cache[ $limit ] = get_posts(
		array(
			'post_type'        => 'gif_service',
			'posts_per_page'   => $limit,
			'orderby'          => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'suppress_filters' => false,
		)
	);

	return $cache[ $limit ];
}

/**
 * The palette class for a service card, cycling through the design's colours
 * when one has not been chosen.
 *
 * @param int $post_id Service ID.
 * @param int $index   Position in the grid.
 * @return string
 */
function gif_card_colour( $post_id, $index = 0 ) {
	$allowed = array_keys( gif_card_colour_choices() );
	$chosen  = get_post_meta( $post_id, '_gif_card_colour', true );

	if ( $chosen && in_array( $chosen, $allowed, true ) ) {
		return $chosen;
	}

	$cycle = array( 'bg-teal', 'bg-rose', 'bg-violet', 'bg-gold', 'bg-steel', 'bg-sage', 'bg-plum', 'bg-teal2', 'bg-ink' );
	return $cycle[ $index % count( $cycle ) ];
}

/**
 * The colours offered in the admin, matching the prototype's palette.
 *
 * @return array
 */
function gif_card_colour_choices() {
	return array(
		'bg-teal'   => __( 'Teal', 'getitframed' ),
		'bg-teal2'  => __( 'Teal (light)', 'getitframed' ),
		'bg-rose'   => __( 'Rose', 'getitframed' ),
		'bg-rose2'  => __( 'Rose (light)', 'getitframed' ),
		'bg-violet' => __( 'Violet', 'getitframed' ),
		'bg-plum'   => __( 'Plum', 'getitframed' ),
		'bg-sage'   => __( 'Sage', 'getitframed' ),
		'bg-steel'  => __( 'Steel', 'getitframed' ),
		'bg-slate'  => __( 'Slate', 'getitframed' ),
		'bg-gold'   => __( 'Gold', 'getitframed' ),
		'bg-ink'    => __( 'Ink', 'getitframed' ),
	);
}

/**
 * The card image for a service: featured image, or the bundled fallback that
 * shipped with the prototype, or nothing at all.
 *
 * @param int    $post_id Service ID.
 * @param string $size    Image size.
 * @return string HTML, escaped.
 */
function gif_card_image( $post_id, $size = 'gif-card' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail(
			$post_id,
			$size,
			array(
				'class'   => 'ga-card-img',
				'loading' => 'lazy',
			)
		);
	}

	$fallback = get_post_meta( $post_id, '_gif_fallback_image', true );
	if ( $fallback ) {
		return sprintf(
			'<img class="ga-card-img" src="%s" alt="%s" loading="lazy" width="600" height="450">',
			esc_url( GIF_URI . '/assets/img/' . $fallback ),
			esc_attr( get_the_title( $post_id ) )
		);
	}

	return '';
}

/**
 * Breadcrumb trail for inner pages.
 */
function gif_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'getitframed' ) . '">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'getitframed' ) . '</a>';

	if ( is_singular( 'gif_service' ) ) {
		$archive = get_post_type_archive_link( 'gif_service' );
		if ( $archive ) {
			echo ' <span>/</span> <a href="' . esc_url( $archive ) . '">' . esc_html__( 'Services', 'getitframed' ) . '</a>';
		}
	}

	if ( is_singular() ) {
		$parent = wp_get_post_parent_id( get_the_ID() );
		if ( $parent ) {
			echo ' <span>/</span> <a href="' . esc_url( get_permalink( $parent ) ) . '">' . esc_html( get_the_title( $parent ) ) . '</a>';
		}
		echo ' <span>/</span> <span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_post_type_archive() ) {
		echo ' <span>/</span> <span>' . esc_html( post_type_archive_title( '', false ) ) . '</span>';
	} elseif ( is_tax() ) {
		echo ' <span>/</span> <span>' . esc_html( single_term_title( '', false ) ) . '</span>';
	} elseif ( is_search() ) {
		echo ' <span>/</span> <span>' . esc_html__( 'Search results', 'getitframed' ) . '</span>';
	} elseif ( is_404() ) {
		echo ' <span>/</span> <span>' . esc_html__( 'Page not found', 'getitframed' ) . '</span>';
	}

	echo '</nav>';
}

/**
 * Print the page banner used by every template except the front page.
 *
 * @param string $label Small uppercase label above the title.
 * @param string $title Heading.
 * @param string $intro Optional standfirst.
 */
function gif_page_banner( $label, $title, $intro = '' ) {
	?>
	<section class="page-banner">
		<div class="container">
			<?php gif_breadcrumbs(); ?>
			<?php if ( $label ) : ?>
				<span class="section-label"><?php echo esc_html( $label ); ?></span>
			<?php endif; ?>
			<h1><?php echo esc_html( $title ); ?></h1>
			<?php if ( $intro ) : ?>
				<p><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Escaped output for a Customizer field that is allowed simple line breaks.
 *
 * @param string $value Raw value.
 * @return string
 */
function gif_nl2br( $value ) {
	return nl2br( esc_html( $value ) );
}

/**
 * Whether there is anything in the gallery yet.
 *
 * Used so the Gallery link is hidden until there are real photographs to show,
 * rather than pointing at an empty page.
 *
 * @return bool
 */
function gif_has_gallery() {
	static $has = null;

	if ( null === $has ) {
		$found = get_posts(
			array(
				'post_type'      => 'gif_gallery',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		$has = ! empty( $found );
	}

	return $has;
}

/**
 * Gallery categories that actually contain something.
 *
 * @return WP_Term[]
 */
function gif_gallery_terms() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'gif_gallery_cat',
			'hide_empty' => true,
			'orderby'    => 'name',
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Point the no-JavaScript fallback at the enquiry form, not just the page.
 *
 * Without this, someone with JavaScript off who is already ON the contact page
 * clicks "Email us" and reloads the page they are standing on. Not fatal --
 * the form is right there -- but it is a dead click, and a dead click is
 * exactly the failure this whole approach exists to avoid.
 *
 * @param string $url Fallback URL from the email guard.
 * @return string
 */
function gif_email_fallback_url( $url ) {
	return $url . '#enquiry';
}
add_filter( 'smile_email_fallback_url', 'gif_email_fallback_url' );

/**
 * The studio email address, protected from harvesters.
 *
 * Why this exists as a wrapper rather than a direct call:
 *
 * The Smile Creative email guard (mu-plugins/smile-email-guard.php) filters
 * the_content and widget_text. Every address on this site is printed by a
 * theme template instead, so installing the guard on its own protected
 * nothing at all -- the decoder loaded into the footer and found no addresses
 * to decode, while the theme carried on publishing them. Checked on the live
 * contact page: decoder present, zero protected spans.
 *
 * function_exists() is deliberate. If the mu-plugin is ever removed the theme
 * must not fatal, and it must not silently print nothing either -- it falls
 * back to what it did before, which is weak but is a working address.
 *
 * @param string $text  Optional link text. Empty means show the address.
 * @param bool   $link  Whether to render as a mailto: link.
 * @param array  $attrs Optional extra attributes.
 * @return string Ready-to-echo HTML.
 */
function gif_email_html( $text = '', $link = true, $attrs = array() ) {
	$address = (string) gif_opt( 'email' );
	if ( '' === $address ) {
		return '';
	}

	if ( function_exists( 'smile_email_link' ) ) {
		return smile_email_link( $address, $text, $attrs );
	}

	// Fallback: WordPress core entity encoding. Weaker -- any harvester that
	// HTML-decodes before matching reads straight through it -- but a real,
	// clickable address is better than a blank space.
	$safe = antispambot( $address );
	if ( ! $link ) {
		return esc_html( $safe );
	}

	$extra = '';
	foreach ( $attrs as $k => $v ) {
		$extra .= sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( $v ) );
	}

	return sprintf(
		'<a href="mailto:%1$s"%2$s>%3$s</a>',
		esc_attr( $safe ),
		$extra,
		esc_html( '' === $text ? $safe : $text )
	);
}
