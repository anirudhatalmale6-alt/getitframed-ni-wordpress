<?php
/**
 * Fallback template. Also used for the blog listing if one is ever turned on.
 *
 * @package getitframed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

gif_page_banner( '', get_the_archive_title() ? wp_strip_all_tags( get_the_archive_title() ) : get_bloginfo( 'name' ), '' );
?>

<section class="content-section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article style="margin-bottom:3rem">
					<h2 class="serif" style="font-size:1.5rem">
						<a href="<?php the_permalink(); ?>" style="text-decoration:none"><?php the_title(); ?></a>
					</h2>
					<div class="entry-content"><?php the_excerpt(); ?></div>
				</article>
				<?php
			endwhile;
			?>
			<div class="gif-pagination"><?php echo wp_kses_post( paginate_links() ); ?></div>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing here yet.', 'getitframed' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
