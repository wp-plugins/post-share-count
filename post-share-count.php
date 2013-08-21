<?php
/*
Plugin Name: Post Share Count
Plugin URI: http://html-and-cms.com/plugins/post-share-count/
Description: Show twitter and facebook share count.
Version: 0.2
Author: zviryatko
Author URI: http://makeyoulivebetter.org.ua/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/*  Copyright 2013  Zviryatko  (email : sanya.davyskiba@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * @file
 * Plugin post-share-count plugin file
 *
 * Created by JetBrains PhpStorm.
 * User: Alex Davyskiba
 * Company: HTML&CMS (http://html-and-cms.com)
 * Date: 8/14/13
 */

/**
 * Display or retrieve the current post share count with optional content.
 *
 * @since 0.1
 *
 * @param array $args
 * @return void|string Zero on no . String if $echo parameter is false.
 */
function the_post_share_count( $args = array() ) {
    $default_args = array(
        'post'            => 0,
        'echo'            => true,
        'before_twitter'  => '',
        'after_twitter'   => '',
        'before_facebook' => '',
        'after_facebook'  => '',
    );
    if( !is_array($args) ) {
        $r                     = array();
        $func_args             = func_get_args();
        $r[ 'before_twitter' ] = array_shift( $func_args );
        $r[ 'after_twitter' ]  = array_shift( $func_args );
        $args                  = $r;
    }
    $args = wp_parse_args( $args, $default_args );

    /**
     * @var $before_twitter string
     * @var $after_twitter string
     * @var $before_facebook string
     * @var $after_facebook string
     * @var $echo bool
     * @var $post int|WP_Post
     */
    extract( $args );
	$count  = get_the_post_share_count( $post );
    $output = '';
    if( isset( $count[ 'twitter' ] ) )
        $output .= $before_twitter . $count[ 'twitter' ] . $after_twitter;
    if( isset( $count[ 'facebook' ] ) )
        $output .= $before_facebook . $count[ 'facebook' ] . $after_facebook;

	if ( $echo )
		echo $output;
	else
		return $output;
}

/**
 * Retrieve post share count.
 *
 * Get share count from post object, if not exists get from cdn.
 *
 * @since 0.1
 *
 * @param int|WP_Post $post Optional. Post ID or WP_Post object.
 * @return array
 */
function get_the_post_share_count( $post = 0 ) {
	$post  = get_post( $post );
	$id    = isset( $post->ID ) ? $post->ID : 0;
	$count = (!isset( $post->post_share_count ) || !is_array( $post->post_share_count ) ) ? array() : $post->post_share_count;

	if ( !isset( $post->post_share_last_sync ) || $post->post_share_last_sync < ( time() - 60 * 60 ) ) {
		$updated_count = post_share_count_sync_post( $id );
        update_post_meta( $id, 'post_share_count', $updated_count );
        update_post_meta( $id, 'post_share_last_sync', time() );
        $count = $updated_count;
	}

	return apply_filters( 'the_post_share_count', $count, $id );
}

/**
 * Get post share count from twitter api cdn.
 *
 * @since 0.1
 *
 * @param int|object $post Optional. Post ID or object.
 * @return array Return array of counts keyed by social network name
 */
function post_share_count_sync_post( $post = 0 ) {
	$url = get_permalink( $post );
	return array(
        'twitter'  => get_twitter_post_share_count( $url ),
        'facebook' => get_facebook_post_share_count( $url ),
    );
}

/**
 * Get twitter share count
 *
 * @since 0.2
 *
 * @param string $url Shared post permalink
 *
 * @return int
 */
function get_twitter_post_share_count($url) {
    $response = wp_remote_get( "https://cdn.api.twitter.com/1/urls/count.json?url=$url" );
    if ( !is_wp_error( $response ) && isset( $response[ 'body' ] ) ) {
        $data = json_decode( $response[ 'body' ] );
        if ( !is_null( $data ) && isset( $data->count ) )
            return $data->count;
    }
    return 0;
}

/**
 * Get facebook share count
 *
 * @since 0.2
 *
 * @param string $url Shared post permalink
 *
 * @return int
 */
function get_facebook_post_share_count($url) {
    $response = wp_remote_get( "https://graph.facebook.com/?id=$url" );
    if ( !is_wp_error( $response ) && isset( $response[ 'body' ] ) ) {
        $data = json_decode( $response[ 'body' ] );
        if ( !is_null( $data ) && isset( $data->shares ) )
            return $data->shares;
    }
    return 0;
}
