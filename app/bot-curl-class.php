<?php

class Bot_Curl {

    private $url;

    public function __construct( $url ) {
        require_once 'vendors/simple-html-dom.php';
        $this->url = $url;
    }

    protected function get_html() {
        $html = file_get_html( $this->url );
        return $html;
    }

    private function strip_url_protocol( $url ) {
        $url = str_replace('http://', '', $url );
        $url = str_replace('https://', '', $url );
        $url = str_replace('//', '', $url );
        return $url;
    }

    private function extract_slug( $url ) {
        $url = rtrim( $url, '/' );
        $parts = explode('/', $url);
        $slug = $parts[ sizeof($parts) - 1 ];
        return $slug;
    }

    public function get_plugin_list() {
        $html = $this->get_html();

        $elements = $html->find('#content-plugins ul li div h3 a');
        $links_map = array();
        foreach ( $elements as $elem ) {
            if( isset( $elem->href ) ) {
                $url = $this->strip_url_protocol( $elem->href );
                $slug = $this->extract_slug( $url );
                $links_map[] = array(
                            'support' => 'https://wordpress.org/support/plugin/' . $slug . '/',
                            'reviews' => 'https://wordpress.org/support/plugin/' . $slug . '/reviews/'
                );
            }
        }

        return $links_map;
    }

    public function get_themes_list() {
        $html = $this->get_html();

        $elements = $html->find('#content-themes ul li h3 a');
        $links_map = array();
        foreach ( $elements as $elem ) {
            if( isset( $elem->href ) ) {
                $url = $this->strip_url_protocol( $elem->href );
                $slug = $this->extract_slug( $url );
                $links_map[] = array(
                            'support' => 'https://wordpress.org/support/theme/' . $slug . '/',
                            'reviews' => 'https://wordpress.org/support/theme/' . $slug . '/reviews/'
                );
            }
        }

        return $links_map;
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