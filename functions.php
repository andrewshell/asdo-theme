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
 * Comment types rendered as facepiles rather than in the comment list.
 *
 * The Webmention plugin's walker strips every type in this list out of the
 * comment template query (see Webmention\Comment_Walker::comment_query), so
 * whatever is listed here must be rendered by asdo_display_reactions() or it
 * will not appear on the post at all. asdo_reaction_comment_count() keeps the
 * "N comments" heading in sync with the same list.
 *
 * This deliberately mirrors the plugin's list verbatim, including the literal
 * 'webmention' entry it appends for backcompat. Dropping that entry would stop
 * legacy rows being fetched and stop them being subtracted from the count,
 * while the walker would still hide them — counted but invisible, the exact
 * bug this pair of functions exists to fix. Collapse it at display time
 * instead; see asdo_canonical_reaction_type().
 *
 * @return string[] Comment type slugs, in display order.
 */
function asdo_reaction_comment_types() {
	if ( ! function_exists( 'get_webmention_comment_type_names' ) ) {
		return array( 'like', 'repost' );
	}

	$types = get_webmention_comment_type_names();

	// Surface likes and reposts first; keep the plugin's order for the rest.
	$preferred = array_values( array_intersect( array( 'like', 'repost' ), $types ) );

	return array_values( array_unique( array_merge( $preferred, $types ) ) );
}

/**
 * Canonical grouping key for a reaction comment type.
 *
 * The plugin's get_webmention_comment_type_names() appends a literal
 * 'webmention' for rows written by older versions, but it is not a registered
 * type — so get_webmention_comment_type_attr() falls through to the 'mention'
 * definition for it (see Webmention\Comment::get_comment_type_attr). Grouping
 * on the raw comment_type would therefore emit two facepiles with the same
 * "Mentions" heading and the same p-mention class whenever both spellings are
 * present. Folding the alias into 'mention' just matches what the plugin
 * already does with the attributes.
 *
 * @param string $type Comment type slug.
 * @return string Slug to group under.
 */
function asdo_canonical_reaction_type( $type ) {
	return 'webmention' === $type ? 'mention' : $type;
}

/**
 * Plural label and microformats class for a reaction comment type.
 *
 * @param string $type Comment type slug.
 * @return array{label: string, class: string, icon: string}
 */
function asdo_reaction_type_attrs( $type ) {
	if ( function_exists( 'get_webmention_comment_type_attr' ) ) {
		return array(
			'label' => get_webmention_comment_type_attr( $type, 'label' ),
			'class' => get_webmention_comment_type_attr( $type, 'class' ),
			'icon'  => get_webmention_comment_type_attr( $type, 'icon' ),
		);
	}

	return array(
		'label' => ucfirst( $type ) . 's',
		'class' => 'p-' . $type,
		'icon'  => '',
	);
}

/**
 * Display webmention/ActivityPub reactions as facepiles.
 *
 * Covers every type in asdo_reaction_comment_types() — not just likes and
 * reposts — so that mentions, bookmarks and the rest are not silently dropped.
 */
function asdo_display_reactions() {
	$types = asdo_reaction_comment_types();

	// An explicit type__in bypasses the Webmention walker's exclusion filter.
	$reactions = get_comments(
		array(
			'post_id'  => get_the_ID(),
			'status'   => 'approve',
			'type__in' => $types,
			'number'   => 200,
		)
	);

	if ( empty( $reactions ) ) {
		return;
	}

	// Aliases collapse onto their canonical slug; the first spelling in $types
	// fixes the group's position, and array_fill_keys folds the duplicates.
	$grouped = array_fill_keys( array_map( 'asdo_canonical_reaction_type', $types ), array() );

	foreach ( $reactions as $reaction ) {
		$type = asdo_canonical_reaction_type( $reaction->comment_type );

		if ( isset( $grouped[ $type ] ) ) {
			$grouped[ $type ][] = $reaction;
		}
	}

	echo '<div class="reactions-section">';

	foreach ( $grouped as $type => $comments ) {
		if ( empty( $comments ) ) {
			continue;
		}

		$attrs = asdo_reaction_type_attrs( $type );

		printf( '<div class="reaction-group %s">', esc_attr( $attrs['class'] ) );
		printf(
			'<h2 class="reaction-title">%s</h2>',
			esc_html(
				sprintf(
					/* translators: 1: reaction type label, e.g. "Likes", 2: number of reactions */
					__( '%1$s (%2$d)', 'asdo-theme' ),
					$attrs['label'],
					count( $comments )
				)
			)
		);
		echo '<div class="facepile">';

		foreach ( $comments as $comment ) {
			asdo_render_reaction( $comment, $attrs['icon'] );
		}

		echo '</div></div>';
	}

	echo '</div>';
}

