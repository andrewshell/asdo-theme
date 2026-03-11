<?php
/**
 * Author bio template part.
 *
 * @package asdo-blog
 */

?>
<div class="bio h-card" itemscope itemtype="https://schema.org/Person">
	<div class="bio-avatar">
	<img
		class="u-photo"
		itemprop="image"
		itemscope
		itemtype="https://schema.org/ImageObject"
		src="<?php echo esc_url( get_template_directory_uri() . '/img/profile-pic-100.png' ); ?>"
		width="50"
		height="50"
		alt="Profile Picture"
	/>
	</div>

	<div>
	<p>
		Written by
		<a
		href="<?php echo esc_url( home_url( '/' ) ); ?>"
		rel="author me"
		class="p-name u-url"
		itemprop="name"
		><strong
			><span class="p-given-name" itemprop="givenName">Andrew</span>
			<span class="p-family-name" itemprop="familyName">Shell</span></strong
		></a
		>, a
		<span class="p-job-title" itemprop="jobTitle">Senior Web Engineer</span>
		from
		<span class="adr"
		><span class="p-locality">Madison</span>,
		<abbr class="p-region" title="Wisconsin">WI</abbr></span
		>.<br />
	</p>
	<ul class="hlist">
		<li><a href="/about/">About</a></li>
		<li><a href="/contact/">Contact</a></li>
		<li><a href="/now/">Now</a></li>
		<li><a href="/essays/">Essays</a></li>
		<li><a href="/notes/">Notes</a></li>
		<li><a href="https://amzn.to/2gdI0Ua">Wishlist</a></li>
		<li><a href="/search/">Search</a></li>
	</ul>
	</div>
</div>
