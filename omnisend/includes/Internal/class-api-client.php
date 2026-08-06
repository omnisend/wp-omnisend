<?php
/**
 * API Client Handler
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

defined( 'ABSPATH' ) || die( 'no direct access' );

class ApiClient {
    private static $plugin_name = 'wp-omnisend';
    private static $plugin_version = '1.0.0';

    /**
     * Make API request
     *
     * @param string $endpoint
     * @param array $args
     * @return array|WP_Error
     */
    public static function request($endpoint, $args = array()) {
        // Get authentication headers
        $auth_headers = Authentication::get_auth_headers();
        if (is_wp_error($auth_headers)) {
            return $auth_headers;
        }

        $defaults = array(
            'method' => 'GET',
            'headers' => array_merge(
                array(
                    'Content-Type' => 'application/json',
                    'X-INTEGRATION-NAME' => self::$plugin_name,
                    'X-INTEGRATION-VERSION' => self::$plugin_version
                ),
                $auth_headers
            ),
            'timeout' => 30
        );

        $args = wp_parse_args($args, $defaults);
        $url = OMNISEND_CORE_API_V3 . $endpoint;

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);

        if ($code >= 400) {
            $err_msg = "HTTP error: {$code} - " . wp_remote_retrieve_response_message($response);
            if ($body) {
                $err_msg .= " - {$body}";
            }
            return new \WP_Error('api_error', $err_msg, array('status' => $code));
        }

        if (!$body) {
            return new \WP_Error('api_error', 'Empty response from API');
        }

        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new \WP_Error('api_error', 'Invalid JSON response: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Get contacts
     *
     * @param array $params
     * @return array|WP_Error
     */
    public static function get_contacts($params = array()) {
        $endpoint = '/contacts';
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }
        return self::request($endpoint);
    }

    /**
     * Create or update contact
     *
     * @param array $contact
     * @return array|WP_Error
     */
    public static function create_or_update_contact($contact) {
        return self::request('/contacts', array(
            'method' => 'POST',
            'body' => wp_json_encode($contact)
        ));
    }

    /**
     * Get contact
     *
     * @param string $contact_id
     * @return array|WP_Error
     */
    public static function get_contact($contact_id) {
        return self::request('/contacts/' . $contact_id);
    }

    /**
     * Update contact
     *
     * @param string $contact_id
     * @param array $contact
     * @return array|WP_Error
     */
    public static function update_contact($contact_id, $contact) {
        return self::request('/contacts/' . $contact_id, array(
            'method' => 'PATCH',
            'body' => wp_json_encode($contact)
        ));
    }

    /**
     * Send event
     *
     * @param array $event
     * @return array|WP_Error
     */
    public static function send_event($event) {
        return self::request('/events', array(
            'method' => 'POST',
            'body' => wp_json_encode($event)
        ));
    }

    /**
     * Get products
     *
     * @param array $params
     * @return array|WP_Error
     */
    public static function get_products($params = array()) {
        $endpoint = '/products';
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }
        return self::request($endpoint);
    }

    /**
     * Create product
     *
     * @param array $product
     * @return array|WP_Error
     */
    public static function create_product($product) {
        return self::request('/products', array(
            'method' => 'POST',
            'body' => wp_json_encode($product)
        ));
    }

    /**
     * Delete product
     *
     * @param string $product_id
     * @return array|WP_Error
     */
    public static function delete_product($product_id) {
        return self::request('/products/' . $product_id, array(
            'method' => 'DELETE'
        ));
    }

    /**
     * Get product
     *
     * @param string $product_id
     * @return array|WP_Error
     */
    public static function get_product($product_id) {
        return self::request('/products/' . $product_id);
    }

    /**
     * Replace product
     *
     * @param string $product_id
     * @param array $product
     * @return array|WP_Error
     */
    public static function replace_product($product_id, $product) {
        return self::request('/products/' . $product_id, array(
            'method' => 'PUT',
            'body' => wp_json_encode($product)
        ));
    }
} 