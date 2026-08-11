<?php
/**
 * Customizer settings.
 *
 * Everything here is content that appears in more than one place -- phone
 * numbers, address, the homepage wording -- so it belongs in one field rather
 * than being retyped into several pages. Live preview also gives the "change a
 * word while they watch" demo without a page builder.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme defaults, taken from the approved prototype so a fresh install looks
 * exactly like the design that was signed off.
 *
 * @return array
 */
function gif_defaults() {
	return array(
		'phone_main'      => '028 2588 2706',
		'phone_mobile'    => '077 6384 2761',
		'email'           => 'GetItFramedNI@gmail.com',
		'address_line1'   => 'Unit 9, 26 Townhill Road',
		'address_line2'   => 'Portglenone, BT44 8AD',
		'address_line3'   => 'Northern Ireland',
		'locality'        => 'Portglenone',
		'region'          => 'Co. Antrim',
		'postcode'        => 'BT44 8AD',
		'country'         => 'GB',
		'topbar_location' => 'Portglenone, Co. Antrim, Northern Ireland',
		'topbar_tagline'  => 'Trade partner for photographers & artists',
		'hours'           => '',
		'turnaround'      => 'Varies by service — tell us your deadline and we’ll do our best to meet it.',
		'map_embed'       => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2305.5!2d-6.4583!3d54.8583!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4861a73db0c3f8a1%3A0x9e5ec23a826c5a06!2s26+Townhill+Rd%2C+Portglenone%2C+Ballymena+BT44+8AD!5e0!3m2!1sen!2suk!4v1',
		'facebook'        => '',
		'instagram'       => '',

		'hero_label'      => 'Your Framing Partner',
		'hero_heading'    => 'Printing, framing and more…',
		'hero_emphasis'   => 'all under one roof',
		'hero_standfirst' => 'Catering for everyone’s needs from retail buyers to trade professionals',
		'hero_intro'      => 'Operating locally for over 15 years, Get It Framed NI is a professional print & framing studio operating in the heart of Northern Ireland and shipping worldwide. Supplying prints, mounts, frames, canvases, albums and a variety of similar services and gifts; we cater for everyone from the home improver to photographers, artists and the business world.',

		'services_heading' => 'Our Services',
		'services_sub'     => '',

		'cta_heading'     => 'Ready to get started?',
		'cta_text'        => "Bring in your piece or get in touch.\nWe're happy to advise on the best approach for your project.",

		'footer_about'    => 'A friendly local studio for printing, framing and digitisation — based in Portglenone, serving public and trade customers locally, nationally & internationally.',

		'enquiry_to'      => '',
		'seo_description' => 'Professional print and framing studio in Portglenone, Co. Antrim. Printing, mounting, framing, canvas, digitising and laser engraving for the public and the trade.',
	);
}

/**
 * Register the panels, sections and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 */
