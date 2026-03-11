<?php
/**
 * Main index template.
 *
 * @package asdo-blog
 */

get_header();
?>

<?php if ( have_posts() ) : ?>
	<?php
	while ( have_posts() ) :
		the_post();
		?>
	<article class="post-list-item">
		<section>
		<header>
			<h2>
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</h2>
			<small><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></small>
		</header>
		<p><?php echo esc_html( asdo_truncate( get_the_content(), 280 ) ); ?></p>
		</section>
	</article>
	<?php endwhile; ?>

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
