<?php
/**
 * Footer.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gif_footer_services = gif_services( 6 );
$gif_contact_link    = gif_page_url( 'contact' );
$gif_about_link      = gif_page_url( 'about' );
$gif_gallery_link    = gif_has_gallery() ? get_post_type_archive_link( 'gif_gallery' ) : false;
$gif_trade_link      = gif_page_url( 'trade' );
?>
</main>

<footer>
	<div class="container">
		<div class="footer-grid">
			<div class="footer-about">
				<h4><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h4>
				<p><?php echo esc_html( gif_opt( 'footer_about' ) ); ?></p>
				<?php if ( gif_opt( 'facebook' ) || gif_opt( 'instagram' ) ) : ?>
					<p style="margin-top:1rem">
						<?php if ( gif_opt( 'facebook' ) ) : ?>
							<a href="<?php echo esc_url( gif_opt( 'facebook' ) ); ?>" rel="noopener" style="display:inline;margin-right:14px"><?php esc_html_e( 'Facebook', 'getitframed' ); ?></a>
						<?php endif; ?>
						<?php if ( gif_opt( 'instagram' ) ) : ?>
							<a href="<?php echo esc_url( gif_opt( 'instagram' ) ); ?>" rel="noopener" style="display:inline"><?php esc_html_e( 'Instagram', 'getitframed' ); ?></a>
						<?php endif; ?>
					</p>
				<?php endif; ?>
			</div>

			<div>
				<h4><?php esc_html_e( 'Quick Links', 'getitframed' ); ?></h4>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'getitframed' ); ?></a>
				<a href="<?php echo esc_url( $gif_about_link ? $gif_about_link : home_url( '/#about' ) ); ?>"><?php esc_html_e( 'About', 'getitframed' ); ?></a>
				<?php if ( gif_services() ) : ?>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'gif_service' ) ); ?>"><?php esc_html_e( 'Services', 'getitframed' ); ?></a>
				<?php endif; ?>
				<?php if ( $gif_gallery_link ) : ?>
					<a href="<?php echo esc_url( $gif_gallery_link ); ?>"><?php esc_html_e( 'Gallery', 'getitframed' ); ?></a>
				<?php endif; ?>
				<?php if ( $gif_trade_link ) : ?>
					<a href="<?php echo esc_url( $gif_trade_link ); ?>"><?php esc_html_e( 'Trade', 'getitframed' ); ?></a>
				<?php endif; ?>
				<a href="<?php echo esc_url( $gif_contact_link ? $gif_contact_link : home_url( '/#location' ) ); ?>"><?php esc_html_e( 'Contact', 'getitframed' ); ?></a>
			</div>

			<div>
				<h4><?php esc_html_e( 'Services', 'getitframed' ); ?></h4>
				<?php foreach ( $gif_footer_services as $gif_footer_service ) : ?>
					<a href="<?php echo esc_url( get_permalink( $gif_footer_service ) ); ?>"><?php echo esc_html( get_the_title( $gif_footer_service ) ); ?></a>
				<?php endforeach; ?>
			</div>

			<div>
				<span class="contact-label"><?php esc_html_e( 'Studio', 'getitframed' ); ?></span>
				<p class="contact-val">
					<?php echo esc_html( gif_opt( 'address_line1' ) ); ?><br>
					<?php echo esc_html( gif_opt( 'address_line2' ) ); ?>
				</p>
				<span class="contact-label"><?php esc_html_e( 'Phone', 'getitframed' ); ?></span>
				<p class="contact-val" style="font-size: 0.95rem;">
					<?php echo esc_html( gif_opt( 'phone_main' ) ); ?>
					<?php if ( gif_opt( 'phone_mobile' ) ) : ?>
						<br><?php echo esc_html( gif_opt( 'phone_mobile' ) ); ?>
					<?php endif; ?>
				</p>
				<span class="contact-label"><?php esc_html_e( 'Email', 'getitframed' ); ?></span>
				<p class="contact-val" style="font-size: 0.85rem;"><?php echo esc_html( antispambot( gif_opt( 'email' ) ) ); ?></p>
			</div>
		</div>

		<div class="footer-bottom">
			&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'getitframed' ); ?>
			<span class="footer-credit">
				<?php esc_html_e( 'Designed by', 'getitframed' ); ?>
				<a href="https://smilecreative.agency" rel="noopener">Smile Creative</a>
			</span>
		</div>
	</div>
</footer>

<div class="lightbox" id="gifLightbox" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Enlarged image', 'getitframed' ); ?>">
	<button class="lightbox-close" type="button" aria-label="<?php esc_attr_e( 'Close', 'getitframed' ); ?>">&times;</button>
	<div>
		<img src="" alt="" id="gifLightboxImg">
		<div class="lightbox-caption" id="gifLightboxCaption"></div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
