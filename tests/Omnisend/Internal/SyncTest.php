<?php
namespace Omnisend\Internal;

use PHPUnit\Framework\TestCase;
use WP_Http_Test_Stub;
use WP_User;

require_once( __DIR__ . '/../../dependencies/dependencies.php' );

final class SyncTest extends TestCase
{
    private const USER_ID = 7;
    private const EMAIL = 'user@example.com';
    private const CONTACT_ID = 'contact-abc';

    protected function setUp(): void
    {
        WP_Http_Test_Stub::reset();
        wp_test_reset_options();

        Options::set_api_key('brandid-secret');
        wp_test_add_user(new WP_User(self::USER_ID, self::EMAIL));
    }

    private function queue_lookup_found(string $contact_id): void
    {
        WP_Http_Test_Stub::queue(
            WP_Http_Test_Stub::response(200, array('contacts' => array(array('id' => $contact_id))))
        );
    }

    private function queue_lookup_not_found(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, array('contacts' => array())));
    }

    private function queue_write(string $contact_id): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, array('id' => $contact_id)));
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(202, ''));
    }

    private function request_count(string $method): int
    {
        return count(
            array_filter(
                WP_Http_Test_Stub::$requests,
                function ($request) use ($method) {
                    return $request['method'] === $method;
                }
            )
        );
    }

    public function test_first_sync_creates_contact_and_binds_its_id(): void
    {
        $this->queue_lookup_not_found();
        $this->queue_write(self::CONTACT_ID);

        Sync::identify_user_by_id(self::USER_ID);

        $this->assertSame(self::CONTACT_ID, UserMetaData::get_contact_id(self::USER_ID));
        $this->assertTrue(UserMetaData::has_synced(self::USER_ID));
        $this->assertSame(2, $this->request_count('POST'));
    }

    public function test_sync_writes_to_contact_bound_to_the_user(): void
    {
        update_user_meta(self::USER_ID, UserMetaData::CONTACT_ID, self::CONTACT_ID);

        $this->queue_lookup_found(self::CONTACT_ID);
        $this->queue_write(self::CONTACT_ID);

        Sync::identify_user_by_id(self::USER_ID);

        $this->assertTrue(UserMetaData::has_synced(self::USER_ID));
        $this->assertSame(2, $this->request_count('POST'));
    }

    public function test_sync_refuses_to_write_a_contact_bound_to_someone_else(): void
    {
        update_user_meta(self::USER_ID, UserMetaData::CONTACT_ID, self::CONTACT_ID);

        $this->queue_lookup_found('someone-elses-contact');

        Sync::identify_user_by_id(self::USER_ID);

        $this->assertSame('ERROR', get_user_meta(self::USER_ID, UserMetaData::LAST_SYNC, true));
        $this->assertSame(0, $this->request_count('POST'));
    }

    public function test_never_synced_user_cannot_take_over_an_existing_contact(): void
    {
        $this->queue_lookup_found('preexisting-contact');

        Sync::identify_user_by_id(self::USER_ID);

        $this->assertSame('ERROR', get_user_meta(self::USER_ID, UserMetaData::LAST_SYNC, true));
        $this->assertSame('', UserMetaData::get_contact_id(self::USER_ID));
        $this->assertSame(0, $this->request_count('POST'));
    }

    public function test_previously_synced_user_adopts_the_contact_under_their_email(): void
    {
        update_user_meta(self::USER_ID, UserMetaData::LAST_SYNC, gmdate(DATE_ATOM));

        $this->queue_lookup_found('legacy-contact');
        $this->queue_write('legacy-contact');

        Sync::identify_user_by_id(self::USER_ID);

        $this->assertSame('legacy-contact', UserMetaData::get_contact_id(self::USER_ID));
        $this->assertSame(2, $this->request_count('POST'));
    }

    public function test_email_change_to_an_address_without_a_contact_rebinds(): void
    {
        update_user_meta(self::USER_ID, UserMetaData::CONTACT_ID, 'old-contact');

        $this->queue_lookup_not_found();
        $this->queue_write('new-contact');

        Sync::identify_user_by_id(self::USER_ID);

        $this->assertSame('new-contact', UserMetaData::get_contact_id(self::USER_ID));
        $this->assertSame(2, $this->request_count('POST'));
    }

    public function test_lookup_failure_is_retryable(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(500, 'server error'));

        Sync::identify_user_by_id(self::USER_ID);

        $this->assertNotSame('ERROR', get_user_meta(self::USER_ID, UserMetaData::LAST_SYNC, true));
        $this->assertSame(1, UserMetaData::get_sync_attempts(self::USER_ID));
        $this->assertGreaterThan(time(), UserMetaData::get_next_sync_attempt(self::USER_ID));
        $this->assertSame(0, $this->request_count('POST'));
    }

    public function test_invalid_email_is_skipped_permanently(): void
    {
        wp_test_add_user(new WP_User(self::USER_ID, 'not-an-email'));

        Sync::identify_user_by_id(self::USER_ID);

        $this->assertSame('SKIPPED', get_user_meta(self::USER_ID, UserMetaData::LAST_SYNC, true));
        $this->assertSame(0, $this->request_count('GET'));
    }

    public function test_server_error_on_create_leaves_the_user_retryable_and_keeps_the_cron(): void
    {
        $this->queue_lookup_not_found();
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(500, 'server error'));

        Sync::sync_contacts();

        $this->assertNotSame('ERROR', get_user_meta(self::USER_ID, UserMetaData::LAST_SYNC, true));
        $this->assertSame(1, UserMetaData::get_sync_attempts(self::USER_ID));
        $this->assertGreaterThan(time(), UserMetaData::get_next_sync_attempt(self::USER_ID));
        $this->assertEmpty($GLOBALS['wp_test_cron']['cleared']);
    }

    public function test_server_error_reschedules_the_cron_when_it_is_not_scheduled(): void
    {
        wp_test_set_next_scheduled(OMNISEND_CORE_CRON_SYNC_CONTACT, false);

        $this->queue_lookup_not_found();
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(500, 'server error'));

        Sync::sync_contacts();

        $this->assertNotEmpty($GLOBALS['wp_test_cron']['scheduled']);
    }

    public function test_forbidden_create_shows_an_admin_notice_and_stays_retryable(): void
    {
        $this->queue_lookup_not_found();
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(403, '{"title":"Forbidden","detail":"Missing contacts.write scope."}'));

        Sync::sync_contacts();

        $this->assertStringContainsString('missing permissions (contacts.write)', Options::get_sync_error_notice());
        $this->assertStringContainsString('connect again', Options::get_sync_error_notice());
        $this->assertNotSame('ERROR', get_user_meta(self::USER_ID, UserMetaData::LAST_SYNC, true));
    }

    public function test_successful_sync_clears_the_admin_notice(): void
    {
        Options::set_sync_error_notice('Omnisend rejected this request because of missing permissions.');

        $this->queue_lookup_not_found();
        $this->queue_write(self::CONTACT_ID);

        Sync::sync_contacts();

        $this->assertSame('', Options::get_sync_error_notice());
    }

    public function test_retry_exhaustion_marks_the_user_as_error(): void
    {
        update_user_meta(self::USER_ID, UserMetaData::SYNC_ATTEMPTS, 4);

        $this->queue_lookup_not_found();
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(500, 'server error'));

        Sync::sync_contacts();

        $this->assertSame('ERROR', get_user_meta(self::USER_ID, UserMetaData::LAST_SYNC, true));
        $this->assertSame(0, UserMetaData::get_sync_attempts(self::USER_ID));
        $this->assertSame(0, UserMetaData::get_next_sync_attempt(self::USER_ID));
    }

    public function test_user_awaiting_retry_is_not_attempted_and_does_not_clear_the_cron(): void
    {
        update_user_meta(self::USER_ID, UserMetaData::SYNC_ATTEMPTS, 1);
        UserMetaData::set_next_sync_attempt(self::USER_ID, time() + 300);

        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, array('contacts' => array())));

        Sync::sync_contacts();

        $this->assertSame(0, $this->request_count('GET'));
        $this->assertSame(0, $this->request_count('POST'));
        $this->assertEmpty($GLOBALS['wp_test_cron']['cleared']);
    }

    public function test_sync_contacts_clears_the_cron_when_nothing_is_pending(): void
    {
        $this->queue_lookup_not_found();
        $this->queue_write(self::CONTACT_ID);

        Sync::sync_contacts();
        Sync::sync_contacts();

        $this->assertSame(array(OMNISEND_CORE_CRON_SYNC_CONTACT), $GLOBALS['wp_test_cron']['cleared']);
    }

    public function test_clear_all_sync_errors_returns_given_up_users_to_the_queue(): void
    {
        update_user_meta(self::USER_ID, UserMetaData::LAST_SYNC, 'ERROR');
        update_user_meta(self::USER_ID, UserMetaData::SYNC_ATTEMPTS, 5);

        UserMetaData::clear_all_sync_errors();

        $this->assertSame('', get_user_meta(self::USER_ID, UserMetaData::LAST_SYNC, true));
        $this->assertSame(0, UserMetaData::get_sync_attempts(self::USER_ID));
    }
}
