<?php
/**
 * Omnisend Contact Utils
 *
 * @package OmnisendClient
 */

namespace Omnisend\Internal;

use Omnisend\SDK\V1\Contact;
use Omnisend\SDK\V1\Omnisend;
use Omnisend_Core_Bootstrap;

! defined( 'ABSPATH' ) && die( 'no direct access' );

class Sync {

	private const MAX_SYNC_ATTEMPTS = 5;

	private const OWNERSHIP_CONFIRMED = 'confirmed';
	private const OWNERSHIP_REFUSED   = 'refused';
	private const OWNERSHIP_UNKNOWN   = 'unknown';

	/**
	 * Sync and sets omnisend contact id for user
	 *
	 * @param $user_id
	 * @return void
	 */
	public static function identify_user_by_id( $user_id ): void {
		$user = get_userdata( $user_id );
		if ( $user ) {
			$omnisend_contact_id = self::sync_contact( $user );
			if ( $omnisend_contact_id ) {
				Snippet::set_contact_cookie_id( $omnisend_contact_id );
			}
		}
	}

	/**
	 * @param int $limit number of users to sync
	 */
	public static function sync_contacts( int $limit = 100 ): void {
		$users = self::pending_users( $limit, true );

		if ( empty( $users ) ) {
			// Users with a scheduled retry are not due yet, so an empty due set alone does not mean the backlog is gone.
			if ( empty( self::pending_users( 1, false ) ) ) {
				wp_clear_scheduled_hook( OMNISEND_CORE_CRON_SYNC_CONTACT );
			}

			return;
		}

		foreach ( $users as $user ) {
			self::sync_contact( $user );
		}
	}

