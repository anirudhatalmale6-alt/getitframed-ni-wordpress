<?php
/**
 * Gallery.
 *
 * Filtering is done client-side across everything already on the page, so the
 * buttons are instant and still work with JavaScript switched off -- in that
 * case every photograph is simply shown.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$gif_terms = gif_gallery_terms();
$gif_is_tax = is_tax( 'gif_gallery_cat' );

gif_page_banner(
	__( 'Our work', 'getitframed' ),
	$gif_is_tax ? single_term_title( '', false ) : __( 'Gallery', 'getitframed' ),
	__( 'A selection of recent work from the studio.', 'getitframed' )
);
?>

<section class="content-section">
	<div class="container">
		<?php if ( $gif_terms && ! $gif_is_tax ) : ?>
			<div class="gallery-filters" role="group" aria-label="<?php esc_attr_e( 'Filter the gallery', 'getitframed' ); ?>">
				<button type="button" class="gallery-filter active" data-filter="all"><?php esc_html_e( 'All', 'getitframed' ); ?></button>
				<?php foreach ( $gif_terms as $gif_term ) : ?>
					<button type="button" class="gallery-filter" data-filter="<?php echo esc_attr( $gif_term->slug ); ?>">
						<?php echo esc_html( $gif_term->name ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="gallery-grid" id="galleryGrid">
				<?php
				while ( have_posts() ) :
					the_post();

					$gif_slugs = wp_get_post_terms( get_the_ID(), 'gif_gallery_cat', array( 'fields' => 'slugs' ) );
					$gif_slugs = is_wp_error( $gif_slugs ) ? array() : $gif_slugs;
					$gif_full  = wp_get_attachment_image_url( get_post_thumbnail_id(), 'full' );
					$gif_cap   = has_excerpt() ? get_the_excerpt() : get_the_title();

					if ( ! has_post_thumbnail() ) {
						continue;
					}
					?>
					<figure class="gallery-item"
						data-cats="<?php echo esc_attr( implode( ' ', $gif_slugs ) ); ?>"
						data-full="<?php echo esc_url( $gif_full ); ?>"
						data-caption="<?php echo esc_attr( $gif_cap ); ?>">
						<?php the_post_thumbnail( 'gif-gallery', array( 'loading' => 'lazy' ) ); ?>
						<?php if ( get_the_title() ) : ?>
							<figcaption class="gallery-item-caption"><?php the_title(); ?></figcaption>
						<?php endif; ?>
					</figure>
					<?php
				endwhile;
				?>
			</div>

			<p class="gallery-empty" id="galleryEmpty" hidden><?php esc_html_e( 'Nothing in that category yet.', 'getitframed' ); ?></p>

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
			<p class="gallery-empty"><?php esc_html_e( 'Photographs of recent work are being added to this page.', 'getitframed' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