/**
 * Render a single facepile entry.
 *
 * @param WP_Comment $comment The reaction.
 * @param string     $icon    Emoji fallback for reactions with no avatar.
 */
function asdo_render_reaction( $comment, $icon = '' ) {
	$author = $comment->comment_author;
	$url    = $comment->comment_author_url;

	// Mentions and bookmarks often carry no author URL; link to the source post.
	if ( ! $url && function_exists( 'get_url_from_webmention' ) ) {
		$url = get_url_from_webmention( $comment );
	}

	$avatar = get_option( 'show_avatars' ) ? get_avatar( $comment, 32 ) : '';
	$face   = $avatar ? wp_kses_post( $avatar ) : sprintf(
		'<span class="reaction-icon" aria-hidden="true">%s</span>',
		esc_html( $icon ? $icon : '🔗' )
	);

	// Without an avatar the name is the only thing identifying the reaction.
	if ( ! $avatar ) {
		$face .= sprintf( '<span class="reaction-name">%s</span>', esc_html( $author ) );
	}

	if ( $url ) {
		printf(
			'<a href="%s" title="%s" class="u-url">%s</a>',
			esc_url( $url ),
			esc_attr( $author ),
			$face // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		);
	} else {
		printf(
			'<span title="%s">%s</span>',
			esc_attr( $author ),
			$face // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		);
	}
}

/**
 * Keep the stored comment count in sync with what the comment list renders.
 *
 * The ActivityPub and Atmosphere plugins each filter this hook at priority 5 to
 * drop likes and reposts, but the Webmention walker hides *every* webmention
 * type from the comment template query — so mentions and bookmarks were counted
 * in the "N comments" heading while never being listed. Running at priority 1
 * settles the count before either plugin's partial exclusion sees a null, using
 * exactly the set that asdo_display_reactions() takes over.
 *
 * Returning null leaves the count to core / the other plugins.
 *
 * @param int|null $new_count The count so far, or null if undetermined.
 * @param int      $old_count The previous count.
 * @param int      $post_id   The post ID.
 * @return int|null
 */
function asdo_reaction_comment_count( $new_count, $old_count, $post_id ) {
	if ( null !== $new_count || ! function_exists( 'get_webmention_comment_type_names' ) ) {
		return $new_count;
	}

	$excluded = asdo_reaction_comment_types();

	if ( empty( $excluded ) ) {
		return $new_count;
	}

	global $wpdb;

	$placeholders = implode( ', ', array_fill( 0, count( $excluded ), '%s' ) );

	// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->comments}
			 WHERE comment_post_ID = %d AND comment_approved = '1'
			 AND comment_type NOT IN ( {$placeholders} )",
			array_merge( array( $post_id ), $excluded )
		)
	);
	// phpcs:enable
}
add_filter( 'pre_wp_update_comment_count_now', 'asdo_reaction_comment_count', 1, 3 );

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

/**
 * URL of the IndieNews syndication target.
 */
const ASDO_INDIENEWS_URL = 'https://news.indieweb.org/en';

/**
 * Whether a post has the "indienews" tag.
 *
 * @param int|WP_Post|null $post Optional. Post to check. Defaults to the current post.
 * @return bool True if the post has the indienews tag.
 */
function asdo_is_indienews( $post = null ) {
	return has_tag( 'indienews', $post );
}

/**
 * Output the IndieNews u-category link for posts tagged indienews.
 */
function asdo_indienews_link() {
	if ( ! asdo_is_indienews() ) {
		return;
	}
	?>
<a href="<?php echo esc_url( ASDO_INDIENEWS_URL ); ?>" class="u-category small">#indienews</a><br>
	<?php
}

/**
 * Send a Webmention to IndieNews when the post is tagged indienews.
 *
 * @param string[] $urls    URLs extracted from the post content.
 * @param int      $post_id The post being sent.
 * @return string[] The filtered list of Webmention targets.
 */
function asdo_indienews_webmention_link( $urls, $post_id ) {
	if ( asdo_is_indienews( $post_id ) ) {
		$urls[] = ASDO_INDIENEWS_URL;
	}
	return $urls;
}
add_filter( 'webmention_links', 'asdo_indienews_webmention_link', 10, 2 );

/**
 * Post-publish cache warming.
 *
 * Relays forward an Announce carrying only the post URL, so every subscribing
 * instance dereferences the permalink to fetch an authentic copy. Those fetches
 * arrive together within a couple of minutes of publishing — and publishing has
 * just purged the cache, so without warming they all regenerate through PHP at
 * once and exhaust the LSAPI worker pool.
 *
 * LiteSpeed keys entries on the exact Accept header (the permalink sends
 * `Vary: Accept` to content-negotiate HTML against ActivityPub JSON), so every
 * distinct string federating software sends is a separate entry and has to be
 * warmed on its own. User-Agent does not affect the key.
 */

