<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\Internal;

use Omnisend\SDK\V1\Event;
use Omnisend\SDK\V1\Contact;
use Omnisend\SDK\V1\Omnisend;
use WP_User;

class EmailEvents {
	public function __construct() {
		add_filter( 'wp_new_user_notification_email', array( $this, 'new_user_event' ), 10, 2 );
		add_filter( 'retrieve_password_message', array( $this, 'user_password_change_event' ), 10, 4 );
		add_action( 'after_password_reset', array( $this, 'user_password_changed_event' ) );
		add_action( 'profile_update', array( $this, 'user_email_change_event' ), 10, 3 );
		add_action( 'profile_update', array( $this, 'user_email_changed_event' ), 10, 3 );
	}

	/**
	 * Event when new user registers
	 *
	 * @param array   $message
	 * @param WP_User $user
	 *
	 * @return void
	 */
	public function new_user_event( array $message, WP_User $user ): void {
		$user_email = (string) $user->user_email;

		if ( empty( $user_email ) ) {
			return;
		}

		$reset_key = get_password_reset_key( $user );
		$reset_url = add_query_arg(
			array(
				'action' => 'rp',
				'key'    => $reset_key,
				'login'  => rawurlencode( $user->user_login ),
			),
			site_url( 'wp-login.php' )
		);

		$event = $this->get_base_event_with_email( $user_email );
		$event->set_custom_event_name( 'New User Registered' );
		$event->add_custom_event_property( 'reset_url', $reset_url );

		$this->send_event( $event );

		$admins = get_users(
			array(
				'role'   => 'Administrator',
				'fields' => array( 'user_email' ),
			)
		);

		foreach ( $admins as $admin ) {
			$event = $this->get_base_event_with_email( (string) $admin->user_email );
			$event->set_custom_event_name( 'New User Registered (for admins)' );
			$event->add_custom_event_property( 'new_user', $user_email );

			$this->send_event( $event );
		}
	}

	/**
	 * Event when password reset request is initiated
	 *
	 * @param string  $message
	 * @param string  $key
	 * @param string  $user_login
	 * @param WP_User $user_data
	 *
	 * @return void
	 */
	public function user_password_change_event( string $message, string $key, string $user_login, WP_User $user_data ): void {
		$reset_url = add_query_arg(
			array(
				'action' => 'rp',
				'key'    => $key,
				'login'  => rawurlencode( $user_login ),
			),
			site_url( 'wp-login.php' )
		);

		$event = $this->get_base_event_with_email( (string) $user_data->user_email );
		$event->set_custom_event_name( 'Password Reset Requested' );
		$event->add_custom_event_property( 'reset_url', $reset_url );

		$this->send_event( $event );
	}

	/**
	 * Event when user password has been changed
	 *
	 * @param WP_User $user
	 *
	 * @return void
	 */
	public function user_password_changed_event( WP_User $user ): void {
		$request_uri = $this->get_request_uri();

		if ( strpos( $request_uri, 'wp-login.php' ) === false ) {
			return;
		}

		$event = $this->get_base_event_with_email( (string) $user->user_email );
		$event->set_custom_event_name( 'Password Changed' );

		$this->send_event( $event );
	}

	/**
	 * Event when user email change request is initiated
	 *
	 * @param int     $user_id,
	 * @param WP_User $old_user_data
	 * @param array   $user_data
	 *
	 * @return void
	 */
	public function user_email_change_event( int $user_id, WP_User $old_user_data, array $user_data ): void {
		$request_uri = $this->get_request_uri();

		if ( ! is_admin() || strpos( $request_uri, 'profile.php' ) === false ) {
			return;
		}

		$new_email_data = get_user_meta( $user_id, '_new_email', true );

		if ( ! is_array( $new_email_data ) || ! isset( $new_email_data['newemail'] ) ) {
			return;
		}

		$new_email = (string) $new_email_data['newemail'];
		$old_email = (string) $user_data['user_email'];

		if ( $old_email === $new_email ) {
			return;
		}

		$confirm_key = $new_email_data['hash'];
		$confirm_url = admin_url( 'profile.php?newuseremail=' . $confirm_key );

		$event = $this->get_base_event_with_email( $new_email );
		$event->set_custom_event_name( 'Email Address Change Request' );
		$event->add_custom_event_property( 'confirm_url', $confirm_url );

		$this->send_event( $event );
	}

	/**
	 * Event when user email has changed
	 *
	 * @param int     $user_id
	 * @param WP_User $old_user_data
	 * @param array   $user_data
	 *
	 * @return void
	 */
	public function user_email_changed_event( int $user_id, WP_User $old_user_data, array $user_data ): void {
		$request_uri = $this->get_request_uri();

		if ( ! is_admin() || strpos( $request_uri, 'profile.php' ) === false ) {
			return;
		}

		$new_email = (string) $user_data['user_email'];
		$old_email = (string) $old_user_data->user_email;

		if ( $old_email === $new_email ) {
			return;
		}

		$event = $this->get_base_event_with_email( $new_email );
		$event->set_custom_event_name( 'Email Address Changed' );
		$event->add_custom_event_property( 'old_email', $old_email );

		$this->send_event( $event );

		$event = $this->get_base_event_with_email( $old_email );
		$event->set_custom_event_name( 'Email Address Changed' );
		$event->add_custom_event_property( 'new_email', $new_email );

		$this->send_event( $event );
	}

	/**
	 * Sends event to Omnisend
	 *
	 * @param Event $event
	 *
	 * @return void
	 */
	private function send_event( Event $event ): void {
		$client = Omnisend::get_client( OMNISEND_CORE_PLUGIN_NAME, OMNISEND_CORE_PLUGIN_VERSION );
		$client->send_customer_event( $event );
	}

	/**
	 * Builds and returns empty event with attached contact
	 *
	 * @param string $user_email
	 *
	 * @return Event
	 */
	private function get_base_event_with_email( string $user_email ): Event {
		$event   = new Event();
		$contact = new Contact();

		$contact->set_email( $user_email );
		$event->set_contact( $contact );

		return $event;
	}

	/**
	 * Returns sanitized and unslashed request URI
	 *
	 * @return string
	 */
	private function get_request_uri(): string {
		return sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
	}
}
