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
 * Get recent published posts.
 *
 * @param int $min Minimum number of posts to return.
 * @return array
 */
function asdo_recent_content( $min = 5 ) {
	$args = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
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
 * Render a post's categories as a list of links.
 *
 * Uses the microformats2 `p-category` class so the links participate in
 * the same IndieWeb markup as the rest of the theme. Output is fully
 * escaped internally.
 *
 * @param int|WP_Post|null $post Optional. Post or post ID. Defaults to the current post.
 * @return string HTML, or an empty string when the post has no categories.
 */
function asdo_category_links( $post = null ) {
	if ( is_object( $post ) ) {
		$post_id = (int) $post->ID;
	} elseif ( $post ) {
		$post_id = (int) $post;
	} else {
		$post_id = get_the_ID();
	}

	$categories = get_the_category( $post_id );
	if ( empty( $categories ) ) {
		return '';
	}

	$links = array();
	foreach ( $categories as $category ) {
		$links[] = sprintf(
			'<a href="%s" class="p-category" rel="category">%s</a>',
			esc_url( get_category_link( $category ) ),
			esc_html( $category->name )
		);
	}

	return '<p class="post-categories">' . implode( ' ', $links ) . '</p>';
}

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
 * Resolve the canonical author for the current context.
 *
 * Uses the content's author on singular views and the queried user on
 * author archives; otherwise falls back to the first publishing user so
 * the site-wide identity is deterministic and not hardcoded.
 *
 * @return int Author user ID.
 */
function asdo_site_author_id() {
	if ( is_singular() ) {
		$author = (int) get_post_field( 'post_author', get_queried_object_id() );
		if ( $author > 0 ) {
			return $author;
		}
	}

	if ( is_author() ) {
		$obj = get_queried_object();
		if ( $obj instanceof WP_User ) {
			return (int) $obj->ID;
		}
	}

	static $primary = null;
	if ( null === $primary ) {
		$users   = get_users(
			array(
				'capability' => array( 'publish_posts' ),
				'number'     => 1,
				'orderby'    => 'ID',
				'order'      => 'ASC',
				'fields'     => 'ID',
			)
		);
		$primary = ! empty( $users ) ? (int) $users[0] : 1;
	}
	return $primary;
}

/**
 * Build a normalized person record from a user's WordPress profile.
 *
 * Single source of truth shared by the JSON-LD graph and the bio h-card.
 * Social URLs come from custom contact methods (see
 * asdo_user_contactmethods()); job title and location come from custom
 * profile fields (see asdo_user_profile_fields()).
 *
 * @param int $author_id Author user ID.
 * @return array Normalized person fields.
 */
function asdo_person_data( $author_id ) {
	$author_id  = (int) $author_id;
	$first      = (string) get_the_author_meta( 'first_name', $author_id );
	$last       = (string) get_the_author_meta( 'last_name', $author_id );
	$name       = (string) get_the_author_meta( 'display_name', $author_id );
	$author_url = get_author_posts_url( $author_id );
	$website    = (string) get_the_author_meta( 'user_url', $author_id );

	if ( '' === trim( $name ) ) {
		$name = trim( $first . ' ' . $last );
	}

	$same_as = array();
	foreach ( array( 'mastodon', 'github', 'x', 'gravatar' ) as $key ) {
		$value = (string) get_the_author_meta( $key, $author_id );
		if ( '' !== trim( $value ) ) {
			$same_as[] = $value;
		}
	}

	// The WordPress default author URL is also the ActivityPub plugin's
	// stable actor id; asserting it via sameAs bridges the schema.org
	// Person and the fediverse actor across the two systems.
	$same_as[] = home_url( '/?author=' . $author_id );

	return array(
		'id'         => $author_id,
		'url'        => $author_url,
		'website'    => $website,
		'name'       => $name,
		'first_name' => $first,
		'last_name'  => $last,
		'job_title'  => (string) get_the_author_meta( 'asdo_job_title', $author_id ),
		'locality'   => (string) get_the_author_meta( 'asdo_locality', $author_id ),
		'region'     => (string) get_the_author_meta( 'asdo_region', $author_id ),
		'avatar'     => get_avatar_url( $author_id, array( 'size' => 100 ) ),
		'bio'        => (string) get_the_author_meta( 'description', $author_id ),
		'same_as'    => $same_as,
	);
}

/**
 * Output a schema.org JSON-LD graph.
 *
 * Centralized structured data. Replaces inline Microdata; microformats2
 * markup in the templates is retained separately for IndieWeb. The Person
 * identifier is anchored to the WordPress author URL, which is also the
 * ActivityPub actor URL, consolidating the schema.org and fediverse
 * identities onto one canonical URL.
 */
function asdo_jsonld() {
	$home       = home_url( '/' );
	$site_id    = $home . '#website';
	$author_id  = asdo_site_author_id();
	$p          = asdo_person_data( $author_id );
	$author_url = $p['url'];
	$person_id  = $author_url . '#person';

	$person = array(
		'@type' => 'Person',
		'@id'   => $person_id,
		'name'  => $p['name'],
		'url'   => '' !== trim( $p['website'] ) ? $p['website'] : $author_url,
	);
	if ( '' !== trim( $p['first_name'] ) ) {
		$person['givenName'] = $p['first_name'];
	}
	if ( '' !== trim( $p['last_name'] ) ) {
		$person['familyName'] = $p['last_name'];
	}
	if ( '' !== trim( $p['job_title'] ) ) {
		$person['jobTitle'] = $p['job_title'];
	}
	if ( '' !== trim( $p['locality'] ) || '' !== trim( $p['region'] ) ) {
		$address = array( '@type' => 'PostalAddress' );
		if ( '' !== trim( $p['locality'] ) ) {
			$address['addressLocality'] = $p['locality'];
		}
		if ( '' !== trim( $p['region'] ) ) {
			$address['addressRegion'] = $p['region'];
		}
		$person['address'] = $address;
	}
	if ( ! empty( $p['avatar'] ) ) {
		$person['image'] = $p['avatar'];
	}
	if ( '' !== trim( $p['bio'] ) ) {
		$person['description'] = $p['bio'];
	}
	if ( ! empty( $p['same_as'] ) ) {
		$person['sameAs'] = $p['same_as'];
	}

	$website = array(
		'@type'           => 'WebSite',
		'@id'             => $site_id,
		'name'            => get_bloginfo( 'name' ),
		'description'     => get_bloginfo( 'description' ),
		'url'             => $home,
		'publisher'       => array( '@id' => $person_id ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => $home . '?s={search_term_string}',
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	$graph = array( $website, $person );

	if ( is_author() ) {
		$graph[] = array(
			'@type'      => 'ProfilePage',
			'@id'        => $author_url . '#profilepage',
			'url'        => $author_url,
			'name'       => $p['name'],
			'mainEntity' => array( '@id' => $person_id ),
		);
	} elseif ( is_singular( array( 'post', 'page' ) ) ) {
		$post = get_queried_object();
		$type = is_singular( 'post' ) ? 'BlogPosting' : 'Article';

		$entry = array(
			'@type'            => $type,
			'headline'         => get_the_title(),
			'url'              => get_permalink(),
			'mainEntityOfPage' => get_permalink(),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'author'           => array( '@id' => $person_id ),
			'publisher'        => array( '@id' => $person_id ),
		);

		if ( $post && ! empty( $post->post_content ) ) {
			$entry['description'] = asdo_truncate( $post->post_content, 160 );
		}

		if ( has_post_thumbnail() ) {
			$entry['image'] = get_the_post_thumbnail_url( null, 'large' );
		}

		$graph[] = $entry;
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);
	?>
	<script type="application/ld+json"><?php echo wp_json_encode( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
	<?php
}
add_action( 'wp_head', 'asdo_jsonld' );

/**
 * Add social profile URLs to the user contact methods.
 *
 * These appear on the wp-admin user profile screen and feed the Person
 * `sameAs` array.
 *
 * @param array $methods Existing contact methods.
 * @return array
 */
function asdo_user_contactmethods( $methods ) {
	$methods['mastodon'] = __( 'Mastodon URL', 'asdo-theme' );
	$methods['github']   = __( 'GitHub URL', 'asdo-theme' );
	$methods['x']        = __( 'X (Twitter) URL', 'asdo-theme' );
	$methods['gravatar'] = __( 'Gravatar URL', 'asdo-theme' );
	return $methods;
}
add_filter( 'user_contactmethods', 'asdo_user_contactmethods' );

/**
 * Render custom identity fields on the user profile screen.
 *
 * @param WP_User $user The user being edited.
 */
function asdo_user_profile_fields( $user ) {
	wp_nonce_field( 'asdo_user_profile', 'asdo_user_profile_nonce' );
	?>
	<h2><?php esc_html_e( 'Profile Details', 'asdo-theme' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="asdo_job_title"><?php esc_html_e( 'Job Title', 'asdo-theme' ); ?></label></th>
			<td><input type="text" name="asdo_job_title" id="asdo_job_title" class="regular-text" value="<?php echo esc_attr( get_the_author_meta( 'asdo_job_title', $user->ID ) ); ?>" /></td>
		</tr>
		<tr>
			<th><label for="asdo_locality"><?php esc_html_e( 'City / Locality', 'asdo-theme' ); ?></label></th>
			<td><input type="text" name="asdo_locality" id="asdo_locality" class="regular-text" value="<?php echo esc_attr( get_the_author_meta( 'asdo_locality', $user->ID ) ); ?>" /></td>
		</tr>
		<tr>
			<th><label for="asdo_region"><?php esc_html_e( 'State / Region', 'asdo-theme' ); ?></label></th>
			<td><input type="text" name="asdo_region" id="asdo_region" class="regular-text" value="<?php echo esc_attr( get_the_author_meta( 'asdo_region', $user->ID ) ); ?>" /></td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'asdo_user_profile_fields' );
add_action( 'edit_user_profile', 'asdo_user_profile_fields' );

/**
 * Persist the custom identity fields from the user profile screen.
 *
 * @param int $user_id The user ID being saved.
 */
function asdo_save_user_profile_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	if ( ! isset( $_POST['asdo_user_profile_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['asdo_user_profile_nonce'] ) ), 'asdo_user_profile' ) ) {
		return;
	}
	foreach ( array( 'asdo_job_title', 'asdo_locality', 'asdo_region' ) as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_user_meta( $user_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
}
add_action( 'personal_options_update', 'asdo_save_user_profile_fields' );
add_action( 'edit_user_profile_update', 'asdo_save_user_profile_fields' );

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

