<?php
/**
 * The RSS Bot Hackaton
 * Checks for new tickets and reviews on WP.org
 *
 * @author  Bogdan Preda
 * @version 1.0.0
 * @package rss_bot_hack
 */


spl_autoload_register(function ( $class_name ) {
    $class_file_name = strtolower( str_replace( '_', '-', $class_name ) ) . '-class';
    include 'app/' . $class_file_name . '.php';
});

$data = new Bot_Data( 'db' );
$db_data = $data->get_data();

$arhive = new Bot_Data( 'history' );
$history = $arhive->get_data();


$bot_curl = new Bot_Curl( 'https://profiles.wordpress.org/themeisle/' );

echo 'HTTP Status: ' . $bot_curl->get_http_code() . PHP_EOL;

$plugins = $bot_curl->get_plugin_list();
$themes =  $bot_curl->get_themes_list();

$plugins_data = array();
$themes_data = array();
foreach ( $plugins as $plugin ) {
    $topics_support =  $bot_curl->xml_feed( $plugin['support'] );
    $topics_reviews =  $bot_curl->xml_feed( $plugin['reviews'] );

    if( isset( $db_data['plugins'][ $plugin['slug'] ]['count'] ) ) {
        $diff = $db_data['plugins'][ $plugin['slug'] ]['count'] - sizeof( $topics_support );
    } else {
        $diff = sizeof( $topics_support );
    }

    if ( isset( $history['plugins'][ $plugin['slug'] ] ) ) {
        $next = sizeof( $history['plugins'][ $plugin['slug'] ] );
    } else {
        $next = 0;
    }
    $replies_total = 0;
    foreach ( $topics_support as $tmp ) {
        $replies_total += $tmp['replies'];
    }

    $replies_total_rev = 0;
    foreach ( $topics_reviews as $tmp ) {
        $replies_total_rev += $tmp['replies'];
    }

    $history['plugins'][ $plugin['slug'] ][$next]['support']['total'] = sizeof( $topics_support );
    $history['plugins'][ $plugin['slug'] ][$next]['support']['replies'] = $replies_total;
    $history['plugins'][ $plugin['slug'] ][$next]['reviews']['total'] = sizeof( $topics_reviews );
    $history['plugins'][ $plugin['slug'] ][$next]['reviews']['replies'] = $replies_total;
    $plugins_data[ $plugin['slug'] ] = array(
        'support' => array( 'count' => sizeof( $topics_support ), 'data' => $topics_support ),
        'reviews' => array( 'count' => sizeof( $topics_reviews ), 'data' => $topics_reviews ),
    );
}

foreach ( $themes as $theme ) {
    $topics_support =  $bot_curl->xml_feed( $theme['support'] );
    $topics_reviews =  $bot_curl->xml_feed( $theme['reviews'] );

    if( isset( $db_data['themes'][ $theme['slug'] ]['count'] ) ) {
        $diff = $db_data['themes'][ $theme['slug'] ]['count'] - sizeof( $topics_support );
    } else {
        $diff = sizeof( $topics_support );
    }

    if ( isset( $history['themes'][ $theme['slug'] ] ) ) {
        $next = sizeof( $history['themes'][ $theme['slug'] ] );
    } else {
        $next = 0;
    }
    $replies_total = 0;
    foreach ( $topics_support as $tmp ) {
        $replies_total += $tmp['replies'];
    }

    $replies_total_rev = 0;
    foreach ( $topics_reviews as $tmp ) {
        $replies_total_rev += $tmp['replies'];
    }

    $history['themes'][ $theme['slug'] ][$next]['support']['total'] = sizeof( $topics_support );
    $history['themes'][ $theme['slug'] ][$next]['support']['replies'] = $replies_total;
    $history['themes'][ $theme['slug'] ][$next]['reviews']['total'] = sizeof( $topics_reviews );
    $history['themes'][ $theme['slug'] ][$next]['reviews']['replies'] = $replies_total;
    $themes_data[ $theme['slug'] ] = array(
        'support' => array( 'count' => sizeof( $topics_support ), 'data' => $topics_support ),
        'reviews' => array( 'count' => sizeof( $topics_reviews ), 'data' => $topics_reviews ),
    );
}

$save = array(
    'plugins' => $plugins_data,
    'themes' => $themes_data,
);

$data = new Bot_Data( 'db' );
$data->store_data( $save );

$data = new Bot_Data( 'history' );
$data->store_data( $history );

