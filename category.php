<?php
/**
 * Category archive template.
 *
 * @package asdo-blog
 */

get_header();
?>

<header class="category-header">
	<h1><?php single_cat_title(); ?></h1>
	<?php
	$asdo_category_description = category_description();
	if ( $asdo_category_description ) :
		?>
	<div class="category-description">
		<?php echo wp_kses_post( $asdo_category_description ); ?>
	</div>
	<?php endif; ?>
</header>

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
	<p>No posts found in this category.</p>
<?php endif; ?>

<?php get_footer(); ?>
