<?php
namespace Omnisend\Internal;

use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_Http_Test_Stub;

require_once( __DIR__ . '/../../dependencies/dependencies.php' );

final class ApiResponseTest extends TestCase
{
    public function test_transport_error_is_returned_as_is(): void
    {
        $transport_error = new WP_Error('http_request_failed', 'cURL error 28: timeout');

        $result = ApiResponse::parse($transport_error);

        $this->assertSame($transport_error, $result);
    }

    public function test_missing_status_is_an_error(): void
    {
        $result = ApiResponse::parse(array('response' => array('code' => 0), 'body' => ''));

        $this->assertTrue(is_wp_error($result));
        $this->assertEquals(ApiResponse::ERROR_TRANSPORT, $result->get_error_code());
    }

    public function test_empty_status_is_an_error(): void
    {
        $result = ApiResponse::parse(array('body' => '{}'));

        $this->assertTrue(is_wp_error($result));
        $this->assertEquals(ApiResponse::ERROR_TRANSPORT, $result->get_error_code());
    }

    public function test_redirect_is_an_error(): void
    {
        $result = ApiResponse::parse(WP_Http_Test_Stub::response(302, '', 'Found'));

        $this->assertTrue(is_wp_error($result));
        $this->assertEquals(ApiResponse::ERROR_HTTP, $result->get_error_code());
        $this->assertStringContainsString('HTTP error: 302', $result->get_error_message());
    }

    /**
     * @dataProvider status_code_provider
     */
    public function test_status_maps_to_error_code(int $status, string $expected_code): void
    {
        $result = ApiResponse::parse(WP_Http_Test_Stub::response($status, '{}'));

        $this->assertTrue(is_wp_error($result));
        $this->assertEquals($expected_code, $result->get_error_code());
    }

    public static function status_code_provider(): array
    {
        return array(
            array(400, ApiResponse::ERROR_HTTP),
            array(401, ApiResponse::ERROR_UNAUTHORIZED),
            array(403, ApiResponse::ERROR_FORBIDDEN),
            array(404, ApiResponse::ERROR_NOT_FOUND),
            array(409, ApiResponse::ERROR_CONFLICT),
            array(410, ApiResponse::ERROR_VERSION_RETIRED),
            array(429, ApiResponse::ERROR_RATE_LIMITED),
            array(500, ApiResponse::ERROR_SERVER),
            array(503, ApiResponse::ERROR_SERVER),
        );
    }

    public function test_problem_details_are_preserved(): void
    {
        $body = json_encode(array(
            'type' => 'https://problems.omnisend.com/forbidden',
            'title' => 'Forbidden',
            'status' => 403,
            'detail' => 'Missing brands.read scope.',
            'instance' => 'urn:omnisend:request:550e8400',
            'errors' => array(
                array('field' => 'email', 'code' => 'invalid', 'message' => 'Email is invalid'),
            ),
        ));

        $result = ApiResponse::parse(WP_Http_Test_Stub::response(403, $body, 'Forbidden'));

        $this->assertEquals(ApiResponse::ERROR_FORBIDDEN, $result->get_error_code());
        $this->assertStringContainsString('Forbidden', $result->get_error_message());
        $this->assertStringContainsString('Missing brands.read scope.', $result->get_error_message());
        $this->assertStringContainsString('email: invalid Email is invalid', $result->get_error_message());

        $data = $result->get_error_data(ApiResponse::ERROR_FORBIDDEN);
        $this->assertEquals('https://problems.omnisend.com/forbidden', $data['type']);
        $this->assertEquals('Forbidden', $data['title']);
        $this->assertEquals(403, $data['status']);
        $this->assertEquals('urn:omnisend:request:550e8400', $data['instance']);
        $this->assertEquals(array('email: invalid Email is invalid'), $data['errors']);
    }

    public function test_rate_limit_retry_after_is_preserved(): void
    {
        $body = json_encode(array('title' => 'Too many requests', 'retryAfter' => 30));

        $result = ApiResponse::parse(WP_Http_Test_Stub::response(429, $body));

        $this->assertEquals(ApiResponse::ERROR_RATE_LIMITED, $result->get_error_code());
        $this->assertStringContainsString('retry after 30s', $result->get_error_message());
        $this->assertEquals(30, $result->get_error_data(ApiResponse::ERROR_RATE_LIMITED)['retryAfter']);
    }

    public function test_non_problem_error_body_is_included_in_message(): void
    {
        $result = ApiResponse::parse(WP_Http_Test_Stub::response(502, '<html>bad gateway</html>'));

        $this->assertEquals(ApiResponse::ERROR_SERVER, $result->get_error_code());
        $this->assertStringContainsString('bad gateway', $result->get_error_message());
    }

    public function test_empty_body_is_an_error_when_body_is_required(): void
    {
        $result = ApiResponse::parse(WP_Http_Test_Stub::response(200, ''));

        $this->assertTrue(is_wp_error($result));
        $this->assertEquals(ApiResponse::ERROR_EMPTY_BODY, $result->get_error_code());
    }

    public function test_empty_body_is_allowed_when_body_is_not_required(): void
    {
        $result = ApiResponse::parse(WP_Http_Test_Stub::response(204, ''), false);

        $this->assertEquals(array(), $result);
    }

    public function test_malformed_json_is_an_error(): void
    {
        $result = ApiResponse::parse(WP_Http_Test_Stub::response(200, '{"id": '));

        $this->assertTrue(is_wp_error($result));
        $this->assertEquals(ApiResponse::ERROR_INVALID_JSON, $result->get_error_code());
    }

    public function test_non_object_json_is_an_error(): void
    {
        $result = ApiResponse::parse(WP_Http_Test_Stub::response(200, '"ok"'));

        $this->assertTrue(is_wp_error($result));
        $this->assertEquals(ApiResponse::ERROR_INVALID_JSON, $result->get_error_code());
    }

    public function test_successful_response_is_decoded(): void
    {
        $result = ApiResponse::parse(WP_Http_Test_Stub::response(201, '{"id":"abc"}'));

        $this->assertEquals(array('id' => 'abc'), $result);
    }

    public function test_unexpected_shape_error(): void
    {
        $error = ApiResponse::unexpected_shape_error('Contact id');

        $this->assertEquals(ApiResponse::ERROR_UNEXPECTED_SHAPE, $error->get_error_code());
        $this->assertEquals('Contact id not found in response.', $error->get_error_message());
    }
}
