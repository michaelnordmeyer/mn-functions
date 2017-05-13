<?php

// Yoast SEO Replacement Admin ////////////////////////////////////////////////

// Make sure it's WordPress
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


//add_filter( 'user_contactmethods', 'mn_update_contactmethods', 10, 1 );
add_filter( 'name_save_pre', 'mn_remove_stopwords_from_slug', 0 );


/**
 * Filter the $contactmethods array and add Facebook, Google+ and Twitter.
 *
 * These are used with the Facebook author, rel="author" and Twitter cards implementation.
 *
 * @param array $contactmethods currently set contactmethods.
 *
 * @return array $contactmethods with added contactmethods.
 */
function mn_update_contactmethods( $contactmethods ) {
	// Add Facebook
	$contactmethods['facebook'] = __( 'Facebook profile URL', 'wordpress-seo' );
	// Add Github
	$contactmethods['github'] = __( 'Github', 'wordpress-seo' );
	// Add LinkedIn
	$contactmethods['linkedin'] = __( 'LinkedIn', 'wordpress-seo' );
	// Add Twitter
	$contactmethods['twitter'] = __( 'Twitter username (without @)', 'wordpress-seo' );

	return $contactmethods;
}


/**
 * Cleans stopwords out of the slug, if the slug hasn't been set yet.
 *
 * @since 1.1.7
 *
 * @param string $slug if this isn't empty, the function will return an unaltered slug.
 *
 * @return string $clean_slug cleaned slug
 */
function mn_remove_stopwords_from_slug( $slug ) {
//	error_log("-->name_save_pre: [$slug]<--");
	// Don't change an existing slug
	if ( isset( $slug ) && $slug !== '' ) {
		return $slug;
	}

    $post_title = filter_input( INPUT_POST, 'post_title' );
	// When the post title is empty, just return the slug.
	if ( empty( $post_title ) ) {
		return $slug;
	}

	// Don't change the slug if this is a multisite installation and the site has been switched.
	if ( is_multisite() && ms_is_switched() ) {
		return $slug;
	}

	// Don't change slug if the post is a draft, this conflicts with polylang
	// Doesn't work with filter_input() since need current value, not originally submitted one.
    // if ( 'draft' === $_POST['post_status'] ) {
    //     return $slug;
    // }

	// Lowercase the slug and strip slashes
	$clean_slug = sanitize_title( stripslashes( $post_title ) );

	// Turn it to an array and strip stopwords by comparing against an array of stopwords
	$clean_slug_array = array_diff( explode( '-', $clean_slug ), mn_stopwords() );

	// Don't change the slug if there are less than 3 words left.
    // if ( count( $clean_slug_array ) < 3 ) {
    //     return $clean_slug;
    // }

	// Turn the sanitized array into a string
	$clean_slug = join( '-', $clean_slug_array );

	return $clean_slug;
}


/**
 * Returns the stopwords for the current language
 *
 * @since 1.1.7
 *
 * @return array $stopwords array of stop words to check and / or remove from slug
 */
function mn_stopwords() {
	/* translators: this should be an array of stopwords for your language, separated by comma's. */
	$stopwords = explode( ',', __( "a,about,above,after,again,against,all,am,an,and,any,are,as,at,be,because,been,before,being,below,between,both,but,by,could,did,do,does,doing,down,during,each,few,for,from,further,had,has,have,having,he,he'd,he'll,he's,her,here,here's,hers,herself,him,himself,his,how,how's,i,i'd,i'll,i'm,i've,if,in,into,is,it,it's,its,itself,let's,me,more,most,my,myself,nor,of,on,once,only,or,other,ought,our,ours,ourselves,out,over,own,same,she,she'd,she'll,she's,should,so,some,such,than,that,that's,the,their,theirs,them,themselves,then,there,there's,these,they,they'd,they'll,they're,they've,this,those,through,to,too,under,until,up,very,was,we,we'd,we'll,we're,we've,were,what,what's,when,when's,where,where's,which,while,who,who's,whom,why,why's,with,would,you,you'd,you'll,you're,you've,your,yours,yourself,yourselves", 'wordpress-seo' ) );

	/**
	 * Allows filtering of the stop words list
	 * Especially useful for users on a language in which WPSEO is not available yet
	 * and/or users who want to turn off stop word filtering
     *
	 * @api  array  $stopwords  Array of all lowercase stopwords to check and/or remove from slug
	 */
	$stopwords = apply_filters( 'mn_filter_stopwords', $stopwords );

	return $stopwords;
}


/**
 * Check whether the stopword appears in the string
 *
 * @param string $haystack    The string to be checked for the stopword
 * @param bool   $checkingUrl Whether or not we're checking a URL
 *
 * @return bool|mixed
 */
function mn_stopwords_check( $haystack, $checkingUrl = false ) {
	$stopWords = mn_stopwords();

	if ( is_array( $stopWords ) && $stopWords !== array() ) {
		foreach ( $stopWords as $stopWord ) {
			// If checking a URL remove the single quotes
			if ( $checkingUrl ) {
				$stopWord = str_replace( "'", '', $stopWord );
			}

			// Check whether the stopword appears as a whole word
			// @todo [JRF => whomever] check whether the use of \b (=word boundary) would be more efficient ;-)
			$res = preg_match( "`(^|[ \n\r\t\.,'\(\)\"\+;!?:])" . preg_quote( $stopWord, '`' ) . "($|[ \n\r\t\.,'\(\)\"\+;!?:])`iu", $haystack );
			if ( $res > 0 ) {
				return $stopWord;
			}
		}
	}

	return false;
}
