<?php
namespace Omnisend\Tests;

use PHPUnit\Framework\TestCase;

require_once( __DIR__ . '/../dependencies/dependencies.php' );

/**
 * The plugin bootstrap cannot be loaded in these tests, so the registrations it makes are asserted on its source.
 */
final class RestRoutesTest extends TestCase
{
    public function test_core_registers_no_rest_routes(): void
    {
        $bootstrap = file_get_contents(__DIR__ . '/../../omnisend/class-omnisend-core-bootstrap.php');

        $this->assertStringNotContainsString('register_rest_route', $bootstrap);
        $this->assertStringNotContainsString('omnisend/v1', $bootstrap);
    }
}
