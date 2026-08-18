<?php
/**
 * Go-live runner: serve the site from the domain root.
 *
 * WordPress stays physically in /wp/. Only the address changes. This is
 * WordPress's own "giving WordPress its own directory" arrangement, and it is
 * deliberately chosen over moving a few thousand files through a web file
 * manager, which is slow, easy to half-finish, and hard to undo.
 *
 * It also means NO search-and-replace across the database. Page and menu links
 * are generated from the home option at render time, so they follow on their
 * own; the only stored URLs are asset paths under /wp/wp-content/, which keep
 * working exactly as they are. A find-and-replace over serialised WordPress
 * data is the single most common way a site migration corrupts itself, and
 * this avoids needing one at all.
 *
 * Usage:
 *   1. Archive the old static site first — move index.html and assets/ out of
 *      public_html and into a folder such as old-site-2025/. This script will
 *      refuse to run while an index.html is still sitting at the root, because
 *      Apache serves that in preference to WordPress.
 *   2. Put this file in the SAME folder as wp-load.php (that is /wp/).
 *   3. Request  /wp/gif-golive.php?k=THE_TOKEN
 *   4. It deletes itself when it finishes.
 *
 * Everything it changes is listed in its report, and the old .htaccess is kept
 * as a timestamped backup rather than overwritten.
 *
 * @package getitframed
 */

define( 'GIF_GOLIVE_TOKEN', 'REPLACE_ME_BEFORE_UPLOAD' );

// The canonical address the site will answer on, without a trailing slash.
define( 'GIF_LIVE_URL', 'https://getitframedni.co.uk' );

header( 'Content-Type: text/plain; charset=utf-8' );

// Sentinel split in two halves so that setting the token by search-and-replace
// cannot rewrite this check as well.
if ( GIF_GOLIVE_TOKEN === 'REPLACE_ME' . '_BEFORE_UPLOAD' || strlen( GIF_GOLIVE_TOKEN ) < 20 ) {
	http_response_code( 500 );
	exit( "Token not set, or too short.\n" );
}

if ( ! isset( $_GET['k'] ) || ! hash_equals( GIF_GOLIVE_TOKEN, (string) $_GET['k'] ) ) {
	http_response_code( 404 );
	exit( "Not found\n" );
}

$wpdir = __DIR__;
if ( ! file_exists( $wpdir . '/wp-load.php' ) ) {
	http_response_code( 500 );
	exit( "wp-load.php is not beside this file. Put this in the WordPress folder.\n" );
}

require_once $wpdir . '/wp-load.php';

$root    = dirname( $wpdir );          // public_html
$subdir  = basename( $wpdir );         // wp
$oldhome = untrailingslashit( get_option( 'home' ) );

echo "Get It Framed NI — go live at the domain root\n";
echo "=============================================\n\n";
echo "WordPress folder : {$wpdir}\n";
echo "Document root    : {$root}\n";
echo "Home now         : {$oldhome}\n";
echo 'Target           : ' . GIF_LIVE_URL . "\n\n";

// -- Guard 1: the old site must be out of the way ----------------------------
// Apache serves index.html ahead of index.php, so leaving it there means the
// 2025 site keeps answering and the change looks like it silently failed.
foreach ( array( 'index.html', 'index.htm' ) as $stale ) {
	if ( file_exists( $root . '/' . $stale ) ) {
		http_response_code( 409 );
		echo "STOP: {$root}/{$stale} still exists.\n\n";
		echo "Apache serves that in preference to WordPress, so the old site would\n";
		echo "keep answering and this would look like it had done nothing.\n";
		echo "Move index.html and assets/ into a folder such as old-site-2025/ first,\n";
		echo "then run this again. Nothing has been changed.\n";
		exit;
	}
}

// -- Guard 2: constants in wp-config.php beat the database -------------------
// If WP_HOME or WP_SITEURL are defined, update_option() still writes the row and
// still reads it back, so a report built from get_option() would say the address
// had changed while the site carried on at the old one. Stop instead.
$want_home = GIF_LIVE_URL;
$want_site = GIF_LIVE_URL . '/' . $subdir;

$const_home = defined( 'WP_HOME' ) ? untrailingslashit( WP_HOME ) : null;
$const_site = defined( 'WP_SITEURL' ) ? untrailingslashit( WP_SITEURL ) : null;

// Only a constant that DISAGREES with the target is a problem. One that already
// says the right thing is fine, and must not block the run — otherwise fixing
// wp-config.php as instructed would leave this script refusing forever.
$const_conflict = ( null !== $const_home && $const_home !== $want_home )
	|| ( null !== $const_site && $const_site !== $want_site );

if ( $const_conflict ) {
	http_response_code( 409 );
	echo "STOP: wp-config.php defines the address, which overrides the database.\n\n";
	printf( "  WP_HOME    = %s\n", null !== $const_home ? $const_home : '(not set)' );
	printf( "  WP_SITEURL = %s\n\n", null !== $const_site ? $const_site : '(not set)' );
	echo "Changing the database while those lines exist would do nothing visible,\n";
	echo "and this script would wrongly report success. Edit wp-config.php so the\n";
	echo "two lines read exactly:\n\n";
	printf( "  define( 'WP_HOME', '%s' );\n", GIF_LIVE_URL );
	printf( "  define( 'WP_SITEURL', '%s/%s' );\n\n", GIF_LIVE_URL, $subdir );
	echo "then run this again. Nothing has been changed.\n";
	exit;
}

