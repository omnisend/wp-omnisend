<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

defined( 'ABSPATH' ) || die( 'no direct access' );

class UserMetaData {
	public const LAST_SYNC         = 'omni_send_core_last_sync';
	public const CONTACT_ID        = 'omni_send_core_contact_id';
	public const SYNC_ATTEMPTS     = 'omni_send_core_sync_attempts';
	public const NEXT_SYNC_ATTEMPT = 'omni_send_core_next_sync_attempt';

	public static function mark_synced( $user_id, string $contact_id = '' ) {
		update_user_meta( $user_id, self::LAST_SYNC, gmdate( DATE_ATOM, time() ) );
		if ( '' !== $contact_id ) {
			update_user_meta( $user_id, self::CONTACT_ID, $contact_id );
		}
		self::clear_retry_state( $user_id );
	}

	public static function get_contact_id( $user_id ): string {
		$contact_id = get_user_meta( $user_id, self::CONTACT_ID, true );
		return is_string( $contact_id ) ? $contact_id : '';
	}

	public static function has_synced( $user_id ): bool {
		$last_sync = get_user_meta( $user_id, self::LAST_SYNC, true );
		return is_string( $last_sync ) && '' !== $last_sync && 'ERROR' !== $last_sync && 'SKIPPED' !== $last_sync;
	}

	public static function mark_sync_error( $user_id ) {
		update_user_meta( $user_id, self::LAST_SYNC, 'ERROR' );
		self::clear_retry_state( $user_id );
	}

	public static function mark_sync_skipped( $user_id ) {
		update_user_meta( $user_id, self::LAST_SYNC, 'SKIPPED' );
		self::clear_retry_state( $user_id );
	}

	public static function get_sync_attempts( $user_id ): int {
		$attempts = get_user_meta( $user_id, self::SYNC_ATTEMPTS, true );
		return is_numeric( $attempts ) ? (int) $attempts : 0;
	}

	public static function record_sync_attempt( $user_id ): int {
		$attempts = self::get_sync_attempts( $user_id ) + 1;
		update_user_meta( $user_id, self::SYNC_ATTEMPTS, $attempts );

		return $attempts;
	}

	public static function get_next_sync_attempt( $user_id ): int {
		$next_attempt = get_user_meta( $user_id, self::NEXT_SYNC_ATTEMPT, true );
		return is_numeric( $next_attempt ) ? (int) $next_attempt : 0;
	}

	public static function set_next_sync_attempt( $user_id, int $timestamp ) {
		update_user_meta( $user_id, self::NEXT_SYNC_ATTEMPT, $timestamp );
	}

	private static function clear_retry_state( $user_id ) {
		delete_user_meta( $user_id, self::SYNC_ATTEMPTS );
		delete_user_meta( $user_id, self::NEXT_SYNC_ATTEMPT );
	}

	/**
	 * Puts users the sync has given up on back into the queue. Called when the store is connected again,
	 * so a fixed permission or credential lets the backlog drain.
	 */
	public static function clear_all_sync_errors() {
		delete_metadata( 'user', '0', self::LAST_SYNC, 'ERROR', true );
		delete_metadata( 'user', '0', self::SYNC_ATTEMPTS, '', true );
		delete_metadata( 'user', '0', self::NEXT_SYNC_ATTEMPT, '', true );
	}
}
