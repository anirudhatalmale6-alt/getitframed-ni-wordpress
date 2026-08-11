<?php
/**
 * Not found.
 *
 * Rather than a dead end, offer the things people are actually looking for.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<section class="content-section">
	<div class="container error-404">
		<h1 class="serif">404</h1>
		<h2 class="serif" style="font-size:1.6rem"><?php esc_html_e( 'We cannot find that page', 'getitframed' ); ?></h2>
		<p style="color:var(--violet)"><?php esc_html_e( 'It may have moved, or the link may be out of date. Here is where most people are heading:', 'getitframed' ); ?></p>

		<div class="mini-grid" style="max-width:820px;margin:2.5rem auto 0">
			<?php foreach ( gif_services( 8 ) as $gif_service ) : ?>
				<a class="mini-card" href="<?php echo esc_url( get_permalink( $gif_service ) ); ?>"><?php echo esc_html( get_the_title( $gif_service ) ); ?></a>
			<?php endforeach; ?>
		</div>

		<p style="margin-top:2.5rem">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Back to the homepage', 'getitframed' ); ?></a>
		</p>
	</div>
</section>

<?php
get_footer();
