<?php
namespace Omnisend\SDK\V1;

use Omnisend\Internal\ContactFactory;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ContactTest extends TestCase
{
    public function test_validation(): void
    {
        // Test valid contact
        $contact_data = ['email' => 'test@example.com'];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertFalse($contact->validate()->has_errors());

        // Test invalid email
        $contact_data = ['email' => 'invalid-email'];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertTrue($contact->validate()->has_errors());

        // Test missing identifier
        $contact_data = [];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertTrue($contact->validate()->has_errors());
    }

    public function test_to_array(): void
    {
        // Test empty contact
        $contact_data = [];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertEquals([], $contact->to_array());

        // Test valid contact
        $contact_data = [
            'email' => 'test@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe'
        ];
        $contact = ContactFactory::create_contact($contact_data);
        $expected = [
            'identifiers' => [
                [
                    'type' => 'email',
                    'id' => 'test@example.com',
                    'channels' => [
                        'email' => [
                            'status' => 'nonSubscribed',
                            'statusDate' => gmdate('c'),
                        ],
                    ],
                ],
            ],
            'tags' => [],
            'firstName' => 'John',
            'lastName' => 'Doe',
        ];
        $this->assertEquals($expected, $contact->to_array());
    }

    public function test_to_array_for_event(): void
    {
        // Test empty contact
        $contact_data = [];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertEquals([], $contact->to_array_for_event());

        // Test valid contact
        $contact_data = [
            'email' => 'test@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe'
        ];
        $contact = ContactFactory::create_contact($contact_data);
        $expected = [
            'email' => 'test@example.com',
            'consents' => [],
            'optIns' => [],
            'tags' => [],
            'firstName' => 'John',
            'lastName' => 'Doe',
        ];
        $this->assertEquals($expected, $contact->to_array_for_event());
    }

    public function test_set_email(): void
    {
        $contact_data = ['email' => 'test@example.com'];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertEquals('test@example.com', $contact->to_array()['identifiers'][0]['id']);

        $contact_data = ['email' => 'invalid-email'];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertTrue($contact->validate()->has_errors());
    }

    public function test_set_first_name(): void
    {
        $contact_data = ['firstName' => 'John'];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertEquals('John', $contact->to_array()['firstName']);
    }

    public function test_set_last_name(): void
    {
        $contact_data = ['lastName' => 'Doe'];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertEquals('Doe', $contact->to_array()['lastName']);
    }

    public function test_set_phone(): void
    {
        $contact_data = ['phone' => '1234567890'];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertEquals('1234567890', $contact->to_array()['phone']);
    }

    public function test_add_tag(): void
    {
        $contact_data = ['tags' => ['test-tag']];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertContains('test-tag', $contact->to_array()['tags']);
    }

    public function test_add_custom_property(): void
    {
        $contact_data = ['customProperties' => ['property' => 'value']];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertEquals('value', $contact->to_array()['customProperties']['property']);
    }

    public function test_validation_all_properties_set_correctly(): void
    {
        $contact_data = [
            'email' => 'test@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phone' => '1234567890',
            'gender' => 'm',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'Kauno r.',
            'country' => 'LT',
            'postalCode' => '12345',
            'birthdate' => '1990-01-01',
            'emailOptIn' => 'form:signup',
            'emailStatus' => 'subscribed',
            'phoneOptIn' => 'form:signup',
            'phoneStatus' => 'subscribed',
            'emailConsent' => 'GDPR',
            'phoneConsent' => 'GDPR',
            'sendWelcomeEmail' => true,
            'tags' => ['test-tag'],
            'customProperties' => ['custom_key' => 'custom_value']
        ];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertFalse($contact->validate()->has_errors());
    }

    public function test_validation_with_invalid_string_properties(): void
    {
        $contact_data = [
            'firstName' => ['invalid'],
            'lastName' => new stdClass(),
            'email' => 12345,
            'address' => 67890,
            'city' => false,
            'state' => null,
            'country' => [],
            'postalCode' => new stdClass(),
            'phone' => true,
            'birthdate' => 12345,
            'gender' => 0,
            'emailOptIn' => false,
            'phoneOptIn' => [],
        ];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertTrue($contact->validate()->has_errors());
    }

    public function test_validation_with_invalid_gender(): void
    {
        $contact_data = ['gender' => 'x'];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertTrue($contact->validate()->has_errors());
    }

    public function test_validation_with_invalid_tags_and_custom_properties(): void
    {
        $contact_data = [
            'tags' => [''],
            'customProperties' => ['' => 'value']
        ];
        $contact = ContactFactory::create_contact($contact_data);
        $this->assertTrue($contact->validate()->has_errors());
    }

    public function test_to_array_with_all_properties_set(): void
    {
        $contact_data = [
            'email' => 'test@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phone' => '1234567890',
            'gender' => 'm',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'Kauno r.',
            'country' => 'LT',
            'postalCode' => '12345',
            'birthdate' => '1990-01-01',
            'emailOptIn' => 'form:signup',
            'emailStatus' => 'subscribed',
            'phoneOptIn' => 'form:signup',
            'phoneStatus' => 'subscribed',
            'emailConsent' => 'GDPR',
            'phoneConsent' => 'GDPR',
            'sendWelcomeEmail' => true,
            'tags' => ['test-tag'],
            'customProperties' => ['custom_key' => 'custom_value']
        ];
        $contact = ContactFactory::create_contact($contact_data);
        $expected = [
            'identifiers' => [
                [
                    'type' => 'email',
                    'id' => 'test@example.com',
                    'channels' => [
                        'email' => [
                            'status' => 'subscribed',
                            'statusDate' => gmdate('c'),
                        ],
                    ],
                    'consent' => [
                        'source' => 'GDPR',
                        'createdAt' => gmdate('c'),
                        'ip' => 'ip not found',
                        'userAgent' => 'user agent not found',
                    ],
                ],
                [
                    'type' => 'phone',
                    'id' => '1234567890',
                    'channels' => [
                        'sms' => [
                            'status' => 'subscribed',
                            'statusDate' => gmdate('c'),
                        ],
                    ],
                    'consent' => [
                        'source' => 'GDPR',
                        'createdAt' => gmdate('c'),
                        'ip' => 'ip not found',
                        'userAgent' => 'user agent not found',
                    ],
                ],
            ],
            'tags' => ['test-tag'],
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phone' => '1234567890',
            'gender' => 'm',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'Kauno r.',
            'country' => 'LT',
            'postalCode' => '12345',
            'birthdate' => '1990-01-01',
            'customProperties' => ['custom_key' => 'custom_value'],
            'emailOptIn' => 'form:signup',
            'phoneOptIn' => 'form:signup',
            'sendWelcomeEmail' => true,
        ];
        $this->assertEquals($expected, $contact->to_array());
    }
}
