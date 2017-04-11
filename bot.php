<?php

spl_autoload_register(function ( $class_name ) {
    $class_file_name = strtolower( str_replace( '_', '-', $class_name ) ) . '-class';
    include 'app/' . $class_file_name . '.php';
});

$bot_curl = new Bot_Curl( 'https://profiles.wordpress.org/themeisle/' );

echo 'HTTP Status: ' . $bot_curl->get_http_code() . PHP_EOL;

var_dump( $bot_curl->get_plugin_list() );
var_dump( $bot_curl->get_themes_list() );