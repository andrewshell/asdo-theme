<?php
/**
 * Content page template part.
 *
 * @package asdo-blog
 */

?>
<article class="blog-post h-entry">
<header>
	<h1 class="p-name"><?php the_title(); ?></h1>
</header>
<section class="e-content">
<?php the_content(); ?>
<p class="page-meta">
<a href="<?php the_permalink(); ?>" class="u-url"><time class="small dt-published" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">Published <?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></time></a>
<?php
$created  = get_the_date( 'Y-m-d' );
$modified = get_the_modified_date( 'Y-m-d' );
if ( $created !== $modified ) :
	?>
<br><time class="small dt-updated" datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>">Updated <?php echo esc_html( get_the_modified_date( 'F j, Y' ) ); ?></time>
<?php endif; ?>
</p>
</section>
<hr>
<footer>
<?php get_template_part( 'template-parts/bio' ); ?>
</footer>
</article>
