<?php
/**
 * Page template.
 *
 * @package asdo-blog
 */

get_header();
?>

<?php
while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content', 'page' );
endwhile;
?>

<?php
get_footer();
