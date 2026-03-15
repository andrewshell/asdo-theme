<?php
/**
 * Andrew Shell's Weblog - Theme Functions
 *
 * @package asdo-blog
 */

/**
 * Set up theme defaults and register support for WordPress features.
 */
function asdo_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);
}
add_action( 'after_setup_theme', 'asdo_setup' );

/**
 * Enqueue styles and scripts.
 */
function asdo_enqueue_assets() {
	wp_enqueue_style( 'asdo-normalize', get_template_directory_uri() . '/css/normalize.css', array(), '8.0.1' );
	wp_enqueue_style( 'asdo-style', get_stylesheet_uri(), array( 'asdo-normalize' ), '1.0.0' );
	wp_enqueue_style( 'asdo-prism', get_template_directory_uri() . '/css/prism-tomorrow.css', array(), '1.0.0' );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'asdo_enqueue_assets' );

/**
 * Output analytics scripts in production.
 */
function asdo_analytics() {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		return;
	}
	// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript
	?>
	<script defer src="https://cloud.umami.is/script.js" data-website-id="ad25b1ce-ffdd-4f98-8dc8-cea81b233a1a"></script>
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-157PZ293W0"></script>
	<?php
	// phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedScript
	?>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', 'G-157PZ293W0');
	</script>
	<?php
}
add_action( 'wp_head', 'asdo_analytics' );

/**
 * Strip HTML and truncate content at a word boundary.
 *
 * @param string $content The content to truncate.
 * @param int    $length  Maximum character length.
 * @return string
 */
function asdo_truncate( $content, $length = 280 ) {
	if ( empty( $content ) ) {
		return '';
	}
	$stripped = wp_strip_all_tags( $content );
	if ( mb_strlen( $stripped ) <= $length ) {
		return $stripped;
	}
	$truncated  = mb_substr( $stripped, 0, $length );
	$last_space = mb_strrpos( $truncated, ' ' );
	if ( $last_space > 0 ) {
		$truncated = mb_substr( $truncated, 0, $last_space );
	}
	return $truncated . '...';
}

/**
 * Get recent content from the essays category.
 *
 * @param int $min Minimum number of posts to return.
 * @return array
 */
function asdo_recent_content( $min = 5 ) {
	$args = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'category_name'  => 'essays',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'posts_per_page' => max( $min, 20 ),
	);

	$query = new WP_Query( $args );
	$posts = $query->posts;

	if ( empty( $posts ) ) {
		return array();
	}

	// Filter to current month.
	$now                 = new DateTime();
	$current_month_posts = array_filter(
		$posts,
		function ( $post ) use ( $now ) {
			$post_date = new DateTime( $post->post_date );
			return $post_date->format( 'Y-m' ) === $now->format( 'Y-m' );
		}
	);

	if ( count( $current_month_posts ) >= $min ) {
		return array_values( $current_month_posts );
	}

	return array_slice( $posts, 0, $min );
}

/**
 * Auto-create required categories on init.
 */
function asdo_create_categories() {
	if ( ! term_exists( 'essays', 'category' ) ) {
		wp_insert_term( 'Essays', 'category', array( 'slug' => 'essays' ) );
	}
}
add_action( 'init', 'asdo_create_categories' );

/**
 * Rewrite /rss.xml to the main RSS2 feed.
 */
function asdo_rss_rewrite() {
	add_rewrite_rule( '^rss\.xml$', 'index.php?feed=rss2', 'top' );
}
add_action( 'init', 'asdo_rss_rewrite' );

/**
 * Flush rewrite rules on theme activation so /rss.xml works immediately.
 */
function asdo_flush_rewrites() {
	asdo_rss_rewrite();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'asdo_flush_rewrites' );

/**
 * Remove WordPress default feed links from wp_head (we add our own in header.php).
 */
function asdo_remove_feed_links() {
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
}
add_action( 'after_setup_theme', 'asdo_remove_feed_links' );

