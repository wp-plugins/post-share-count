<?php
/*
Plugin Name: Post Share Count
Plugin URI: http://html-and-cms.com/plugins/post-share-count/
Description: Show twitter share count.
Version: 0.1
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
 * @param string $before Optional. Content to prepend to the title.
 * @param string $after Optional. Content to append to the title.
 * @param bool $echo Optional, default to true.Whether to display or return.
 * @return void|string Zero on no . String if $echo parameter is false.
 */
function the_post_share_count( $before = '', $after = '', $echo = TRUE ) {
	$count = get_the_post_share_count();

	$count = $before . $count . $after;

	if ( $echo )
		echo $count;
	else
		return $count;
}

/**
 * Retrieve post share count.
 *
 * Get share count from post object, if not exists get from cdn.
 *
 * @since 0.1
 *
 * @param int|object $post Optional. Post ID or object.
 * @return mixed|void
 */
function get_the_post_share_count( $post = 0 ) {
	$post  = get_post( $post );
	$id    = isset( $post->ID ) ? $post->ID : 0;
	$count = isset( $post->post_share_count ) ? $post->post_share_count : 0;

	if ( !isset( $post->post_share_last_sync ) || $post->post_share_last_sync < ( time() - 60 * 60 ) ) {
		if ( FALSE !== ( $updated_count = post_share_count_sync_post( $id ) ) ) {
			update_post_meta( $id, 'post_share_count', $updated_count );
			update_post_meta( $id, 'post_share_last_sync', time() );
			$count = $updated_count;
		}
	}

	return apply_filters( 'the_post_share_count', $count, $id );
}

/**
 * Get post share count from twitter api cdn.
 *
 * @since 0.1
 *
 * @param int|object $post Optional. Post ID or object.
 * @return bool|int Return false if can't get post count and integer if can.
 */
function post_share_count_sync_post( $post = 0 ) {
	$url      = get_permalink( $post );
	$response = wp_remote_get( "https://cdn.api.twitter.com/1/urls/count.json?url=$url" );
	if ( !is_wp_error( $response ) && isset( $response[ 'body' ] ) ) {
		$data = json_decode( $response[ 'body' ] );
		if ( !is_null( $data ) && isset( $data->count ) )
			return $data->count;
	}
	return FALSE;
}
