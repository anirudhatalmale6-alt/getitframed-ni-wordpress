<?php
/**
 * Search-engine output.
 *
 * The prototype had no meta description, no social tags and no structured
 * data. For a studio whose customers search "picture framing near me", the
 * LocalBusiness markup below is the piece that actually earns its keep.
 *
 * Kept in the theme deliberately: no SEO plugin to install, update or have
 * expire, and nothing that rewrites the site's markup behind our backs.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta description and Open Graph tags.
 */
function gif_head_meta() {
	$description = '';
	$image       = gif_image_url( 'gif_hero_image', 'hero.webp', 'full' );
	$title       = wp_get_document_title();
	$url         = home_url( add_query_arg( array() ) );

	if ( is_front_page() ) {
		$description = gif_opt( 'seo_description' );
	} elseif ( is_singular() ) {
		$post_id     = get_the_ID();
		$description = has_excerpt( $post_id )
			? get_the_excerpt( $post_id )
			: wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 30, '…' );
		$url         = get_permalink( $post_id );
		if ( has_post_thumbnail( $post_id ) ) {
			$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'gif-service-hero' );
			if ( $thumb ) {
				$image = $thumb[0];
			}
		}
	} elseif ( is_post_type_archive( 'gif_service' ) ) {
		$description = __( 'Printing, mounting, framing, canvas, digitising, laser engraving and photo gifts, from a working studio in Portglenone, Co. Antrim.', 'getitframed' );
	} elseif ( is_post_type_archive( 'gif_gallery' ) || is_tax( 'gif_gallery_cat' ) ) {
		$description = __( 'Recent framing, mounting and print work from the Get It Framed NI studio.', 'getitframed' );
	}

	// House standard is under 155 characters, so Google shows the whole thing
	// rather than cutting it off mid-sentence. Trim on a word boundary.
	$description = trim( wp_strip_all_tags( $description ) );
	if ( mb_strlen( $description ) > 155 ) {
		$description = mb_substr( $description, 0, 154 );
		$cut         = mb_strrpos( $description, ' ' );
		if ( $cut && $cut > 100 ) {
			$description = mb_substr( $description, 0, $cut );
		}
		$description = rtrim( $description, " ,.;:-" ) . '…';
	}

	if ( $description ) {
		printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( $description ) );
	}

	printf( "<meta property=\"og:type\" content=\"%s\">\n", is_singular() && ! is_front_page() ? 'article' : 'website' );
	printf( "<meta property=\"og:title\" content=\"%s\">\n", esc_attr( $title ) );
	if ( $description ) {
		printf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( $description ) );
	}
	printf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( $url ) );
	printf( "<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( "<meta property=\"og:locale\" content=\"%s\">\n", 'en_GB' );
	printf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( $image ) );
	printf( "<meta name=\"twitter:card\" content=\"%s\">\n", 'summary_large_image' );
}
add_action( 'wp_head', 'gif_head_meta', 5 );

/**
 * LocalBusiness structured data, built from the Customizer values so it can
 * never drift out of step with what the page says.
 */
function gif_schema() {
	if ( ! is_front_page() && ! is_page( 'contact' ) ) {
		return;
	}

	$defaults = gif_defaults();

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'LocalBusiness',
		'@id'         => home_url( '/#business' ),
		'name'        => get_bloginfo( 'name' ),
		'description' => gif_opt( 'seo_description' ),
		'url'         => home_url( '/' ),
		'image'       => gif_image_url( 'gif_hero_image', 'hero.webp', 'full' ),
		'logo'        => GIF_URI . '/assets/img/logo.png',
		'address'     => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => gif_opt( 'address_line1' ),
			'addressLocality' => $defaults['locality'],
			'addressRegion'   => $defaults['region'],
			'postalCode'      => $defaults['postcode'],
			'addressCountry'  => $defaults['country'],
		),
	);

	$phone = gif_opt( 'phone_main' );
	if ( $phone ) {
		$schema['telephone'] = $phone;
	}

	$email = gif_opt( 'email' );
	if ( is_email( $email ) ) {
		$schema['email'] = $email;
	}

	$social = array_filter( array( gif_opt( 'facebook' ), gif_opt( 'instagram' ) ) );
	if ( $social ) {
		$schema['sameAs'] = array_values( $social );
	}

	$services = gif_services();
	if ( $services ) {
		$schema['makesOffer'] = array_map(
			function ( $service ) {
				return array(
					'@type'       => 'Offer',
					'itemOffered' => array(
						'@type' => 'Service',
						'name'  => get_the_title( $service ),
						'url'   => get_permalink( $service ),
					),
				);
			},
			$services
		);
	}

	gif_print_schema( $schema );
}
add_action( 'wp_head', 'gif_schema', 10 );

