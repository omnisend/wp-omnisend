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

	public static function sync_contacts(): void {
		$wp_user_query = new \WP_User_Query(
			array(
				'number'     => 1000,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'OR',
					array(
						'key'     => MetaData::LAST_SYNC,
						'compare' => 'NOT EXISTS',
						'value'   => '',
					),
				),
			)
		);
		$users         = $wp_user_query->get_results();

		if ( empty( $users ) ) {
			// todo stop cron.
			echo '<pre>' . __FILE__ . ':' . __LINE__ . '<br />' . print_r( 'no user to sync', 1 ) . '</pre>'; // phpcs:ignore
		}

		foreach ( $users as $user ) {
			$contact = new Contact();
			$contact->add_tag( 'WordPress' );

			if ( ! filter_var( $user->user_email, FILTER_VALIDATE_EMAIL ) ) {
				// todo no valid email.
				echo '<pre>' . __FILE__ . ':' .__LINE__. '<br />' . print_r( 'no valid email', 1 ) . '</pre>'; // phpcs:ignore
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

			$response = Omnisend::get_client( 'nerijus', '666' )->create_contact( $contact );

			echo '<pre>' . __FILE__ . ':' . __LINE__ . '<br />' . print_r( $contact, 1 ) . '</pre>'; // phpcs:ignore
			echo '<pre>' . __FILE__ . ':' . __LINE__ . '<br />' . print_r( $response, 1 ) . '</pre>'; // phpcs:ignore

			if ( $response->get_contact_id() ) {
				MetaData::mark_user_synced( $user->ID );
			} else {
				MetaData::mark_user_sync_error( $user->ID );
			}
		}
	}
}
