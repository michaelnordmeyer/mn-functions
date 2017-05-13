<?php

// WP-Piwik Replacement All ////////////////////////////////////////////////////

// Make sure it's WordPress
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


// General Registration ///////////////////////////////////////////////////////

add_action( 'transition_post_status', 'mn_on_post_status_transition', 10, 3 );

function mn_on_post_status_transition( $new_status, $old_status, $post ) {
	if ( $new_status == 'publish' && $old_status != 'publish' ) {
		add_action ( 'publish_post', 'mn_add_piwik_annotation' );
	}
}

// Piwik API call: Annotations.add (idSite, date, note, starred = '0')
function mn_add_piwik_annotation( $post_id ) {
	$debug = false;

	$note = 'Published: ' . apply_filters( 'the_title', get_post( $post_id )->post_title );// . ' - ' . get_permalink( $post_id );

	$params = "module=API&format=php&method=Annotations.add&idSite=" . PIWIK_SITE_ID . "&date=" . get_post( $post_id )->post_date_gmt . "&note={$note}";

	$results = ( function_exists('curl_init') ) ? mn_piwik_curl( PIWIK_URL, $params ) : mn_piwik_fopen( PIWIK_URL, $params );

	if ( $debug ) {
		error_log( 'Added post annotation: ' . $note . ' - ' . serialize ( $result ) );
	}
}

// http://codex.wordpress.org/Function_Reference/wp_remote_get
function mn_piwik_curl( $url, $params ) {
	$debug = false;

	$curl = curl_init( $url );

	curl_setopt( $curl, CURLOPT_POST, true );
	curl_setopt( $curl, CURLOPT_POSTFIELDS, $params . '&token_auth=' . PIWIK_AUTH_TOKEN );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, true );
	curl_setopt( $curl, CURLOPT_USERAGENT, ini_get('user_agent') );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $curl, CURLOPT_HEADER, $debug );
	curl_setopt( $curl, CURLOPT_TIMEOUT, 5 );

	$httpProxyClass = new WP_HTTP_Proxy();

	if ( $httpProxyClass->is_enabled() && $httpProxyClass->send_through_proxy($strURL) ) {
		curl_setopt( $curl, CURLOPT_PROXY, $httpProxyClass->host() );
		curl_setopt( $curl, CURLOPT_PROXYPORT, $httpProxyClass->port() );
		if ( $httpProxyClass->use_authentication() ) {
			curl_setopt( $curl, CURLOPT_PROXYUSERPWD, $httpProxyClass->username() . ':' . $httpProxyClass->password() );
		}
	}

	$result = curl_exec( $curl );

	if ( $debug ) {
		$header_size = curl_getinfo( $curl, CURLINFO_HEADER_SIZE );
		$header = substr( $result, 0, $header_size );
		$body = substr( $result, $header_size );
		$result = mn_unserialize( $body );
		error_log( $header, $url . '?' . $params . '&token_auth=...' );
	} else {
		$result = mn_unserialize( $result );
	}

	curl_close( $curl );

	return $result;
}

function mn_piwik_fopen( $url, $params ) {
	$debug = false;

	$context = stream_context_create( array( 'http'=>array( 'timeout' => 5 ) ) );
	$full_url = $url . '?' . $params . '&token_auth=' . PIWIK_AUTH_TOKEN;
	$result = mn_unserialize( @file_get_contents( $full_url, false, $context ) );

	if ( $debug ) {
		error_log( get_headers( $full_url, 1 ), $url . '?' . $params. '&token_auth=...' );
	}

	return $result;
}

function mn_unserialize( $str ) {
	return ( $str == serialize( false ) || @unserialize( $str ) !== false ) ? unserialize( $str ) : array();
}
