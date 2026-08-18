<?php
/**
 * One-time deployment runner.
 *
 * The seeder (gif-seed.php) is a CLI script. Plenty of small-business hosting
 * has no shell access at all, so this file lets the same seeder be run once
 * over HTTP instead, behind a one-time token, and then removes itself.
 *
 * Usage:
 *   1. Put a long random string in GIF_DEPLOY_TOKEN below.
 *   2. Upload this file and gif-seed.php to the WordPress root.
 *   3. Request  /gif-deploy.php?k=THE_TOKEN
 *   4. It deletes itself when it finishes. Confirm that it is gone.
 *
 * Optional: &public=1 lifts the search-engine block. The default is 0, which
 * is what you want while the site is still sitting at a staging URL.
 *
 * @package getitframed
 */

define( 'GIF_DEPLOY_TOKEN', 'REPLACE_ME_BEFORE_UPLOAD' );

header( 'Content-Type: text/plain; charset=utf-8' );

// The sentinel is written in two halves on purpose: setting the token with a
// search-and-replace across the file would otherwise rewrite this check too,
// and the guard would pass while the token was still the placeholder.
if ( GIF_DEPLOY_TOKEN === 'REPLACE_ME' . '_BEFORE_UPLOAD' || strlen( GIF_DEPLOY_TOKEN ) < 20 ) {
	http_response_code( 500 );
	exit( "Token not set, or too short. Put at least 20 random characters in GIF_DEPLOY_TOKEN.\n" );
}

$supplied = isset( $_GET['k'] ) ? (string) $_GET['k'] : '';
if ( ! hash_equals( GIF_DEPLOY_TOKEN, $supplied ) ) {
	http_response_code( 404 );
	exit( "Not found\n" );
}

$root = __DIR__;
if ( ! file_exists( $root . '/wp-load.php' ) ) {
	http_response_code( 500 );
	exit( "wp-load.php is not beside this file. Put both in the WordPress root.\n" );
}

echo "Get It Framed NI — deployment\n";
echo "=============================\n\n";

require_once $root . '/wp-load.php';

echo 'WordPress ' . get_bloginfo( 'version' ) . ' on PHP ' . PHP_VERSION . "\n";
echo 'Site URL: ' . home_url( '/' ) . "\n\n";

// -- 1. The theme must actually be there before anything switches to it ------
$theme = wp_get_theme( 'getitframed' );
if ( ! $theme->exists() ) {
	http_response_code( 500 );
	exit( "STOP: wp-content/themes/getitframed is missing. Upload the theme first.\n" );
}
echo "Theme found: {$theme->get( 'Name' )} {$theme->get( 'Version' )}\n";

// -- 2. Keep it out of Google until someone says otherwise -------------------
$public = isset( $_GET['public'] ) && '1' === $_GET['public'] ? 1 : 0;
update_option( 'blog_public', $public );
echo 'Search engines: ' . ( $public ? "allowed\n\n" : "blocked (blog_public=0)\n\n" );

// -- 3. Seed --------------------------------------------------------------
define( 'GIF_SEED_ALLOW_WEB', true );

if ( ! file_exists( $root . '/gif-seed.php' ) ) {
	http_response_code( 500 );
	exit( "STOP: gif-seed.php is missing. Upload it beside this file.\n" );
}

require $root . '/gif-seed.php';

// -- 4. Remove the default WordPress sample content --------------------------
// Matched on title as well as slug so that nothing of the client's own is
// caught by a slug collision. Some installs have these twice.
echo "\nRemoving default sample content:\n";

$samples = array(
	'post' => 'Hello world!',
	'page' => 'Sample Page',
);

$removed = 0;
foreach ( $samples as $type => $title ) {
	$found = get_posts(
		array(
			'post_type'        => $type,
			'post_status'      => 'any',
			'posts_per_page'   => -1,
			'suppress_filters' => true,
		)
	);
	foreach ( $found as $post ) {
		if ( $post->post_title !== $title ) {
			continue;
		}
		wp_delete_post( $post->ID, true );
		echo "  deleted {$type} #{$post->ID} \"{$title}\"\n";
		$removed++;
	}
}
echo $removed ? "  {$removed} removed\n" : "  nothing to remove\n";

// The sample comment goes with the sample post.
$comments = get_comments( array( 'status' => 'all' ) );
foreach ( $comments as $comment ) {
	wp_delete_comment( $comment->comment_ID, true );
}

// -- 5. Report ---------------------------------------------------------------
echo "\nResult:\n";
printf( "  services published : %d\n", (int) wp_count_posts( 'gif_service' )->publish );
printf( "  pages published    : %d\n", (int) wp_count_posts( 'page' )->publish );
printf( "  pages draft        : %d\n", (int) wp_count_posts( 'page' )->draft );
printf( "  media items        : %d\n", (int) wp_count_posts( 'attachment' )->inherit );
printf( "  active theme       : %s\n", get_option( 'stylesheet' ) );
printf( "  permalinks         : %s\n", get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'PLAIN — set them in Settings > Permalinks' );
printf( "  front page         : %s\n", 'page' === get_option( 'show_on_front' ) ? get_the_title( (int) get_option( 'page_on_front' ) ) : 'latest posts' );

// -- 6. Take this file away ---------------------------------------------------
$gone = @unlink( __FILE__ ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
echo "\n" . ( $gone
	? "This deployment file has deleted itself.\n"
	: "WARNING: could not delete this file. Remove gif-deploy.php by hand, now.\n" );
