<?php

// Yoast SEO Replacement //////////////////////////////////////////////////////

// Make sure it's WordPress
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


// Remove feeds for categories, tags, post type archives, author archives and search
remove_action( 'wp_head', 'feed_links_extra', 3 );
// Remove tagging support for Windows Live Writer
remove_action( 'wp_head', 'wlwmanifest_link' );
// Remove WordPress wp.me shortlink
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
add_action( 'mn_head', 'mn_add_meta_description' );
add_action( 'mn_head', 'mn_meta_robots' );
add_action( 'mn_head', 'mn_add_google_structured_data' );
add_action( 'wp', 'mn_archive_redirect' );
add_action( 'template_redirect', 'mn_attachment_redirect', 1 );
add_filter( 'document_title_separator', function ( $separator ) { return SEPARATOR; }, 99 );


function mn_add_meta_description() {
	if ( is_front_page()  && ! is_paged() ) {
		echo "\t" . '<meta name="description" content="' . esc_attr( META_DESCRIPTION_SITE ) . '">' . "\n";
	}
	elseif ( is_single() || is_page() ) {
		echo "\t" . '<meta name="description" content="' . esc_attr( get_the_excerpt() ) . '">' . "\n";
	}
	elseif ( is_category() || is_tag() ) {
		// for category, tag, but not format (no meaningful description possible, only generic)
		echo "\t" . '<meta name="description" content="' . esc_attr( wp_strip_all_tags( get_the_archive_description() ) ) . '">' . "\n";
	}
}


function mn_meta_robots() {
	// is_singular() == is_single() || is_page() || is_attachment()
	// is_archive() == is_post_type_archive() || is_date() || is_author() || is_category() || is_tag() || is_tax()
	// is_front_page() ==
	// This is for what is displayed at your site's main URL. Depends on the site's "Front page displays" Reading Settings 'show_on_front' and 'page_on_front'. If you set a static page for the front page of your site, this function will return true when viewing that page. Otherwise the same as is_home().
	// is_home() ==
	// This is the page which shows the time based blog content of your site. Depends on the site's "Front page displays" Reading Settings 'show_on_front' and 'page_for_posts'. If you set a static page for the front page of your site, this function will return true only on the page you set as the "Posts page".
//echo '<link rel="canonical" href="' . esc_url( $canonical, null, 'other' ) . '" />' . "\n";
    $robots_content = 'noodp';
    
	if ( is_archive() || is_attachment() || is_search() || ( is_front_page() && is_paged() ) ) {
		$robots_content .= ',noindex,follow';
	}
    echo "\t<meta name=\"robots\" content=\"$robots_content\">\n";
}


function mn_get_social_type() {
    
}


function mn_get_social_title() {
	if ( is_front_page() ) {
		return esc_attr( META_DESCRIPTION_SITE );
	}
	elseif( is_single() || is_page() ) {
		return esc_attr( get_the_excerpt() );
	}
    return '';   
}


function mn_get_social_meta_description() {
	if ( is_front_page() ) {
		return esc_attr( META_DESCRIPTION_SITE );
	}
	elseif( is_single() || is_page() ) {
		return esc_attr( get_the_excerpt() );
	}
    return '';
}


function mn_get_social_url() {
    return get_permalink( get_the_ID() );
}


function mn_get_social_site_name() {
    
}


function mn_add_facebook_opengraph() {
    $type = mn_get_social_type();
    $title = mn_get_social_title();
	$description = mn_get_social_meta_description();
    $url = mn_get_social_url();
    $site_name = mn_get_social_site_name();
    
	echo <<<EOL
	<meta property="og:locale" content="en_US">
	<meta property="og:type" content="$type">
	<meta property="og:title" content="$title">
	<meta property="og:description" content="$description">
	<meta property="og:url" content="$url">
	<meta property="og:site_name" content="$site_name">
	<meta property="fb:admins" content="$FB_ADMIN">
EOL;
}
//add_action( 'mn_head', 'mn_add_facebook_opengraph' );


function mn_add_twitter_cards() {
 	echo <<<EOL
	<meta name="twitter:card" content="summary">
	<meta name="twitter:description" content="$description">
	<meta name="twitter:title" content="$title">
	<meta name="twitter:site" content="@mnordmeyer">
	<meta name="twitter:domain" content="Michael Nordmeyer">
EOL;
}
//add_action( 'mn_head', 'mn_add_twitter_cards' );


function mn_add_google_structured_data() {
	echo <<<EOL
	<script type='application/ld+json'>{ "@context": "http://schema.org", "@type": "Person", "name": "Michael Nordmeyer", "url": "https://michaelnordmeyer.com/", "sameAs": [ "https://www.linkedin.com/in/michaelnordmeyer", "https://www.xing.com/profile/Michael_Nordmeyer", "https://twitter.com/mnordmeyer", "https://github.com/michaelnordmeyer" ] }</script>
	<script type='application/ld+json'>{ "@context": "http://schema.org", "@type": "WebSite", "url": "https://michaelnordmeyer.com/", "name": "Michael Nordmeyer", "potentialAction": { "@type": "SearchAction", "target": "https://michaelnordmeyer.com/?s={search_term}", "query-input": "required name=search_term" } }</script>
EOL;
}


/**
 * When certain archives are disabled, this redirects those to the homepage.
 * @return boolean False when no redirect was triggered
 *
 * Source: Yoast SEO
 */
function mn_archive_redirect() {
	global $wp_query;

	if (
		$wp_query->is_date() ||
		$wp_query->is_author() ||
		$wp_query->is_post_type_archive() ||
		$wp_query->is_tax( 'post_format' )
	) {
		wp_safe_redirect( get_bloginfo( 'url' ), 301 );
		exit;
	}

	return false;
}
/*
Or put this in the theme's author.php:

<?php
header( 'HTTP/1.1 301 Moved Permanently' );
header( 'Location: /' );
?>
*/

/**
 * If the option to redirect attachments to their parent is checked, this performs the redirect.
 *
 * An extra check is done for when the attachment has no parent.
 * @return boolean False when no redirect was triggered
 *
 * Source: Yoast SEO
 */
function mn_attachment_redirect() {
	$post = get_post();

	if ( is_attachment() && is_object( $post ) && isset( $post->post_parent ) && is_numeric( $post->post_parent ) && $post->post_parent != 0 ) {
		wp_safe_redirect( get_permalink( $post->post_parent ), 301 );
		exit;
	}

	return false;
}


/**
 * Determine whether the current page is the homepage and shows posts.
 *
 * @return bool
 *
 * Source: Yoast SEO
 */
function is_home_posts_page() {
	return ( is_home() && 'posts' == get_option( 'show_on_front' ) );
}

/**
 * Determine whether the current page is a static homepage.
 *
 * @return bool
 *
 * Source: Yoast SEO
 */
function is_home_static_page() {
	return ( is_front_page() && 'page' == get_option( 'show_on_front' ) && is_page( get_option( 'page_on_front' ) ) );
}

/**
 * Determine whether this is the posts page, regardless of whether it's the frontpage or not.
 *
 * @return bool
 *
 * Source: Yoast SEO
 */
function is_posts_page() {
	return ( is_home() && 'page' == get_option( 'show_on_front' ) );
}
