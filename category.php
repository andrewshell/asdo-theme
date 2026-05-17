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
		<?php echo asdo_category_links(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
	<p>No posts found in this category.</p>
<?php endif; ?>

<?php get_footer(); ?>