/**
 * Print one JSON-LD block.
 *
 * @param array $schema Schema array.
 */
function gif_print_schema( $schema ) {
	printf(
		"<script type=\"application/ld+json\">%s</script>\n",
		wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}

/**
 * WebSite markup on the homepage. Tells search engines the site's canonical
 * name, which is what stops them inventing one from the domain.
 */
function gif_schema_website() {
	if ( ! is_front_page() ) {
		return;
	}

	gif_print_schema(
		array(
			'@context'      => 'https://schema.org',
			'@type'         => 'WebSite',
			'@id'           => home_url( '/#website' ),
			'name'          => get_bloginfo( 'name' ),
			'url'           => home_url( '/' ),
			'inLanguage'    => 'en-GB',
			'publisher'     => array( '@id' => home_url( '/#business' ) ),
			'alternateName' => 'Get It Framed',
		)
	);
}
add_action( 'wp_head', 'gif_schema_website', 11 );

/**
 * Service markup on each service page, and BreadcrumbList on every inner page.
 * Breadcrumbs are what produce the "Home > Services > Framing" line under a
 * search result instead of a bare URL.
 */
function gif_schema_inner() {
	if ( is_front_page() ) {
		return;
	}

	$crumbs = array( array( 'name' => __( 'Home', 'getitframed' ), 'url' => home_url( '/' ) ) );

	if ( is_singular( 'gif_service' ) ) {
		$archive = get_post_type_archive_link( 'gif_service' );
		if ( $archive ) {
			$crumbs[] = array( 'name' => __( 'Services', 'getitframed' ), 'url' => $archive );
		}
		$crumbs[] = array( 'name' => get_the_title(), 'url' => get_permalink() );

		$defaults = gif_defaults();
		$service  = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Service',
			'@id'         => get_permalink() . '#service',
			'name'        => get_the_title(),
			'url'         => get_permalink(),
			'description' => has_excerpt() ? wp_strip_all_tags( get_the_excerpt() ) : '',
			'provider'    => array( '@id' => home_url( '/#business' ) ),
			'areaServed'  => array(
				'@type' => 'AdministrativeArea',
				'name'  => $defaults['region'],
			),
		);
		if ( has_post_thumbnail() ) {
			$service['image'] = get_the_post_thumbnail_url( null, 'gif-service-hero' );
		}
		gif_print_schema( $service );
	} elseif ( is_singular() ) {
		$crumbs[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_post_type_archive( 'gif_service' ) ) {
		$crumbs[] = array( 'name' => __( 'Services', 'getitframed' ), 'url' => get_post_type_archive_link( 'gif_service' ) );
	} elseif ( is_post_type_archive( 'gif_gallery' ) ) {
		$crumbs[] = array( 'name' => __( 'Gallery', 'getitframed' ), 'url' => get_post_type_archive_link( 'gif_gallery' ) );
	} else {
		return;
	}

	$items = array();
	foreach ( $crumbs as $i => $crumb ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $crumb['name'],
			'item'     => $crumb['url'],
		);
	}

	gif_print_schema(
		array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		)
	);
}
add_action( 'wp_head', 'gif_schema_inner', 12 );

/**
 * Canonical for the front page, which WordPress does not output by default
 * on a static front page in every configuration.
 */
function gif_front_canonical() {
	if ( is_front_page() ) {
		printf( "<link rel=\"canonical\" href=\"%s\">\n", esc_url( home_url( '/' ) ) );
	}
}
add_action( 'wp_head', 'gif_front_canonical', 6 );

/**
 * Favicon.
 *
 * Bundled with the theme rather than uploaded, so it is present the moment the
 * theme is activated and survives a media-library clear-out. If someone later
 * sets a Site Icon in Settings, that wins and this steps aside.
 */
function gif_favicon() {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
		return;
	}

	$img = GIF_URI . '/assets/img';
	printf( "<link rel=\"icon\" href=\"%s/favicon.ico\" sizes=\"any\">\n", esc_url( $img ) );
	printf( "<link rel=\"icon\" type=\"image/png\" sizes=\"32x32\" href=\"%s/favicon-32.png\">\n", esc_url( $img ) );
	printf( "<link rel=\"apple-touch-icon\" href=\"%s/apple-touch-icon.png\">\n", esc_url( $img ) );
	printf( "<meta name=\"theme-color\" content=\"%s\">\n", '#048A81' );
}
add_action( 'wp_head', 'gif_favicon', 4 );

/**
 * Enquiry records must never be indexed, even by accident.
 */
function gif_noindex_enquiries() {
	if ( is_singular( 'gif_enquiry' ) ) {
		echo "<meta name=\"robots\" content=\"noindex, nofollow\">\n";
	}
}
add_action( 'wp_head', 'gif_noindex_enquiries', 1 );
