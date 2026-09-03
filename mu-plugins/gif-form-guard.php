<?php
/**
 * Plugin Name: Get It Framed — Contact Form Guard
 * Description: The content-filter layer of the Smile Creative anti-spam standard, adapted to the site's own contact form. Adds language, URL, disposable-domain, keyword and bot-name checks on top of the honeypot, timing and rate limit already in the theme.
 * Version: 1.1.0
 * Author: Smile Creative
 *
 * WHY THIS EXISTS
 * ---------------
 * The June 2026 anti-spam standard is four layers: reCAPTCHA v3, honeypot, a timing
 * check, and a content filter. It shipped as a Contact Form 7 plugin, because every
 * site it went onto used CF7. This site does not — it has a bespoke form — so that
 * plugin was never applicable here and layer 4 was missing.
 *
 * A honeypot and a two-second timer stop crude bots. They do not stop the current
 * generation, which runs JavaScript, leaves hidden fields alone and waits before
 * submitting. Those get through on content alone, which is what this adds.
 *
 * HOW IT HOOKS IN
 * ---------------
 * The theme handles the form on `template_redirect` at the default priority 10.
 * This runs at 9, so it sees every submission first and the theme is never reached
 * for one that is rejected. Nothing in the theme needed changing to allow it.
 *
 * WHAT HAPPENS TO A REJECTED MESSAGE
 * ----------------------------------
 * Nothing is ever deleted. Two different treatments, deliberately:
 *
 *   - Honeypot or impossible timing  -> dropped, logged. These cannot be a person.
 *   - Anything caught on CONTENT     -> stored as an Enquiry in the Trash, logged,
 *                                       and no email sent.
 *
 * The second one matters. A content rule is a judgement, and a judgement can be
 * wrong — someone linking to a photograph they want framed will trip the URL rule,
 * and that is a real customer. Quarantining instead of deleting means a mistake
 * costs a look in Enquiries -> Trash rather than a lost order. WordPress empties
 * the trash after 30 days on its own; the log file keeps the text for longer.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inspect a submission before the theme gets to it.
 */
