<?php
/**
 * Contact form.
 *
 * The prototype had no way at all for a customer to make an enquiry online --
 * only mailto: and tel: links. This is a plain, self-contained form: nonce,
 * honeypot, timing check, per-IP rate limit, everything sanitised on the way in
 * and escaped on the way out.
 *
 * Every submission is stored as an Enquiry before the email is attempted, so a
 * message is never lost to a delivery failure. That matters here: the domain
 * currently has no MX and no SPF record, so mail from the site will need an
 * authenticated SMTP route before launch.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle a submission. Runs before headers are sent so it can redirect.
 */
function gif_handle_contact_form() {
	if ( ! isset( $_POST['gif_contact_submit'] ) ) {
		return;
	}

	// Where to send them back to. The form carries its own page URL, because
	// the Referer header is stripped by some browsers and privacy settings --
	// relying on it silently dumps people on the homepage after submitting.
	$redirect = '';
	if ( isset( $_POST['gif_redirect'] ) ) {
		$redirect = wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['gif_redirect'] ) ), '' );
	}
	if ( ! $redirect ) {
		$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	}

	// 1. Nonce.
	if ( ! isset( $_POST['gif_contact_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['gif_contact_nonce'] ) ), 'gif_contact' ) ) {
		gif_form_bounce( $redirect, 'expired' );
	}

	// 2. Honeypot. A real person never fills this in; it is off-screen.
	if ( ! empty( $_POST['gif_website'] ) ) {
		// Pretend it worked. Telling a bot it failed only teaches it.
		gif_form_bounce( $redirect, 'sent' );
	}

	// 3. Timing. Anything submitted within two seconds of loading is automated.
	$loaded = isset( $_POST['gif_loaded'] ) ? absint( $_POST['gif_loaded'] ) : 0;
	if ( $loaded && ( time() - $loaded ) < 2 ) {
		gif_form_bounce( $redirect, 'sent' );
	}

	// 4. Rate limit: five submissions per IP per hour.
	$ip  = gif_client_ip();
	$key = 'gif_rl_' . md5( $ip );
	$hit = (int) get_transient( $key );
	if ( $hit >= 5 ) {
		gif_form_bounce( $redirect, 'throttled' );
	}
	set_transient( $key, $hit + 1, HOUR_IN_SECONDS );

	// 5. Collect and validate.
	$data = array(
		'name'    => isset( $_POST['gif_name'] ) ? sanitize_text_field( wp_unslash( $_POST['gif_name'] ) ) : '',
		'email'   => isset( $_POST['gif_email'] ) ? sanitize_email( wp_unslash( $_POST['gif_email'] ) ) : '',
		'phone'   => isset( $_POST['gif_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['gif_phone'] ) ) : '',
		'service' => isset( $_POST['gif_service'] ) ? sanitize_text_field( wp_unslash( $_POST['gif_service'] ) ) : '',
		'message' => isset( $_POST['gif_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['gif_message'] ) ) : '',
	);

	$errors = array();
	if ( '' === $data['name'] ) {
		$errors['name'] = __( 'Please tell us your name.', 'getitframed' );
	}
	if ( ! is_email( $data['email'] ) ) {
		$errors['email'] = __( 'Please give a valid email address so we can reply.', 'getitframed' );
	}
	if ( strlen( $data['message'] ) < 10 ) {
		$errors['message'] = __( 'Please give us a little more detail.', 'getitframed' );
	}
	// Header injection attempt via the name field.
	if ( preg_match( '/[\r\n]/', $data['name'] . $data['email'] . $data['phone'] ) ) {
		$errors['name'] = __( 'That does not look right — please try again.', 'getitframed' );
	}

	if ( $errors ) {
		set_transient( 'gif_form_' . md5( $ip ), array( 'errors' => $errors, 'data' => $data ), 5 * MINUTE_IN_SECONDS );
		gif_form_bounce( $redirect, 'invalid' );
	}

	// 6. Store it first, so it survives a mail failure.
	$enquiry_id = wp_insert_post(
		array(
			'post_type'   => 'gif_enquiry',
			'post_status' => 'publish',
			'post_title'  => sprintf(
				/* translators: 1: sender name, 2: service */
				__( '%1$s — %2$s', 'getitframed' ),
				$data['name'],
				$data['service'] ? $data['service'] : __( 'General enquiry', 'getitframed' )
			),
		)
	);

	if ( $enquiry_id && ! is_wp_error( $enquiry_id ) ) {
		foreach ( $data as $key => $value ) {
			update_post_meta( $enquiry_id, '_gif_' . $key, $value );
		}
		update_post_meta( $enquiry_id, '_gif_ip', $ip );
	}

	// 7. Send it.
	$to = gif_opt( 'enquiry_to' );
	if ( ! is_email( $to ) ) {
		$to = gif_opt( 'email' );
	}

	$site   = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$domain = wp_parse_url( home_url(), PHP_URL_HOST );
	$domain = preg_replace( '/^www\./', '', (string) $domain );

	$subject = sprintf(
		/* translators: 1: service or general, 2: sender name */
		__( 'Website enquiry: %1$s — %2$s', 'getitframed' ),
		$data['service'] ? $data['service'] : __( 'General', 'getitframed' ),
		$data['name']
	);

	$body = array(
		__( 'A new enquiry has come in from the website.', 'getitframed' ),
		'',
		sprintf( '%s: %s', __( 'Name', 'getitframed' ), $data['name'] ),
		sprintf( '%s: %s', __( 'Email', 'getitframed' ), $data['email'] ),
		sprintf( '%s: %s', __( 'Phone', 'getitframed' ), $data['phone'] ? $data['phone'] : __( 'not given', 'getitframed' ) ),
		sprintf( '%s: %s', __( 'Service', 'getitframed' ), $data['service'] ? $data['service'] : __( 'not specified', 'getitframed' ) ),
		'',
		__( 'Message:', 'getitframed' ),
		$data['message'],
		'',
		'---',
		sprintf(
			/* translators: %s: admin URL */
			__( 'A copy is saved in the website dashboard: %s', 'getitframed' ),
			admin_url( 'edit.php?post_type=gif_enquiry' )
		),
	);

	// From must be on this domain or it will fail SPF/DMARC at the far end.
	// Reply-To is the customer, so hitting reply just works.
	$headers = array(
		sprintf( 'From: %s <%s>', $site, 'website@' . $domain ),
		sprintf( 'Reply-To: %s <%s>', $data['name'], $data['email'] ),
		'Content-Type: text/plain; charset=UTF-8',
	);

	$sent = wp_mail( $to, $subject, implode( "\n", $body ), $headers );

	if ( $enquiry_id && ! is_wp_error( $enquiry_id ) ) {
		update_post_meta( $enquiry_id, '_gif_sent', $sent ? 1 : 0 );
	}

	gif_form_bounce( $redirect, 'sent' );
}
add_action( 'template_redirect', 'gif_handle_contact_form' );

/**
 * Redirect back to the form with a status flag, so a refresh cannot resubmit.
 *
 * @param string $url    Where to go.
 * @param string $status Status flag.
 */
function gif_form_bounce( $url, $status ) {
	wp_safe_redirect( add_query_arg( 'enquiry', $status, remove_query_arg( 'enquiry', $url ) ) . '#enquiry' );
	exit;
}

/**
 * Best-effort client IP, used only for rate limiting.
 *
 * @return string
 */
function gif_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
}

/**
 * Any validation errors from the last attempt by this visitor.
 *
 * @return array
 */
function gif_form_state() {
	$state = get_transient( 'gif_form_' . md5( gif_client_ip() ) );
	return is_array( $state ) ? $state : array(
		'errors' => array(),
		'data'   => array(),
	);
}

/**
 * The notice shown above the form after a submission.
 */
function gif_form_notice() {
	$status = isset( $_GET['enquiry'] ) ? sanitize_key( wp_unslash( $_GET['enquiry'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $status ) {
		return;
	}

	$messages = array(
		'sent'      => array( 'is-success', __( 'Thank you — your message is with us. We normally reply the same working day.', 'getitframed' ) ),
		'invalid'   => array( 'is-error', __( 'Almost there — please check the highlighted fields below.', 'getitframed' ) ),
		'expired'   => array( 'is-error', __( 'That form had been open a while and timed out. Please send it again.', 'getitframed' ) ),
		'throttled' => array( 'is-error', __( 'That is a few messages in a short time. Please give it an hour, or ring the studio.', 'getitframed' ) ),
	);

	if ( ! isset( $messages[ $status ] ) ) {
		return;
	}

	printf(
		'<div class="form-notice %1$s" role="status">%2$s</div>',
		esc_attr( $messages[ $status ][0] ),
		esc_html( $messages[ $status ][1] )
	);
}
