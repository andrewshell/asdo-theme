<?php
/**
 * Front page template.
 *
 * @package asdo-blog
 */

get_header();
?>

<?php
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
endif;
?>

	<h2>Recent Posts</h2>

	<?php
	$recent_posts = asdo_recent_content( 5 );
	if ( ! empty( $recent_posts ) ) :
		?>
	<div class="feed">
		<?php
		$count = count( $recent_posts );
		foreach ( $recent_posts as $i => $feed_post ) :
			include get_template_directory() . '/template-parts/feeditem.php';
			if ( $i < $count - 1 ) :
				?>
			<hr class="feed-separator">
				<?php
			endif;
		endforeach;
		?>
	</div>
	<?php endif; ?>

	<p><a href="<?php echo esc_url( home_url( '/essays/' ) ); ?>">See all essays &rarr;</a> | <a href="<?php echo esc_url( home_url( '/notes/' ) ); ?>">See all notes &rarr;</a> | <a href="<?php echo esc_url( home_url( '/search/' ) ); ?>">Search &rarr;</a></p>

<?php get_footer(); ?>