function gif_guard_check_submission() {
	if ( ! isset( $_POST['gif_contact_submit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	// The nonce is the theme's job. If it is bad the theme will bounce with a
	// proper "that form timed out" message, and a nonce failure is not spam.
	$fields = gif_guard_posted_fields();

	// 1. Honeypot. Checked here as well as in the theme so it can be logged.
	if ( '' !== $fields['website'] ) {
		gif_guard_log( 'honeypot', $fields );
		gif_guard_pretend_sent();
	}

	// 2. Timing. Three seconds, up from the theme's two. There is deliberately NO
	// upper bound: rejecting an "expired" form would throw away a genuine enquiry
	// from someone who left the page open, which is a worse failure than spam.
	$loaded = isset( $_POST['gif_loaded'] ) ? absint( $_POST['gif_loaded'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( $loaded > 0 && ( time() - $loaded ) < 3 ) {
		gif_guard_log( 'too-fast', $fields );
		gif_guard_pretend_sent();
	}

	// 3. Content. The layer that was missing.
	$reason = gif_guard_content_reason( $fields );

	if ( '' !== $reason ) {
		gif_guard_log( $reason, $fields );
		gif_guard_quarantine( $reason, $fields );
		gif_guard_pretend_sent();
	}

	gif_guard_log( 'PASSED', $fields );
}
add_action( 'template_redirect', 'gif_guard_check_submission', 9 );

/**
 * The submitted values, sanitised the same way the theme sanitises them.
 *
 * @return array
 */
function gif_guard_posted_fields() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	$fields = array(
		'name'    => isset( $_POST['gif_name'] ) ? sanitize_text_field( wp_unslash( $_POST['gif_name'] ) ) : '',
		'email'   => isset( $_POST['gif_email'] ) ? sanitize_email( wp_unslash( $_POST['gif_email'] ) ) : '',
		'phone'   => isset( $_POST['gif_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['gif_phone'] ) ) : '',
		'service' => isset( $_POST['gif_service'] ) ? sanitize_text_field( wp_unslash( $_POST['gif_service'] ) ) : '',
		'message' => isset( $_POST['gif_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['gif_message'] ) ) : '',
		'website' => isset( $_POST['gif_website'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['gif_website'] ) ) ) : '',
	);

	/*
	 * The content rules have to read the RAW values, not the sanitised ones.
	 *
	 * sanitize_text_field() and sanitize_textarea_field() both run the input
	 * through wp_strip_all_tags(). By the time the sanitised message exists, an
	 * anchor tag and the link inside it have already been deleted -- so a rule
	 * looking for "<a href=" or "http://" would never fire, and link spam would
	 * sail through a filter that appears to be working. Caught by testing the
	 * filter against an actual tagged message rather than trusting it.
	 *
	 * These two are used for pattern matching only. Everything that gets stored,
	 * emailed or displayed still comes from the sanitised values above.
	 */
	$fields['raw_name']    = isset( $_POST['gif_name'] ) ? (string) wp_unslash( $_POST['gif_name'] ) : '';
	$fields['raw_message'] = isset( $_POST['gif_message'] ) ? (string) wp_unslash( $_POST['gif_message'] ) : '';
	// phpcs:enable

	return $fields;
}

/**
 * Why this submission looks automated, or an empty string if it does not.
 *
 * Rules are ordered cheapest and most certain first, and the first hit wins, so the
 * log records one clear reason rather than a pile of them.
 *
 * @param array $f Submitted fields.
 * @return string
 */
function gif_guard_content_reason( $f ) {
	$name     = isset( $f['raw_name'] ) ? $f['raw_name'] : $f['name'];
	$message  = isset( $f['raw_message'] ) ? $f['raw_message'] : $f['message'];
	$all_text = strtolower( $name . ' ' . $message );
	$msg_low  = strtolower( $message );

	// Non-Latin scripts. This is an English-language studio in Ballymena; a message
	// in Cyrillic, Chinese or Arabic has never been a customer and always been a bot.
	if ( preg_match( '/[\x{0400}-\x{04FF}]/u', $all_text ) ) {
		return 'cyrillic';
	}
	if ( preg_match( '/[\x{4E00}-\x{9FFF}]/u', $all_text ) ) {
		return 'chinese';
	}
	if ( preg_match( '/[\x{0600}-\x{06FF}]/u', $all_text ) ) {
		return 'arabic';
	}

	// Disposable and bulk-signup mailbox providers.
	$at = strrpos( $f['email'], '@' );
	$email_domain = ( false === $at ) ? '' : strtolower( substr( $f['email'], $at + 1 ) );

	$spam_domains = array(
		'rambler.ru', 'mail.ru', 'yandex.ru', 'bk.ru', 'list.ru', 'inbox.ru',
		'tempmail.com', 'guerrillamail.com', 'sharklasers.com', 'guerrillamailblock.com',
		'grr.la', 'dispostable.com', 'mailinator.com', 'throwaway.email',
	);
	if ( '' !== $email_domain && in_array( $email_domain, $spam_domains, true ) ) {
		return 'spam-domain';
	}

	$spam_tlds = array(
		'world', 'xyz', 'top', 'buzz', 'click', 'link', 'icu', 'fun', 'club', 'site',
		'online', 'space', 'website', 'stream', 'download', 'win', 'bid', 'racing',
		'trade', 'webcam', 'science', 'party', 'loan', 'gq', 'cf', 'tk', 'ml', 'ga',
	);
	if ( '' !== $email_domain ) {
		$dot = strrpos( $email_domain, '.' );
		if ( false !== $dot ) {
			$tld = substr( $email_domain, $dot + 1 );
			if ( in_array( $tld, $spam_tlds, true ) ) {
				return 'spam-tld';
			}
		}
	}

	// Markup in a plain-text message field.
	if ( preg_match( '/<[a-z][a-z0-9]*[\s>\/]/i', $message ) ) {
		return 'html-tags';
	}

	// Links. Quarantine, never a silent drop — see the note at the top of the file.
	if ( preg_match( '/https?:\/\/|www\./i', $msg_low ) ) {
		return 'url-in-msg';
	}
	if ( preg_match( '/[a-z0-9][-a-z0-9]*\.(com|net|org|info|biz|ru|cn|tk|ml|ga|cf|gq|xyz|top|pw|cc|ws|click|link|site|online|icu|buzz|world|club)\b/i', $msg_low ) ) {
		return 'url-in-msg';
	}

	// Terms that end the conversation on their own.
	$instant = array( 'darknet', 'dark-net', 'dark net', 'tor market', 'onion link', '.onion', 'telegram @', 't.me/' );
	foreach ( $instant as $term ) {
		if ( false !== strpos( $all_text, $term ) ) {
			return 'instant-block';
		}
	}

	// Sales-spam vocabulary. TWO hits required, so one unlucky word in a real
	// enquiry is not enough — "I'd like to buy now" on its own is a customer.
	$patterns = array(
		'href=', 'bitcoin', 'crypto', 'forex', 'casino',
		'viagra', 'cialis', 'pharmacy', 'investment opportunity',
		'earn money', 'make money online', 'work from home',
		'click here', 'free trial', 'act now', 'buy now',
		'seo service', 'web traffic', 'backlink',
		'lottery', 'winner', 'prize', 'congratulations you',
		'beneficiary', 'inheritance',
		'weight loss', 'diet pill', 'male enhancement',
		'payday loan', 'debt relief',
	);
	$hits = 0;
	foreach ( $patterns as $pattern ) {
		if ( false !== strpos( $all_text, $pattern ) ) {
			$hits++;
		}
	}
	if ( $hits >= 2 ) {
		return 'keyword-' . $hits;
	}

	// Machine-shaped names.
	if ( preg_match( '/\d{4,}/', $name ) ) {
		return 'digits-in-name';
	}
	if ( '' !== $name && ! preg_match( '/\s/', $name ) && strlen( $name ) > 12 ) {
		return 'bot-name';
	}

	/*
	 * An unsolicited pitch for SEO or marketing services.
	 *
	 * This is the rule that matters here. The three real examples from the studio's
	 * inbox were polite, correctly spelled, signed with a plausible name, sent from a
	 * gmail address, contained no links in two cases out of three, and were typed
	 * slowly enough to clear every timing check. Nothing about their SHAPE says bot.
	 * What gives them away is the subject: all three are selling search rankings to
	 * the business.
	 *
	 * A customer of a framing studio wants a picture framed. They do not write about
	 * keyword rankings, meta tags or organic growth. So the topic is the signal.
	 *
	 * ⚠ TWO phrases are required, not one, and the list avoids bare "google" -- "I
	 * found you on Google" is how a real customer opens.
	 *
	 * ⚠ DO NOT put this rule on a site whose clients legitimately talk about SEO -- a
	 * marketing agency, a web business. Both the list and the threshold are
	 * filterable so it can be softened or emptied per site.
	 */
	$pitch = apply_filters(
		'gif_guard_pitch_phrases',
		array(
			'seo', 'search engine optimi', 'google ranking', 'keyword ranking',
			'serps', 'backlink', 'link building', 'guest post', 'domain authority',
			'organic growth', 'organic traffic', 'organic ranking', 'high-intent traffic',
			'meta tags', 'page speed', 'indexing issue', 'google visibility',
			'first page of google', 'rank higher', 'web traffic', 'digital marketing',
			'boost your ranking', 'improve your ranking', 'website audit',
			'increase your sales', 'grow your business online',
		)
	);

	$pitch_hits = 0;
	foreach ( $pitch as $phrase ) {
		// "seo" needs word boundaries or it matches inside ordinary words.
		$found = ( 'seo' === $phrase )
			? (bool) preg_match( '/\bseo\b/i', $all_text )
			: ( false !== strpos( $all_text, $phrase ) );
		if ( $found ) {
			$pitch_hits++;
		}
	}

	if ( $pitch_hits >= (int) apply_filters( 'gif_guard_pitch_threshold', 2 ) ) {
		return 'sales-pitch-' . $pitch_hits;
	}

	/*
	 * A "bulk-signup shape" rule was written here and then DELETED. Recording why,
	 * so nobody adds it back.
	 *
	 * It fired when the mailbox had a run of four or more digits AND the phone number
	 * could not be a UK one -- meant as an independent net for a future campaign that
	 * is not about SEO. Both halves were required, which felt cautious enough.
	 *
	 * In testing it blocked this, which is a customer:
	 *
	 *     Pat Devlin, patdevlin2004@gmail.com, phone "2563 1234"
	 *     "Wondering what you would charge to print and mount six A3 photographs
	 *      from a wedding. No rush on them at all."
	 *
	 * A birth year in a gmail address and a local number typed without the area code.
	 * Utterly ordinary in Ballymena. And it caught nothing the sales-pitch rule had
	 * not already caught, so it was pure cost.
	 *
	 * The lesson worth keeping: a rule justified by a campaign that has not happened
	 * yet cannot be tested against anything, so its false positives are the only real
	 * thing about it. Wait for the samples, then write the rule.
	 */

	return '';
}

/**
 * Keep a content-rejected message where it can be recovered.
 *
 * Stored in the Trash so it stays out of the Enquiries list, is still readable, and
 * is cleared out by WordPress on its own after 30 days.
 *
 * @param string $reason Rule that caught it.
 * @param array  $f      Submitted fields.
 */
function gif_guard_quarantine( $reason, $f ) {
	if ( ! post_type_exists( 'gif_enquiry' ) ) {
		return; // Theme not active; the log still has it.
	}

	$id = wp_insert_post(
		array(
			'post_type'   => 'gif_enquiry',
			'post_status' => 'trash',
			'post_title'  => sprintf(
				/* translators: 1: rule name, 2: sender name */
				__( '[spam: %1$s] %2$s', 'getitframed' ),
				$reason,
				'' !== $f['name'] ? $f['name'] : __( 'no name given', 'getitframed' )
			),
		),
		true
	);

	if ( is_wp_error( $id ) || ! $id ) {
		return;
	}

	foreach ( array( 'name', 'email', 'phone', 'service', 'message' ) as $key ) {
		update_post_meta( $id, '_gif_' . $key, $f[ $key ] );
	}
	update_post_meta( $id, '_gif_spam', $reason );
	update_post_meta( $id, '_gif_sent', 0 );
	update_post_meta( $id, '_gif_ip', gif_guard_ip() );
}

/**
 * Send them to the same thank-you the theme shows.
 *
 * A bot told it failed simply tries again differently. The submission is already
 * logged, and anything caught on content is already quarantined, so nothing is lost
 * by being quiet about it.
 */
function gif_guard_pretend_sent() {
	$redirect = '';
	if ( isset( $_POST['gif_redirect'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$redirect = wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['gif_redirect'] ) ), '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
	if ( ! $redirect ) {
		$redirect = home_url( '/contact/' );
	}
	wp_safe_redirect( add_query_arg( 'enquiry', 'sent', remove_query_arg( 'enquiry', $redirect ) ) . '#enquiry' );
	exit;
}

/**
 * Best-effort client IP, for the log only.
 *
 * @return string
 */
function gif_guard_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
}

/**
 * Append one line to this month's log.
 *
 * Passes are logged as well as blocks. A log of blocks alone cannot tell you whether
 * the filter is throwing away real enquiries, which is the only question worth
 * asking about a spam filter.
 *
 * @param string $reason Rule name, or PASSED.
 * @param array  $f      Submitted fields.
 */
function gif_guard_log( $reason, $f ) {
	$dir = WP_CONTENT_DIR . '/antispam-logs';
	if ( ! is_dir( $dir ) ) {
		if ( ! wp_mkdir_p( $dir ) ) {
			return;
		}
		// Nothing in here should ever be readable from a browser.
		@file_put_contents( $dir . '/.htaccess', "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" ); // phpcs:ignore
		@file_put_contents( $dir . '/index.php', "<?php // Silence is golden.\n" ); // phpcs:ignore
	}

	// The raw message, because the whole point of reading this file later is to see
	// what the sender actually typed -- including the markup a rule fired on, which
	// the sanitised copy no longer contains. Newlines are flattened so one
	// submission can never become several log lines.
	$msg = isset( $f['raw_message'] ) ? $f['raw_message'] : $f['message'];

	$line = sprintf(
		"[%s] %-14s ip=%s | name=%s | email=%s | msg=%s\n",
		gmdate( 'Y-m-d H:i:s' ),
		$reason,
		gif_guard_ip(),
		substr( str_replace( array( "\r", "\n" ), ' ', isset( $f['raw_name'] ) ? $f['raw_name'] : $f['name'] ), 0, 60 ),
		substr( $f['email'], 0, 80 ),
		substr( str_replace( array( "\r", "\n" ), ' ', $msg ), 0, 200 )
	);

	@file_put_contents( $dir . '/enquiries-' . gmdate( 'Y-m' ) . '.log', $line, FILE_APPEND | LOCK_EX ); // phpcs:ignore
}
