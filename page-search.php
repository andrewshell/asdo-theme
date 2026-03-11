<?php
/**
 * Template Name: Search Page
 * Slug: search
 */
get_header();
?>

<article class="blog-post" itemscope itemtype="https://schema.org/Article">
<header>
	<h1 itemprop="headline">Search</h1>
</header>
<section itemprop="articleBody">

<?php $search_query = isset($_GET['keywords']) ? sanitize_text_field($_GET['keywords']) : ''; ?>

<form role="search" autocomplete="off" method="get" action="<?php echo esc_url(home_url('/search/')); ?>" class="searchform">
  <input type="text" id="search-input" name="keywords" value="<?php echo esc_attr($search_query); ?>" />
  <button type="submit">Search</button>
</form>

<?php
if ($search_query) :
    $search = new WP_Query(array(
        's'              => $search_query,
        'post_type'      => array('post', 'page'),
        'posts_per_page' => 20,
    ));

    if ($search->have_posts()) : ?>
<ol class="list-none">
    <?php while ($search->have_posts()) : $search->the_post(); ?>
    <li>
        <article class="post-list-item">
            <section>
                <header>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <small><?php echo esc_html(get_the_date('F j, Y')); ?></small>
                </header>
                <p><?php echo esc_html(asdo_truncate(get_the_content(), 280)); ?></p>
            </section>
        </article>
    </li>
    <?php endwhile; ?>
</ol>
    <?php wp_reset_postdata(); ?>
    <?php else : ?>
<p>No results found for "<?php echo esc_html($search_query); ?>".</p>
    <?php endif; ?>
<?php endif; ?>

</section>
<hr>
<footer>
<?php get_template_part('template-parts/bio'); ?>
</footer>
</article>

<?php get_footer(); ?>
