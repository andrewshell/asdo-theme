<?php
/**
 * Search results template.
 *
 * @package asdo-blog
 */

get_header();
?>

<h1>Search Results for: <?php echo esc_html( get_search_query() ); ?></h1>

<?php get_search_form(); ?>

<?php if ( have_posts() ) : ?>
	<div class="feed h-feed">
	<?php
	$feed_heading_level = 2;
	$asdo_first         = true;
	while ( have_posts() ) :
		the_post();
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

	<?php
	the_posts_pagination(
		array(
			'prev_text' => '&larr; Previous',
			'next_text' => 'Next &rarr;',
		)
	);
	?>
<?php else : ?>
	<p>No results found. Try a different search term.</p>
	<?php get_search_form(); ?>
<?php endif; ?>

<?php get_footer(); ?>
