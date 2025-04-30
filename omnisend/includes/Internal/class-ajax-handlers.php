<?php
/**
 * AJAX Handlers for Omnisend plugin
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

defined( 'ABSPATH' ) || die( 'no direct access' );

class AjaxHandlers {
    /**
     * Initialize AJAX handlers
     */
    public static function init() {
        add_action('wp_ajax_omnisend_register_oauth_client', [self::class, 'register_oauth_client']);
        add_action('wp_ajax_nopriv_omnisend_register_oauth_client', [self::class, 'unauthorized']);
    }

    /**
     * Handle AJAX request to register OAuth client
     */
    public static function register_oauth_client() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'omnisend_oauth')) {
            wp_send_json_error([
                'message' => 'Security check failed.'
            ]);
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => 'You do not have permission to perform this action.'
            ]);
        }

        // Register OAuth client
        $result = OAuthClient::register_client();

        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message()
            ]);
        }

        // Get authorization URL
        $auth_url = OAuthClient::get_authorization_url();

        wp_send_json_success([
            'auth_url' => $auth_url
        ]);
    }

    /**
     * Handle unauthorized AJAX requests
     */
    public static function unauthorized() {
        wp_send_json_error([
            'message' => 'You must be logged in to perform this action.'
        ]);
    }
}