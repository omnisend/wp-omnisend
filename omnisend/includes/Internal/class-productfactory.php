<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\Internal;

use Omnisend\SDK\V1\Product;

class ProductFactory {
	/**
	 * Create a product object from an array of category data.
	 *
	 * @param array $product_data
	 *
	 * @return Product
	 */
	public static function create_product( array $product_data ): Product {
		$product = new Product();

		if ( isset( $product_data['categoryIDs'] ) && is_array( $product_data['categoryIDs'] ) ) {
			foreach ( $product_data['categoryIDs'] as $category_id ) {
				$product->add_category_id( $category_id );
			}
		}

		if ( isset( $product_data['variants'] ) && is_array( $product_data['variants'] ) ) {
			foreach ( $product_data['variants'] as $variant ) {
				$product->add_variant( $variant );
			}
		}

		if ( isset( $product_data['images'] ) && is_array( $product_data['images'] ) ) {
			foreach ( $product_data['images'] as $image ) {
				$product->add_image( $image );
			}
		}

		if ( isset( $product_data['createdAt'] ) ) {
			$product->set_created_at( $product_data['createdAt'] );
		}

		if ( isset( $product_data['currency'] ) ) {
			$product->set_currency( $product_data['currency'] );
		}

		if ( isset( $product_data['defaultImageUrl'] ) ) {
			$product->set_default_image_url( $product_data['defaultImageUrl'] );
		}

		if ( isset( $product_data['description'] ) ) {
			$product->set_descripton( $product_data['description'] );
		}

		if ( isset( $product_data['id'] ) ) {
			$product->set_id( $product_data['id'] );
		}

		if ( isset( $product_data['status'] ) ) {
			$product->set_status( $product_data['status'] );
		}

		if ( isset( $product_data['tags'] ) ) {
			foreach ( $product_data['tags'] as $tag ) {
				$product->add_tag( $tag );
			}
		}

		if ( isset( $product_data['title'] ) ) {
			$product->set_title( $product_data['title'] );
		}

		if ( isset( $product_data['type'] ) ) {
			$product->set_type( $product_data['type'] );
		}

		if ( isset( $product_data['updatedAt'] ) ) {
			$product->set_updated_at( $product_data['updatedAt'] );
		}

		if ( isset( $product_data['url'] ) ) {
			$product->set_url( $product_data['url'] );
		}

		if ( isset( $product_data['vendor'] ) ) {
			$product->set_vendor( $product_data['vendor'] );
		}

		return $product;
	}
}
