<?php
/**
 * Feed item template part.
 *
 * Renders one post as a microformats2 h-entry. Reused by the homepage feed
 * and every post-listing template (archives, category, search) so the markup
 * and styling stay consistent.
 *
 * Callers may set, before including this file:
 *   $feed_post           WP_Post to render. Defaults to the current global post,
 *                        so it works inside a standard loop without extra setup.
 *   $feed_heading_level  Heading level (2-6) for the title, so the document
 *                        outline never skips a level. Defaults to 2.
 *
 * @package asdo-blog
 */

if ( ! isset( $feed_post ) ) {
	$feed_post = get_post();
}

if ( ! $feed_post ) {
	return;
}

$feed_heading_level = isset( $feed_heading_level ) ? (int) $feed_heading_level : 2;
$feed_heading_level = max( 2, min( 6, $feed_heading_level ) );
$feed_heading_tag   = 'h' . $feed_heading_level;

setup_postdata( $feed_post );
?>
<article class="feed-item h-entry">
	<div class="feed-content">
	<?php if ( get_the_title( $feed_post ) ) : ?>
		<<?php echo esc_html( $feed_heading_tag ); ?> class="feed-title p-name">
		<a href="<?php echo esc_url( get_permalink( $feed_post ) ); ?>" class="u-url"><?php echo esc_html( get_the_title( $feed_post ) ); ?></a>
		</<?php echo esc_html( $feed_heading_tag ); ?>>
	<?php endif; ?>

	<div class="feed-excerpt p-summary">
		<p>
			<?php echo esc_html( asdo_truncate( $feed_post->post_content, 280 ) ); ?>
		</p>
	</div>

	<p class="feed-more">
		<a href="<?php echo esc_url( get_permalink( $feed_post ) ); ?>"
		aria-label="Continue reading: <?php echo esc_attr( get_the_title( $feed_post ) ); ?>">
			Continue reading<span aria-hidden="true"> &rarr;</span>
		</a>
	</p>

	<div class="feed-meta">
		<p>
		<time class="feed-date dt-published" datetime="<?php echo esc_attr( get_the_date( 'c', $feed_post ) ); ?>">
			<?php echo esc_html( get_the_date( 'F j, Y', $feed_post ) ); ?>
		</time>
		</p>
		<?php echo asdo_category_links( $feed_post ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	</div>
</article>
<?php wp_reset_postdata(); ?>
