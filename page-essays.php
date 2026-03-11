<?php
/**
 * Template Name: Essays Page
 * Slug: essays
 */
get_header();
?>

<article class="blog-post" itemscope itemtype="https://schema.org/Article">
<header>
	<h1 itemprop="headline">All Essays</h1>
</header>
<section itemprop="articleBody">

<?php
$essays = new WP_Query(array(
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'category_name'  => 'essays',
    'orderby'        => 'date',
    'order'          => 'DESC',
    'posts_per_page' => -1,
));

$last_month = '';
if ($essays->have_posts()) :
    while ($essays->have_posts()) : $essays->the_post();
        $this_month = get_the_date('F Y');
        if ($this_month !== $last_month) :
            if ($last_month !== '') echo '</ol>';
            ?>
            <h2><?php echo esc_html($this_month); ?></h2>
            <ol class="list-none">
            <?php
            $last_month = $this_month;
        endif;
        ?>
        <li><a href="<?php the_permalink(); ?>" itemprop="url">
            <span itemprop="headline"><?php the_title(); ?></span>
        </a></li>
    <?php endwhile; ?>
    </ol>
    <?php wp_reset_postdata(); ?>
<?php endif; ?>

</section>
<hr>
<footer>
<?php get_template_part('template-parts/bio'); ?>
</footer>
</article>

<?php get_footer(); ?>
