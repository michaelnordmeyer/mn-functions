<?php
/* https://wordpress.org/plugins/simple-xml-sitemap-generator/ */

function mn_create_sitemap() {
  $pagesForSitemap = get_posts( array(
    'numberposts'   => -1,
    'orderby'       => 'modified',
    'post_type'     => array( 'page' ),
    'order'         => 'DESC'
  ));

  $postsForSitemap = get_posts( array(
    'numberposts'   => -1,
    'orderby'       => 'modified',
    'post_type'     => array( 'post' ),
    'order'         => 'DESC'
  ));

  $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
  $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

  $sitemap .= mn_create_sitemap_xml( $pagesForSitemap, "weekly", "0.8" );
  $sitemap .= mn_create_sitemap_xml( $postsForSitemap, "monthly", "0.6" );

  $sitemap .= '</urlset>';

  $file = fopen( ABSPATH . "sitemap.xml", 'w' );
  fwrite( $file, $sitemap );
  fclose( $file );
}

function mn_create_sitemap_xml( $posts, $change_frequency, $priority ) {
  $sitemap = '';

  foreach ( $posts as $post ) {
    setup_postdata( $post );

    $postdate = explode( " ", $post->post_modified );

    $sitemap .= '<url>'.
        '<loc>'. get_permalink( $post->ID ) .'</loc>'.
        "<lastmod>$postdate[0]</lastmod>".
        "<changefreq>$change_frequency</changefreq>".
        $priority ? "<priority>$priority</priority>" : ''.
        '</url>';
  }

  return $sitemap;
}

add_action( "publish_post", "mn_create_sitemap" );
add_action( "publish_page", "mn_create_sitemap" );
mn_create_sitemap();
