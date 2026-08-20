<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\Internal\V1;

use Omnisend\SDK\V1\ConnectStoreResponse;
use Omnisend\SDK\V1\Contact;
use Omnisend\SDK\V1\CreateContactResponse;
use Omnisend\SDK\V1\Event;
use Omnisend\SDK\V1\SendCustomerEventResponse;
use Omnisend\SDK\V1\SaveContactResponse;
use Omnisend\SDK\V1\CreateCategoryResponse;
use Omnisend\SDK\V1\UpdateCategoryResponse;
use Omnisend\SDK\V1\DeleteCategoryResponse;
use Omnisend\SDK\V1\DeleteProductResponse;
use Omnisend\SDK\V1\CreateProductResponse;
use Omnisend\SDK\V1\GetContactResponse;
use Omnisend\SDK\V1\GetCategoryResponse;
use Omnisend\SDK\V1\GetProductResponse;
use Omnisend\Internal\ApiResponse;
use Omnisend\Internal\ContactFactory;
use Omnisend\Internal\CategoryFactory;
use Omnisend\Internal\ProductFactory;
use Omnisend\SDK\V1\SendBatchResponse;
use Omnisend\SDK\V1\Batch;
use Omnisend\SDK\V1\Category;
use Omnisend\SDK\V1\Product;
use Omnisend\SDK\V1\Options;
use WP_Error;

defined( 'ABSPATH' ) || die( 'no direct access' );

class Client implements \Omnisend\SDK\V1\Client {

	private string $api_key;
	private string $plugin_name;
	private string $plugin_version;
	private ?Options $options;

	/**
	 * @param string $plugin_name
	 * @param string $plugin_version
	 * @param string $api_key
	 * @param Options|null $options
	 */
	public function __construct( string $api_key, string $plugin_name, string $plugin_version, ?Options $options ) {
		$this->api_key        = $api_key;
		$this->plugin_name    = substr( $plugin_name, 0, 50 );
		$this->plugin_version = substr( $plugin_version, 0, 50 );
		$this->options        = $options;
	}

	private function get_request_headers(): array {
		return array(
			'Content-Type'          => 'application/json',
			'Authorization'         => 'Omnisend-API-Key ' . $this->api_key,
			'Omnisend-Version'      => '2026-03-15',
			'X-INTEGRATION-NAME'    => $this->plugin_name,
			'X-INTEGRATION-VERSION' => $this->plugin_version,
		);
	}


	public function create_contact( $contact ): CreateContactResponse {
		$error = new WP_Error();

		if ( $contact instanceof Contact ) {
			$error->merge_from( $contact->validate() );
		} else {
			$error->add( 'contact', 'Contact is not instance of Omnisend\SDK\V1\Contact.' );
		}

		$error->merge_from( $this->check_setup() );

		if ( $error->has_errors() ) {
			return new CreateContactResponse( '', $error );
		}

		$options = array();

		if ( $this->options !== null ) {
			$options = array(
				'X-OMNISEND-ORIGIN' => $this->options->get_origin(),
			);
		}

		$response = wp_remote_post(
			OMNISEND_CORE_API . '/contacts',
			array(
				'body'    => wp_json_encode( $contact->to_array() ),
				'headers' => array_merge( $this->get_request_headers(), $options ),
				'timeout' => 10,
			)
		);

		$arr = ApiResponse::parse( $response );
		if ( is_wp_error( $arr ) ) {
			$error->merge_from( $arr );
			return new CreateContactResponse( '', $error );
		}

		if ( empty( $arr['id'] ) ) {
			$error->merge_from( ApiResponse::unexpected_shape_error( 'Contact id' ) );
			return new CreateContactResponse( '', $error );
		}

		return new CreateContactResponse( (string) $arr['id'], $error );
	}

	public function save_contact( Contact $contact ): SaveContactResponse {
		$error = new WP_Error();

		if ( $contact instanceof Contact ) {
			$error->merge_from( $contact->validate() );
		} else {
			$error->add( 'contact', 'Contact is not instance of Omnisend\SDK\V1\Contact.' );
		}

		$error->merge_from( $this->check_setup() );

		if ( $error->has_errors() ) {
			return new SaveContactResponse( '', $error );
		}

		$options = array();

		if ( $this->options !== null ) {
			$options = array(
				'X-OMNISEND-ORIGIN' => $this->options->get_origin(),
			);
		}

		$response = wp_remote_post(
			OMNISEND_CORE_API . '/contacts',
			array(
				'body'    => wp_json_encode( $contact->to_array() ),
				'headers' => array_merge( $this->get_request_headers(), $options ),
				'timeout' => 10,
			)
		);

		$arr = ApiResponse::parse( $response );
		if ( is_wp_error( $arr ) ) {
			$error->merge_from( $arr );
			return new SaveContactResponse( '', $error );
		}

		if ( empty( $arr['id'] ) ) {
			$error->merge_from( ApiResponse::unexpected_shape_error( 'Contact id' ) );
			return new SaveContactResponse( '', $error );
		}

		return new SaveContactResponse( (string) $arr['id'], $error );
	}

