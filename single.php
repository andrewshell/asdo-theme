<?php
/**
 * Single post template.
 *
 * @package asdo-blog
 */

get_header();
?>

<?php
while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content', 'essay' );
endwhile;

if ( comments_open() || get_comments_number() ) {
	comments_template();
}
?>

<?php
get_footer();
