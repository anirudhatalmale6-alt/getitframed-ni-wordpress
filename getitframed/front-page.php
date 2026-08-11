<?php
/**
 * Front page.
 *
 * Section for section this is the approved prototype homepage. The difference
 * is that the services grid is generated from the Services list rather than
 * being twelve hand-written blocks, so adding a service adds it to the grid,
 * the dropdown and the footer at once -- and every "More" button goes
 * somewhere real.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$gif_services    = gif_services();
$gif_contact_url = gif_page_url( 'contact' );
$gif_cta_url     = $gif_contact_url ? $gif_contact_url : '#location';
$gif_about_page  = gif_page_url( 'about' );
?>

<section class="container hero">
	<div>
		<?php if ( gif_opt( 'hero_label' ) ) : ?>
			<span class="section-label"><?php echo esc_html( gif_opt( 'hero_label' ) ); ?></span>
		<?php endif; ?>

		<h1 class="serif">
			<?php echo esc_html( gif_opt( 'hero_heading' ) ); ?>
			<?php if ( gif_opt( 'hero_emphasis' ) ) : ?>
				<em style="color:var(--rose);font-style:normal"><?php echo esc_html( gif_opt( 'hero_emphasis' ) ); ?></em>
			<?php endif; ?>
		</h1>

		<?php if ( gif_opt( 'hero_standfirst' ) ) : ?>
			<p style="font-size: 1.15rem; margin-bottom: 0.5rem;"><?php echo esc_html( gif_opt( 'hero_standfirst' ) ); ?></p>
		<?php endif; ?>

		<?php if ( gif_opt( 'hero_intro' ) ) : ?>
			<p><?php echo esc_html( gif_opt( 'hero_intro' ) ); ?></p>
		<?php endif; ?>

		<div class="cta-group">
			<a href="<?php echo esc_url( $gif_cta_url ); ?>" class="btn btn-primary"><?php esc_html_e( 'Get in Touch', 'getitframed' ); ?></a>
			<?php if ( $gif_services ) : ?>
				<a href="#services" class="btn btn-secondary"><?php esc_html_e( 'Explore Services', 'getitframed' ); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<div class="image-frame">
		<img src="<?php echo esc_url( gif_image_url( 'gif_hero_image', 'hero.webp', 'large' ) ); ?>"
			alt="<?php esc_attr_e( 'Inside the Get It Framed NI print and framing studio', 'getitframed' ); ?>"
			width="800" height="600" fetchpriority="high">
	</div>
</section>

<?php if ( $gif_services ) : ?>
	<section id="services" class="services-section">
		<div class="container">
			<h2 class="serif services-heading" style="font-size: 2rem; color: #ffffff;">
				<?php echo esc_html( gif_opt( 'services_heading' ) ); ?>
			</h2>
			<?php if ( gif_opt( 'services_sub' ) ) : ?>
				<p class="services-sub"><?php echo esc_html( gif_opt( 'services_sub' ) ); ?></p>
			<?php endif; ?>
		</div>
		<div class="container">
			<div class="services-grid">
				<?php foreach ( $gif_services as $gif_index => $gif_service ) : ?>
					<?php
					$gif_link   = get_permalink( $gif_service );
					$gif_colour = gif_card_colour( $gif_service->ID, $gif_index );
					$gif_blurb  = get_the_excerpt( $gif_service );
					$gif_img    = gif_card_image( $gif_service->ID );
					?>
					<article class="ga-card <?php echo esc_attr( $gif_colour ); ?><?php echo $gif_img ? '' : ' ga-card--noimg'; ?>" data-href="<?php echo esc_url( $gif_link ); ?>">
						<h3><?php echo esc_html( get_the_title( $gif_service ) ); ?></h3>
						<?php echo wp_kses_post( $gif_img ); ?>
						<?php if ( $gif_blurb ) : ?>
							<p><?php echo esc_html( $gif_blurb ); ?></p>
						<?php endif; ?>
						<a href="<?php echo esc_url( $gif_link ); ?>" class="ga-card-btn">
							<?php esc_html_e( 'More', 'getitframed' ); ?>
							<span class="screen-reader-text"><?php echo esc_html( get_the_title( $gif_service ) ); ?></span>
						</a>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<section id="about" class="about-section">
	<div class="container">
		<div class="about-grid">
			<div>
				<span class="section-label"><?php esc_html_e( 'The Studio Standard', 'getitframed' ); ?></span>
				<h2 class="serif">
					<?php esc_html_e( 'Professional printing & framing services for public and trade.', 'getitframed' ); ?>
				</h2>
				<div class="about-image" style="margin-top: 2rem;">
					<img src="<?php echo esc_url( gif_image_url( 'gif_about_image', 'about-studio.webp', 'large' ) ); ?>"
						alt="<?php esc_attr_e( 'Framed artwork and prints in the Get It Framed NI studio', 'getitframed' ); ?>"
						width="800" height="600" loading="lazy">
				</div>
			</div>
			<div>
				<?php
				// The About page's own content is the single source of truth for
				// this copy, so it is written once and shown in both places.
				$gif_about_id = $gif_about_page ? get_page_by_path( 'about' ) : null;

				if ( $gif_about_id && trim( $gif_about_id->post_content ) ) {
					echo '<div class="about-copy">';
					echo wp_kses_post( apply_filters( 'the_content', $gif_about_id->post_content ) );
					echo '</div>';
					?>
					<p style="margin-top:1.6rem">
						<a href="<?php echo esc_url( $gif_about_page ); ?>" class="btn btn-secondary"><?php esc_html_e( 'More about the studio', 'getitframed' ); ?></a>
					</p>
					<?php
				} else {
					?>
					<p><?php esc_html_e( 'We work closely within the trade sector and are the chosen framing partner for photographers and artists who need dependable production. That means consistent output across repeat orders, exhibitions, and client deliveries — with careful attention to borders, mounting alignment, and finish.', 'getitframed' ); ?></p>
					<p><?php esc_html_e( 'Because we print, mount, and frame under one roof, the details tie up: the print is sized for the mount, the mount sits perfectly in the frame, and the finished piece feels cohesive.', 'getitframed' ); ?></p>
					<ul class="about-bullets">
						<li><?php esc_html_e( 'Consistent output across repeat orders', 'getitframed' ); ?></li>
						<li><?php esc_html_e( 'Accurate glass cutting and precise mounting', 'getitframed' ); ?></li>
						<li><?php esc_html_e( 'Careful attention to borders and finish', 'getitframed' ); ?></li>
						<li><?php esc_html_e( 'Studio standards for the general public', 'getitframed' ); ?></li>
					</ul>
					<?php
				}
				?>
			</div>
		</div>
	</div>
</section>

<section class="cta-section">
	<div class="container">
		<h2 class="serif"><?php echo esc_html( gif_opt( 'cta_heading' ) ); ?></h2>
		<p><?php echo wp_kses_post( gif_nl2br( gif_opt( 'cta_text' ) ) ); ?></p>
		<div class="cta-buttons">
			<a href="<?php echo esc_url( $gif_cta_url ); ?>" class="btn btn-primary"><?php esc_html_e( 'Get in Touch', 'getitframed' ); ?></a>
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

<?php get_template_part( 'template-parts/location' ); ?>

<?php
get_footer();
