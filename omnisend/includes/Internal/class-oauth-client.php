<?php
/**
 * OAuth Client Registration Handler
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

defined( 'ABSPATH' ) || die( 'no direct access' );

class OAuthClient {
    /**
     * Register a new OAuth client with Omnisend
     *
     * @return array|WP_Error
     */
    public static function register_client() {
        $registration_endpoint = OMNISEND_CORE_API_V3 . '/oauth/register';
        
        $registration_data = array(
            'client_name' => 'WordPress Omnisend Plugin',
            'redirect_uris' => array(admin_url('admin.php?page=omnisend&oauth_callback=1')),
            'token_endpoint_auth_method' => 'client_secret_basic',
            'grant_types' => array('authorization_code', 'refresh_token'),
            'scope' => 'contacts events products',
            'software_id' => 'omnisend',
            'software_version' => OMNISEND_CORE_PLUGIN_VERSION
        );

        $response = wp_remote_post($registration_endpoint, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode($registration_data),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['client_id']) && isset($body['client_secret'])) {
            Options::set_oauth_credentials(array(
                'client_id' => $body['client_id'],
                'client_secret' => $body['client_secret']
            ));
            return $body;
        }

        return new \WP_Error('registration_failed', 'Failed to register OAuth client');
    }

    /**
     * Get authorization URL for OAuth flow
     *
     * @return string
     */
    public static function get_authorization_url() {
        $client_id = Options::get_oauth_client_id();
        $redirect_uri = urlencode(admin_url('admin.php?page=omnisend&oauth_callback=1'));
        
        return OMNISEND_CORE_API_V3 . '/oauth/authorize?' . http_build_query(array(
            'response_type' => 'code',
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'scope' => 'contacts events products'
        ));
    }

    /**
     * Exchange authorization code for access token
     *
     * @param string $code
     * @return array|WP_Error
     */
    public static function get_access_token($code) {
        $token_endpoint = OMNISEND_CORE_API_V3 . '/oauth/token';
        $client_id = Options::get_oauth_client_id();
        $client_secret = Options::get_oauth_client_secret();
        
        $response = wp_remote_post($token_endpoint, array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret)
            ),
            'body' => array(
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => admin_url('admin.php?page=omnisend&oauth_callback=1')
            ),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['access_token'])) {
            Options::set_oauth_tokens(array(
                'access_token' => $body['access_token'],
                'refresh_token' => $body['refresh_token'],
                'expires_in' => $body['expires_in']
            ));
            return $body;
        }

        return new \WP_Error('token_failed', 'Failed to get access token');
    }

    /**
     * Refresh access token using refresh token
     *
     * @return array|WP_Error
     */
    public static function refresh_access_token() {
        $token_endpoint = OMNISEND_CORE_API_V3 . '/oauth/token';
        $client_id = Options::get_oauth_client_id();
        $client_secret = Options::get_oauth_client_secret();
        $refresh_token = Options::get_oauth_refresh_token();
        
        $response = wp_remote_post($token_endpoint, array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret)
            ),
            'body' => array(
                'grant_type' => 'refresh_token',
                'refresh_token' => $refresh_token
            ),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['access_token'])) {
            Options::set_oauth_tokens(array(
                'access_token' => $body['access_token'],
                'refresh_token' => $body['refresh_token'],
                'expires_in' => $body['expires_in']
            ));
            return $body;
        }

        return new \WP_Error('refresh_failed', 'Failed to refresh access token');
    }

    /**
     * Get current access token, refreshing if needed
     *
     * @return string|WP_Error
     */
    public static function get_valid_access_token() {
        $access_token = Options::get_oauth_access_token();
        $expiry = Options::get_oauth_token_expiry();
        
        if (!$access_token || !$expiry) {
            return new \WP_Error('no_token', 'No access token available');
        }

        if (time() >= $expiry - 60) { // Refresh if less than 1 minute until expiry
            $result = self::refresh_access_token();
            if (is_wp_error($result)) {
                return $result;
            }
            $access_token = Options::get_oauth_access_token();
        }

        return $access_token;
    }
} 