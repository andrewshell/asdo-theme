<?php
/**
 * Template Name: Search Page
 * Slug: search
 *
 * @package asdo-blog
 */

get_header();
?>

<article class="blog-post">
<header>
	<h1>Search</h1>
</header>
<section>

<?php
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public search form, no state change.
$search_query = isset( $_GET['keywords'] ) ? sanitize_text_field( wp_unslash( $_GET['keywords'] ) ) : '';
?>

<form role="search" autocomplete="off" method="get" action="<?php echo esc_url( home_url( '/search/' ) ); ?>" class="searchform">
	<label for="search-input" class="screen-reader-text"><?php esc_html_e( 'Search', 'asdo-theme' ); ?></label>
	<input type="text" id="search-input" name="keywords" value="<?php echo esc_attr( $search_query ); ?>" />
	<button type="submit">Search</button>
</form>

<?php
if ( $search_query ) :
	$search_results = new WP_Query(
		array(
			's'              => $search_query,
			'post_type'      => array( 'post', 'page' ),
			'posts_per_page' => 20,
		)
	);

	if ( $search_results->have_posts() ) :
		?>
<div class="feed h-feed">
		<?php
		$feed_heading_level = 2;
		$asdo_first         = true;
		while ( $search_results->have_posts() ) :
			$search_results->the_post();
			if ( ! $asdo_first ) :
				?>
	<hr class="feed-separator">
				<?php
			endif;
			$asdo_first = false;
			$feed_post  = get_post();
			include get_template_directory() . '/template-parts/feeditem.php';
		endwhile;
		?>
</div>
		<?php wp_reset_postdata(); ?>
	<?php else : ?>
<p>No results found for "<?php echo esc_html( $search_query ); ?>".</p>
	<?php endif; ?>
<?php endif; ?>

</section>
<hr>
<footer>
<?php get_template_part( 'template-parts/bio' ); ?>
</footer>
</article>

<?php get_footer(); ?>
