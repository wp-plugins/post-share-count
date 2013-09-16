<?php
/*
Plugin Name: Post Share Count
Plugin URI: http://html-and-cms.com/plugins/post-share-count/
Description: Show twitter and facebook share count.
Version: 0.3
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
 * Return a post share services.
 *
 * @since 0.3
 *
 * @return array
 *  Services array keyed by machine name contain:
 *      `title` - Human readable social service name
 *      `callback` - Callback function that get post `url` and return share count
 *      `before` - Default `before` text
 *      `after` - Default `after` text
 */
function post_share_count_get_services() {
    $built_in = array(
        'total'      => array(
            'title'  => __( 'Total', 'post_share_count' ),
            'before' => '<span class="share-link"><span class="genericon-share">',
            'after'  => '</span></span>',
        ),
        'twitter'    => array(
            'title'    => __( 'Twitter' ),
            'callback' => 'get_twitter_post_share_count',
            'before'   => '<span class="share-link"><a class="genericon-twitter" href="https://twitter.com/intent/tweet?text=%title%&url=%url%" rel="nofollow" target="_blank">',
            'after'    => '</a></span>',
        ),
        'facebook'   => array(
            'title'    => __( 'Facebook' ),
            'callback' => 'get_facebook_post_share_count',
            'before'   => '<span class="share-link"><a class="genericon-facebook" href="https://www.facebook.com/sharer/sharer.php?u=%url%" rel="nofollow" target="_blank">',
            'after'    => '</a></span>',
        ),
        'pinterest'  => array(
            'title'    => __( 'Pinterest' ),
            'callback' => 'get_pinterest_post_share_count',
            'before'   => '<span class="share-link"><a class="genericon-pinterest" href="http://pinterest.com/pin/create/button/?description=%title%&url=%url%&media=%thumb%" rel="nofollow" target="_blank">',
            'after'    => '</a></span>',
        ),
        'googleplus' => array(
            'title'    => __( 'Google Plus' ),
            'callback' => 'get_googleplus_post_share_count',
            'before'   => '<span class="share-link"><a class="genericon-googleplus" href="https://plus.google.com/share?url=%url%" rel="nofollow" target="_blank">',
            'after'    => '</a></span>',
        ),
    );

    return apply_filters( 'post_share_count_services', $built_in );
}

/**
 * Display or retrieve the current post share count with optional content.
 *
 * @since 0.1
 *
 * @param array $args
 * @return void|string Zero on no . String if $echo parameter is false.
 */
