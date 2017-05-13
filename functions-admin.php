<?php

// Make sure it's WordPress
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

// General Functionality //////////////////////////////////////////////////////

// Remove devicepx-jetpack.js
add_action( 'admin_enqueue_scripts', create_function( null, "wp_dequeue_script('devicepx');" ), 20 );
add_action( 'admin_init', 'mn_image_link_setup' );
add_action( 'admin_menu', 'mn_sort_dashboard_menu', 99990 );
add_filter( 'admin_footer_text', function ( $text ) {
//	return sprintf( $text . " | " . mn_get_usage_stats() );
	return sprintf( mn_get_usage_stats() );
} ); // Add usage stats to admin
// Add excerpts to posts
add_post_type_support( 'page', 'excerpt' );


// General Functionality //////////////////////////////////////////////////////

// Set default link type for media
function mn_image_link_setup() {
	$image_set = get_option( 'image_default_link_type' );

	if ( $image_set !== 'none' ) {
		update_option( 'image_default_link_type', 'none' );
	}
}


// https://wordpress.org/plugins/sort-admin-menus/
function mn_sort_dashboard_menu() {
	global $submenu;

	function ws_sdm_comparator( $a, $b ) {
		return strcasecmp( $a[0], $b[0] );
	}

	$menus_to_sort = array();
	$menus_to_sort['tools.php'] = array( __('Available Tools'), __('Import'), __('Export') );
 	$menus_to_sort['options-general.php'] = array( __('General'), __('Writing'), __('Reading'), __('Discussion'), __('Media'), __('Permalinks') );

	foreach ( $submenu as $key => $menu_items ) {
		if ( ! array_key_exists( $key, $menus_to_sort ) ) {
			continue;
		}

		$wordpress_menu_items = array();
		$sortable_menu_items = array();

		foreach ( $menu_items as $menu_item ) {
			if ( in_array( $menu_item[0], $menus_to_sort[$key] ) ) {
				$wordpress_menu_items[] = $menu_item;
			}
			else {
				$sortable_menu_items[] = $menu_item;
			}
		}

		usort( $sortable_menu_items, 'ws_sdm_comparator' );
		$submenu[$key] = array_merge( $wordpress_menu_items, $sortable_menu_items );
	}
}


// Yoast SEO Replacement //////////////////////////////////////////////////

require_once( 'yoast-seo-replacement-admin.php' );
