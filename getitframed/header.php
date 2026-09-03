<?php
/**
 * Header: top bar, logo and navigation.
 *
 * The menu is generated. A service appears in the dropdown only when it is
 * published, and a page link appears only when that page exists, so the
 * href="#" placeholders that filled the prototype cannot come back.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gif_contact_url = gif_page_url( 'contact' );
$gif_about_url   = gif_page_url( 'about' );
$gif_trade_url   = gif_page_url( 'trade' );
$gif_gallery_url = get_post_type_archive_link( 'gif_gallery' );
$gif_services    = gif_services();
$gif_services_url = get_post_type_archive_link( 'gif_service' );

// Fall back to the homepage anchors when a page has not been built yet.
if ( ! $gif_about_url ) {
	$gif_about_url = home_url( '/#about' );
}
if ( ! $gif_contact_url ) {
	$gif_contact_url = home_url( '/#location' );
}
if ( ! $gif_gallery_url || ! gif_has_gallery() ) {
	$gif_gallery_url = false;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'getitframed' ); ?></a>

<div class="top-bar">
	<div class="container">
		<div class="top-bar-left">
			<span>&#9742;
				<a href="tel:<?php echo esc_attr( gif_tel( gif_opt( 'phone_main' ) ) ); ?>"><?php echo esc_html( gif_opt( 'phone_main' ) ); ?></a>
				<?php if ( gif_opt( 'phone_mobile' ) ) : ?>
					/ <a href="tel:<?php echo esc_attr( gif_tel( gif_opt( 'phone_mobile' ) ) ); ?>"><?php echo esc_html( gif_opt( 'phone_mobile' ) ); ?></a>
				<?php endif; ?>
			</span>
			<span>&#9993; <?php echo gif_email_html( __( 'Email Us', 'getitframed' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?></span>
			<span>&#9906; <?php echo esc_html( gif_opt( 'topbar_location' ) ); ?></span>
		</div>
		<div class="top-bar-right"><?php echo esc_html( gif_opt( 'topbar_tagline' ) ); ?></div>
	</div>
</div>

<header>
	<div class="container nav-wrapper">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand" rel="home">
			<?php
			// the_custom_logo() prints its own <a>. Nesting that inside .brand is
			// invalid HTML: the parser closes .brand early, so the logo ends up a
			// SIBLING of .brand rather than a child. .brand img then matches nothing,
			// the logo renders at its natural size and sits adrift of the left edge.
			// Print the image on its own instead and keep the single wrapping link.
			$gif_logo_id = (int) get_theme_mod( 'custom_logo' );
			if ( $gif_logo_id && wp_attachment_is_image( $gif_logo_id ) ) {
				echo wp_get_attachment_image(
					$gif_logo_id,
					'full',
					false,
					array(
						'class' => 'custom-logo',
						'alt'   => esc_attr( get_bloginfo( 'name' ) ),
					)
				);
			} else {
				?>
				<img src="<?php echo esc_url( GIF_URI . '/assets/img/logo.png' ); ?>"
					alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="216" height="106">
				<?php
			}
			?>
		</a>

		<nav id="mainNav" aria-label="<?php esc_attr_e( 'Main menu', 'getitframed' ); ?>">
			<a href="<?php echo esc_url( $gif_about_url ); ?>"
				class="<?php echo is_page( 'about' ) ? 'current' : ''; ?>"><?php esc_html_e( 'About', 'getitframed' ); ?></a>

			<?php if ( $gif_services ) : ?>
				<div class="nav-dropdown">
					<a href="<?php echo esc_url( $gif_services_url ? $gif_services_url : home_url( '/#services' ) ); ?>"
						class="<?php echo ( is_post_type_archive( 'gif_service' ) || is_singular( 'gif_service' ) ) ? 'current' : ''; ?>">
						<?php esc_html_e( 'Services', 'getitframed' ); ?>
					</a>
					<div class="dropdown-menu">
						<?php foreach ( $gif_services as $gif_service ) : ?>
							<a href="<?php echo esc_url( get_permalink( $gif_service ) ); ?>"
								class="<?php echo ( is_singular( 'gif_service' ) && get_the_ID() === $gif_service->ID ) ? 'current' : ''; ?>">
								<?php echo esc_html( get_the_title( $gif_service ) ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $gif_gallery_url ) : ?>
				<a href="<?php echo esc_url( $gif_gallery_url ); ?>"
					class="<?php echo ( is_post_type_archive( 'gif_gallery' ) || is_tax( 'gif_gallery_cat' ) ) ? 'current' : ''; ?>"><?php esc_html_e( 'Gallery', 'getitframed' ); ?></a>
			<?php endif; ?>

			<?php if ( $gif_trade_url ) : ?>
				<a href="<?php echo esc_url( $gif_trade_url ); ?>"
					class="<?php echo is_page( 'trade' ) ? 'current' : ''; ?>"><?php esc_html_e( 'Trade', 'getitframed' ); ?></a>
			<?php endif; ?>

			<a href="<?php echo esc_url( $gif_contact_url ); ?>" class="nav-cta"><?php esc_html_e( 'Get in Touch', 'getitframed' ); ?></a>
		</nav>

		<button class="mobile-toggle" type="button" aria-expanded="false" aria-controls="mainNav">
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'getitframed' ); ?></span>
			<span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
		</button>
	</div>
</header>

<main id="main">