function the_post_share_count( $args = array() ) {
    $services     = post_share_count_get_services();
    $default_args = array(
        'post'       => 0,
        'echo'       => true,
        'show_only'  => false,
        'before'     => '',
        'after'      => '',
        'show_total' => false,
    );

    // Set default params `{$service}_before` and `{$service}_after`
    foreach ( $services as $key => $params ) {
        $default_args["before_{$key}"] = isset( $params['before'] ) ? $params['before'] : '';
        $default_args["after_{$key}"]  = isset( $params['after'] ) ? $params['after'] : '';
    }

    $args     = wp_parse_args( $args, $default_args );
    $post     = get_post( $args['post'] );
    $counters = get_the_post_share_count( $args['post'], $services );
    $output   = '';

    if ( is_string( $args['show_only'] ) && isset( $counters[$args['show_only']] ) ) {
        $key = $args['show_only'];
        $output .= $args["before_{$key}"] . $counters[$key] . $args["after_{$key}"];
    }
    else {
        foreach ( $counters as $key => $count ) {
            if ( is_array( $args['show_only'] ) && ! in_array( $key, $args['show_only'] )
                || $key == 'total' && ! $args['show_total']
            )
                continue;
            $output .= $args["before_{$key}"] . $count . $args["after_{$key}"];
        }
    }
    $search  = array( '%title%', '%url%', '%thumb%' );
    $replace = array(
        urlencode( get_the_title( $args['post'] ) ),
        urlencode( get_permalink( $args['post'] ) ),
        urlencode( wp_get_attachment_thumb_url( get_post_thumbnail_id( $post->ID ) ) ),
    );
    $output  = str_replace( $search, $replace, $args['before'] . $output . $args['after'] );

    if ( $args['echo'] )
        echo $output;

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
 * @param array $services Array of services key by service machine name, contain params array.
 * @return array
 */
function get_the_post_share_count( $post = 0, $services ) {
    $post           = get_post( $post );
    $id             = isset( $post->ID ) ? $post->ID : 0;
    $count['total'] = ( isset( $post->post_share_total_count ) ) ? $post->post_share_total_count : 0;
    if ( isset( $post->post_share_count ) && is_array( $post->post_share_count ) )
        $count = array_merge( $count, $post->post_share_count );

    if ( ! isset( $post->post_share_last_sync ) || $post->post_share_last_sync < ( time() - 60 * 60 ) ) {
        $updated_counters = post_share_count_sync_post( $id, $services );
        update_post_meta( $id, 'post_share_last_sync', time() );
        if ( ! empty( $updated_counters ) ) {
            update_post_meta( $id, 'post_share_count', $updated_counters );
            if ( $total = array_sum( $updated_counters ) ) {
                update_post_meta( $id, 'post_share_total_count', $total );
                $updated_counters['total'] = $total;
            }
            $count = $updated_counters;
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
 * @param array $services Array of services key by service machine name, contain params array.
 * @return array Return array of counts keyed by social network name
 */
function post_share_count_sync_post( $post = 0, $services ) {
    $url      = get_permalink( $post );
    $counters = array();
    foreach ( $services as $key => $params ) {
        if ( isset( $params['callback'] ) && ! empty( $params['callback'] ) && is_callable( $params['callback'] ) ) {
            $count = call_user_func( $params['callback'], $url, $params, $post );
            $counters[$key] = (int) $count;
        }
    }

    return $counters;
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
function get_twitter_post_share_count( $url ) {
    $response = wp_remote_get( "https://cdn.api.twitter.com/1/urls/count.json?url=$url" );
    if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
        $data = json_decode( $response['body'] );
        if ( ! is_null( $data ) && isset( $data->count ) )
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
function get_facebook_post_share_count( $url ) {
    $response = wp_remote_get( "https://graph.facebook.com/?id=$url" );
    if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
        $data = json_decode( $response['body'] );
        if ( ! is_null( $data ) && isset( $data->shares ) )
            return $data->shares;
    }

    return 0;
}

/**
 * Get pinterest share count
 *
 * @since 0.3
 *
 * @param string $url Shared post permalink
 *
 * @return int
 */
function get_pinterest_post_share_count( $url ) {
    $response = wp_remote_get( "http://api.pinterest.com/v1/urls/count.json?callback=&url=$url" );
    if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
        $data = json_decode( substr( $response['body'], 1, - 1 ) );
        if ( ! is_null( $data ) && isset( $data->count ) )
            return $data->count;
    }

    return 0;
}

/**
 * Get +google share count
 *
 * @since 0.3
 *
 * @param string $url Shared post permalink
 *
 * @return int
 */
function get_googleplus_post_share_count( $url ) {
    $args       = array(
        'headers' => array( 'Content-type' => 'application/json-rpc' ),
        'body'    => '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . $url . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]',
    );
    $google_url = 'https://clients6.google.com/rpc?key=AIzaSyCKSbrvQasunBoV16zDH9R33D88CeLr9gQ';

    $response = wp_remote_post( $google_url, $args );
    if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
        $data = json_decode( $response['body'] );
        if ( ! is_null( $data ) ) {
            if ( is_array( $data ) && count( $data ) == 1 )
                $data = array_shift( $data );
            if ( isset( $data->result->metadata->globalCounts->count ) )
                return $data->result->metadata->globalCounts->count;
        }
    }

    return 0;
}