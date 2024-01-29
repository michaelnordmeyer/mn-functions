<?php

// WP-Piwik Replacement ///////////////////////////////////////////////////////

// Make sure it's WordPress
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


// General Registration ///////////////////////////////////////////////////////

add_action( 'wp_footer', 'mn_add_piwik_tracking', 99 );
add_filter( 'the_content_feed', 'mn_add_piwik_feed_tracking', 99 );
add_filter( 'the_excerpt_rss', 'mn_add_piwik_feed_tracking' );

function mn_add_piwik_tracking() {
	if ( ! is_user_logged_in() && ! current_user_can( 'administrator' ) && ! current_user_can( 'editor' ) && ! current_user_can( 'author' ) && ! current_user_can( 'contributor' ) ) {
		$mn_piwik_url = PIWIK_URL;
    $mn_piwik_site_id = PIWIK_SITE_ID;

		$push_variables = "";

		if ( is_search() ) {
			$search_query = get_search_query();
			$search_results = new WP_Query( "s={$search_query}&showposts=-1" );
			$push_variables .= "_paq.push(['trackSiteSearch', '{$search_query}', false, " . $search_results->post_count . "]);";
		}

		if ( is_404() ) {
			$push_variables .= "_paq.push(['setDocumentTitle', '404/URL = ' + String(document.location.pathname + document.location.search).replace(/\//g,'%2f') + '/From = ' + String(document.referrer).replace(/\//g,'%2f')]);";
		}

		echo <<<EOL
<script type="text/javascript">
  var _paq = _paq || [];
  $push_variables
  _paq.push(['trackPageView']);
  _paq.push(['enableHeartBeatTimer', 2]);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="$mn_piwik_url";
    _paq.push(['setTrackerUrl', u+'p.php']);
    _paq.push(['setSiteId', $mn_piwik_site_id]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'p.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
EOL;
	}
}


function mn_add_piwik_feed_tracking( $content ) {
	if ( is_feed () ) {
		$title = the_title( null, null, false );
		$post_url = get_permalink( get_the_ID() );
		$url_ref = get_bloginfo( 'rss2_url' );
		$tracking_image_url = PIWIK_URL . "p.php?idsite=" . PIWIK_SITE_ID . "&amp;rec=1&amp;url=" . urlencode( $post_url ) . '&amp;action_name=' . urlencode( $title ) . '&amp;urlref=' . urlencode( $url_ref ) . '&amp;send_image=0';
		$content .= '<img src="' . $tracking_image_url . '" style="border:0;width:0;height:0" width="0" height="0" alt="" />';
	}

	return $content;
}
