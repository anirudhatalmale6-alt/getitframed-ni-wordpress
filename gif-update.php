<?php
/**
 * Get It Framed NI — one-shot content update, 26 Aug 2026.
 *
 * 1. Installs the correct header logo (the "Printing ~ Mounting ~ Framing ~ Albums"
 *    plaque Bev sent) and sets it as the site logo.
 * 2. Gives the Commercial Printing service a placeholder photograph so its card and
 *    its page are no longer blank.
 * 3. Reports how site email is configured, and what happened to the enquiries that
 *    have already come through the contact form.
 *
 * Token-guarded. Deletes itself and its asset folder when it has finished, so it
 * cannot be run twice or found later.
 */

$tok = 'GIFUPD' . '_REPLACE_WITH_A_RANDOM_TOKEN';
if ( ! isset( $_GET['t'] ) || $_GET['t'] !== $tok ) {
	http_response_code( 404 );
	exit;
}

require dirname( __FILE__ ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

header( 'Content-Type: text/plain; charset=utf-8' );
@set_time_limit( 300 );

$assets = dirname( __FILE__ ) . '/gif-assets/';
$upload = wp_upload_dir();

echo "GET IT FRAMED NI — update run " . gmdate( 'Y-m-d H:i:s' ) . " UTC\n";
echo str_repeat( '=', 62 ) . "\n\n";

/**
 * Copy a file from the asset folder into the media library and return its ID.
 */
function gif_add_media( $filename, $title, $alt, $assets, $upload ) {
	$src = $assets . $filename;
	if ( ! file_exists( $src ) ) {
		echo "  !! missing asset: $filename\n";
		return 0;
	}
	$dst = trailingslashit( $upload['path'] ) . wp_unique_filename( $upload['path'], $filename );
	if ( ! copy( $src, $dst ) ) {
		echo "  !! could not write into " . $upload['path'] . "\n";
		return 0;
	}
	$type = wp_check_filetype( $dst );
	$id   = wp_insert_attachment(
		array(
			'post_mime_type' => $type['type'],
			'post_title'     => $title,
			'post_status'    => 'inherit',
		),
		$dst
	);
	if ( ! $id || is_wp_error( $id ) ) {
		echo "  !! attachment insert failed for $filename\n";
		return 0;
	}
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $dst ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	echo "  media #$id  " . basename( $dst ) . "\n";
	return (int) $id;
}

/* ------------------------------------------------------------------ 1. LOGO */
echo "1. HEADER LOGO\n";
$old_logo = (int) get_theme_mod( 'custom_logo' );
echo "  previous logo attachment: " . ( $old_logo ? '#' . $old_logo . ' (' . basename( (string) get_attached_file( $old_logo ) ) . ')' : 'none' ) . "\n";

$logo_id = gif_add_media(
	'logo-2026.webp',
	'Get It Framed NI logo',
	'Get It Framed NI — printing, mounting, framing and albums',
	$assets,
	$upload
);
if ( $logo_id ) {
	set_theme_mod( 'custom_logo', $logo_id );
	echo "  site logo set to #$logo_id\n";
	$sz = wp_get_attachment_image_src( $logo_id, 'full' );
	echo "  natural size: " . ( $sz ? $sz[1] . 'x' . $sz[2] : '?' ) . " (rendered at 84px tall by the theme)\n";
} else {
	echo "  LOGO NOT CHANGED\n";
}
echo "\n";

/* ------------------------------------------ 2. COMMERCIAL PRINTING IMAGE */
echo "2. COMMERCIAL PRINTING IMAGE\n";
$svc = get_page_by_path( 'commercial-printing', OBJECT, 'gif_service' );
if ( ! $svc ) {
	// Fall back to a title match in case the slug differs.
	$found = get_posts(
		array(
			'post_type'      => 'gif_service',
			'posts_per_page' => 1,
			's'              => 'Commercial Printing',
			'post_status'    => 'any',
		)
	);
	$svc = $found ? $found[0] : null;
}
if ( ! $svc ) {
	echo "  !! could not find the Commercial Printing service\n";
} else {
	echo "  service #" . $svc->ID . " — " . $svc->post_title . " (" . $svc->post_name . ")\n";
	$existing = get_post_thumbnail_id( $svc->ID );
	echo "  existing featured image: " . ( $existing ? '#' . $existing : 'none' ) . "\n";
	$img_id = gif_add_media(
		'svc-commercial.jpg',
		'Commercial printing — colour swatches',
		'Fanned printing colour swatch books showing the full colour range',
		$assets,
		$upload
	);
	if ( $img_id ) {
		set_post_thumbnail( $svc->ID, $img_id );
		echo "  featured image set to #$img_id\n";
		$crop = wp_get_attachment_image_src( $img_id, 'gif-card' );
		echo "  card crop generated: " . ( $crop ? $crop[1] . 'x' . $crop[2] : 'NOT generated' ) . "\n";
		$hero = wp_get_attachment_image_src( $img_id, 'gif-service-hero' );
		echo "  hero crop generated: " . ( $hero ? $hero[1] . 'x' . $hero[2] : 'NOT generated' ) . "\n";
	}
}
echo "\n";

/* -------------------------------------------------- 3. HOW MAIL IS SET UP */
echo "3. SITE EMAIL — HOW IT IS CONFIGURED\n";
echo "  admin_email      : " . get_option( 'admin_email' ) . "\n";
$mu = WPMU_PLUGIN_DIR;
foreach ( array( 'smile-smtp.php', 'smile-smtp-config.php', 'smile-wp-admin.php', 'smile-security-headers.php' ) as $f ) {
	echo "  mu-plugin        : " . str_pad( $f, 28 ) . ( file_exists( $mu . '/' . $f ) ? 'present' : 'MISSING' ) . "\n";
}
foreach ( array( 'SMILE_SMTP_HOST', 'SMILE_SMTP_PORT', 'SMILE_SMTP_USER', 'SMILE_SMTP_PASS' ) as $c ) {
	if ( 'SMILE_SMTP_PASS' === $c ) {
		echo "  constant         : " . str_pad( $c, 28 ) . ( defined( $c ) ? 'defined' : 'NOT DEFINED' ) . "\n";
	} else {
		echo "  constant         : " . str_pad( $c, 28 ) . ( defined( $c ) ? constant( $c ) : 'NOT DEFINED' ) . "\n";
	}
}
echo "  enquiries go to  : " . ( function_exists( 'gif_opt' ) ? ( gif_opt( 'enquiry_to' ) ? gif_opt( 'enquiry_to' ) : gif_opt( 'email' ) ) : '?' ) . "\n";
$fail = get_option( 'smile_smtp_last_error' );
echo "  last mail error  : " . ( $fail ? ( is_array( $fail ) ? wp_json_encode( $fail ) : $fail ) : 'none recorded' ) . "\n";
echo "\n";

/* --------------------------------------------- 4. WHAT THE FORM RECORDED */
echo "4. ENQUIRIES RECEIVED (newest first)\n";
$enq = get_posts(
	array(
		'post_type'      => 'gif_enquiry',
		'posts_per_page' => 20,
		'post_status'    => 'any',
	)
);
if ( ! $enq ) {
	echo "  none stored — nobody has submitted the form yet\n";
} else {
	printf( "  %-20s %-30s %-8s\n", 'when', 'from', 'emailed' );
	foreach ( $enq as $e ) {
		printf(
			"  %-20s %-30s %-8s\n",
			$e->post_date,
			substr( (string) get_post_meta( $e->ID, '_gif_name', true ), 0, 29 ),
			'1' === (string) get_post_meta( $e->ID, '_gif_sent', true ) ? 'yes' : 'NO'
		);
	}
	echo "\n  'emailed: NO' means the message was saved but wp_mail() refused it.\n";
	echo "  'emailed: yes' means WordPress handed it off — it can still have been\n";
	echo "  rejected or spam-filed further along, which is what SPF/DKIM are for.\n";
}
echo "\n";

/* ------------------------------------------------------- 5. LIVE MAIL TEST */
echo "5. LIVE MAIL TEST\n";
if ( isset( $_GET['mailto'] ) && is_email( wp_unslash( $_GET['mailto'] ) ) ) {
	$to  = sanitize_email( wp_unslash( $_GET['mailto'] ) );
	$err = '';
	add_action(
		'wp_mail_failed',
		function ( $e ) use ( &$err ) {
			$err = $e->get_error_message();
		}
	);
	$ok = wp_mail(
		$to,
		'Get It Framed NI — website mail test',
		"This is a test of outgoing mail from getitframedni.co.uk.\nSent " . gmdate( 'Y-m-d H:i:s' ) . " UTC.",
		array( 'Content-Type: text/plain; charset=UTF-8' )
	);
	echo "  sent to $to : " . ( $ok ? 'accepted by WordPress' : 'REFUSED' ) . "\n";
	if ( $err ) {
		echo "  error: $err\n";
	}
} else {
	echo "  skipped (add &mailto=someone@example.com to the URL to run one)\n";
}
echo "\n";

/* ------------------------------------------------------------- 6. TIDY UP */
$removed = 0;
if ( is_dir( $assets ) ) {
	foreach ( (array) glob( $assets . '*' ) as $f ) {
		if ( is_file( $f ) && @unlink( $f ) ) {
			$removed++;
		}
	}
	@rmdir( $assets );
}
echo "6. CLEAN UP\n";
echo "  asset files removed: $removed\n";
echo "  this script deleting itself: " . ( @unlink( __FILE__ ) ? 'done' : 'FAILED — please delete gif-update.php by hand' ) . "\n";
echo "\nFinished.\n";
