<?php

class Bot_Curl {

    private $url;

    public function __construct( $url ) {
        require_once 'vendors/simple-html-dom.php';
        $this->url = $url;
    }

    public function get_html() {
        $html = file_get_html( $this->url );
        return $html;
    }

    public function get_http_code() {
        $ch = curl_init( $this->url );
        curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
        curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT,10);
        $output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpcode;
    }
}