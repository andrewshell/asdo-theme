<?php get_header(); ?>

<?php
while (have_posts()) :
    the_post();
    if (asdo_is_update()) {
        get_template_part('template-parts/content', 'update');
    } else {
        get_template_part('template-parts/content', 'essay');
    }
endwhile;
?>

<?php get_footer(); ?>