	/**
	 * @param int  $limit    number of users to sync
	 * @param bool $due_only whether users whose next retry is still in the future are left out
	 * @return \WP_User[]
	 */
	private static function pending_users( int $limit, bool $due_only ): array {
		$meta_query = array(
			'relation' => 'AND',
			array(
				'key'     => UserMetaData::LAST_SYNC,
				'compare' => 'NOT EXISTS',
				'value'   => '',
			),
		);

		if ( $due_only ) {
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => UserMetaData::NEXT_SYNC_ATTEMPT,
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => UserMetaData::NEXT_SYNC_ATTEMPT,
					'value'   => time(),
					'compare' => '<=',
					'type'    => 'NUMERIC',
				),
			);
		}

		$wp_user_query = new \WP_User_Query(
			array(
				'number'     => $limit,
				// meta_query is required to work as sync information is stored in contact metadata.
				'meta_query' => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);

		return $wp_user_query->get_results();
	}

	/**
	 * @param \WP_User $user
	 * @return string
	 */
	private static function sync_contact( $user ): string {
		$contact = new Contact();
		$contact->add_tag( 'WordPress' );

		if ( ! filter_var( $user->user_email, FILTER_VALIDATE_EMAIL ) ) {
			UserMetaData::mark_sync_skipped( $user->ID );
			return '';
		}

		if ( UserMetaData::get_sync_attempts( $user->ID ) > 0 && UserMetaData::get_next_sync_attempt( $user->ID ) > time() ) {
			// The user failed recently and the next attempt is scheduled for later.
			return '';
		}

		$contact->set_email( $user->user_email );

		$first_name = get_user_meta( $user->ID, 'first_name', true );
		if ( $first_name ) {
			$contact->set_first_name( $first_name );
		}

		$last_name = get_user_meta( $user->ID, 'last_name', true );
		if ( $last_name ) {
			$contact->set_last_name( $last_name );
		}

		$roles        = get_user_meta( $user->ID, 'wp_capabilities', true );
		$parsed_roles = array();
		if ( is_array( $roles ) ) {
			foreach ( $roles as $role => $active ) {
				if ( $active ) {
					$parsed_roles[] = $role;
				}
			}

			if ( ! empty( $parsed_roles ) ) {
				$contact->add_custom_property( 'wordpress_roles', array_unique( $parsed_roles ) );
			}
		}

		$client = Omnisend::get_client( OMNISEND_CORE_PLUGIN_NAME, OMNISEND_CORE_PLUGIN_VERSION );

		$lookup    = $client->get_contact_by_email( $user->user_email );
		$ownership = self::ownership_state( $lookup, $user );

		if ( self::OWNERSHIP_REFUSED === $ownership ) {
			UserMetaData::mark_sync_error( $user->ID );
			Logger::error( 'Contact sync refused, the contact under this email belongs to another user.', array( 'user_id' => $user->ID ) );

			return '';
		}

		if ( self::OWNERSHIP_UNKNOWN === $ownership ) {
			// The lookup failed, so ownership cannot be proven and the user stays pending for the next attempt.
			self::handle_sync_failure( $user->ID, $lookup->get_wp_error(), 'get_contact_by_email' );

			return '';
		}

		$response = $client->create_contact( $contact );
		if ( $response->get_contact_id() ) {
			UserMetaData::mark_synced( $user->ID, $response->get_contact_id() );
			Options::clear_sync_error_notice();

			return $response->get_contact_id();
		}

		$error = $response->get_wp_error();

		if ( $error->has_errors() && self::is_retryable_error( $error ) ) {
			self::handle_sync_failure( $user->ID, $error, 'create_contact' );

			return '';
		}

		UserMetaData::mark_sync_error( $user->ID );
		Logger::error(
			'Contact sync failed permanently.',
			array(
				'user_id'       => $user->ID,
				'error_code'    => $error->get_error_code(),
				'error_message' => $error->get_error_message(),
			)
		);

		return '';
	}

	/**
	 * Records a retryable failure without stamping the user, so the next cron tick picks them up again.
	 * After MAX_SYNC_ATTEMPTS the user is stamped and left alone.
	 *
	 * @param int      $user_id
	 * @param \WP_Error $error
	 * @param string   $step
	 */
	private static function handle_sync_failure( $user_id, \WP_Error $error, string $step ): void {
		if ( self::is_auth_error( $error ) ) {
			Options::set_sync_error_notice( Connection::get_connection_error_message( $error, 'contacts.write' ) );
		}

		$attempts = UserMetaData::record_sync_attempt( $user_id );

		if ( $attempts >= self::MAX_SYNC_ATTEMPTS ) {
			UserMetaData::mark_sync_error( $user_id );
			Logger::error(
				'Contact sync gave up after repeated failures.',
				array(
					'user_id'       => $user_id,
					'step'          => $step,
					'attempts'      => $attempts,
					'error_code'    => $error->get_error_code(),
					'error_message' => $error->get_error_message(),
				)
			);

			return;
		}

		UserMetaData::set_next_sync_attempt( $user_id, time() + self::retry_delay( $attempts ) );
		Logger::error(
			'Contact sync failed, will retry.',
			array(
				'user_id'       => $user_id,
				'step'          => $step,
				'attempt'       => $attempts,
				'error_code'    => $error->get_error_code(),
				'error_message' => $error->get_error_message(),
			)
		);

		self::ensure_sync_scheduled();
	}

	/**
	 * @param \WP_Error $error
	 */
	private static function is_retryable_error( \WP_Error $error ): bool {
		return in_array(
			$error->get_error_code(),
			array(
				ApiResponse::ERROR_TRANSPORT,
				ApiResponse::ERROR_UNAUTHORIZED,
				ApiResponse::ERROR_FORBIDDEN,
				ApiResponse::ERROR_RATE_LIMITED,
				ApiResponse::ERROR_SERVER,
			),
			true
		);
	}

	/**
	 * @param \WP_Error $error
	 */
	private static function is_auth_error( \WP_Error $error ): bool {
		return in_array(
			$error->get_error_code(),
			array(
				ApiResponse::ERROR_UNAUTHORIZED,
				ApiResponse::ERROR_FORBIDDEN,
			),
			true
		);
	}

	private static function retry_delay( int $attempts ): int {
		return (int) min( 5 * MINUTE_IN_SECONDS * ( 2 ** ( $attempts - 1 ) ), HOUR_IN_SECONDS );
	}

	private static function ensure_sync_scheduled(): void {
		if ( Omnisend_Core_Bootstrap::is_omnisend_woocommerce_plugin_connected() ) {
			return;
		}

		if ( ! wp_next_scheduled( OMNISEND_CORE_CRON_SYNC_CONTACT ) ) {
			wp_schedule_event( time(), OMNISEND_CORE_CRON_SCHEDULE_EVERY_MINUTE, OMNISEND_CORE_CRON_SYNC_CONTACT );
		}
	}

	/**
	 * Whether the user may write to the Omnisend contact registered under their email address.
	 *
	 * The write below upserts by email, so an address the user does not own would let them overwrite
	 * someone else's contact. The contact this plugin synced for the user is bound in user meta; users
	 * synced before the binding existed may adopt the contact found under their current email once,
	 * everyone else's write to an already existing contact is refused.
	 *
	 * @param \Omnisend\SDK\V1\GetContactResponse $lookup
	 * @param \WP_User                            $user
	 * @return string One of the OWNERSHIP_ constants.
	 */
	private static function ownership_state( $lookup, \WP_User $user ): string {
		$lookup_error = $lookup->get_wp_error();

		if ( $lookup_error->has_errors() ) {
			return ApiResponse::ERROR_NOT_FOUND === $lookup_error->get_error_code() ? self::OWNERSHIP_CONFIRMED : self::OWNERSHIP_UNKNOWN;
		}

		$bound_contact_id = UserMetaData::get_contact_id( $user->ID );
		if ( '' !== $bound_contact_id ) {
			return $bound_contact_id === $lookup->get_contact()->get_id() ? self::OWNERSHIP_CONFIRMED : self::OWNERSHIP_REFUSED;
		}

		return UserMetaData::has_synced( $user->ID ) ? self::OWNERSHIP_CONFIRMED : self::OWNERSHIP_REFUSED;
	}
}
