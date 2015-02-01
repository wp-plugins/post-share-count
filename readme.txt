=== Plugin Name ===
Contributors: zviryatko
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=sanya%2edavyskiba%40gmail%2ecom&lc=UA&item_name=MakeYouLiveBetter&item_number=post%2dshare%2dcount&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted
Tags: twitter, share, counter
Requires at least: 3.0
Tested up to: 3.6
Stable tag: 0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Show twitter and facebook share count.

== Description ==

Show twitter and facebook share count. Send your feature request.

== Installation ==

1. Upload `post-share-count.zip` and extract to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place `<?php the_post_share_count(); ?>` in your templates

== Frequently Asked Questions ==

= How to add html wrapper or some css class to counter and/or display counter as link? =

Use array as argument with keys `before_` and `after_` prefix and social link name, here is example how to add @via parameter twitter sharing link:
`
<?php
$share_args = array(
    'before_twitter' => '<span class="share-link"><a href="https://twitter.com/intent/tweet?text=' . urlencode(get_the_title()) . '&url=' . urlencode(get_permalink()) . '&via=' . urlencode('your-twitter-name') . '" rel="nofollow" target="_blank"><span class="genericon genericon-twitter"></span> ',
    'after_twitter' => '</a></span>',
);
the_post_share_count( $share_args );
?>
`
Also you can add changes to `functions.php`:
`function your_theme_post_share_count_services( $services ) {
    $services['twitter']['before'] = '<span class="share-link"><a href="https://twitter.com/intent/tweet?text=%title%&url=%url%&via=' . urlencode('your-twitter-name') . '" rel="nofollow" target="_blank"><span class="genericon genericon-twitter"></span> ';
    return $services;
}
add_filter('post_share_count_services', 'your_theme_post_share_count_services');
`
= How to limit list of social networks? (or "I want only twitter counter") =

Add `show_only` parameter:
`<?php
$args = array('show_only' => array( 'twitter', 'facebook' ));
the_post_share_count($args);
?>
`
Also you can do it in `functions.php`:
`function your_theme_post_share_count_services( $services ) {
    unset( $services['facebook'] );
    unset( $services['pinterest'] );
    unset( $services['googleplus'] );
    unset( $services['linkedin'] );
    return $services;
}
add_filter('post_share_count_services', 'your_theme_post_share_count_services');
`

== Changelog ==

= 0.5 =
* Fix pinterest counter

= 0.4 =
* Added LinkedIn counter
* Added genericon css classes for icon font support
* Added parameters `time` and `max_sync`, see `get_the_post_share_count()` function for details
* Change donate link to PayPal

= 0.2 =
* Added facebook counter
* Changed `the_post_share_count` arguments, now one arg as array, see faq.

== Upgrade Notice ==

= 0.4 =
Add param `'show_only' => array('twitter', 'facebook')` because now 5 counters available.