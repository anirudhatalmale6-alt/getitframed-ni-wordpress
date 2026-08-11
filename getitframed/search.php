<?php
/**
 * Search results.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

gif_page_banner(
	__( 'Search', 'getitframed' ),
	sprintf(
		/* translators: %s: search term */
		__( 'Results for “%s”', 'getitframed' ),
		get_search_query()
	),
	''
);
?>

<section class="content-section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article style="margin-bottom:2.5rem">
					<h2 class="serif" style="font-size:1.35rem">
						<a href="<?php the_permalink(); ?>" style="text-decoration:none"><?php the_title(); ?></a>
					</h2>
					<p style="color:var(--violet);font-size:0.95rem"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
				</article>
				<?php
			endwhile;
			?>
			<div class="gif-pagination"><?php echo wp_kses_post( paginate_links() ); ?></div>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing matched that search. Try a service name, or ring the studio.', 'getitframed' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
