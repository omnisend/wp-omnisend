<?php
namespace Omnisend\Tests\SDK;

use Omnisend\SDK\V1\Contact;
use Omnisend\SDK\V1\GetContactResponse;
use Omnisend\SDK\V1\Omnisend;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TypeError;
use WP_Error;

require_once(__DIR__ . '/../../dependencies/dependencies.php');
require_once(__DIR__ . '/SdkSurface.php');

/**
 * Guards the compatibility boundary described in
 * projects/wp-omnisend-deprecated-api-removal/decisions.md of omnisend/ecom-platforms-projects:
 * consumer plugins integrate through Omnisend\SDK\V1, so its class names, method names,
 * signatures, response classes and public constants are a contract.
 */
final class SdkSurfaceTest extends TestCase
{
    public function test_sdk_surface_matches_the_approved_baseline(): void
    {
        foreach (SdkSurface::sdk_class_names() as $class_name) {
            $this->assertTrue(
                class_exists($class_name) || interface_exists($class_name),
                "SDK class {$class_name} could not be loaded"
            );
        }

        $baseline = require __DIR__ . '/sdk-surface-baseline.php';

        $this->assertEquals(
            $baseline,
            SdkSurface::collect(),
            'The public SDK surface changed. Consumer plugins integrate through these classes, so '
            . 'the change has to be an approved deviation: record it in the project decisions file, '
            . 'then regenerate the baseline with '
            . '`php tests/tools/dump-sdk-surface.php > tests/Omnisend/SDK/sdk-surface-baseline.php`.'
        );
    }

    public function test_internal_client_implements_the_sdk_client_interface(): void
    {
        $this->assertTrue(
            (new ReflectionClass(\Omnisend\Internal\V1\Client::class))->implementsInterface(\Omnisend\SDK\V1\Client::class),
            'The client returned to consumers must keep implementing Omnisend\SDK\V1\Client.'
        );
    }

    public function test_get_client_returns_a_client_implementing_the_sdk_interface(): void
    {
        $client = Omnisend::get_client('integration name', '1.0.0');

        $this->assertInstanceOf(\Omnisend\SDK\V1\Client::class, $client);
    }

    /**
     * A failed contact lookup reaches the consumer as a TypeError, because the client passes null
     * while GetContactResponse declares a non-nullable Contact: today at construction, and once the
     * property becomes nullable, when the contact is read. Consumers must check get_wp_error()
     * before get_contact(); this is a listed deviation, not a behaviour that may silently change.
     */
    public function test_a_failed_contact_lookup_surfaces_as_a_type_error(): void
    {
        $this->expectException(TypeError::class);

        $error = new WP_Error();
        $error->add('omnisend_api', 'HTTP error: 401');

        $response = new GetContactResponse(null, $error);
        $response->get_contact();
    }

    public function test_get_contact_response_returns_the_contact_it_was_given(): void
    {
        $contact = new Contact();
        $contact->set_email('test@example.com');

        $response = new GetContactResponse($contact, new WP_Error());

        $this->assertSame($contact, $response->get_contact());
        $this->assertFalse($response->get_wp_error()->has_errors());
    }
}