/**
 * Override the feed permalink to /rss.xml.
 *
 * WordPress normalizes the default feed ('rss2') to '' before applying this filter.
 *
 * @param string $url  The feed URL.
 * @param string $feed The feed type.
 * @return string
 */
function asdo_feed_link( $url, $feed ) {
	if ( '' === $feed || 'rss2' === $feed ) {
		return home_url( '/rss.xml' );
	}
	return $url;
}
add_filter( 'feed_link', 'asdo_feed_link', 10, 2 );

/**
 * Prevent redirect_canonical from redirecting /rss.xml to /rss.xml/feed/.
 *
 * @param string $redirect_url  The canonical redirect URL.
 * @param string $requested_url The originally requested URL.
 * @return string|false
 */
function asdo_disable_rss_redirect( $redirect_url, $requested_url ) {
	if ( preg_match( '#/rss\.xml$#', $requested_url ) ) {
		return false;
	}
	return $redirect_url;
}
add_filter( 'redirect_canonical', 'asdo_disable_rss_redirect', 10, 2 );

/**
 * Shortcode: [embed_post slug="post-slug"].
 *
 * Embeds the content of a post inline.
 * Mirrors the 11ty embed shortcode used on the /now page.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function asdo_embed_post_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'slug' => '',
		),
		$atts,
		'embed_post'
	);

	if ( empty( $atts['slug'] ) ) {
		return '<!-- embed_post: no slug provided -->';
	}

	$posts = get_posts(
		array(
			'name'           => $atts['slug'],
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
		)
	);

	if ( empty( $posts ) ) {
		return '<!-- embed_post: post not found: ' . esc_html( $atts['slug'] ) . ' -->';
	}

	$post    = $posts[0];
	$content = apply_filters( 'the_content', $post->post_content );
	$output  = '';

	$output .= $content;

	return $output;
}
add_shortcode( 'embed_post', 'asdo_embed_post_shortcode' );

/**
 * Custom comment callback with microformats2 markup.
 *
 * Opens <li> but does not close it — WordPress handles closing for threaded comments.
 *
 * @param WP_Comment $comment The comment object.
 * @param array      $args    Formatting arguments.
 * @param int        $depth   Depth of the comment in the thread.
 */
function asdo_comment_callback( $comment, $args, $depth ) {
	$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
	?>
	<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( 'h-entry', $comment ); ?>>
		<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
			<footer class="comment-meta">
				<div class="comment-author vcard p-author h-card">
					<?php
					if ( 0 !== (int) $args['avatar_size'] ) {
						echo get_avatar( $comment, $args['avatar_size'] );
					}
					printf(
						'<b class="fn p-name">%s</b>',
						get_comment_author_link( $comment )
					);
					?>
				</div>

				<div class="comment-metadata">
					<a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>" class="u-url">
						<time class="dt-published" datetime="<?php comment_date( 'c' ); ?>">
							<?php
							printf(
								/* translators: 1: date, 2: time */
								esc_html__( '%1$s at %2$s', 'asdo-theme' ),
								esc_html( get_comment_date( '', $comment ) ),
								esc_html( get_comment_time() )
							);
							?>
						</time>
					</a>
					<?php edit_comment_link( esc_html__( 'Edit', 'asdo-theme' ), '<span class="edit-link">', '</span>' ); ?>
				</div>

				<?php if ( '0' === $comment->comment_approved ) : ?>
					<p class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'asdo-theme' ); ?></p>
				<?php endif; ?>
			</footer>

			<div class="comment-content e-content">
				<?php comment_text(); ?>
			</div>

			<?php
			comment_reply_link(
				array_merge(
					$args,
					array(
						'add_below' => 'div-comment',
						'depth'     => $depth,
						'max_depth' => $args['max_depth'],
						'before'    => '<div class="reply">',
						'after'     => '</div>',
					)
				)
			);
			?>
		</article>
	<?php
}

