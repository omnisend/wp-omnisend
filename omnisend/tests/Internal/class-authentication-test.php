<?php
/**
 * Authentication Test
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Tests\Internal;

use Omnisend\Internal\Authentication;
use Omnisend\Internal\Options;
use WP_Error;

class Authentication_Test extends \WP_UnitTestCase {
    private $original_options = array();

    public function setUp(): void {
        parent::setUp();
        
        // Store original options
        $this->original_options = array(
            'api_key' => Options::get_api_key(),
            'oauth_client_id' => Options::get_oauth_client_id(),
            'oauth_client_secret' => Options::get_oauth_client_secret(),
            'oauth_access_token' => Options::get_oauth_access_token(),
            'oauth_refresh_token' => Options::get_oauth_refresh_token(),
            'oauth_token_expiry' => Options::get_oauth_token_expiry()
        );

        // Clear all options before each test
        Options::disconnect();
    }

    public function tearDown(): void {
        // Restore original options
        if (!empty($this->original_options['api_key'])) {
            Options::set_api_key($this->original_options['api_key']);
        }
        if (!empty($this->original_options['oauth_client_id'])) {
            Options::set_oauth_credentials(
                $this->original_options['oauth_client_id'],
                $this->original_options['oauth_client_secret']
            );
        }
        if (!empty($this->original_options['oauth_access_token'])) {
            Options::set_oauth_tokens(
                $this->original_options['oauth_access_token'],
                $this->original_options['oauth_refresh_token'],
                $this->original_options['oauth_token_expiry']
            );
        }

        parent::tearDown();
    }

    /**
     * Test get_auth_headers with OAuth token
     */
    public function test_get_auth_headers_with_valid_oauth() {
        // Set up valid OAuth tokens
        Options::set_oauth_credentials('test_client_id', 'test_client_secret');
        Options::set_oauth_tokens('test_access_token', 'test_refresh_token', time() + 3600);

        $headers = Authentication::get_auth_headers();
        
        $this->assertIsArray($headers);
        $this->assertEquals('Bearer test_access_token', $headers['Authorization']);
    }

    /**
     * Test get_auth_headers with expired OAuth token
     */
    public function test_get_auth_headers_with_expired_oauth() {
        // Set up expired OAuth tokens
        Options::set_oauth_credentials('test_client_id', 'test_client_secret');
        Options::set_oauth_tokens('test_access_token', 'test_refresh_token', time() - 3600);

        // Mock the refresh token response
        add_filter('pre_http_request', function($preempt, $args, $url) {
            if (strpos($url, '/token') !== false) {
                return array(
                    'response' => array('code' => 200),
                    'body' => json_encode(array(
                        'access_token' => 'new_access_token',
                        'refresh_token' => 'new_refresh_token',
                        'expires_in' => 3600
                    ))
                );
            }
            return $preempt;
        }, 10, 3);

        $headers = Authentication::get_auth_headers();
        
        $this->assertIsArray($headers);
        $this->assertEquals('Bearer new_access_token', $headers['Authorization']);
    }

    /**
     * Test get_auth_headers with API key fallback
     */
    public function test_get_auth_headers_with_api_key_fallback() {
        // Set up API key for existing user
        Options::set_api_key('test_api_key');

        $headers = Authentication::get_auth_headers();
        
        $this->assertIsArray($headers);
        $this->assertEquals('test_api_key', $headers['X-API-Key']);
    }

    /**
     * Test get_auth_headers with no authentication
     */
    public function test_get_auth_headers_with_no_auth() {
        $result = Authentication::get_auth_headers();
        
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('authentication_failed', $result->get_error_code());
    }

    /**
     * Test is_api_key_fallback_allowed
     */
    public function test_is_api_key_fallback_allowed() {
        // Initially should be false
        $this->assertFalse(Authentication::is_api_key_fallback_allowed());

        // Set API key for existing user
        Options::set_api_key('test_api_key');
        $this->assertTrue(Authentication::is_api_key_fallback_allowed());

        // Clear API key
        Options::disconnect();
        $this->assertFalse(Authentication::is_api_key_fallback_allowed());
    }

    /**
     * Test refresh_oauth_token with valid refresh token
     */
    public function test_refresh_oauth_token_success() {
        // Set up OAuth credentials
        Options::set_oauth_credentials('test_client_id', 'test_client_secret');

        // Mock the refresh token response
        add_filter('pre_http_request', function($preempt, $args, $url) {
            if (strpos($url, '/token') !== false) {
                return array(
                    'response' => array('code' => 200),
                    'body' => json_encode(array(
                        'access_token' => 'new_access_token',
                        'refresh_token' => 'new_refresh_token',
                        'expires_in' => 3600
                    ))
                );
            }
            return $preempt;
        }, 10, 3);

        $result = Authentication::refresh_oauth_token('test_refresh_token');
        
        $this->assertEquals('new_access_token', $result);
        $this->assertEquals('new_access_token', Options::get_oauth_access_token());
        $this->assertEquals('new_refresh_token', Options::get_oauth_refresh_token());
    }

    /**
     * Test refresh_oauth_token with missing credentials
     */
    public function test_refresh_oauth_token_missing_credentials() {
        $result = Authentication::refresh_oauth_token('test_refresh_token');
        
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('oauth_refresh_failed', $result->get_error_code());
    }

    /**
     * Test refresh_oauth_token with failed request
     */
    public function test_refresh_oauth_token_failed_request() {
        // Set up OAuth credentials
        Options::set_oauth_credentials('test_client_id', 'test_client_secret');

        // Mock failed response
        add_filter('pre_http_request', function($preempt, $args, $url) {
            if (strpos($url, '/token') !== false) {
                return array(
                    'response' => array('code' => 400),
                    'body' => json_encode(array(
                        'error' => 'invalid_grant',
                        'error_description' => 'Invalid refresh token'
                    ))
                );
            }
            return $preempt;
        }, 10, 3);

        $result = Authentication::refresh_oauth_token('test_refresh_token');
        
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('oauth_refresh_failed', $result->get_error_code());
    }
} 