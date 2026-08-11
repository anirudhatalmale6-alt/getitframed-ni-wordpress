<?php
/**
 * A standard page.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	gif_page_banner( '', get_the_title(), has_excerpt() ? get_the_excerpt() : '' );
	?>

	<section class="content-section">
		<div class="container">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="service-hero-img" style="margin-bottom:2.4rem">
					<?php the_post_thumbnail( 'gif-service-hero' ); ?>
				</div>
			<?php endif; ?>

			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before' => '<div class="gif-pagination">',
						'after'  => '</div>',
					)
				);
				?>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
