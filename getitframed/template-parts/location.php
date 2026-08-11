<?php
/**
 * The "Visit the Studio" block: address, phone, email, hours and the map.
 *
 * Given its own id of "contact" as well as "location", because the prototype's
 * Get in Touch links all pointed at #contact while the section was called
 * #location -- so the main call to action did nothing.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="location" class="location-section" style="padding-bottom: 3.5rem;">
	<span id="contact" style="display:block;height:0;overflow:hidden" aria-hidden="true"></span>
	<div class="container">
		<div class="location-grid">
			<div class="location-info">
				<span class="section-label"><?php esc_html_e( 'Find Us', 'getitframed' ); ?></span>
				<h2 class="serif"><?php esc_html_e( 'Visit the Studio', 'getitframed' ); ?></h2>

				<div class="location-detail">
					<span class="detail-label"><?php esc_html_e( 'Address', 'getitframed' ); ?></span>
					<p>
						<?php echo esc_html( gif_opt( 'address_line1' ) ); ?><br>
						<?php echo esc_html( gif_opt( 'address_line2' ) ); ?>
						<?php if ( gif_opt( 'address_line3' ) ) : ?>
							<br><?php echo esc_html( gif_opt( 'address_line3' ) ); ?>
						<?php endif; ?>
					</p>
				</div>

				<div class="location-detail">
					<span class="detail-label"><?php esc_html_e( 'Phone', 'getitframed' ); ?></span>
					<p><a href="tel:<?php echo esc_attr( gif_tel( gif_opt( 'phone_main' ) ) ); ?>"><?php echo esc_html( gif_opt( 'phone_main' ) ); ?></a></p>
					<?php if ( gif_opt( 'phone_mobile' ) ) : ?>
						<p><a href="tel:<?php echo esc_attr( gif_tel( gif_opt( 'phone_mobile' ) ) ); ?>"><?php echo esc_html( gif_opt( 'phone_mobile' ) ); ?></a></p>
					<?php endif; ?>
				</div>

				<div class="location-detail">
					<span class="detail-label"><?php esc_html_e( 'Email', 'getitframed' ); ?></span>
					<p><a href="mailto:<?php echo esc_attr( antispambot( gif_opt( 'email' ) ) ); ?>"><?php echo esc_html( antispambot( gif_opt( 'email' ) ) ); ?></a></p>
				</div>

				<?php if ( gif_opt( 'hours' ) ) : ?>
					<div class="location-detail">
						<span class="detail-label"><?php esc_html_e( 'Opening hours', 'getitframed' ); ?></span>
						<p><?php echo wp_kses_post( gif_nl2br( gif_opt( 'hours' ) ) ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( gif_opt( 'turnaround' ) ) : ?>
					<div class="location-detail">
						<span class="detail-label"><?php esc_html_e( 'Turnaround', 'getitframed' ); ?></span>
						<p><?php echo esc_html( gif_opt( 'turnaround' ) ); ?></p>
					</div>
				<?php endif; ?>
			</div>

			<div>
				<?php if ( gif_opt( 'map_embed' ) ) : ?>
					<div class="map-wrapper">
						<iframe src="<?php echo esc_url( gif_opt( 'map_embed' ) ); ?>"
							title="<?php esc_attr_e( 'Map showing the Get It Framed NI studio', 'getitframed' ); ?>"
							allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