	public function get_contact_by_email( string $email ): GetContactResponse {
		$error = new WP_Error();
		$email = rawurlencode( $email );

		$response = wp_remote_get(
			OMNISEND_CORE_API . '/contacts?email=' . $email,
			array(
				'headers' => $this->get_request_headers(),
				'timeout' => 10,
			)
		);

		$contact_data = ApiResponse::parse( $response );
		if ( is_wp_error( $contact_data ) ) {
			$error->merge_from( $contact_data );
			return new GetContactResponse( null, $error );
		}

		if ( ! isset( $contact_data['contacts'] ) || ! is_array( $contact_data['contacts'] ) ) {
			$error->merge_from( ApiResponse::unexpected_shape_error( 'Contact list' ) );
			return new GetContactResponse( null, $error );
		}

		if ( empty( $contact_data['contacts'] ) ) {
			$error->merge_from( ApiResponse::not_found_error( 'Contact' ) );
			return new GetContactResponse( null, $error );
		}

		if ( empty( $contact_data['contacts'][0]['id'] ) ) {
			$error->merge_from( ApiResponse::unexpected_shape_error( 'Contact id' ) );
			return new GetContactResponse( null, $error );
		}

		$contact = ContactFactory::create_contact( $contact_data['contacts'][0] );

		return new GetContactResponse( $contact, $error );
	}

	public function send_customer_event( $event ): SendCustomerEventResponse {
		$error = new WP_Error();

		if ( $event instanceof Event ) {
			$error->merge_from( $event->validate() );
		} else {
			$error->add( 'event', 'Event is not instance of Omnisend\SDK\V1\Event.' );
		}

		$error->merge_from( $this->check_setup() );

		if ( $error->has_errors() ) {
			return new SendCustomerEventResponse( $error );
		}

		$response = wp_remote_post(
			OMNISEND_CORE_API . '/events',
			array(
				'body'    => wp_json_encode( $event->to_array() ),
				'headers' => $this->get_request_headers(),
				'timeout' => 10,
			)
		);

		$parsed = ApiResponse::parse( $response, false );
		if ( is_wp_error( $parsed ) ) {
			$error->merge_from( $parsed );
		}

		return new SendCustomerEventResponse( $error );
	}

	public function connect_store( $platform ): ConnectStoreResponse {
		$error = new WP_Error();
		$error->merge_from( $this->check_setup() );

		if ( ! is_string( $platform ) ) {
			$error->add( 'platform', 'Platform must be string' );
		}

		$brand_id = $this->get_brand_id();
		if ( ! $brand_id ) {
			$error->add( 'brand_id', 'Unable to get brand_id. Please reinstall Omnisend plugin.' );
		}

		if ( $error->has_errors() ) {
			return new ConnectStoreResponse( $error );
		}

		$data = array(
			'website'         => site_url(),
			'platform'        => $platform,
			'version'         => $this->plugin_version,
			'phpVersion'      => phpversion(),
			'platformVersion' => get_bloginfo( 'version' ),
		);

		$response = wp_remote_post(
			OMNISEND_CORE_API . '/accounts/' . $brand_id,
			array(
				'body'    => wp_json_encode( $data ),
				'headers' => $this->get_request_headers(),
				'timeout' => 10,
			)
		);

		$parsed = ApiResponse::parse( $response, false );
		if ( is_wp_error( $parsed ) ) {
			$error->merge_from( $parsed );
		}

		return new ConnectStoreResponse( $error );
	}

	public function send_batch( $batch ): SendBatchResponse {
		$error = new WP_Error();
		$error->merge_from( $this->check_setup() );

		if ( $batch instanceof Batch ) {
			$error->merge_from( $batch->validate() );
		} else {
			$error->add( 'batch', 'batch is not an instance of Omnisend/SDK/V1/Batch' );
		}

		if ( $error->has_errors() ) {
			return new SendBatchResponse( $error );
		}

		$response = wp_remote_post(
			OMNISEND_CORE_API . '/batches',
			array(
				'body'    => wp_json_encode( $batch->to_array() ),
				'headers' => $this->get_request_headers(),
				'timeout' => 10,
			)
		);

		$arr = ApiResponse::parse( $response );

		if ( is_wp_error( $arr ) ) {
			$error->merge_from( $arr );

			return new SendBatchResponse( $error );
		}

		if ( empty( $arr['batchID'] ) ) {
			$error->merge_from( ApiResponse::unexpected_shape_error( 'batchID' ) );

			return new SendBatchResponse( $error );
		}

		return new SendBatchResponse( $error, $arr['batchID'] );
	}

