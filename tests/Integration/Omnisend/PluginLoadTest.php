<?php

namespace Omnisend\Tests\Integration;

use Omnisend\Internal\Options;
use Omnisend\Internal\UserMetaData;

final class PluginLoadTest extends IntegrationTestCase {

	public function test_expect_sync_and_snippet_hooks_when_plugin_is_loaded(): void {
		$this->assertSame( 10, has_action( 'user_register', 'Omnisend\Internal\Sync::identify_user_by_id' ) );
		$this->assertSame( 10, has_action( 'profile_update', 'Omnisend\Internal\Sync::identify_user_on_profile_update' ) );
		$this->assertSame( 10, has_action( OMNISEND_CORE_CRON_SYNC_CONTACT, 'Omnisend\Internal\Sync::sync_contacts' ) );
		$this->assertSame( 10, has_action( 'wp_footer', 'Omnisend\Internal\Snippet::add' ) );
	}

	public function test_expect_contact_bound_when_user_registers_on_connected_store(): void {
		Options::set_api_key( 'brandid-secret' );
		Options::set_store_connected();
		$this->http->respond( 'GET', OMNISEND_CORE_API . '/contacts?email=jane%40example.com', 200, array( 'contacts' => array() ) );
		$this->http->respond( 'POST', OMNISEND_CORE_API . '/contacts', 200, array( 'id' => 'contact-1' ) );
		$this->http->respond( 'POST', OMNISEND_CORE_API . '/contacts/tags', 204 );

		$user_id = self::factory()->user->create( array( 'user_email' => 'jane@example.com' ) );

		$this->assertSame( 'contact-1', UserMetaData::get_contact_id( $user_id ) );
		$contact_write = json_decode( $this->http->requests()[1]['body'], true );
		$this->assertSame( 'jane@example.com', $contact_write['identifiers'][0]['id'] );
	}
}
