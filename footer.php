<?php
/**
 * Footer template.
 *
 * @package asdo-blog
 */

?>
</main>
<footer>
&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>, Built with <a href="https://wordpress.org/">WordPress</a>
<ul class="hlist">
<li><strong>Social:</strong></li>
<li><a href="<?php echo esc_url( get_bloginfo( 'rss2_url' ) ); ?>">RSS</a></li>
<li><a rel="me" href="https://twitter.com/andrewshell">Twitter</a></li>
<li><a rel="me" href="https://indieweb.social/@andrewshell">Mastodon</a></li>
<li><a rel="me" href="https://github.com/andrewshell">GitHub</a></li>
<li><a rel="me" href="https://gravatar.com/andrewshell">Gravatar</a></li>
</ul>
<p>
	<a href="https://xn--sr8hvo.ws/previous">&larr;</a>
	An <a href="https://xn--sr8hvo.ws">IndieWeb Webring</a> &#x1F578;&#x1F48D;
	<a href="https://xn--sr8hvo.ws/next">&rarr;</a>
	<br>
	<a href="https://fediring.net/previous?host=blog.andrewshell.org">&larr;</a>
	<a href="https://fediring.net/">Fediring</a>
	<a href="https://fediring.net/next?host=blog.andrewshell.org">&rarr;</a>
</p>
</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
