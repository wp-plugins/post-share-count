<?php
/**
 * @file
 * Plugin post_share_count uninstall functions.
 *
 * Created by JetBrains PhpStorm.
 * User: Alex Davyskiba
 * Company: HTML&CMS (http://html-and-cms.com)
 * Date: 8/14/13
 */

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

global $wpdb;
$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE 'post_share_%'");