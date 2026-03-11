<article class="blog-post h-entry" itemscope itemtype="https://schema.org/BlogPosting">
<header>
	<h1 class="p-name" itemprop="headline"><?php the_title(); ?></h1>
<?php
$enclosure_url = get_post_meta(get_the_ID(), 'enclosure_url', true);
if ($enclosure_url) : ?>
<audio controls><source src="<?php echo esc_url($enclosure_url); ?>" type="<?php echo esc_attr(get_post_meta(get_the_ID(), 'enclosure_type', true) ?: 'audio/mpeg'); ?>">Your browser does not support the audio tag.</audio>
<?php endif; ?>
</header>
<section class="e-content" itemprop="articleBody">
<?php the_content(); ?>
<?php
$canonical_href = get_post_meta(get_the_ID(), 'canonical_href', true);
if ($canonical_href) :
    $canonical_name = get_post_meta(get_the_ID(), 'canonical_name', true) ?: 'my Substack newsletter';
?>
<p><a href="<?php echo esc_url($canonical_href); ?>">This essay was originally published on <?php echo esc_html($canonical_name); ?>.</a></p>
<?php endif; ?>
<p>
<a href="<?php the_permalink(); ?>" class="u-url" rel="me" itemprop="url"><time class="small dt-published" itemprop="datePublished" datetime="<?php echo esc_attr(get_the_date('Y-m-d')); ?>">Published <?php echo esc_html(get_the_date('F j, Y')); ?></time></a>
<?php
$created = get_the_date('Y-m-d');
$modified = get_the_modified_date('Y-m-d');
if ($created !== $modified) : ?>
<br><time class="small dt-updated" itemprop="dateModified" datetime="<?php echo esc_attr($modified); ?>">Updated <?php echo esc_html(get_the_modified_date('F j, Y')); ?></time>
<?php endif; ?>
</p>
</section>
<hr>
<footer>
<?php get_template_part('template-parts/bio'); ?>
</footer>
</article>

<?php
$prev_post = get_previous_post();
$next_post = get_next_post();
if ($prev_post || $next_post) : ?>
<nav class="blog-post-nav">
<ul>
<?php if ($prev_post) : ?>
<li><a rel="prev" href="<?php echo esc_url(get_permalink($prev_post)); ?>">&larr; <?php echo esc_html($prev_post->post_title); ?></a></li>
<?php endif; ?>
<?php if ($next_post) : ?>
<li><a rel="next" href="<?php echo esc_url(get_permalink($next_post)); ?>"><?php echo esc_html($next_post->post_title); ?> &rarr;</a></li>
<?php endif; ?>
</ul>
</nav>
<?php endif; ?>
