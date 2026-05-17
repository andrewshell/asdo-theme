<?php
/**
 * Archive template.
 *
 * @package asdo-blog
 */

get_header();
?>

<h1><?php the_archive_title(); ?></h1>

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
	<p>No posts found.</p>
<?php endif; ?>

<?php get_footer(); ?>