/** Cron hook fired to warm a published post. */
const ASDO_WARM_HOOK = 'asdo_warm_cache';

/**
 * Seconds to wait after publish before warming.
 *
 * Must stay below the offset the ActivityPub plugin uses to schedule
 * `activitypub_process_outbox` (`time() + 3`, Scheduler::schedule_outbox_activity_for_federation).
 * WP-Cron runs due events in timestamp order, so a smaller offset makes warming
 * run before the activity is delivered — on the same tick, whenever that tick
 * lands. That removes any dependence on cron granularity or on how quickly the
 * relay fans out.
 */
const ASDO_WARM_DELAY = 1;

/** Accept headers to warm the permalink under — one cache entry each. */
const ASDO_WARM_ACCEPTS = array(
	'text/html',
	'application/activity+json, application/ld+json; profile="https://www.w3.org/ns/activitystreams"',
	'application/activity+json',
	'application/ld+json; profile="https://www.w3.org/ns/activitystreams"',
);

/**
 * Queue a warm-up after a post is published or updated.
 *
 * Deferred to cron rather than run inline: LiteSpeed applies its purge as the
 * publish response is sent, so warming during that same request would populate
 * entries the purge then discards. Running under cron also keeps the loopback
 * requests off the web worker pool.
 *
 * Ordering, not punctuality, is what makes this reliable — see ASDO_WARM_DELAY.
 * Warming runs ahead of federation on the same cron tick, so the cache is warm
 * before the activity is delivered no matter when that tick fires.
 *
 * @param string  $new_status New post status.
 * @param string  $old_status Previous post status.
 * @param WP_Post $post       Post being transitioned.
 */
function asdo_schedule_cache_warm( $new_status, $old_status, $post ) {
	if ( 'publish' !== $new_status ) {
		return;
	}

	if ( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) {
		return;
	}

	if ( ! is_post_type_viewable( $post->post_type ) ) {
		return;
	}

	$args = array( (int) $post->ID );

	if ( wp_next_scheduled( ASDO_WARM_HOOK, $args ) ) {
		return;
	}

	wp_schedule_single_event( time() + ASDO_WARM_DELAY, ASDO_WARM_HOOK, $args );
}
add_action( 'transition_post_status', 'asdo_schedule_cache_warm', 10, 3 );

/**
 * Build the list of URLs to warm, as `array( $url, $accept )` pairs.
 *
 * @param int $post_id Published post ID.
 * @return array[] List of [ url, accept ] pairs.
 */
function asdo_warm_targets( $post_id ) {
	$targets   = array();
	$permalink = get_permalink( $post_id );

	if ( $permalink ) {
		foreach ( ASDO_WARM_ACCEPTS as $accept ) {
			$targets[] = array( $permalink, $accept );
		}
	}

	// Publishing purges these too, and they carry the human traffic.
	$targets[] = array( home_url( '/' ), 'text/html' );
	$targets[] = array( get_feed_link(), 'application/rss+xml' );

	/**
	 * Filters the URLs warmed after publishing.
	 *
	 * @param array[] $targets List of [ url, accept ] pairs.
	 * @param int     $post_id Published post ID.
	 */
	return apply_filters( 'asdo_warm_targets', $targets, $post_id );
}

/**
 * Request each target so LiteSpeed stores a fresh copy before the fan-out lands.
 *
 * @param int $post_id Published post ID.
 */
function asdo_run_cache_warm( $post_id ) {
	foreach ( asdo_warm_targets( (int) $post_id ) as $target ) {
		list( $url, $accept ) = $target;

		if ( empty( $url ) ) {
			continue;
		}

		$response = wp_remote_get(
			$url,
			array(
				// Bounded deliberately: warming runs before federation on the
				// same tick, so a slow target delays delivery by this much.
				// Keep (number of targets x timeout) well under
				// WP_CRON_LOCK_TIMEOUT (60s). If warming overruns the lock,
				// another process steals it and wp-cron.php returns right
				// after this hook — before activitypub_process_outbox — which
				// is exactly the ordering ASDO_WARM_DELAY exists to guarantee.
				'timeout'     => 5,
				'redirection' => 2,
				'user-agent'  => 'asdo-cache-warmer (+' . home_url( '/' ) . ')',
				'headers'     => array( 'Accept' => $accept ),
			)
		);

		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			continue;
		}

		$result = is_wp_error( $response )
			? $response->get_error_message()
			: wp_remote_retrieve_response_code( $response ) . ' ' . wp_remote_retrieve_header( $response, 'x-litespeed-cache' );

		error_log( sprintf( '[asdo-warm] %s [%s] -> %s', $url, $accept, $result ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}
add_action( ASDO_WARM_HOOK, 'asdo_run_cache_warm' );

