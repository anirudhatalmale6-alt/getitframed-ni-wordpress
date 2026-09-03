<?php
/**
 * Template Name: Contact
 *
 * The enquiry form the prototype never had, plus the studio details.
 * Applied automatically to a page with the slug "contact".
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$gif_state    = gif_form_state();
$gif_errors   = $gif_state['errors'];
$gif_old      = $gif_state['data'];
$gif_services = gif_services();

/**
 * Previously submitted value, so a failed submission does not lose what was typed.
 *
 * @param array  $data Old data.
 * @param string $key  Field key.
 * @return string
 */
$gif_val = function ( $data, $key ) {
	return isset( $data[ $key ] ) ? $data[ $key ] : '';
};

while ( have_posts() ) :
	the_post();

	gif_page_banner(
		__( 'Get in touch', 'getitframed' ),
		get_the_title(),
		has_excerpt() ? get_the_excerpt() : __( 'Tell us what you have and what you would like done with it.', 'getitframed' )
	);
	?>

	<section class="content-section" id="enquiry">
		<div class="container">
			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="entry-content" style="margin-bottom:2.5rem"><?php the_content(); ?></div>
			<?php endif; ?>

			<div class="contact-layout">
				<div>
					<?php gif_form_notice(); ?>

					<h2 class="serif" style="font-size:1.6rem"><?php esc_html_e( 'Send us a message', 'getitframed' ); ?></h2>

					<form class="gif-form" method="post" action="<?php echo esc_url( get_permalink() ); ?>#enquiry">
						<?php wp_nonce_field( 'gif_contact', 'gif_contact_nonce' ); ?>
						<input type="hidden" name="gif_loaded" value="<?php echo esc_attr( time() ); ?>">
						<input type="hidden" name="gif_redirect" value="<?php echo esc_url( get_permalink() ); ?>">

						<?php
						// The hiding is inline, not only in the stylesheet. If style.css ever
						// fails to load, a class-hidden honeypot renders as a visible field --
						// and anyone who fills it in gets a thank-you while their enquiry is
						// thrown away. The class is kept for maintenance; the inline rule is
						// what guarantees it.
						?>
						<div class="gif-field gif-hp" aria-hidden="true"
							style="position:absolute!important;left:-9999px!important;width:1px!important;height:1px!important;overflow:hidden!important">
							<label for="gif_website"><?php esc_html_e( 'Leave this field empty', 'getitframed' ); ?></label>
							<input type="text" name="gif_website" id="gif_website" tabindex="-1" autocomplete="off">
						</div>

						<div class="gif-field <?php echo isset( $gif_errors['name'] ) ? 'has-error' : ''; ?>">
							<label for="gif_name"><?php esc_html_e( 'Your name', 'getitframed' ); ?> <span aria-hidden="true">*</span></label>
							<input type="text" name="gif_name" id="gif_name" required autocomplete="name"
								value="<?php echo esc_attr( $gif_val( $gif_old, 'name' ) ); ?>">
							<?php if ( isset( $gif_errors['name'] ) ) : ?>
								<span class="field-error"><?php echo esc_html( $gif_errors['name'] ); ?></span>
							<?php endif; ?>
						</div>

						<div class="gif-field <?php echo isset( $gif_errors['email'] ) ? 'has-error' : ''; ?>">
							<label for="gif_email"><?php esc_html_e( 'Email', 'getitframed' ); ?> <span aria-hidden="true">*</span></label>
							<input type="email" name="gif_email" id="gif_email" required autocomplete="email"
								value="<?php echo esc_attr( $gif_val( $gif_old, 'email' ) ); ?>">
							<?php if ( isset( $gif_errors['email'] ) ) : ?>
								<span class="field-error"><?php echo esc_html( $gif_errors['email'] ); ?></span>
							<?php endif; ?>
						</div>

						<div class="gif-field">
							<label for="gif_phone"><?php esc_html_e( 'Phone', 'getitframed' ); ?></label>
							<input type="tel" name="gif_phone" id="gif_phone" autocomplete="tel"
								value="<?php echo esc_attr( $gif_val( $gif_old, 'phone' ) ); ?>">
						</div>

						<?php if ( $gif_services ) : ?>
							<div class="gif-field">
								<label for="gif_service"><?php esc_html_e( 'What is it about?', 'getitframed' ); ?></label>
								<select name="gif_service" id="gif_service">
									<option value=""><?php esc_html_e( 'General enquiry', 'getitframed' ); ?></option>
									<?php foreach ( $gif_services as $gif_service ) : ?>
										<option value="<?php echo esc_attr( get_the_title( $gif_service ) ); ?>"
											<?php selected( $gif_val( $gif_old, 'service' ), get_the_title( $gif_service ) ); ?>>
											<?php echo esc_html( get_the_title( $gif_service ) ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php endif; ?>

						<div class="gif-field <?php echo isset( $gif_errors['message'] ) ? 'has-error' : ''; ?>">
							<label for="gif_message"><?php esc_html_e( 'Your message', 'getitframed' ); ?> <span aria-hidden="true">*</span></label>
							<textarea name="gif_message" id="gif_message" required><?php echo esc_textarea( $gif_val( $gif_old, 'message' ) ); ?></textarea>
							<?php if ( isset( $gif_errors['message'] ) ) : ?>
								<span class="field-error"><?php echo esc_html( $gif_errors['message'] ); ?></span>
							<?php endif; ?>
						</div>

						<p class="form-consent">
							<?php esc_html_e( 'We use what you send us only to answer your enquiry. We do not pass it to anyone else.', 'getitframed' ); ?>
						</p>

						<button type="submit" name="gif_contact_submit" value="1" class="btn btn-primary">
							<?php esc_html_e( 'Send enquiry', 'getitframed' ); ?>
						</button>
					</form>
				</div>

				<div>
					<div class="service-aside" style="position:static">
						<h4><?php esc_html_e( 'The studio', 'getitframed' ); ?></h4>
						<p>
							<?php echo esc_html( gif_opt( 'address_line1' ) ); ?><br>
							<?php echo esc_html( gif_opt( 'address_line2' ) ); ?>
							<?php if ( gif_opt( 'address_line3' ) ) : ?>
								<br><?php echo esc_html( gif_opt( 'address_line3' ) ); ?>
							<?php endif; ?>
						</p>

						<h4 style="margin-top:1.6rem"><?php esc_html_e( 'Phone', 'getitframed' ); ?></h4>
						<p>
							<a href="tel:<?php echo esc_attr( gif_tel( gif_opt( 'phone_main' ) ) ); ?>"><?php echo esc_html( gif_opt( 'phone_main' ) ); ?></a>
							<?php if ( gif_opt( 'phone_mobile' ) ) : ?>
								<br><a href="tel:<?php echo esc_attr( gif_tel( gif_opt( 'phone_mobile' ) ) ); ?>"><?php echo esc_html( gif_opt( 'phone_mobile' ) ); ?></a>
							<?php endif; ?>
						</p>

						<h4 style="margin-top:1.6rem"><?php esc_html_e( 'Email', 'getitframed' ); ?></h4>
						<p><a href="mailto:<?php echo esc_attr( antispambot( gif_opt( 'email' ) ) ); ?>"><?php echo esc_html( antispambot( gif_opt( 'email' ) ) ); ?></a></p>

						<?php if ( gif_opt( 'hours' ) ) : ?>
							<h4 style="margin-top:1.6rem"><?php esc_html_e( 'Opening hours', 'getitframed' ); ?></h4>
							<p><?php echo wp_kses_post( gif_nl2br( gif_opt( 'hours' ) ) ); ?></p>
						<?php endif; ?>
					</div>

					<?php if ( gif_opt( 'map_embed' ) ) : ?>
						<div class="map-wrapper" style="margin-top:1.5rem">
							<iframe src="<?php echo esc_url( gif_opt( 'map_embed' ) ); ?>"
								title="<?php esc_attr_e( 'Map showing the Get It Framed NI studio', 'getitframed' ); ?>"
								style="height:300px" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
