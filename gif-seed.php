<?php
/**
 * One-off content seeder.
 *
 * Recreates the approved prototype as real WordPress content: the twelve
 * services, their images and card colours, and the pages. Run once on a fresh
 * install, then delete it. Nothing here is part of the theme.
 *
 * Usage: php gif-seed.php
 */

if ( 'cli' !== php_sapi_name() ) {
	exit( "CLI only\n" );
}

require_once __DIR__ . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

echo "Seeding Get It Framed NI...\n";

// -- Theme and basic settings ------------------------------------------------
switch_theme( 'getitframed' );
update_option( 'blogname', 'Get It Framed NI' );
update_option( 'blogdescription', 'Professional Print & Framing Services' );
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'timezone_string', 'Europe/London' );
update_option( 'date_format', 'j F Y' );
update_option( 'start_of_week', 1 );
update_option( 'default_comment_status', 'closed' );
update_option( 'default_ping_status', 'closed' );

/**
 * Import a bundled theme image into the media library.
 *
 * @param string $file Filename inside assets/img.
 * @param string $alt  Alt text.
 * @return int Attachment ID, or 0.
 */
function gif_seed_image( $file, $alt ) {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_gif_seed_file', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $file, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}

	$path = get_template_directory() . '/assets/img/' . $file;
	if ( ! file_exists( $path ) ) {
		echo "  ! missing image {$file}\n";
		return 0;
	}

	$upload = wp_upload_bits( $file, null, file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! empty( $upload['error'] ) ) {
		echo "  ! upload failed for {$file}: {$upload['error']}\n";
		return 0;
	}

	$id = wp_insert_attachment(
		array(
			'post_mime_type' => $upload['type'],
			'post_title'     => $alt,
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}

	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, '_gif_seed_file', $file );

	return (int) $id;
}

/**
 * Create or update a post by slug.
 *
 * @param array $args Post args, must include post_name.
 * @return int
 */
function gif_seed_post( $args ) {
	$existing = get_page_by_path( $args['post_name'], OBJECT, $args['post_type'] );
	if ( $existing ) {
		$args['ID'] = $existing->ID;
	}
	$id = wp_insert_post( $args, true );
	if ( is_wp_error( $id ) ) {
		echo '  ! ' . $id->get_error_message() . "\n";
		return 0;
	}
	return (int) $id;
}

