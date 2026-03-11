<?php
/**
 * Andrew Shell's Weblog - Theme Functions
 *
 * @package asdo-blog
 */

// Theme setup
function asdo_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));
}
add_action('after_setup_theme', 'asdo_setup');

// Enqueue styles and scripts
function asdo_enqueue_assets() {
    wp_enqueue_style('asdo-normalize', get_template_directory_uri() . '/css/normalize.css', array(), '8.0.1');
    wp_enqueue_style('asdo-style', get_stylesheet_uri(), array('asdo-normalize'), '1.0.0');
    wp_enqueue_style('asdo-prism', get_template_directory_uri() . '/css/prism-tomorrow.css', array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'asdo_enqueue_assets');

// Analytics (production only)
function asdo_analytics() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        return;
    }
    ?>
    <script defer src="https://cloud.umami.is/script.js" data-website-id="ad25b1ce-ffdd-4f98-8dc8-cea81b233a1a"></script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-157PZ293W0"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-157PZ293W0');
    </script>
    <?php
}
add_action('wp_head', 'asdo_analytics');

// Helper: check if current post is in "essays" category
function asdo_is_essay() {
    return has_category('essays');
}

// Helper: check if current post is in "updates" category
function asdo_is_update() {
    return has_category('updates');
}

// Helper: strip HTML and truncate at word boundary
function asdo_truncate($content, $length = 280) {
    if (empty($content)) {
        return '';
    }
    $stripped = wp_strip_all_tags($content);
    if (mb_strlen($stripped) <= $length) {
        return $stripped;
    }
    $truncated = mb_substr($stripped, 0, $length);
    $last_space = mb_strrpos($truncated, ' ');
    if ($last_space > 0) {
        $truncated = mb_substr($truncated, 0, $last_space);
    }
    return $truncated . '...';
}

// Helper: get recent content (essays + updates)
function asdo_recent_content($min = 5) {
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'category_name'  => 'essays,updates',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'posts_per_page' => max($min, 20),
    );

    $query = new WP_Query($args);
    $posts = $query->posts;

    if (empty($posts)) {
        return array();
    }

    // Filter to current month
    $now = new DateTime();
    $current_month_posts = array_filter($posts, function ($post) use ($now) {
        $post_date = new DateTime($post->post_date);
        return $post_date->format('Y-m') === $now->format('Y-m');
    });

    if (count($current_month_posts) >= $min) {
        return array_values($current_month_posts);
    }

    return array_slice($posts, 0, $min);
}

// Auto-create required categories
function asdo_create_categories() {
    if (!term_exists('essays', 'category')) {
        wp_insert_term('Essays', 'category', array('slug' => 'essays'));
    }
    if (!term_exists('updates', 'category')) {
        wp_insert_term('Updates', 'category', array('slug' => 'updates'));
    }
}
add_action('init', 'asdo_create_categories');

// Rewrite /rss.xml to the main RSS2 feed
function asdo_rss_rewrite() {
    add_rewrite_rule('^rss\.xml$', 'index.php?feed=rss2', 'top');
}
add_action('init', 'asdo_rss_rewrite');

// Flush rewrite rules on theme activation so /rss.xml works immediately
function asdo_flush_rewrites() {
    asdo_rss_rewrite();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'asdo_flush_rewrites');

// Remove WordPress default feed links from wp_head (we add our own in header.php)
function asdo_remove_feed_links() {
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);
}
add_action('after_setup_theme', 'asdo_remove_feed_links');

// Override the feed permalink to /rss.xml
// WordPress normalizes the default feed ('rss2') to '' before applying this filter
function asdo_feed_link($url, $feed) {
    if ($feed === '' || $feed === 'rss2') {
        return home_url('/rss.xml');
    }
    return $url;
}
add_filter('feed_link', 'asdo_feed_link', 10, 2);

// Shortcode: [embed_post slug="post-slug"]
// Embeds the content of a post inline, optionally with its title as an h2.
// Mirrors the 11ty {% embed %} shortcode used on the /now page.
function asdo_embed_post_shortcode($atts) {
    $atts = shortcode_atts(array(
        'slug' => '',
    ), $atts, 'embed_post');

    if (empty($atts['slug'])) {
        return '<!-- embed_post: no slug provided -->';
    }

    $posts = get_posts(array(
        'name'           => $atts['slug'],
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
    ));

    if (empty($posts)) {
        return '<!-- embed_post: post not found: ' . esc_html($atts['slug']) . ' -->';
    }

    $post = $posts[0];
    $content = apply_filters('the_content', $post->post_content);
    $output = '';

    $output .= $content;

    return $output;
}
add_shortcode('embed_post', 'asdo_embed_post_shortcode');

// Include custom fields
require_once get_template_directory() . '/inc/custom-fields.php';
