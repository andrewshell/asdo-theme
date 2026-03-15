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
<article class="feed-item h-entry" itemscope itemtype="https://schema.org/BlogPosting">
	<div class="feed-content">
	<?php if ( get_the_title( $feed_post ) ) : ?>
		<h3 class="feed-title p-name" itemprop="headline">
		<a href="<?php echo esc_url( get_permalink( $feed_post ) ); ?>" class="u-url" itemprop="url"><?php echo esc_html( get_the_title( $feed_post ) ); ?></a>
		</h3>
	<?php endif; ?>

	<div class="feed-excerpt e-content" itemprop="articleBody">
		<p>
			<?php echo esc_html( asdo_truncate( $feed_post->post_content, 280 ) ); ?>
		<a href="<?php echo esc_url( get_permalink( $feed_post ) ); ?>" class="read-more" rel="nofollow">Read more &rarr;</a>
		</p>
	</div>

	<div class="feed-meta">
		<p><a href="<?php echo esc_url( get_permalink( $feed_post ) ); ?>" class="read-more" rel="nofollow">
		<time class="feed-date dt-published" itemprop="datePublished" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $feed_post ) ); ?>">
			<?php echo esc_html( get_the_date( 'F j, Y', $feed_post ) ); ?>
		</time>
		<?php
		$categories = get_the_category( $feed_post->ID );
		if ( $categories ) :
			foreach ( $categories as $category ) :
				?>
				<span class="feed-tag p-category"><?php echo esc_html( $category->name ); ?></span>
				<?php
			endforeach;
		endif;
		?>
		</a></p>
	</div>
	</div>
</article>
<?php wp_reset_postdata(); ?>
