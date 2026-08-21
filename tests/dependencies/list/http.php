<?php

function wp_remote_get( $url, $args = array() ) {
	return $GLOBALS['omnisend_test_http_response'] ?? array();
}

function wp_remote_retrieve_body( $response ) {
	return $response['body'] ?? '';
}

function wp_remote_retrieve_response_code( $response ) {
	return $response['response']['code'] ?? 0;
}

function is_wp_error( $thing ) {
	return $thing instanceof WP_Error;
}

function wp_parse_url( $url, $component = -1 ) {
	return parse_url( $url, $component );
}
