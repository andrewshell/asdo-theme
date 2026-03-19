<?php
/**
 * Feed item template part.
 * Expects $feed_post to be set before including this template.
 *
 * @package asdo-blog
 */

if ( ! isset( $feed_post ) ) {
	return;
}

setup_postdata( $feed_post );
?>
<article class="feed-item h-entry">
	<div class="feed-content">
	<?php if ( get_the_title( $feed_post ) ) : ?>
		<h3 class="feed-title p-name">
		<a href="<?php echo esc_url( get_permalink( $feed_post ) ); ?>" class="u-url"><?php echo esc_html( get_the_title( $feed_post ) ); ?></a>
		</h3>
	<?php endif; ?>

	<div class="feed-excerpt e-content">
		<p>
			<?php echo esc_html( asdo_truncate( $feed_post->post_content, 280 ) ); ?>
		</p>
	</div>

	<div class="feed-meta">
		<p>
		<time class="feed-date dt-published" datetime="<?php echo esc_attr( get_the_date( 'c', $feed_post ) ); ?>">
			<?php echo esc_html( get_the_date( 'F j, Y', $feed_post ) ); ?>
		</time>
		</p>
	</div>
	</div>
</article>
<?php wp_reset_postdata(); ?>
