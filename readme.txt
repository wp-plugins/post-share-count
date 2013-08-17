=== Plugin Name ===
Contributors: zviryatko
Donate link: http://makeyoulivebetter.org.ua/buy-beer
Tags: twitter, share, counter
Requires at least: 3.0
Tested up to: 3.6
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Show twitter share count.

== Description ==

Show twitter share count.
In future I want to add facebook counter.

== Installation ==

1. Upload `post-share-count.zip` and extract to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place `<?php the_post_share_count(); ?>` in your templates

== Frequently Asked Questions ==

= How to add html wrapper or some css class to counter? =

Function `the_post_share_count` support `$before` and `$after` arguments, same as `the_title`
`<?php the_post_share_count( '<span class="twitter-share-link">', '</span>' ); ?>`

= How to display counter as link? =

Use `$before` and `$after` arguments, here is example with using twitter api:
`<?php the_post_share_count( '<span class="twitter-share-link"><a href="https://twitter.com/intent/tweet?text=' . urlencode(get_the_title()) . '&url=' . urlencode(get_permalink()) . '" rel="nofollow" target="_blank">', '</a></span>' ); ?>`

= How to add beautiful image to share link with counter? =

Use CSS `background-image` property for it.
Or if you are using twentythirteen [child theme](http://codex.wordpress.org/Child_Themes "How to add child theme")
 put code in previous example to you template file into the mail loop and add this code to your style.css:
`
@import url("../twentythirteen/style.css");

.twitter-share-link a:before {
	content: '\f202'; /* twitter icon code in the Genericons icon font */
	display: inline-block;
	font: 16px/1 Genericons;
	vertical-align: text-bottom;
}
`