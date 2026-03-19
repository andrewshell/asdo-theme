<?php
/**
 * Content page template part.
 *
 * @package asdo-blog
 */

?>
<article class="blog-post" itemscope itemtype="https://schema.org/Article">
<header>
	<h1 itemprop="headline"><?php the_title(); ?></h1>
</header>
<section itemprop="articleBody">
<?php the_content(); ?>
<p class="page-meta">
<time class="small" itemprop="datePublished" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">Published <?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></time>
<?php
$created  = get_the_date( 'Y-m-d' );
$modified = get_the_modified_date( 'Y-m-d' );
if ( $created !== $modified ) :
	?>
<br><time class="small" itemprop="dateModified" datetime="<?php echo esc_attr( $modified ); ?>">Updated <?php echo esc_html( get_the_modified_date( 'c' ) ); ?></time>
<?php endif; ?>
</p>
</section>
<hr>
<footer>
<?php get_template_part( 'template-parts/bio' ); ?>
</footer>
</article>
