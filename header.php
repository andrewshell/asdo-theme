<?php
/**
 * Header template.
 *
 * @package asdo-blog
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<?php
	$canonical_href = get_post_meta( get_the_ID(), 'canonical_href', true );
	if ( $canonical_href ) :
		?>
	<link rel="canonical" href="<?php echo esc_url( $canonical_href ); ?>" />
	<?php endif; ?>

	<link rel="alternate" href="<?php echo esc_url( get_bloginfo( 'rss2_url' ) ); ?>" type="application/rss+xml" title="<?php bloginfo( 'name' ); ?>">

	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">

	<link rel="webmention" href="https://webmention.io/blog.andrewshell.org/webmention" />
	<link rel="alternate" type="application/activity+json" href="https://fed.brid.gy/r/<?php echo rawurlencode( get_permalink() ); ?>">

	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div class="global-wrapper" 
<?php
if ( is_front_page() ) {
	echo 'data-is-root-path="true"';}
?>
itemscope itemtype="<?php echo esc_attr( is_single() ? 'https://schema.org/BlogPosting' : 'https://schema.org/WebPage' ); ?>">
<header class="global-header">
<?php if ( is_front_page() ) : ?>
<h1 class="main-heading">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
</h1>
<p><?php bloginfo( 'description' ); ?></p>
<?php else : ?>
<a class="header-link-home" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
<?php endif; ?>
</header>
<main>
