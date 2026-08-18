<?php
/**
 * Plugin Name: Smile Creative — authenticated SMTP
 * Description: Sends WordPress mail through a real, authenticated mailbox instead of the server's PHP mail(), so it passes SPF, gets DKIM-signed, and actually arrives.
 * Version: 1.0.0
 * Author: Smile Creative
 *
 * WHY THIS EXISTS
 *
 * PHP mail() hands the message to whatever server the site happens to sit on.
 * If that server is not listed in the sending domain's SPF record, the mail
 * fails SPF, carries no DKIM signature for that domain, and — where the domain
 * publishes DMARC p=reject — is refused outright rather than junked. The better
 * the client's mail authentication, the harder this bites. A site hosted
 * somewhere other than the mail domain's own server cannot send as that domain
 * without authenticating first.
 *
 * The fix is not to widen the SPF record to cover the web host: that authorises
 * an entire third-party server to send as the client, permanently. It is to log
 * in to the real mail server and send from there.
 *
 * CONFIGURATION — in wp-config.php, above the "stop editing" line. Credentials
 * belong there and not in this file, so this file stays safe to commit.
 *
 *   define( 'SMILE_SMTP_HOST', 'mail.example.com' );
 *   define( 'SMILE_SMTP_PORT', 465 );            // 465 implicit TLS, 587 STARTTLS
 *   define( 'SMILE_SMTP_USER', 'wpadmin@example.com' );
 *   define( 'SMILE_SMTP_PASS', '...' );
 *   define( 'SMILE_SMTP_NAME', 'Example Ltd' );  // optional display name
 *
 * Optional:
 *   define( 'SMILE_SMTP_FORCE_FROM', false );    // see the note below
 *
 * @package smile-creative
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the mailbox is configured.
 *
 * Everything below no-ops until all four constants exist, so this file is safe
 * to deploy before the credentials are known.
 *
 * @return bool
 */
function smile_smtp_ready() {
	return defined( 'SMILE_SMTP_HOST' )
		&& defined( 'SMILE_SMTP_PORT' )
		&& defined( 'SMILE_SMTP_USER' )
		&& defined( 'SMILE_SMTP_PASS' );
}

/**
 * Point PHPMailer at the real mail server.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer Mailer instance, by reference.
 * @return void
 */
function smile_smtp_configure( $phpmailer ) {
	if ( ! smile_smtp_ready() ) {
		return;
	}

	$port = (int) SMILE_SMTP_PORT;

	$phpmailer->isSMTP();
	$phpmailer->Host       = SMILE_SMTP_HOST; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$phpmailer->Port       = $port;           // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$phpmailer->SMTPAuth   = true;            // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$phpmailer->Username   = SMILE_SMTP_USER; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$phpmailer->Password   = SMILE_SMTP_PASS; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$phpmailer->SMTPSecure = ( 587 === $port ) ? 'tls' : 'ssl'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$phpmailer->CharSet    = 'UTF-8';         // phpcs:ignore WordPress.NamingConventions.ValidVariableName

	/*
	 * Alignment, which is the whole point of the exercise.
	 *
	 * Authenticating is not enough on its own: DMARC also requires that the
	 * visible From domain matches the domain the message is signed and
	 * authorised for. Sending as website@clientsite.com through a mailbox at
	 * agency.com authenticates fine and still fails alignment.
	 *
	 * So the From is rewritten to the mailbox actually being used, while
	 * Reply-To — set by whatever called wp_mail() — is left alone, so replying
	 * to an enquiry still reaches the customer.
	 *
	 * Set SMILE_SMTP_FORCE_FROM to false only when the sending domain and the
	 * mailbox domain are the same, and the From is already correct.
	 */
	$force = defined( 'SMILE_SMTP_FORCE_FROM' ) ? (bool) SMILE_SMTP_FORCE_FROM : true;
	if ( $force ) {
		$name = defined( 'SMILE_SMTP_NAME' ) ? SMILE_SMTP_NAME : get_bloginfo( 'name' );
		$phpmailer->setFrom( SMILE_SMTP_USER, $name, false );
		$phpmailer->Sender = SMILE_SMTP_USER; // Return-Path. phpcs:ignore WordPress.NamingConventions.ValidVariableName
	}
}
add_action( 'phpmailer_init', 'smile_smtp_configure' );

/**
 * Record why a send failed.
 *
 * A failed wp_mail() returns false and says nothing more. On a live site that
 * is indistinguishable from a form nobody filled in, which is how a broken
 * contact form survives for months. Keep the last error where it can be read.
 *
 * @param WP_Error $error The failure.
 * @return void
 */
function smile_smtp_log_failure( $error ) {
	$message = $error->get_error_message();

	update_option(
		'smile_smtp_last_error',
		array(
			'time'    => time(),
			'message' => $message,
		),
		false
	);

	error_log( 'smile-smtp: send failed — ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
}
add_action( 'wp_mail_failed', 'smile_smtp_log_failure' );

/**
 * Warn in the admin when mail is misconfigured or last failed.
 *
 * @return void
 */
function smile_smtp_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! smile_smtp_ready() ) {
		printf(
			'<div class="notice notice-warning"><p><strong>%s</strong> %s</p></div>',
			esc_html__( 'Site mail is not configured.', 'smile' ),
			esc_html__( 'Messages are being handed to the server\'s own mail, which may be rejected. Add the SMILE_SMTP_ constants to wp-config.php.', 'smile' )
		);
		return;
	}

	$last = get_option( 'smile_smtp_last_error' );
	if ( ! empty( $last['time'] ) && ( time() - (int) $last['time'] ) < DAY_IN_SECONDS ) {
		printf(
			'<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
			esc_html__( 'The site failed to send an email in the last 24 hours.', 'smile' ),
			esc_html( $last['message'] )
		);
	}
}
add_action( 'admin_notices', 'smile_smtp_admin_notice' );
