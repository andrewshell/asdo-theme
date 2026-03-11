<?php get_header(); ?>

<div class="h-entry">
  <span style="display: none;" class="p-name"><?php bloginfo('name'); ?></span>
  <div class="e-content">
    <div style="display: flex; align-items: center; gap: var(--spacing-4); margin-bottom: var(--spacing-4);" class="h-card" itemscope itemtype="https://schema.org/Person">
      <div>
        <img class="circle u-photo" itemprop="image" itemscope itemtype="https://schema.org/ImageObject" src="<?php echo esc_url(get_template_directory_uri() . '/img/profile-pic.png'); ?>" alt="Photo of Andrew Shell" style="width: 500px">
      </div>
      <div>
        <p>Hi!, I'm <a class="p-name u-url u-uid" rel="me" href="<?php echo esc_url(home_url('/')); ?>" itemprop="name"><span class="p-given-name" itemprop="givenName">Andrew</span> <span class="p-family-name" itemprop="familyName">Shell</span></a> a <span class="p-job-title" itemprop="jobTitle">Senior Web Engineer</span> from <span class="adr"><span class="p-locality">Madison</span>, <abbr class="p-region" title="Wisconsin">WI</abbr></span>.</p>
        <p style="margin: 0;" class="p-note">
            I'm a <a href="<?php echo esc_url(home_url('/essays/a-brief-history-of-me-programming/')); ?>">software developer</a>,
            <a href="<?php echo esc_url(home_url('/ship-30-for-30-october-2021-cohort/')); ?>">writer</a>, and
            <a href="<?php echo esc_url(home_url('/essays/teaching-is-an-unfair-advantage/')); ?>">community builder</a> passionate about
            <a href="<?php echo esc_url(home_url('/notes/rsscloud-server/')); ?>">open-source technology</a> and
            <a href="https://feeds.fedwikiriver.com/">collaboration</a>.
        </p>
      </div>
    </div>

    <p>Welcome to my weblog! Here you'll find my <a href="<?php echo esc_url(home_url('/essays/')); ?>">essays</a> and quick updates. Check out my <a href="<?php echo esc_url(home_url('/notes/')); ?>">notes</a> for technical documentation. You can also learn more <a href="<?php echo esc_url(home_url('/about/')); ?>">about me</a> and what I'm up to <a href="<?php echo esc_url(home_url('/now/')); ?>">now</a>.</p>

    <p>Subscribe via <a href="<?php echo esc_url(home_url('/rss.xml')); ?>">RSS</a>.</p>

    <h2>Recent Posts</h2>

    <?php
    $recent_posts = asdo_recent_content(5);
    if (!empty($recent_posts)) : ?>
    <div class="feed">
      <?php
      $count = count($recent_posts);
      foreach ($recent_posts as $i => $feed_post) :
          include get_template_directory() . '/template-parts/feeditem.php';
          if ($i < $count - 1) : ?>
            <hr class="feed-separator">
          <?php endif;
      endforeach;
      ?>
    </div>
    <?php endif; ?>

    <p><a href="<?php echo esc_url(home_url('/essays/')); ?>">See all essays &rarr;</a> | <a href="<?php echo esc_url(home_url('/notes/')); ?>">See all notes &rarr;</a> | <a href="<?php echo esc_url(home_url('/search/')); ?>">Search &rarr;</a></p>
  </div>
</div>

<?php get_footer(); ?>