// -- Services ----------------------------------------------------------------
$services = array(
	array(
		'title'   => 'Prints',
		'slug'    => 'prints',
		'excerpt' => 'Gloss, lustre, giclee, canvas and fine art prints. We print from mobile photos, memory cards, online galleries and files from amateur and professional photographers.',
		'image'   => 'svc-photo-printing.webp',
		'alt'     => 'Hand holding mounted photo prints including sunset and landscape photographs',
		'colour'  => 'bg-teal',
		'strap'   => 'Prints & fine art photo printing',
		'content' => '<!-- wp:heading --><h2>High-quality printing tailored to you</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>We don\'t believe in one-size-fits-all printing. Different images call for different textures, weights and finishes. We offer a comprehensive range of professional photo printing options.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Standard &amp; custom sizes</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>From small pocket prints and standard desk-frame sizes to striking, large-format statement pieces for your living room or office.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Premium paper finishes</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Choose from classic high-gloss for vibrant contrast, smooth satin or lustre to minimise glare, or heavy matte for a contemporary look.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Fine art &amp; giclée printing</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Perfect for artists, illustrators and photographers who need archival-quality prints with flawless colour accuracy and longevity.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Flexible presentation options</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Order your prints loose, paired with custom-cut photo mounts, or handed straight to our in-house framing team.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Why choose Get It Framed NI?</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>The high street photo booth and the cheap online print factories simply cannot match the human eye. When you bring your printing to us you benefit from fifteen years of colour calibration, paper knowledge and technical expertise.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>We review your files to catch low-resolution warnings or awkward cropping before ink ever touches paper. We use premium, fade-resistant inks and archival-grade papers, so your prints look as good decades from now as they do the day you collect them.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul><!-- wp:list-item --><li>Fifteen years of colour calibration expertise</li><!-- /wp:list-item --><!-- wp:list-item --><li>Premium, fade-resistant inks and archival-grade papers</li><!-- /wp:list-item --><!-- wp:list-item --><li>Every file reviewed before printing begins</li><!-- /wp:list-item --></ul><!-- /wp:list -->
<!-- wp:heading --><h2>Serving County Antrim and shipping worldwide</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Our studio is a fixture of the Portglenone community, and we welcome local clients from across County Antrim, Ballymena, Magherafelt and beyond to come and feel the paper samples in person.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Distance is no barrier. If you are ordering from further afield we package prints in heavy-duty protective tubes or flat-packs and ship worldwide.</p><!-- /wp:paragraph -->',
	),
	array(
		'title'   => 'Canvas Printing',
		'slug'    => 'canvas-printing',
		'excerpt' => 'Rolled, stretched only, or stretched and framed.',
		'image'   => 'svc-canvas.webp',
		'alt'     => 'Canvas being stretched and stapled onto a wooden stretcher frame',
		'colour'  => 'bg-rose',
		'content' => '',
	),
	array(
		'title'   => 'Mounting',
		'slug'    => 'mounting',
		'excerpt' => 'Photo mounts, single, multi-aperture, round, oval and custom shapes. Bulk orders welcome. Permanent mounting onto mount board, foam board and MDF. Overseal options available.',
		'image'   => 'svc-mounting.webp',
		'alt'     => 'Cut photo mounts in a range of apertures and colours',
		'colour'  => 'bg-violet',
		'content' => '',
	),
	array(
		'title'   => 'Framing',
		'slug'    => 'framing',
		'excerpt' => 'We frame prints, canvases, artwork, needlework, football shirts, memorabilia, jigsaws and unique items. Minor frame repairs also available.',
		'image'   => 'svc-framing.webp',
		'alt'     => 'Stack of wooden picture frame mouldings in various finishes',
		'colour'  => 'bg-gold',
		'content' => '',
	),
	array(
		'title'   => 'Glass',
		'slug'    => 'glass',
		'excerpt' => 'Replace old, scratched or broken glass. Float, art glass with UV protection, museum-quality plastic glazing and mirrored glass, all available.',
		'image'   => 'svc-glass.webp',
		'alt'     => 'Framed ZX Spectrum computer behind glass in a deep box frame with a red mount',
		'colour'  => 'bg-steel',
		'content' => '',
	),
	array(
		'title'   => 'Artist Services',
		'slug'    => 'artist-services',
		'excerpt' => 'Digitising originals, reprints on various media, mounting and framing of originals and reprints. Portfolio albums to showcase your work.',
		'image'   => 'svc-artist.webp',
		'alt'     => 'Mounted and packaged art prints of farm animals and countryside scenes',
		'colour'  => 'bg-violet',
		'content' => '',
	),
	array(
		'title'   => 'Photographer Services',
		'slug'    => 'photographer-services',
		'excerpt' => 'Prints on a range of media, mounted, mounted and framed, or supplied ready for your own presentation.',
		'image'   => 'svc-photographer.webp',
		'alt'     => 'Collection of mounted landscape photographs laid out on a studio table',
		'colour'  => 'bg-rose',
		'content' => '',
	),
	array(
		'title'   => 'Storybook Albums',
		'slug'    => 'storybook-albums',
		'excerpt' => 'Lay flat storybook albums on high quality lustre paper. Leather or linen covers in a range of colours. Album design service available.',
		'image'   => 'svc-albums.webp',
		'alt'     => 'Lay flat storybook photo albums in various cover finishes',
		'colour'  => 'bg-sage',
		'content' => '',
	),
	array(
		'title'   => 'Digitising',
		'slug'    => 'digitising',
		'excerpt' => 'Digitise artwork, old photos, negatives, slides and albums. Photo restoration and VHS tape conversion. Digital images provided on your memory device.',
		'image'   => 'svc-digitising.webp',
		'alt'     => 'Collection of film negatives and slides ready for digitising',
		'colour'  => 'bg-plum',
		'content' => '',
	),
	array(
		'title'   => 'Photo Products',
		'slug'    => 'photo-products',
		'excerpt' => 'Keyrings, coasters, photo blocks, fridge magnets, glitter and snow blocks, and seasonal Christmas gifts.',
		'image'   => 'svc-gifts.webp',
		'alt'     => 'Glass photo coaster with a sunset seascape print held in a hand',
		'colour'  => 'bg-teal2',
		'content' => '',
	),
	array(
		'title'   => 'Laser Engraving',
		'slug'    => 'laser-engraving',
		'excerpt' => 'Custom laser engraving onto wooden gifts for private and corporate clients. Keyrings, coasters, bottle openers and more.',
		'image'   => 'svc-engraving.webp',
		'alt'     => 'Laser engraved wooden keyring',
		'colour'  => 'bg-ink',
		'content' => '',
	),
	array(
		'title'   => 'Commercial Printing',
		'slug'    => 'commercial-printing',
		'excerpt' => 'Business cards, flyers, pull up banners, PVC banners and leaflets. Design available.',
		// Deliberately no image: the prototype reused the engraving photo here,
		// which shows the wrong thing. Better to flag it as missing.
		'image'   => '',
		'alt'     => '',
		'colour'  => 'bg-gold',
		'content' => '',
	),
);

