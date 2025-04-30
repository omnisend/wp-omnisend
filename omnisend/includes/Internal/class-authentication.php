<?php
/**
 * Authentication Handler
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

use WP_Error;

defined( 'ABSPATH' ) || die( 'no direct access' );

class Authentication {
    /**
     * Get authentication headers
     *
     * @return array|WP_Error Authentication headers or WP_Error if no valid authentication is available
     */
    public static function get_auth_headers() {
        // Try OAuth first
        $access_token = Options::get_oauth_access_token();
        
        if (!empty($access_token)) {
            // Check if token is expired
            $expiry = Options::get_oauth_token_expiry();
            if ($expiry && time() < $expiry) {
                return array(
                    'Authorization' => 'Bearer ' . $access_token
                );
            }

            // Token expired, try to refresh
            $refresh_token = Options::get_oauth_refresh_token();
            if ($refresh_token) {
                $new_token = self::refresh_oauth_token($refresh_token);
                if (!is_wp_error($new_token)) {
                    return array(
                        'Authorization' => 'Bearer ' . $new_token
                    );
                }
            }
        }

        // For existing users with API key, allow fallback
        $api_key = Options::get_api_key();
        if (!empty($api_key)) {
            return array(
                'X-API-Key' => $api_key
            );
        }

        return new WP_Error(
            'authentication_failed',
            'Please connect your store using OAuth authentication.'
        );
    }

    /**
     * Check if API key fallback is allowed
     * This is for existing users who have already connected with API key
     */
    public static function is_api_key_fallback_allowed() {
        return !empty(Options::get_api_key());
    }

    /**
     * Refresh OAuth token
     *
     * @param string $refresh_token
     * @return string|WP_Error New access token or WP_Error if refresh failed
     */
    private static function refresh_oauth_token($refresh_token) {
        $client_id = Options::get_oauth_client_id();
        $client_secret = Options::get_oauth_client_secret();

        if (empty($client_id) || empty($client_secret)) {
            return new WP_Error(
                'oauth_refresh_failed',
                'Missing OAuth client credentials'
            );
        }

        $response = wp_remote_post(
            OMNISEND_AUTH_URL . '/token',
            array(
                'body' => array(
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refresh_token,
                    'client_id' => $client_id,
                    'client_secret' => $client_secret
                ),
                'timeout' => 30
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $code = wp_remote_retrieve_response_code($response);

        if ($code !== 200 || empty($body['access_token'])) {
            return new WP_Error(
                'oauth_refresh_failed',
                'Failed to refresh OAuth token: ' . ($body['error_description'] ?? 'Unknown error')
            );
        }

        // Save new tokens
        Options::set_oauth_tokens(
            $body['access_token'],
            $body['refresh_token'] ?? $refresh_token,
            time() + ($body['expires_in'] ?? 3600)
        );

        return $body['access_token'];
    }
} 