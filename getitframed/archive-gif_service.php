<?php
/**
 * All services.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

gif_page_banner(
	__( 'What we do', 'getitframed' ),
	__( 'Our Services', 'getitframed' ),
	__( 'Printing, mounting, framing and finishing, all under one roof in Portglenone.', 'getitframed' )
);
?>

<section class="services-section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="services-grid">
				<?php
				$gif_index = 0;
				while ( have_posts() ) :
					the_post();
					$gif_colour = gif_card_colour( get_the_ID(), $gif_index );
					$gif_img    = gif_card_image( get_the_ID() );
					$gif_index++;
					?>
					<article class="ga-card <?php echo esc_attr( $gif_colour ); ?><?php echo $gif_img ? '' : ' ga-card--noimg'; ?>" data-href="<?php the_permalink(); ?>">
						<h2 style="font-family:'Libre Baskerville',serif;font-size:1.05rem;text-align:center;min-height:3.2rem"><?php the_title(); ?></h2>
						<?php echo wp_kses_post( $gif_img ); ?>
						<?php if ( has_excerpt() ) : ?>
							<p><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>
						<a href="<?php the_permalink(); ?>" class="ga-card-btn">
							<?php esc_html_e( 'More', 'getitframed' ); ?>
							<span class="screen-reader-text"><?php the_title(); ?></span>
						</a>
					</article>
					<?php
				endwhile;
				?>
			</div>

			<div class="gif-pagination">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'prev_text' => __( '&larr; Previous', 'getitframed' ),
							'next_text' => __( 'Next &rarr;', 'getitframed' ),
						)
					)
				);
				?>
			</div>
		<?php else : ?>
			<p style="color:rgba(255,255,255,0.7)"><?php esc_html_e( 'Services are being added shortly.', 'getitframed' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/location' ); ?>

<?php
get_footer();
