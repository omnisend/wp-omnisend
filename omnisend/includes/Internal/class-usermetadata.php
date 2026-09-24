<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

defined( 'ABSPATH' ) || die( 'no direct access' );

class UserMetaData {
	public const LAST_SYNC  = 'omni_send_core_last_sync';
	public const CONTACT_ID = 'omni_send_core_contact_id';

	public static function mark_synced( $user_id, string $contact_id = '' ) {
		update_user_meta( $user_id, self::LAST_SYNC, gmdate( DATE_ATOM, time() ) );
		if ( '' !== $contact_id ) {
			update_user_meta( $user_id, self::CONTACT_ID, $contact_id );
		}
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
	}

	public static function mark_sync_skipped( $user_id ) {
		update_user_meta( $user_id, self::LAST_SYNC, 'SKIPPED' );
	}
}
