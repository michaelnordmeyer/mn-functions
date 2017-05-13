<?php

// Make sure it's WordPress
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

// Automatic Updates //////////////////////////////////////////////////////////

// See http://codex.wordpress.org/Configuring_Automatic_Background_Updates
/*
define( 'WP_AUTO_UPDATE_CORE', true );
Value of true – Development, minor, and major updates are all enabled
Value of false – Development, minor, and major updates are all disabled
Value of 'minor' – Minor updates are enabled, development, and major updates are disabled
*/
//add_filter( 'allow_dev_auto_core_updates', '__return_true', 1 );
add_filter( 'allow_major_auto_core_updates', '__return_true', 1 );
add_filter( 'auto_update_plugin', '__return_true', 1 );
add_filter( 'auto_update_theme', '__return_true', 1 );


// General Functionality //////////////////////////////////////////////////////

//add_filter( 'xmlrpc_enabled', '__return_false' );
add_action( 'init', 'mn_remove_emojis' );
add_action( 'phpmailer_init', 'mn_smtp_setup' );
add_filter( 'wp_mail_from', function( $email ) { return SMTP_FROM_EMAIL; } );
add_filter( 'wp_mail_from_name', function( $name ) { return SMTP_FROM_NAME; } );
add_action( 'pre_ping', 'mn_no_self_ping' );
//add_filter( 'pre_comment_approved', 'mn_moderate_trackback', 20 );
// Removes formatting?
// add_filter( 'excerpt_length', function ( $length ) { return 160; } );
// add_filter( 'excerpt_more', function ( $more ) { return '&#8230;'; } ); // … instead of [...]
// add_filter( 'excerpt_content', function ( $excerpt ) { return $excerpt . "Read on…"; } );


require_once( 'wp-piwik-replacement-all.php' );
if ( is_admin() ) {
    require_once( 'functions-admin.php' );
}
else {
    require_once( 'functions-frontend.php' );
//    require_once( 'auto-excerpts-everywhere.php' );
}


// Remove WordPress Emoji support (4-byte character support, since 4.2)
// Saves one request and 6 KiB when used with Autoptimize
function mn_remove_emojis() {
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}


// SMTP Setup /////////////////////////////////////////////////////////////////

function mn_smtp_setup( $phpmailer ) {
	$phpmailer->isSMTP();
	$phpmailer->SMTPAuth = true;

	if ( defined( 'SMTP_HOST' ) ) {
		$phpmailer->Host = SMTP_HOST;
	}
	if ( defined( 'SMTP_PORT' ) ) {
		$phpmailer->Port = SMTP_PORT;
	}
	if ( $phpmailer->SMTPAuth && defined( 'SMTP_USER' ) && defined( 'SMTP_PASS' ) ) {
		$phpmailer->Username = SMTP_USER;
		$phpmailer->Password = SMTP_PASS;
	}
	if ( defined( 'SMTP_SECURE' ) && SMTP_SECURE != '' ) {
		$phpmailer->SMTPSecure = SMTP_SECURE;
	}
	if ( defined( 'SMTP_WORDWRAP' ) && SMTP_WORDWRAP > 0 ) {
		$phpmailer->WordWrap = SMTP_WORDWRAP;
	}
	if ( defined( 'SMTP_DEBUG' ) && SMTP_DEBUG > 0 ) {
		$phpmailer->SMTPDebug = SMTP_DEBUG;
		$phpmailer->Debugoutput = 'error_log';
	}
}
/*
function mn_test_email() {
	$headers = "From: SMTP_FROM_NAME <".SMTP_FROM_EMAIL.">\r\n";
	$sentSuccessfully = wp_mail( SMTP_FROM_EMAIL, "Test Email from WordPress", "Email is correctly setup on your WordPress blog.", $headers );
	if ( $sentSuccessfully ) {
		error_log( "Test email has successfully been sent" );
	}
}
add_action( 'plugins_loaded', 'mn_test_email', 11 );
*/


// General Functionality //////////////////////////////////////////////////////

// https://wordpress.org/plugins/no-self-ping/
function mn_no_self_ping( &$links ) {
	$home = get_option( 'home' );

	foreach ( $links as $key => $link ) {
		if ( 0 === strpos( $link, $home ) ) {
			unset( $links[$key] );
		}
	}
}


// Force trackbacks to always go to moderation queue
function mn_moderate_trackback( $approved ) {
	global $wp_query;

	if ( 1 == $approved && $wp_query->is_trackback() ) {
		$approved = 0;
	}

	return $approved;
}


// Rewrite category
/**
 * Schedules a rewrite flush to happen at shutdown
 */
/*
function schedule_rewrite_flush() {
	add_action( 'shutdown', 'flush_rewrite_rules' );
}
add_action( 'created_category', array( $this, 'schedule_rewrite_flush' ) );
add_action( 'edited_category', array( $this, 'schedule_rewrite_flush' ) );
add_action( 'delete_category', array( $this, 'schedule_rewrite_flush' ) );
*/


// Used for functions-frontend and frontend-admin
function mn_get_usage_stats() {
	return sprintf( "Generated in %ss | Memory Usage: %s (current), %s (peak)", timer_stop(), number_format( memory_get_usage() / 1024 / 1024, 1 ) . " MiB", number_format( memory_get_peak_usage() / 1024 / 1024, 1 ) . " MiB" );
}


/**
 * Clears the WP or W3TC cache depending on which is used
 */
/*
function mn_clear_cache() {
	if ( function_exists( 'w3tc_pgcache_flush' ) ) {
		w3tc_pgcache_flush();
	}
	elseif ( function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache();
	}
}
*/


// Theme-specific functionality ///////////////////////////////////////////////

// Set content width according to css files
/*
if ( ! isset( $content_width ) ) {
	$content_width = 600;
}
*/


// Plugin-specific functionality //////////////////////////////////////////////

//include_once( 'p3-auto-scan-pages.php' );


// One-time functionality for cleanup etc. ////////////////////////////////////

/* Remove all featured images
global $wpdb;
$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_thumbnail_id'" );
*/

/*
add_action( "init", "clear_crons_left" );
function clear_crons_left() {
	wp_clear_scheduled_hook( "wb_cron_job" ); //wordbooker
	wp_clear_scheduled_hook( "wp_cache_gc" ); // wp super cache
	wp_clear_scheduled_hook( "wp_cache_gc_watcher" ); // wp super cache
}
*/
