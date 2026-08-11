<?php
/**
 * A single service page.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$gif_strap       = get_post_meta( get_the_ID(), '_gif_strapline', true );
	$gif_contact_url = gif_page_url( 'contact' );
	$gif_siblings    = gif_services();
	?>

	<?php gif_page_banner( __( 'Our Services', 'getitframed' ), get_the_title(), $gif_strap ); ?>

	<section class="content-section">
		<div class="container">
			<div class="service-layout">
				<div>
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="service-hero-img">
							<?php the_post_thumbnail( 'gif-service-hero', array( 'loading' => 'eager' ) ); ?>
						</div>
					<?php endif; ?>

					<div class="entry-content">
						<?php
						if ( trim( get_the_content() ) ) {
							the_content();
						} else {
							// Rather than an empty page, show the summary and say plainly
							// that the detail is still being written.
							if ( has_excerpt() ) {
								printf( '<p>%s</p>', esc_html( get_the_excerpt() ) );
							}
							printf(
								'<p>%s</p>',
								esc_html__( 'Full details for this service are being written up. In the meantime, ring the studio or send us a message and we will talk you through the options.', 'getitframed' )
							);
						}
						?>
					</div>
				</div>

				<aside class="service-aside">
					<h4><?php esc_html_e( 'Talk to the studio', 'getitframed' ); ?></h4>
					<p><?php esc_html_e( 'Not sure which option suits? Tell us what you have and what you want it to look like, and we will advise.', 'getitframed' ); ?></p>
					<p>
						<strong><a href="tel:<?php echo esc_attr( gif_tel( gif_opt( 'phone_main' ) ) ); ?>"><?php echo esc_html( gif_opt( 'phone_main' ) ); ?></a></strong>
					</p>
					<a href="<?php echo esc_url( $gif_contact_url ? $gif_contact_url : home_url( '/#contact' ) ); ?>" class="btn btn-primary">
						<?php esc_html_e( 'Send an enquiry', 'getitframed' ); ?>
					</a>

					<?php if ( count( $gif_siblings ) > 1 ) : ?>
						<h4 style="margin-top:2rem"><?php esc_html_e( 'Other services', 'getitframed' ); ?></h4>
						<ul class="service-aside-list">
							<?php foreach ( $gif_siblings as $gif_sibling ) : ?>
								<li class="<?php echo ( get_the_ID() === $gif_sibling->ID ) ? 'is-current' : ''; ?>">
									<a href="<?php echo esc_url( get_permalink( $gif_sibling ) ); ?>"><?php echo esc_html( get_the_title( $gif_sibling ) ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</aside>
			</div>
		</div>
	</section>

	<section class="cta-section">
		<div class="container">
			<h2 class="serif"><?php echo esc_html( gif_opt( 'cta_heading' ) ); ?></h2>
			<p><?php echo wp_kses_post( gif_nl2br( gif_opt( 'cta_text' ) ) ); ?></p>
			<div class="cta-buttons">
				<a href="<?php echo esc_url( $gif_contact_url ? $gif_contact_url : home_url( '/#contact' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Get in Touch', 'getitframed' ); ?></a>
				<a href="tel:<?php echo esc_attr( gif_tel( gif_opt( 'phone_main' ) ) ); ?>" class="btn btn-secondary">
					<?php
					printf(
						/* translators: %s: phone number */
						esc_html__( 'Call %s', 'getitframed' ),
						esc_html( gif_opt( 'phone_main' ) )
					);
					?>
				</a>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
