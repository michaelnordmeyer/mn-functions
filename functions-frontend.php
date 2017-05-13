<?php

// Make sure it's WordPress
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

// Registration ///////////////////////////////////////////////////////////////

$mn_blog_title_description = esc_attr( get_bloginfo( 'name' ) . " " . SEPARATOR . " " . get_bloginfo( 'description' ) );


// General Functionality //////////////////////////////////////////////////////

// Security by obscurity
add_filter( 'the_generator', function() { return ''; } );

add_action( 'wp_head', 'mn_head_init', 1 );
add_action( 'mn_head', 'mn_add_dns_prefetch' );
// add_filter( 'wp_resource_hints', 'mn_resource_hints', 10, 2 );
remove_action( 'wp_head', 'wp_resource_hints', 2 );
// Remove WordPress embed (since 4.4)
// add_action( 'wp_footer', function () { wp_deregister_script( 'wp-embed' ); } );
// Remove devicepx-jetpack.js
add_action( 'wp_enqueue_scripts', function () { wp_dequeue_script( 'devicepx' ); }, 100 );
add_action( 'mn_head', 'mn_add_website_icons' );
add_action( 'mn_head', 'mn_remove_comments_feed' );
// Add usage stats to page as comment
add_action( 'wp_footer', function () { printf( "<!-- %s -->", mn_get_usage_stats() ); }, 100 );
add_filter( 'the_content_feed', 'mn_add_feed_post_thumbnail', 99 );
add_filter( 'the_excerpt_rss', 'mn_add_feed_post_thumbnail' );
// By https://wordpress.org/plugins/remove-more-jump/
add_filter( 'the_content_more_link', function ( $link ) { return preg_replace( '/#more\-\d+/', '', $link ); } );


// wp_head ////////////////////////////////////////////////////////////////////

/**
 * Main wrapper function attached to wp_head. This combines all the output on the frontend of this plugin.
 */
function mn_head_init() {
	global $wp_query;

	$old_wp_query = null;

	if ( ! $wp_query->is_main_query() ) {
		$old_wp_query = $wp_query;
		wp_reset_query();
	}

	echo "\n\t<!-- MN -->\n";

	/**
	 * Action: 'mn_head' - Allow other plugins to output inside the custom section of the head section.
	 */
	do_action( 'mn_head' );

	echo "\n\t<!-- / MN -->\n";

	if ( ! empty( $old_wp_query ) ) {
		$GLOBALS['wp_query'] = $old_wp_query;
		unset( $old_wp_query );
	}

	return;
}

function mn_add_website_icons() {
	echo "\t" . '<link rel="Shortcut Icon" type="image/x-icon" href="' . get_bloginfo( 'wpurl' ) . '/favicon.ico">' . PHP_EOL;
}

function mn_remove_comments_feed() {
	remove_action( 'wp_head', 'feed_links', 2 );
	echo "\t" . '<link rel="alternate" type="application/rss+xml" title="' . get_bloginfo( 'name' ) . ' Feed" href="' . get_bloginfo( 'rss2_url' ) . '">' . PHP_EOL;
}

function mn_add_dns_prefetch() {
	echo <<<EOL
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="dns-prefetch" href="https://stats.michaelnordmeyer.com">
    <link rel="dns-prefetch" href="https://s0.wp.com">
    <link rel="dns-prefetch" href="https://secure.gravatar.com">
    <link rel="dns-prefetch" href="https://0.gravatar.com">
    <link rel="dns-prefetch" href="https://1.gravatar.com">
    <link rel="dns-prefetch" href="https://2.gravatar.com">
EOL;
}

// function mn_resource_hints( $hints, $relation_type ) {
//     if ( 'dns-prefetch' === $relation_type ) {
//         $hints[] = 'https://stats.michaelnordmeyer.com/';
//     }
//
//     return $hints;
// }


// General Functionality //////////////////////////////////////////////////////

