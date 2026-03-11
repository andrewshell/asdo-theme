<article class="h-entry" itemscope itemtype="https://schema.org/SocialMediaPosting">
<section itemprop="articleBody">
<?php the_content(); ?>
<p>
<time class="small dt-published" itemprop="datePublished" datetime="<?php echo esc_attr(get_the_date('Y-m-d')); ?>">Published <?php echo esc_html(get_the_date('F j, Y')); ?></time>
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