	public function get_category_by_id( string $category_id ): GetCategoryResponse {
		$error = new WP_Error();

		$response = wp_remote_get(
			OMNISEND_CORE_API . '/product-categories/' . $category_id,
			array(
				'headers' => $this->get_request_headers(),
				'timeout' => 10,
			)
		);

		$category_data = ApiResponse::parse( $response );

		if ( is_wp_error( $category_data ) ) {
			$error->merge_from( $category_data );

			return new GetCategoryResponse( $error );
		}

		if ( empty( $category_data['categoryID'] ) ) {
			$error->merge_from( ApiResponse::unexpected_shape_error( 'categoryID' ) );

			return new GetCategoryResponse( $error );
		}

		$category = CategoryFactory::create_category( $category_data );

		return new GetCategoryResponse( $error, $category );
	}

	public function get_product_by_id( string $product_id ): GetProductResponse {
		$error = new WP_Error();

		$response = wp_remote_get(
			OMNISEND_CORE_API . '/products/' . $product_id,
			array(
				'headers' => $this->get_request_headers(),
				'timeout' => 10,
			)
		);

		$product_data = ApiResponse::parse( $response );

		if ( is_wp_error( $product_data ) ) {
			$error->merge_from( $product_data );

			return new GetProductResponse( $error );
		}

		if ( empty( $product_data['id'] ) ) {
			$error->merge_from( ApiResponse::unexpected_shape_error( 'Product id' ) );

			return new GetProductResponse( $error );
		}

		$product = ProductFactory::create_product( $product_data );

		return new GetProductResponse( $error, $product );
	}

	public function create_category( $category ): CreateCategoryResponse {
		$error = new WP_Error();

		if ( $category instanceof Category ) {
			$error->merge_from( $category->validate() );
		} else {
			$error->add( 'category', 'Category is not instance of Omnisend\SDK\V1\Category.' );
		}

		$error->merge_from( $this->check_setup() );

		if ( $error->has_errors() ) {
			return new CreateCategoryResponse( $error );
		}

		$response = wp_remote_post(
			OMNISEND_CORE_API . '/product-categories',
			array(
				'body'    => wp_json_encode( $category->to_array() ),
				'headers' => $this->get_request_headers(),
				'timeout' => 10,
			)
		);

		$arr = ApiResponse::parse( $response );

		if ( is_wp_error( $arr ) ) {
			$error->merge_from( $arr );

			return new CreateCategoryResponse( $error );
		}

		if ( empty( $arr['categoryID'] ) ) {
			$error->merge_from( ApiResponse::unexpected_shape_error( 'categoryID' ) );

			return new CreateCategoryResponse( $error );
		}

		return new CreateCategoryResponse( $error, $arr['categoryID'] );
	}

	public function update_category( $category ): UpdateCategoryResponse {
		$error = new WP_Error();

		if ( $category instanceof Category ) {
			$error->merge_from( $category->validate() );
		} else {
			$error->add( 'category', 'Category is not instance of Omnisend\SDK\V1\Category.' );
		}

		$error->merge_from( $this->check_setup() );

		if ( $error->has_errors() ) {
			return new UpdateCategoryResponse( $error );
		}

		$response = wp_remote_post(
			OMNISEND_CORE_API . '/product-categories/' . $category->get_category_id(),
			array(
				'method'  => 'PATCH',
				'body'    => wp_json_encode( $category->to_array_for_update() ),
				'headers' => $this->get_request_headers(),
				'timeout' => 10,
			)
		);

		$arr = ApiResponse::parse( $response );

		if ( is_wp_error( $arr ) ) {
			$error->merge_from( $arr );

			return new UpdateCategoryResponse( $error );
		}

		if ( empty( $arr['categoryID'] ) ) {
			$error->merge_from( ApiResponse::unexpected_shape_error( 'categoryID' ) );

			return new UpdateCategoryResponse( $error );
		}

		return new UpdateCategoryResponse( $error, $arr['categoryID'] );
	}

