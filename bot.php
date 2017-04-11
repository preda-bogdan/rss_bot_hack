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

    $sum_1 = 0;
    $sum_2 = 0;
    $sum_3 = 0;
    $sum_4 = 0;
    if( $next != 0 ) {
        for( $j = 0; $j <= $next; $j++ ) {
            $sum_1 += $history['plugins'][ $plugin['slug'] ][$j]['support']['total'];
            $sum_2 += $history['plugins'][ $plugin['slug'] ][$j]['support']['replies'];
            $sum_3 += $history['plugins'][ $plugin['slug'] ][$j]['reviews']['total'];
            $sum_4 += $history['plugins'][ $plugin['slug'] ][$j]['reviews']['replies'];
        }
        $count = $j;
    } else {
        $sum_1 += $history['plugins'][ $plugin['slug'] ][$next]['support']['total'];
        $sum_2 += $history['plugins'][ $plugin['slug'] ][$next]['support']['replies'];
        $sum_3 += $history['plugins'][ $plugin['slug'] ][$next]['reviews']['total'];
        $sum_4 += $history['plugins'][ $plugin['slug'] ][$next]['reviews']['replies'];
        $count = 1;
    }

    $v1 = ($sum_1/$count);
    $v2 = ($sum_1 - $history['plugins'][ $plugin['slug'] ][$next]['support']['total'] );
    $proc_dif_1 = ($v1 - $v2) / ( ($v1+$v2/2) ) * 100;

    $v1 = ($sum_2/$count);
    $v2 = ($sum_2 - $history['plugins'][ $plugin['slug'] ][$next]['support']['replies'] );
    $proc_dif_2 = ($v1 - $v2) / ( ($v1+$v2/2) ) * 100;

    $v1 = ($sum_3/$count);
    $v2 = ($sum_3 - $history['plugins'][ $plugin['slug'] ][$next]['reviews']['total'] );
    $proc_dif_3 = ($v1 - $v2) / ( ($v1+$v2/2) ) * 100;

    $v1 = ($sum_4/$count);
    $v2 = ($sum_4 - $history['plugins'][ $plugin['slug'] ][$next]['reviews']['replies'] );
    $proc_dif_4 = ($v1 - $v2) / ( ($v1+$v2/2) ) * 100;

    if( $proc_dif_1 >= 5 ) {
        mail("bogdan.preda@themeisle.com","Plugin: " . $plugin['slug'] . ' support tickets! ' , $proc_dif_1);
    }
    if( $proc_dif_2 >= 5 ) {
        mail("bogdan.preda@themeisle.com","Plugin: " . $plugin['slug'] . ' support replies! ' , $proc_dif_2);
    }

    if( $proc_dif_3 >= 5 ) {
        mail("bogdan.preda@themeisle.com","Plugin: " . $plugin['slug'] . ' reviews tickets! ' , $proc_dif_3);
    }

    if( $proc_dif_4 >= 5 ) {
        mail("bogdan.preda@themeisle.com","Plugin: " . $plugin['slug'] . ' reviews replies! ' , $proc_dif_4);
    }

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

    $sum_1 = 0;
    $sum_2 = 0;
    $sum_3 = 0;
    $sum_4 = 0;
    if( $next != 0 ) {
        for( $j = 0; $j <= $next; $j++ ) {
            $sum_1 += $history['themes'][ $theme['slug'] ][$j]['support']['total'];
            $sum_2 += $history['themes'][ $theme['slug'] ][$j]['support']['replies'];
            $sum_3 += $history['themes'][ $theme['slug'] ][$j]['reviews']['total'];
            $sum_4 += $history['themes'][ $theme['slug'] ][$j]['reviews']['replies'];
        }
        $count = $j;
    } else {
        $sum_1 += $history['themes'][ $theme['slug'] ][$next]['support']['total'];
        $sum_2 += $history['themes'][ $theme['slug'] ][$next]['support']['replies'];
        $sum_3 += $history['themes'][ $theme['slug'] ][$next]['reviews']['total'];
        $sum_4 += $history['themes'][ $theme['slug'] ][$next]['reviews']['replies'];
        $count = 1;
    }

    $v1 = ($sum_1/$count);
    $v2 = ($sum_1 - $history['themes'][ $theme['slug'] ][$next]['support']['total'] );
    $proc_dif_1 = ($v1 - $v2) / ( ($v1+$v2/2) ) * 100;

    $v1 = ($sum_2/$count);
    $v2 = ($sum_2 - $history['themes'][ $theme['slug'] ][$next]['support']['replies'] );
    $proc_dif_2 = ($v1 - $v2) / ( ($v1+$v2/2) ) * 100;

    $v1 = ($sum_3/$count);
    $v2 = ($sum_3 - $history['themes'][ $theme['slug'] ][$next]['reviews']['total'] );
    $proc_dif_3 = ($v1 - $v2) / ( ($v1+$v2/2) ) * 100;

    $v1 = ($sum_4/$count);
    $v2 = ($sum_4 - $history['themes'][ $theme['slug'] ][$next]['reviews']['replies'] );
    $proc_dif_4 = ($v1 - $v2) / ( ($v1+$v2/2) ) * 100;

    if( $proc_dif_1 >= 5 ) {
        mail("bogdan.preda@themeisle.com","Theme: " . $theme['slug'] . ' support tickets! ' , $proc_dif_1);
    }
    if( $proc_dif_2 >= 5 ) {
        mail("bogdan.preda@themeisle.com","Theme: " . $theme['slug'] . ' support replies! ' , $proc_dif_2);
    }

    if( $proc_dif_3 >= 5 ) {
        mail("bogdan.preda@themeisle.com","Theme: " . $theme['slug'] . ' reviews tickets! ' , $proc_dif_3);
    }

    if( $proc_dif_4 >= 5 ) {
        mail("bogdan.preda@themeisle.com","Theme: " . $theme['slug'] . ' reviews replies! ' , $proc_dif_4);
    }

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