// -- Guard 3: we must be able to write where it matters ----------------------
if ( ! is_writable( $root ) ) {
	http_response_code( 500 );
	exit( "STOP: {$root} is not writable. Nothing has been changed.\n" );
}

$changes = array();

// -- 1. The root index.php that hands the request to WordPress ---------------
$index_php = "<?php\n"
	. "/**\n"
	. " * Front controller. WordPress itself lives in /{$subdir}/.\n"
	. " */\n"
	. "define( 'WP_USE_THEMES', true );\n"
	. "require __DIR__ . '/{$subdir}/wp-blog-header.php';\n";

if ( false === file_put_contents( $root . '/index.php', $index_php ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
	http_response_code( 500 );
	exit( "STOP: could not write {$root}/index.php. Nothing else has been changed.\n" );
}
$changes[] = "wrote {$root}/index.php";

// -- 2. Move the address, but not the files ----------------------------------
// home = where visitors see the site. siteurl = where WordPress actually is.
// They are deliberately different here; that is the whole mechanism.
update_option( 'home', GIF_LIVE_URL );
update_option( 'siteurl', GIF_LIVE_URL . '/' . $subdir );
$changes[] = 'home = ' . GIF_LIVE_URL;
$changes[] = 'siteurl = ' . GIF_LIVE_URL . '/' . $subdir;

// -- 3. Let search engines in ------------------------------------------------
// Easy to forget, and the site is then quietly invisible for months.
update_option( 'blog_public', 1 );
$changes[] = 'blog_public = 1 (search engines allowed)';

// -- 4. Rewrite rules for the new address ------------------------------------
global $wp_rewrite;
$wp_rewrite->init();          // re-read home/siteurl, so RewriteBase comes out as /
$wp_rewrite->flush_rules( false );

$wp_rules = $wp_rewrite->mod_rewrite_rules();

$htaccess = $root . '/.htaccess';
if ( file_exists( $htaccess ) ) {
	$backup = $root . '/.htaccess.before-golive';
	copy( $htaccess, $backup );
	$changes[] = "backed up existing .htaccess to {$backup}";
}

$block = "# Force https. The certificate covers both names and is already valid.\n"
	. "<IfModule mod_rewrite.c>\n"
	. "RewriteEngine On\n"
	. "RewriteCond %{HTTPS} !=on\n"
	. "RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]\n"
	. "</IfModule>\n\n"
	. "# Do not hand out the WordPress version number.\n"
	. "<FilesMatch \"^(readme\\.html|license\\.txt|wp-config-sample\\.php)$\">\n"
	. "Require all denied\n"
	. "</FilesMatch>\n\n"
	. "# No directory listings.\n"
	. "Options -Indexes\n\n"
	// The markers are added by hand because mod_rewrite_rules() does not include
	// them — insert_with_markers() normally does. WordPress and plugins look for
	// this pair when they rewrite the block later, so without it a core update
	// would append a second, duplicate set of rules.
	. "# BEGIN WordPress\n"
	. $wp_rules
	. "# END WordPress\n";

if ( false === file_put_contents( $htaccess, $block ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
	echo "WARNING: could not write {$htaccess}. Pretty URLs will 404 until it is written.\n";
} else {
	clearstatcache( true, $htaccess );
	$written = false !== strpos( (string) file_get_contents( $htaccess ), 'BEGIN WordPress' );
	$changes[] = $written
		? "wrote {$htaccess}, and read it back to confirm"
		: "WROTE {$htaccess} BUT THE WORDPRESS BLOCK IS NOT IN IT — check it by hand";
}

// -- 5. Tidy the files that should not be public -----------------------------
foreach ( array( 'readme.html', 'license.txt', 'gif-seed.php' ) as $tidy ) {
	$path = $wpdir . '/' . $tidy;
	if ( file_exists( $path ) && @unlink( $path ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
		$changes[] = "deleted {$tidy}";
	}
}

// -- 6. Report ---------------------------------------------------------------
echo "Changed:\n";
foreach ( $changes as $line ) {
	echo "  - {$line}\n";
}

echo "\nNow:\n";
printf( "  home            : %s\n", get_option( 'home' ) );
printf( "  siteurl         : %s\n", get_option( 'siteurl' ) );
printf( "  indexing        : %s\n", get_option( 'blog_public' ) ? 'allowed' : 'BLOCKED' );
printf( "  permalinks      : %s\n", get_option( 'permalink_structure' ) );
printf( "  rewrite rules   : %d\n", count( (array) get_option( 'rewrite_rules' ) ) );

echo "\nCheck these by hand before telling anyone it is done:\n";
echo '  ' . GIF_LIVE_URL . "/\n";
echo '  ' . GIF_LIVE_URL . "/services/prints/\n";
echo '  ' . GIF_LIVE_URL . "/contact/\n";
echo "  http://getitframedni.co.uk/  (must end up on https)\n";

echo "\nTo undo: put index.html and assets/ back, delete index.php, restore\n";
echo ".htaccess.before-golive, and set home and siteurl back to {$oldhome}.\n";

$gone = @unlink( __FILE__ ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
echo "\n" . ( $gone
	? "This file has deleted itself.\n"
	: "WARNING: could not delete this file. Remove gif-golive.php by hand, now.\n" );