$order = 0;
foreach ( $services as $service ) {
	$order += 10;

	$id = gif_seed_post(
		array(
			'post_type'    => 'gif_service',
			'post_name'    => $service['slug'],
			'post_title'   => $service['title'],
			'post_excerpt' => $service['excerpt'],
			'post_content' => $service['content'],
			'post_status'  => 'publish',
			'menu_order'   => $order,
		)
	);

	if ( ! $id ) {
		continue;
	}

	update_post_meta( $id, '_gif_card_colour', $service['colour'] );
	if ( ! empty( $service['strap'] ) ) {
		update_post_meta( $id, '_gif_strapline', $service['strap'] );
	}

	if ( $service['image'] ) {
		$attachment = gif_seed_image( $service['image'], $service['alt'] );
		if ( $attachment ) {
			set_post_thumbnail( $id, $attachment );
		}
	}

	printf( "  service: %-24s %s\n", $service['title'], $service['content'] ? '(full copy)' : '(summary only)' );
}

// -- Pages -------------------------------------------------------------------
$home_id = gif_seed_post(
	array(
		'post_type'   => 'page',
		'post_name'   => 'home',
		'post_title'  => 'Home',
		'post_status' => 'publish',
	)
);

if ( $home_id ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_id );
}

gif_seed_post(
	array(
		'post_type'    => 'page',
		'post_name'    => 'about',
		'post_title'   => 'About the Studio',
		'post_excerpt' => 'A working print and framing studio in the heart of Portglenone, serving the public and the trade for over fifteen years.',
		'post_status'  => 'publish',
		'post_content' => '<!-- wp:paragraph --><p>We work closely within the trade sector and are the chosen framing partner for photographers and artists who need dependable production. That means consistent output across repeat orders, exhibitions and client deliveries, with careful attention to borders, mounting alignment and finish.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Because we print, mount and frame under one roof, the details tie up: the print is sized for the mount, the mount sits perfectly in the frame, and the finished piece feels cohesive.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul><!-- wp:list-item --><li>Consistent output across repeat orders</li><!-- /wp:list-item --><!-- wp:list-item --><li>Accurate glass cutting and precise mounting</li><!-- /wp:list-item --><!-- wp:list-item --><li>Careful attention to borders and finish</li><!-- /wp:list-item --><!-- wp:list-item --><li>Studio standards for the general public</li><!-- /wp:list-item --></ul><!-- /wp:list -->
<!-- wp:paragraph --><p>At Get It Framed NI we believe your favourite memories, digital photography and artwork deserve to live on walls, not just on screens and hard drives. From our dedicated print and framing studio in the heart of Portglenone, we turn digital files into tangible prints.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Whether you are printing a single family photo from a phone or need a full collection of exhibition-ready fine art reproductions, we treat every image with the care and precision it deserves. Operating locally for over fifteen years, we combine current print technology with a real interest in the craft.</p><!-- /wp:paragraph -->',
	)
);

gif_seed_post(
	array(
		'post_type'    => 'page',
		'post_name'    => 'contact',
		'post_title'   => 'Contact',
		'post_excerpt' => 'Tell us what you have and what you would like done with it, and we will come back to you.',
		'post_status'  => 'publish',
		'post_content' => '',
	)
);

// Trade stays a draft until the client supplies the copy. A draft is invisible
// to visitors and drops out of the menu automatically, which is why there is no
// dead "Trade" link.
gif_seed_post(
	array(
		'post_type'    => 'page',
		'post_name'    => 'trade',
		'post_title'   => 'Trade',
		'post_status'  => 'draft',
		'post_content' => '<!-- wp:paragraph --><p>Awaiting content from the client.</p><!-- /wp:paragraph -->',
	)
);

// -- Gallery categories, ready for real photographs --------------------------
foreach ( array( 'Framing', 'Mounting', 'Canvas', 'Prints', 'Engraving' ) as $term ) {
	if ( ! term_exists( $term, 'gif_gallery_cat' ) ) {
		wp_insert_term( $term, 'gif_gallery_cat' );
	}
}

// -- Homepage imagery --------------------------------------------------------
$hero = gif_seed_image( 'hero.webp', 'Inside the Get It Framed NI print and framing studio' );
if ( $hero ) {
	set_theme_mod( 'gif_hero_image', $hero );
}
$about_img = gif_seed_image( 'about-studio.webp', 'Framed artwork and prints in the Get It Framed NI studio' );
if ( $about_img ) {
	set_theme_mod( 'gif_about_image', $about_img );
}
$logo = gif_seed_image( 'logo.png', 'Get It Framed NI' );
if ( $logo ) {
	set_theme_mod( 'custom_logo', $logo );
}

// Rewrite rules for the new post types.
gif_register_post_types();
flush_rewrite_rules();

echo "Done.\n";
