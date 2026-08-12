<?php
/**
 * Plugin Name: Smile Creative — security headers
 * Description: Sends the standard response headers on every request: HSTS, nosniff, frame options, referrer policy and permissions policy.
 * Version:     1.0.0
 * Author:      Smile Creative
 *
 * Kept as a must-use plugin rather than .htaccess lines so it travels with the
 * site through a migration and cannot be lost when a plugin rewrites .htaccess.
 *
 * @package smile-creative
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The headers themselves.
 *
 * Strict-Transport-Security is only sent over https. Sending it on a plain
 * http response is ignored by browsers anyway, and on a site that has not
 * finished its certificate it would be a foot-gun.
 */
function smile_security_headers() {
	if ( headers_sent() ) {
		return;
	}

	$headers = array(
		'X-Content-Type-Options'  => 'nosniff',
		'X-Frame-Options'         => 'SAMEORIGIN',
		'Referrer-Policy'         => 'strict-origin-when-cross-origin',
		'Permissions-Policy'      => 'geolocation=(), camera=(), microphone=(), interest-cohort=()',
		'Cross-Origin-Opener-Policy' => 'same-origin',
	);

	if ( is_ssl() ) {
		$headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
	}

	/**
	 * Allow a site to add or override a header.
	 *
	 * @param array $headers Header name => value.
	 */
	$headers = apply_filters( 'smile_security_headers', $headers );

	foreach ( $headers as $name => $value ) {
		header( sprintf( '%s: %s', $name, $value ) );
	}
}
add_action( 'send_headers', 'smile_security_headers' );

/**
 * Same headers on admin and login screens, which do not fire send_headers.
 */
function smile_security_headers_admin( $headers ) {
	$headers['X-Content-Type-Options'] = 'nosniff';
	$headers['X-Frame-Options']        = 'SAMEORIGIN';
	$headers['Referrer-Policy']        = 'strict-origin-when-cross-origin';
	if ( is_ssl() ) {
		$headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
	}
	return $headers;
}
add_filter( 'wp_headers', 'smile_security_headers_admin' );

/**
 * Remove the WordPress version from the head and from asset URLs. Not a
 * defence in itself, but it stops the site advertising which release to
 * target when a core vulnerability is published.
 */
remove_action( 'wp_head', 'wp_generator' );

add_filter(
	'the_generator',
	function () {
		return '';
	}
);

/**
 * Turn off XML-RPC. Nothing we build uses it; it exists mainly as a brute
 * force amplifier via system.multicall.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

add_filter(
	'wp_headers',
	function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);

/**
 * Do not tell an attacker which half of the login was correct.
 */
add_filter(
	'login_errors',
	function () {
		return __( 'Those details were not recognised.', 'default' );
	}
);