// Put echo reading_time(); in e.g. entry-meta.php
function mn_reading_time( $post_ID ) {
    if ( $post_ID == null ) {
        $post_ID = get_the_ID();
    }
    $reading_time = ceil( str_word_count( strip_tags( get_post_field( 'post_content', $post_ID ) ) ) / 200 );
    $unit = ( $reading_time == 1 ) ? " minute" : " minutes";
    return $reading_time . $unit;
}


// Feed Functionality /////////////////////////////////////////////////////////

function mn_add_feed_post_thumbnail( $content ) {
	if ( is_feed() && has_post_thumbnail( get_the_ID() ) ) {
		$content = '<p>' . get_the_post_thumbnail( get_the_ID() ) . '</p>' . $content;
	}

	return $content;
}

/*
function mn_add_feed_published_first( $content ) {
	if ( is_feed() ) {
		$content .= '<p>This post was published first on <a href="' . get_home_url() . '/">' . $mn_blog_title_description . '</a></p>';
	}

	return $content;
}
add_filter( 'the_content_feed', 'mn_add_feed_published_first' );
*/

/*
function mn_add_feed_sharing_icons( $content ) {
	if ( is_feed() ) {
		$permalink_encoded = urlencode( get_permalink() );
		$post_title = get_the_title();

		$content .= '<p><a href="https://www.facebook.com/sharer/sharer.php?u=' . $permalink_encoded . '" title="Share on Facebook"><img src="Facebook icon file url goes here" title="Share on Facebook" alt="Facebook sharing icon" width="64px" height="64px" /></a> <a href="https://twitter.com/share?text='. $post_title . '&amp;url=' . $permalink_encoded . '" title="Share on Twitter"><img src="Twitter icon file url goes here" title="Share on Twitter" alt="Twitter sharing icon" width="64px" height="64px" /></a></p>';
	}

	return $content;
}
add_filter( 'the_content_feed', 'mn_add_feed_sharing_icons' );
*/


// Totally block WordPress comments so that bots cannot post to wp-comments-post.php directly
/*
function mn_block_wp_comments() {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
add_action( 'pre_comment_on_post', 'mn_block_wp_comments' );
*/


/*
function mn_add_pingdom_rum() {
	echo <<<EOL
<script>
var _prum = [['id', '52627d1cabe53d142e000000'],
             ['mark', 'firstbyte', (new Date()).getTime()]];
(function() {
    var s = document.getElementsByTagName('script')[0]
      , p = document.createElement('script');
    p.async = 'async';
    p.src = 'https://rum-static.pingdom.net/prum.min.js';
    s.parentNode.insertBefore(p, s);
})();
</script>
EOL;
}
add_action( 'wp_footer', 'mn_add_pingdom_rum', 98 );
*/


// Theme-specific functionality ///////////////////////////////////////////////

/*
// Change text from WordPress afterwards to avoid copying php files
// Functions is called for _every_ translation call, even when the blog runs in English
function mn_twenty_something_text_wrangler( $translation, $text, $domain ) {
    if ( substr( $domain, 0, strlen('twenty') ) === 'twenty' ) {
    	if ( $text == 'https://wordpress.org/' ) {
    		return WP_HOME . '/';
    	}
    	if ( $text == 'Proudly powered by %s' ) {
    		return '&copy 2008&ndash;' . date('Y') . ' Michael Nordmeyer';
    	}
    }

	return $translation;
}
add_filter( 'gettext', 'mn_twenty_something_text_wrangler', 10, 4 );
*/


// WP-Piwik Replacement ///////////////////////////////////////////////////////

require_once( 'wp-piwik-replacement.php' );


// Yoast SEO Replacement //////////////////////////////////////////////////////

require_once( 'yoast-seo-replacement.php' );


// Jetpack Sitemap Replacement ////////////////////////////////////////////////
/*
if ( '1' == get_option( 'blog_public' ) ) {
    require_once( 'jetpack-replacement/sitemap-xml.php' );
    require_once( 'jetpack-replacement/jetpack-postimages.php' );
    require_once( 'jetpack-replacement/jetpack-sitemap-xml.php' );
}*/