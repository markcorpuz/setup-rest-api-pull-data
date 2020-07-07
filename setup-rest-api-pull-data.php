<?php
/**
 * Plugin Name: SWP - REST API
 * Description: Pull entries from an external site
 * Version: 1.0
 * Author: Jake Almeda
 * Author URI: http://smarterwebpackages.com/
 * Network: true
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/* ----------------------------------------------------------------------------
 * REST MAIN FUNCTION
 * ------------------------------------------------------------------------- */
function swp_pull_contents_func( $atts, $content = null ) {

    global $wpdb;

    // RETRIEVE ATTRIBUTE(S)
    // ---------------------------------------------------------------------
    $attr = shortcode_atts( array(
            'site'          => 'site',
            'post_type'     => 'post_type',
            'id'            => 'id',
            'field'         => 'field',
        ), $atts );
    /*
            'context'               => 'context',
            'page'                  => 'page',
            'per_page'              => 'per_page',
            'search'                => 'search',
            'after'                 => 'after',
            'author'                => 'author',
            'author_exclude'        => 'author_exclude',
            'before'                => 'before',
            'exclude'               => 'exclude',
            'include'               => 'include',
            'offset'                => 'offset',
            'order'                 => 'order',
            'orderby'               => 'orderby',
            'slug'                  => 'slug',
            'status'                => 'status',
            'categories'            => 'categories',
            'categories_exclude'    => 'categories_exclude',
            'tags'                  => 'tags',
            'tags_exclude'          => 'tags_exclude',
            'sticky'                => 'sticky',
    */
    
    // ---------------------------------------------------------------------
    // http://plan.smarterwebpackages.com/wp-json/wp/v2/posts
    // http://plan.smarterwebpackages.com/wp-json/wp/v2/partners
    if( swp_validate_atts( $attr[ 'site' ], 'site' ) && swp_validate_atts( $attr[ 'post_type' ], 'post_type' ) ) {

        if( swp_validate_atts( $attr[ 'id' ], 'id' ) && swp_validate_atts( $attr[ 'field' ], 'field' ) ) {
            // display specific entry
            return swp_get_field( $attr[ 'site' ], $attr[ 'post_type' ], $attr[ 'id' ], $attr[ 'field' ] );
        } else {
            return "Please specify the ID and the field you want to retrieve.";
        }

    } else {
        return "Please specify the target site (URL) and/or the post type.";
    }

    //return 'Jake';

}

/* ----------------------------------------------------------------------------
 * GET REST DATA
 * ------------------------------------------------------------------------- */
function swp_get_field( $site, $post_type, $id, $field ) {

    //$target = 'http://plan.smarterwebpackages.com/wp-json/wp/v2/partners';
    //$target = 'http://plan.smarterwebpackages.com/wp-json/wp/v2/partners/170';
    if( $id ) {
        $target = file_get_contents( rtrim( $site, "/" ).'/wp-json/wp/v2/'.$post_type.'/'.$id );
    } else {
        $target = file_get_contents( rtrim( $site, "/" ).'/wp-json/wp/v2/'.$post_type );
    }

    $array = json_decode( $target, TRUE, 512 );
    foreach( $array as $key => $value ) {

        if( $key == $field ) {
            
            if( is_array( $value ) ) {
                return $value[ 'rendered' ];
            } else {
                return $value;
            }

        }
    }

}

/* ----------------------------------------------------------------------------
 * VALIDATE ATTRIBUTE'S CONTENT
 * ------------------------------------------------------------------------- */
function swp_validate_atts( $atts, $default ) {

	if( $atts && $atts != $default ) {
		return true;
	}

}

/* ----------------------------------------------------------------------------
 * DISPLAY TEMPLATE
 * ------------------------------------------------------------------------- */
function swp_rest_display() {
    return '<div id="target"></div>';
}

if( !is_admin() ){
    // register shortcode
    add_shortcode( 'swp_pull_contents', 'swp_pull_contents_func' );
}