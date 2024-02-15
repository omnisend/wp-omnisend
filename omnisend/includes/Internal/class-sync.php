<?php
/**
 * Omnisend Contact Utils
 *
 * @package OmnisendClient
 */

namespace Omnisend\Internal;

use Omnisend\SDK\V1\Contact;
use Omnisend\SDK\V1\Omnisend;

! defined( 'ABSPATH' ) && die( 'no direct access' );

class Sync {

	/**
	 * @param int $limit number of users to sync
	 *
	 * @return int processed users (synced, skipped, error)
	 */
	public static function sync_contacts( int $limit = 100 ): int {
		$wp_user_query = new \WP_User_Query(
			array(
				'number'     => $limit,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'OR',
					array(
						'key'     => UserMetaData::LAST_SYNC,
						'compare' => 'NOT EXISTS',
						'value'   => '',
					),
				),
			)
		);
		$users         = $wp_user_query->get_results();

		if ( empty( $users ) ) {
			return 0;
		}

		foreach ( $users as $user ) {
			$contact = new Contact();
			$contact->add_tag( 'WordPress' );

			if ( ! filter_var( $user->user_email, FILTER_VALIDATE_EMAIL ) ) {
				UserMetaData::mark_sync_skipped( $user->ID );
				continue;
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

			$response = Omnisend::get_client( OMNISEND_CORE_PLUGIN_NAME, OMNISEND_CORE_PLUGIN_VERSION )->create_contact( $contact );
			if ( $response->get_contact_id() ) {
				UserMetaData::mark_synced( $user->ID );
			} else {
				UserMetaData::mark_sync_error( $user->ID );
			}
		}

		return count( $users );
	}
}
