=== Plugin Name ===
Contributors: zviryatko
Donate link: http://makeyoulivebetter.org.ua/buy-beer
Tags: twitter, share, counter
Requires at least: 3.0
Tested up to: 3.6
Stable tag: 0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Show twitter and facebook share count.

== Description ==

Show twitter and facebook share count.
In feature I want:
* create API for easy add new counters
* add ability to show only needed counters
* show statistics on dashboard

== Installation ==

1. Upload `post-share-count.zip` and extract to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place `<?php the_post_share_count(); ?>` in your templates

== Frequently Asked Questions ==

= How to add html wrapper or some css class to counter and/or display counter as link? =

Use array as argument with keys `before_` and `after_` prefix and social link name, here is example with using twitter and facebook api:
`
<?php
$share_args = array(
    'before_twitter' => '<span class="twitter-share-link"><a href="https://twitter.com/intent/tweet?text=' . urlencode(get_the_title()) . '&url=' . urlencode(get_permalink()) . '" rel="nofollow" target="_blank">',
    'after_twitter' => '</a></span>',
    'before_facebook' => '<span class="facebook-share-link"><a href="https://www.facebook.com/sharer/sharer.php?u=' .  urlencode(get_permalink()) . '" target="_blank">',
    'after_facebook' => '</a></span>'
);
the_post_share_count( $share_args );
?>
`

= How to add beautiful image to share link with counter? =

Use CSS `background-image` property for it.
Or if you are using twentythirteen [child theme](http://codex.wordpress.org/Child_Themes "How to add child theme")
 put code in previous example to you template file into the mail loop and add this code to your style.css:
`
@import url("../twentythirteen/style.css");

.twitter-share-link a:before,
.facebook-share-link a:before {
	display: inline-block;
	font: 16px/1 Genericons;
	vertical-align: text-bottom;
}
.facebook-share-link a:before {
	content: '\f202'; /* twitter icon code in the Genericons icon font */
}
.facebook-share-link a:before {
	content: '\f204'; /* facebook icon code in the Genericons icon font */
}
`

== Changelog ==

= 0.2 =
* Added facebook counter
* Changed `the_post_share_count` arguments, now one arg as array, see faq.
