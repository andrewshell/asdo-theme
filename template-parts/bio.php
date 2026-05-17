<?php
/**
 * Author bio template part.
 *
 * Sources identity from the resolved site author's WordPress profile via
 * asdo_person_data(), the same record that feeds the JSON-LD Person, so
 * the visible h-card and the structured data cannot drift.
 *
 * @package asdo-blog
 */

$asdo_person = asdo_person_data( asdo_site_author_id() );
$asdo_url    = '' !== trim( $asdo_person['website'] ) ? $asdo_person['website'] : $asdo_person['url'];
?>
<div class="bio h-card">
	<div class="bio-avatar">
	<img
		class="u-photo"
		src="<?php echo esc_url( $asdo_person['avatar'] ); ?>"
		width="50"
		height="50"
		alt="<?php echo esc_attr( $asdo_person['name'] ); ?>"
	/>
	</div>

	<div>
	<p>
		Written by
		<a
		href="<?php echo esc_url( $asdo_url ); ?>"
		rel="author me"
		class="p-name u-url"
		><strong>
		<?php if ( '' !== trim( $asdo_person['first_name'] ) || '' !== trim( $asdo_person['last_name'] ) ) : ?>
			<span class="p-given-name"><?php echo esc_html( $asdo_person['first_name'] ); ?></span>
			<span class="p-family-name"><?php echo esc_html( $asdo_person['last_name'] ); ?></span>
		<?php else : ?>
			<?php echo esc_html( $asdo_person['name'] ); ?>
		<?php endif; ?>
		</strong></a>
		<?php if ( '' !== trim( $asdo_person['job_title'] ) ) : ?>
			, a <span class="p-job-title"><?php echo esc_html( $asdo_person['job_title'] ); ?></span>
		<?php endif; ?>
		<?php if ( '' !== trim( $asdo_person['locality'] ) || '' !== trim( $asdo_person['region'] ) ) : ?>
			from
			<span class="adr">
				<?php if ( '' !== trim( $asdo_person['locality'] ) ) : ?>
					<span class="p-locality"><?php echo esc_html( $asdo_person['locality'] ); ?></span><?php echo ( '' !== trim( $asdo_person['region'] ) ) ? ',' : ''; ?>
				<?php endif; ?>
				<?php if ( '' !== trim( $asdo_person['region'] ) ) : ?>
					<span class="p-region"><?php echo esc_html( $asdo_person['region'] ); ?></span>
				<?php endif; ?>
			</span>
		<?php endif; ?>
		.<br />
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
