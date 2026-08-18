<?php
/**
 * One-shot mail test.
 *
 * Proves the SMTP settings actually deliver, without submitting a fake enquiry
 * through the contact form — which would put a test message in the studio's real
 * inbox and look like a customer.
 *
 * Usage:
 *   1. Set GIF_MAILTEST_TOKEN below.
 *   2. Upload beside wp-load.php.
 *   3. Visit  /gif-mailtest.php?k=THE_TOKEN&to=someone@example.com
 *   4. It deletes itself afterwards.
 *
 * @package getitframed
 */

define( 'GIF_MAILTEST_TOKEN', 'REPLACE_ME_BEFORE_UPLOAD' );

header( 'Content-Type: text/plain; charset=utf-8' );

// Sentinel in two halves so setting the token by search-and-replace cannot
// rewrite this check as well.
if ( GIF_MAILTEST_TOKEN === 'REPLACE_ME' . '_BEFORE_UPLOAD' || strlen( GIF_MAILTEST_TOKEN ) < 20 ) {
	http_response_code( 500 );
	exit( "Token not set, or too short.\n" );
}

if ( ! isset( $_GET['k'] ) || ! hash_equals( GIF_MAILTEST_TOKEN, (string) $_GET['k'] ) ) {
	http_response_code( 404 );
	exit( "Not found\n" );
}

if ( ! file_exists( __DIR__ . '/wp-load.php' ) ) {
	http_response_code( 500 );
	exit( "wp-load.php is not beside this file.\n" );
}

require_once __DIR__ . '/wp-load.php';

$to = isset( $_GET['to'] ) ? sanitize_email( wp_unslash( $_GET['to'] ) ) : '';
if ( ! is_email( $to ) ) {
	http_response_code( 400 );
	exit( "Add &to=your@address to say where the test should go.\n" );
}

echo "Mail test\n=========\n\n";

printf( "SMTP configured : %s\n", function_exists( 'smile_smtp_ready' ) && smile_smtp_ready() ? 'yes' : 'NO' );
if ( defined( 'SMILE_SMTP_HOST' ) ) {
	printf( "Host / port     : %s : %s\n", SMILE_SMTP_HOST, defined( 'SMILE_SMTP_PORT' ) ? SMILE_SMTP_PORT : '?' );
	printf( "Authenticating  : %s\n", defined( 'SMILE_SMTP_USER' ) ? SMILE_SMTP_USER : '?' );
} else {
	echo "  (no SMILE_SMTP_ constants found — this will go out via PHP mail(),\n";
	echo "   which is the thing we are trying to avoid)\n";
}
printf( "Sending to      : %s\n\n", $to );

// Capture the real reason rather than just a false return value.
$failure = '';
add_action(
	'wp_mail_failed',
	function ( $error ) use ( &$failure ) {
		$failure = $error->get_error_message();
	}
);

$sent = wp_mail(
	$to,
	'Get It Framed NI — mail test',
	"If you are reading this, the site's mail is working.\n\n"
		. 'Sent ' . gmdate( 'Y-m-d H:i:s' ) . " UTC from " . home_url( '/' ) . "\n",
	array( 'Content-Type: text/plain; charset=UTF-8' )
);

if ( $sent ) {
	echo "RESULT: accepted by the mail server.\n\n";
	echo "That means it authenticated and the server took the message. Check the\n";
	echo "inbox to confirm it actually arrived, and check the spam folder too —\n";
	echo "'accepted' and 'delivered to the inbox' are not the same thing.\n";
} else {
	echo "RESULT: FAILED.\n\n";
	echo $failure ? "  {$failure}\n" : "  wp_mail() returned false and gave no reason.\n";
}

$gone = @unlink( __FILE__ ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
echo "\n" . ( $gone ? "This file has deleted itself.\n" : "WARNING: delete gif-mailtest.php by hand.\n" );
