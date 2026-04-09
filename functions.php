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
 * Output Open Graph, Twitter Card, and meta description tags.
 */
function asdo_meta_tags() {
	$title       = esc_attr( wp_get_document_title() );
	$url         = esc_url( get_permalink() ? get_permalink() : home_url( '/' ) );
	$site_name   = esc_attr( get_bloginfo( 'name' ) );
	$description = get_bloginfo( 'description' );
	$type        = 'website';
	$image       = esc_url( get_theme_file_uri( 'img/apple-touch-icon.png' ) );

	if ( is_singular() ) {
		$type = 'article';
		$post = get_queried_object();
		if ( $post && ! empty( $post->post_content ) ) {
			$description = asdo_truncate( $post->post_content, 160 );
		}
		if ( has_post_thumbnail() ) {
			$image = esc_url( get_the_post_thumbnail_url( null, 'large' ) );
		}
	}

	$description = esc_attr( $description );
	?>
	<meta name="description" content="<?php echo esc_attr( $description ); ?>">
	<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
	<meta property="og:url" content="<?php echo esc_url( $url ); ?>">
	<meta property="og:type" content="<?php echo esc_attr( $type ); ?>">
	<meta property="og:site_name" content="<?php echo esc_attr( $site_name ); ?>">
	<meta property="og:image" content="<?php echo esc_url( $image ); ?>">
	<meta name="twitter:card" content="summary">
	<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
	<meta name="twitter:description" content="<?php echo esc_attr( $description ); ?>">
	<meta name="twitter:image" content="<?php echo esc_url( $image ); ?>">
	<?php
}
add_action( 'wp_head', 'asdo_meta_tags' );

/**
 * Display ActivityPub likes and reposts as facepiles.
 */
function asdo_display_reactions() {
	$post_id   = get_the_ID();
	$reactions = get_comments(
		array(
			'post_id'  => $post_id,
			'status'   => 'approve',
			'type__in' => array( 'like', 'repost' ),
			'number'   => 200,
		)
	);

	if ( empty( $reactions ) ) {
		return;
	}

	$grouped = array(
		'like'   => array(),
		'repost' => array(),
	);

	foreach ( $reactions as $reaction ) {
		if ( isset( $grouped[ $reaction->comment_type ] ) ) {
			$grouped[ $reaction->comment_type ][] = $reaction;
		}
	}

	$labels = array(
		'like'   => array(
			/* translators: %d: number of likes */
			'label' => __( 'Likes (%d)', 'asdo-theme' ),
			'class' => 'p-like',
		),
		'repost' => array(
			/* translators: %d: number of reposts */
			'label' => __( 'Reposts (%d)', 'asdo-theme' ),
			'class' => 'p-repost',
		),
	);

	echo '<div class="reactions-section">';

	foreach ( $grouped as $type => $comments ) {
		if ( empty( $comments ) ) {
			continue;
		}

		$count = count( $comments );
		$label = sprintf( $labels[ $type ]['label'], $count );
		$class = $labels[ $type ]['class'];

		printf( '<div class="reaction-group %s">', esc_attr( $class ) );
		printf( '<h2 class="reaction-title">%s</h2>', esc_html( $label ) );
		echo '<div class="facepile">';

		foreach ( $comments as $comment ) {
			$author_url = $comment->comment_author_url;
			$author     = $comment->comment_author;
			$avatar     = get_avatar( $comment, 32 );

			if ( $author_url ) {
				printf(
					'<a href="%s" title="%s" class="u-url">%s</a>',
					esc_url( $author_url ),
					esc_attr( $author ),
					wp_kses_post( $avatar )
				);
			} else {
				printf(
					'<span title="%s">%s</span>',
					esc_attr( $author ),
					wp_kses_post( $avatar )
				);
			}
		}

		echo '</div></div>';
	}

	echo '</div>';
}

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
					<p class="comment-awaiting-moderation" role="status"><?php esc_html_e( 'Your comment is awaiting moderation.', 'asdo-theme' ); ?></p>
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