function gif_customize_register( $wp_customize ) {
	$defaults = gif_defaults();

	$wp_customize->add_panel(
		'gif_panel',
		array(
			'title'       => __( 'Get It Framed', 'getitframed' ),
			'description' => __( 'Business details and homepage wording.', 'getitframed' ),
			'priority'    => 20,
		)
	);

	/**
	 * Add a text control in one line.
	 *
	 * @param string $id      Setting id, without prefix.
	 * @param string $label   Label.
	 * @param string $section Section id.
	 * @param string $type    Control type.
	 * @param string $desc    Optional description.
	 */
	$add = function ( $id, $label, $section, $type = 'text', $desc = '' ) use ( $wp_customize, $defaults ) {
		$sanitize = 'sanitize_text_field';
		if ( 'textarea' === $type ) {
			$sanitize = 'sanitize_textarea_field';
		} elseif ( 'email' === $type ) {
			$sanitize = 'sanitize_email';
		} elseif ( 'url' === $type ) {
			$sanitize = 'esc_url_raw';
		}

		$wp_customize->add_setting(
			'gif_' . $id,
			array(
				'default'           => isset( $defaults[ $id ] ) ? $defaults[ $id ] : '',
				'sanitize_callback' => $sanitize,
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'gif_' . $id,
			array(
				'label'       => $label,
				'section'     => $section,
				'type'        => $type,
				'description' => $desc,
			)
		);
	};

	// -- Contact details ---------------------------------------------------
	$wp_customize->add_section(
		'gif_contact',
		array(
			'title' => __( 'Contact details', 'getitframed' ),
			'panel' => 'gif_panel',
		)
	);
	$add( 'phone_main', __( 'Studio phone', 'getitframed' ), 'gif_contact' );
	$add( 'phone_mobile', __( 'Mobile', 'getitframed' ), 'gif_contact' );
	$add( 'email', __( 'Public email address', 'getitframed' ), 'gif_contact', 'email' );
	$add( 'enquiry_to', __( 'Send enquiries to', 'getitframed' ), 'gif_contact', 'email', __( 'Leave blank to use the public email address above.', 'getitframed' ) );
	$add( 'address_line1', __( 'Address line 1', 'getitframed' ), 'gif_contact' );
	$add( 'address_line2', __( 'Address line 2', 'getitframed' ), 'gif_contact' );
	$add( 'address_line3', __( 'Address line 3', 'getitframed' ), 'gif_contact' );
	$add( 'hours', __( 'Opening hours', 'getitframed' ), 'gif_contact', 'textarea', __( 'One line per day. Leave blank to hide.', 'getitframed' ) );
	$add( 'turnaround', __( 'Turnaround note', 'getitframed' ), 'gif_contact', 'textarea' );
	$add( 'map_embed', __( 'Google Maps embed URL', 'getitframed' ), 'gif_contact', 'url' );
	$add( 'facebook', __( 'Facebook page URL', 'getitframed' ), 'gif_contact', 'url' );
	$add( 'instagram', __( 'Instagram URL', 'getitframed' ), 'gif_contact', 'url' );

	// -- Top bar -----------------------------------------------------------
	$wp_customize->add_section(
		'gif_topbar',
		array(
			'title' => __( 'Top bar', 'getitframed' ),
			'panel' => 'gif_panel',
		)
	);
	$add( 'topbar_location', __( 'Location text', 'getitframed' ), 'gif_topbar' );
	$add( 'topbar_tagline', __( 'Right-hand tagline', 'getitframed' ), 'gif_topbar' );

	// -- Homepage ----------------------------------------------------------
	$wp_customize->add_section(
		'gif_home',
		array(
			'title'       => __( 'Homepage', 'getitframed' ),
			'panel'       => 'gif_panel',
			'description' => __( 'The wording on the front page. The services grid builds itself from the Services list.', 'getitframed' ),
		)
	);
	$add( 'hero_label', __( 'Small label', 'getitframed' ), 'gif_home' );
	$add( 'hero_heading', __( 'Heading', 'getitframed' ), 'gif_home' );
	$add( 'hero_emphasis', __( 'Heading, highlighted part', 'getitframed' ), 'gif_home', 'text', __( 'Shown in rose at the end of the heading.', 'getitframed' ) );
	$add( 'hero_standfirst', __( 'Standfirst', 'getitframed' ), 'gif_home', 'textarea' );
	$add( 'hero_intro', __( 'Intro paragraph', 'getitframed' ), 'gif_home', 'textarea' );
	$add( 'services_heading', __( 'Services heading', 'getitframed' ), 'gif_home' );
	$add( 'services_sub', __( 'Services sub-heading', 'getitframed' ), 'gif_home', 'textarea' );
	$add( 'cta_heading', __( 'Call-to-action heading', 'getitframed' ), 'gif_home' );
	$add( 'cta_text', __( 'Call-to-action text', 'getitframed' ), 'gif_home', 'textarea' );

	// Hero image, with the prototype photo as the default.
	$wp_customize->add_setting(
		'gif_hero_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'gif_hero_image',
			array(
				'label'     => __( 'Hero image', 'getitframed' ),
				'section'   => 'gif_home',
				'mime_type' => 'image',
			)
		)
	);

	$wp_customize->add_setting(
		'gif_about_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'gif_about_image',
			array(
				'label'     => __( 'About section image', 'getitframed' ),
				'section'   => 'gif_home',
				'mime_type' => 'image',
			)
		)
	);

	// -- Footer ------------------------------------------------------------
	$wp_customize->add_section(
		'gif_footer',
		array(
			'title' => __( 'Footer', 'getitframed' ),
			'panel' => 'gif_panel',
		)
	);
	$add( 'footer_about', __( 'Footer blurb', 'getitframed' ), 'gif_footer', 'textarea' );

	// -- Search engines ----------------------------------------------------
	$wp_customize->add_section(
		'gif_seo',
		array(
			'title'       => __( 'Search engines', 'getitframed' ),
			'panel'       => 'gif_panel',
			'description' => __( 'The description Google shows for the homepage. Aim for about 155 characters.', 'getitframed' ),
		)
	);
	$add( 'seo_description', __( 'Homepage description', 'getitframed' ), 'gif_seo', 'textarea' );
}
add_action( 'customize_register', 'gif_customize_register' );

/**
 * Hero image URL, falling back to the prototype photo.
 *
 * @param string $mod  Theme mod name.
 * @param string $file Bundled fallback filename.
 * @param string $size Image size.
 * @return string
 */
function gif_image_url( $mod, $file, $size = 'large' ) {
	$id = (int) get_theme_mod( $mod, 0 );
	if ( $id ) {
		$src = wp_get_attachment_image_src( $id, $size );
		if ( $src ) {
			return $src[0];
		}
	}
	return GIF_URI . '/assets/img/' . $file;
}