	public function delete_category_by_id( string $category_id ): DeleteCategoryResponse {
		$error = new WP_Error();

		if ( empty( $category_id ) ) {
			$error->add( 'category', 'Provided ID should not be empty' );
		}

		$error->merge_from( $this->check_setup() );

		if ( $error->has_errors() ) {
			return new DeleteCategoryResponse( $error );
		}

		$response = wp_remote_post(
			OMNISEND_CORE_API . '/product-categories/' . $category_id,
			array(
				'method'  => 'DELETE',
				'headers' => $this->get_request_headers(),
				'timeout' => 10,
			)
		);

		$parsed = ApiResponse::parse( $response, false );

		if ( is_wp_error( $parsed ) ) {
			$error->merge_from( $parsed );

			return new DeleteCategoryResponse( $error );
		}

		return new DeleteCategoryResponse( $error, true );
	}

	public function create_product( $product ): CreateProductResponse {
		$error = new WP_Error();

		if ( $product instanceof Product ) {
			$error->merge_from( $product->validate() );
		} else {
			$error->add( 'Product', 'Product is not instance of Omnisend\SDK\V1\Product.' );
		}

		$error->merge_from( $this->check_setup() );

		if ( $error->has_errors() ) {
			return new CreateProductResponse( $error );
		}

		$response = wp_remote_post(
			OMNISEND_CORE_API . '/products',
			array(
				'body'    => wp_json_encode( $product->to_array() ),
				'headers' => $this->get_request_headers(),
				'timeout' => 10,
			)
		);

		$arr = ApiResponse::parse( $response );

		if ( is_wp_error( $arr ) ) {
			$error->merge_from( $arr );

			return new CreateProductResponse( $error );
		}

		if ( empty( $arr['id'] ) ) {
			$error->merge_from( ApiResponse::unexpected_shape_error( 'Product id' ) );

			return new CreateProductResponse( $error );
		}

		return new CreateProductResponse( $error, $arr['id'] );
	}

	public function replace_product( $product ): CreateProductResponse {
		$error = new WP_Error();

		if ( $product instanceof Product ) {
			$error->merge_from( $product->validate() );
		} else {
			$error->add( 'product', 'Product is not instance of Omnisend\SDK\V1\Product.' );
		}

		$error->merge_from( $this->check_setup() );

		if ( $error->has_errors() ) {
			return new CreateProductResponse( $error );
		}

		$response = wp_remote_post(
			OMNISEND_CORE_API . '/products/' . $product->get_id(),
			array(
				'method'  => 'PUT',
				'body'    => wp_json_encode( $product->to_array() ),
				'headers' => $this->get_request_headers(),
				'timeout' => 10,
			)
		);

		$arr = ApiResponse::parse( $response );

		if ( is_wp_error( $arr ) ) {
			$error->merge_from( $arr );

			return new CreateProductResponse( $error );
		}

		if ( empty( $arr['id'] ) ) {
			$error->merge_from( ApiResponse::unexpected_shape_error( 'Product id' ) );

			return new CreateProductResponse( $error );
		}

		return new CreateProductResponse( $error, $arr['id'] );
	}

	public function delete_product_by_id( string $product_id ): DeleteProductResponse {
		$error = new WP_Error();

		if ( empty( $product_id ) ) {
			$error->add( 'product', 'Provided ID should not be empty' );
		}

		$error->merge_from( $this->check_setup() );

		if ( $error->has_errors() ) {
			return new DeleteProductResponse( $error );
		}

		$response = wp_remote_post(
			OMNISEND_CORE_API . '/products/' . $product_id,
			array(
				'method'  => 'DELETE',
				'headers' => $this->get_request_headers(),
				'timeout' => 10,
			)
		);

		$parsed = ApiResponse::parse( $response, false );

		if ( is_wp_error( $parsed ) ) {
			$error->merge_from( $parsed );

			return new DeleteProductResponse( $error );
		}

		return new DeleteProductResponse( $error, true );
	}

	/**
	 * @return WP_Error
	 */
	private function check_setup(): WP_Error {
		$error = new WP_Error();

		if ( ! $this->plugin_name ) {
			$error->add( 'initialisation', 'Client is created with empty plugin name.' );
		}

		if ( ! $this->plugin_version ) {
			$error->add( 'initialisation', 'Client is created with empty plugin version.' );
		}

		if ( ! $this->api_key ) {
			$error->add( 'api_key', 'Omnisend plugin is not connected.' );
		}

		return $error;
	}

	private function get_brand_id(): string {
		$list = explode( '-', $this->api_key );
		if ( count( $list ) != 2 ) {
			return '';
		}

		return $list[0];
	}
}
