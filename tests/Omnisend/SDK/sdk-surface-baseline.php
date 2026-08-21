<?php
/**
 * Baseline of the public SDK surface. Regenerate with tests/tools/dump-sdk-surface.php
 * only when the change to the surface is deliberate and approved.
 */

return array (
  'Omnisend\\SDK\\V1\\Batch' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
      'POST_METHOD' => 'POST',
      'PUT_METHOD' => 'PUT',
    ),
    'methods' => 
    array (
      'add_item' => 'public function add_item($item): void',
      'set_items' => 'public function set_items($items): void',
      'set_method' => 'public function set_method($method): void',
      'set_origin' => 'public function set_origin($origin): void',
      'to_array' => 'public function to_array(): array',
      'validate' => 'public function validate(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\Category' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      'get_category_id' => 'public function get_category_id(): string',
      'get_title' => 'public function get_title(): string',
      'set_category_id' => 'public function set_category_id($category_id): void',
      'set_title' => 'public function set_title($title): void',
      'to_array' => 'public function to_array(): array',
      'to_array_for_update' => 'public function to_array_for_update(): array',
      'validate' => 'public function validate(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\Client' => 
  array (
    'kind' => 'interface',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      'connect_store' => 'public function connect_store(string $platform): Omnisend\\SDK\\V1\\ConnectStoreResponse',
      'create_category' => 'public function create_category($category): Omnisend\\SDK\\V1\\CreateCategoryResponse',
      'create_contact' => 'public function create_contact($contact): Omnisend\\SDK\\V1\\CreateContactResponse',
      'create_product' => 'public function create_product($product): Omnisend\\SDK\\V1\\CreateProductResponse',
      'delete_category_by_id' => 'public function delete_category_by_id(string $category_id): Omnisend\\SDK\\V1\\DeleteCategoryResponse',
      'delete_product_by_id' => 'public function delete_product_by_id(string $product_id): Omnisend\\SDK\\V1\\DeleteProductResponse',
      'get_category_by_id' => 'public function get_category_by_id(string $category_id): Omnisend\\SDK\\V1\\GetCategoryResponse',
      'get_contact_by_email' => 'public function get_contact_by_email(string $email): Omnisend\\SDK\\V1\\GetContactResponse',
      'get_product_by_id' => 'public function get_product_by_id(string $product_id): Omnisend\\SDK\\V1\\GetProductResponse',
      'replace_product' => 'public function replace_product($product): Omnisend\\SDK\\V1\\CreateProductResponse',
      'save_contact' => 'public function save_contact(Omnisend\\SDK\\V1\\Contact $contact): Omnisend\\SDK\\V1\\SaveContactResponse',
      'send_batch' => 'public function send_batch($batch): Omnisend\\SDK\\V1\\SendBatchResponse',
      'send_customer_event' => 'public function send_customer_event($event): Omnisend\\SDK\\V1\\SendCustomerEventResponse',
      'update_category' => 'public function update_category($category): Omnisend\\SDK\\V1\\UpdateCategoryResponse',
    ),
  ),
  'Omnisend\\SDK\\V1\\ConnectStoreResponse' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      '__construct' => 'public function __construct(WP_Error $wp_error)',
      'get_wp_error' => 'public function get_wp_error(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\Contact' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      'add_custom_property' => 'public function add_custom_property($key, $value, $clean_up_key = true): void',
      'add_tag' => 'public function add_tag($tag, $clean_up_tag = true): void',
      'get_address' => 'public function get_address(): string',
      'get_birthday' => 'public function get_birthday(): string',
      'get_city' => 'public function get_city(): string',
      'get_country' => 'public function get_country(): string',
      'get_custom_properties' => 'public function get_custom_properties(): array',
      'get_email' => 'public function get_email(): string',
      'get_email_status' => 'public function get_email_status(): string',
      'get_first_name' => 'public function get_first_name(): string',
      'get_gender' => 'public function get_gender(): string',
      'get_last_name' => 'public function get_last_name(): string',
      'get_phone' => 'public function get_phone(): string',
      'get_phone_status' => 'public function get_phone_status(): string',
      'get_postal_code' => 'public function get_postal_code(): string',
      'get_state' => 'public function get_state(): string',
      'get_tags' => 'public function get_tags(): array',
      'set_address' => 'public function set_address($address): void',
      'set_birthday' => 'public function set_birthday($birthday): void',
      'set_city' => 'public function set_city($city): void',
      'set_country' => 'public function set_country($country): void',
      'set_email' => 'public function set_email($email): void',
      'set_email_consent' => 'public function set_email_consent($consent_text): void',
      'set_email_opt_in' => 'public function set_email_opt_in($opt_in_text): void',
      'set_email_subscriber' => 'public function set_email_subscriber(): void',
      'set_email_unsubscriber' => 'public function set_email_unsubscriber(): void',
      'set_first_name' => 'public function set_first_name($first_name): void',
      'set_gender' => 'public function set_gender($gender): void',
      'set_id' => 'public function set_id($id): void',
      'set_last_name' => 'public function set_last_name($last_name): void',
      'set_phone' => 'public function set_phone($phone): void',
      'set_phone_consent' => 'public function set_phone_consent($consent_text): void',
      'set_phone_opt_in' => 'public function set_phone_opt_in($opt_in_text): void',
      'set_phone_subscriber' => 'public function set_phone_subscriber(): void',
      'set_phone_unsubscriber' => 'public function set_phone_unsubscriber(): void',
      'set_postal_code' => 'public function set_postal_code($postal_code): void',
      'set_state' => 'public function set_state($state): void',
      'set_welcome_email' => 'public function set_welcome_email($send_welcome_email): void',
      'to_array' => 'public function to_array(): array',
      'to_array_for_event' => 'public function to_array_for_event(): array',
      'validate' => 'public function validate(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\CreateCategoryResponse' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      '__construct' => 'public function __construct(WP_Error $wp_error, string $category_id = \'\')',
      'get_category_id' => 'public function get_category_id(): string',
      'get_wp_error' => 'public function get_wp_error(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\CreateContactResponse' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      '__construct' => 'public function __construct(string $contact_id, WP_Error $wp_error)',
      'get_contact_id' => 'public function get_contact_id(): string',
      'get_wp_error' => 'public function get_wp_error(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\CreateProductResponse' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      '__construct' => 'public function __construct(WP_Error $wp_error, string $product_id = \'\')',
      'get_product_id' => 'public function get_product_id(): string',
      'get_wp_error' => 'public function get_wp_error(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\DeleteCategoryResponse' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      '__construct' => 'public function __construct(WP_Error $wp_error, bool $success = false)',
      'get_response' => 'public function get_response(): bool',
      'get_wp_error' => 'public function get_wp_error(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\DeleteProductResponse' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      '__construct' => 'public function __construct(WP_Error $wp_error, bool $success = false)',
      'get_response' => 'public function get_response(): bool',
      'get_wp_error' => 'public function get_wp_error(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\Event' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      'add_custom_event_property' => 'public function add_custom_event_property($key, $value): void',
      'set_contact' => 'public function set_contact($contact): void',
      'set_custom_event_name' => 'public function set_custom_event_name($event_name): void',
      'set_custom_event_properties' => 'public function set_custom_event_properties($properties): void',
      'set_event_time' => 'public function set_event_time($event_time): void',
      'set_event_version' => 'public function set_event_version($event_version): void',
      'set_origin' => 'public function set_origin($origin): void',
      'set_recommended_event' => 'public function set_recommended_event($event): void',
      'to_array' => 'public function to_array(): array',
      'validate' => 'public function validate(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\Events\\AddedProductToCart' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
      'EVENT_NAME' => 'added product to cart',
    ),
    'methods' => 
    array (
      'add_line_item' => 'public function add_line_item($item): void',
      'set_abandoned_checkout_url' => 'public function set_abandoned_checkout_url($abandoned_checkout_url): void',
      'set_added_item' => 'public function set_added_item($item): void',
      'set_cart_id' => 'public function set_cart_id($cart_id): void',
      'set_currency' => 'public function set_currency($currency): void',
      'set_value' => 'public function set_value($value): void',
      'to_array' => 'public function to_array(): array',
      'validate' => 'public function validate(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\Events\\Components\\Address' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      'set_billing_address_1' => 'public function set_billing_address_1($billing_address_1): void',
      'set_billing_address_2' => 'public function set_billing_address_2($billing_address_2): void',
      'set_billing_city' => 'public function set_billing_city($billing_city): void',
      'set_billing_company' => 'public function set_billing_company($billing_company): void',
      'set_billing_country' => 'public function set_billing_country($billing_country): void',
      'set_billing_first_name' => 'public function set_billing_first_name($billing_first_name): void',
      'set_billing_last_name' => 'public function set_billing_last_name($billing_last_name): void',
      'set_billing_phone' => 'public function set_billing_phone($billing_phone): void',
      'set_billing_state' => 'public function set_billing_state($billing_state): void',
      'set_billing_state_code' => 'public function set_billing_state_code($billing_state_code): void',
      'set_billing_zip' => 'public function set_billing_zip($billing_zip): void',
      'set_shipping_address_1' => 'public function set_shipping_address_1($shipping_address_1): void',
      'set_shipping_address_2' => 'public function set_shipping_address_2($shipping_address_2): void',
      'set_shipping_city' => 'public function set_shipping_city($shipping_city): void',
      'set_shipping_company' => 'public function set_shipping_company($shipping_company): void',
      'set_shipping_country' => 'public function set_shipping_country($shipping_country): void',
      'set_shipping_first_name' => 'public function set_shipping_first_name($shipping_first_name): void',
      'set_shipping_last_name' => 'public function set_shipping_last_name($shipping_last_name): void',
      'set_shipping_phone' => 'public function set_shipping_phone($shipping_phone): void',
      'set_shipping_state' => 'public function set_shipping_state($shipping_state): void',
      'set_shipping_state_code' => 'public function set_shipping_state_code($shipping_state_code): void',
      'set_shipping_zip' => 'public function set_shipping_zip($shipping_zip): void',
      'to_array_billing' => 'public function to_array_billing(): array',
      'to_array_shipping' => 'public function to_array_shipping(): array',
      'validate' => 'public function validate(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\Events\\Components\\Discount' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      'set_amount' => 'public function set_amount($amount): void',
      'set_code' => 'public function set_code($code): void',
      'set_type' => 'public function set_type($type): void',
      'to_array' => 'public function to_array()',
      'validate' => 'public function validate(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\Events\\Components\\LineItem' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      'add_category' => 'public function add_category($product_category): void',
      'add_tag' => 'public function add_tag($tag, $clean_up_tag = true): void',
      'set_description' => 'public function set_description($description): void',
      'set_discount' => 'public function set_discount($discount_amount): void',
      'set_id' => 'public function set_id($id): void',
      'set_image_url' => 'public function set_image_url($image_url): void',
      'set_price' => 'public function set_price($price): void',
      'set_quantity' => 'public function set_quantity($quantity): void',
      'set_sku' => 'public function set_sku($sku): void',
      'set_strike_through_price' => 'public function set_strike_through_price($strike_through_price): void',
      'set_title' => 'public function set_title($title): void',
      'set_url' => 'public function set_url($url): void',
      'set_variant_id' => 'public function set_variant_id($variant_id): void',
      'set_variant_image_url' => 'public function set_variant_image_url($variant_image_url): void',
      'set_variant_title' => 'public function set_variant_title($variant_title): void',
      'set_vendor' => 'public function set_vendor($vendor): void',
      'set_weight' => 'public function set_weight($weight): void',
      'to_array' => 'public function to_array(): array',
      'validate' => 'public function validate(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\Events\\Components\\ProductCategory' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      'set_id' => 'public function set_id($id): void',
      'set_title' => 'public function set_title($title): void',
      'to_array' => 'public function to_array()',
      'validate' => 'public function validate(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\Events\\Components\\Tracking' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      'set_code' => 'public function set_code($code): void',
      'set_courier_title' => 'public function set_courier_title($title): void',
      'set_courier_url' => 'public function set_courier_url($url): void',
      'to_array' => 'public function to_array()',
      'validate' => 'public function validate(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\Events\\OrderBase' => 
  array (
    'kind' => 'abstract class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      'to_array' => 'public function to_array(): array',
      'validate' => 'public function validate(): WP_Error',
      'validate_extra_properties' => 'public function validate_extra_properties(WP_Error $wp_error): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\Events\\OrderCanceled' => 
  array (
    'kind' => 'class',
    'parent' => 'Omnisend\\SDK\\V1\\Events\\OrderBase',
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
      'EVENT_NAME' => 'order canceled',
    ),
    'methods' => 
    array (
      'add_discount' => 'public function add_discount($discount): void',
      'add_line_item' => 'public function add_line_item($line_item): void',
      'add_tag' => 'public function add_tag($tag, $clean_up_tag = true): void',
      'set_address' => 'public function set_address($address): void',
      'set_cancel_reason' => 'public function set_cancel_reason($cancel_reason): void',
      'set_created_at' => 'public function set_created_at($created_at): void',
      'set_currency' => 'public function set_currency($currency): void',
      'set_fulfillment_status' => 'public function set_fulfillment_status($fulfillment_status): void',
      'set_id' => 'public function set_id($id): void',
      'set_note' => 'public function set_note($note): void',
      'set_number' => 'public function set_number($number): void',
      'set_payment_method' => 'public function set_payment_method($payment_method): void',
      'set_payment_status' => 'public function set_payment_status($payment_status): void',
      'set_shipping_method' => 'public function set_shipping_method($shipping_method): void',
      'set_shipping_price' => 'public function set_shipping_price($shipping_price): void',
      'set_status_url' => 'public function set_status_url($status_url): void',
      'set_subtotal_price' => 'public function set_subtotal_price($subtotal_price): void',
      'set_subtotal_tax_included' => 'public function set_subtotal_tax_included($subtotal_tax_included): void',
      'set_total_discount' => 'public function set_total_discount($total_discount): void',
      'set_total_price' => 'public function set_total_price($total_price): void',
      'set_total_tax' => 'public function set_total_tax($total_tax): void',
      'set_tracking' => 'public function set_tracking($tracking): void',
    ),
  ),
  'Omnisend\\SDK\\V1\\Events\\OrderFulfilled' => 
  array (
    'kind' => 'class',
    'parent' => 'Omnisend\\SDK\\V1\\Events\\OrderBase',
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
      'EVENT_NAME' => 'order fulfilled',
    ),
    'methods' => 
    array (
      'add_discount' => 'public function add_discount($discount): void',
      'add_line_item' => 'public function add_line_item($line_item): void',
      'add_tag' => 'public function add_tag($tag, $clean_up_tag = true): void',
      'set_address' => 'public function set_address($address): void',
      'set_created_at' => 'public function set_created_at($created_at): void',
      'set_currency' => 'public function set_currency($currency): void',
      'set_fulfillment_status' => 'public function set_fulfillment_status($fulfillment_status): void',
      'set_id' => 'public function set_id($id): void',
      'set_note' => 'public function set_note($note): void',
      'set_number' => 'public function set_number($number): void',
      'set_payment_method' => 'public function set_payment_method($payment_method): void',
      'set_payment_status' => 'public function set_payment_status($payment_status): void',
      'set_shipping_method' => 'public function set_shipping_method($shipping_method): void',
      'set_shipping_price' => 'public function set_shipping_price($shipping_price): void',
      'set_status_url' => 'public function set_status_url($status_url): void',
      'set_subtotal_price' => 'public function set_subtotal_price($subtotal_price): void',
      'set_subtotal_tax_included' => 'public function set_subtotal_tax_included($subtotal_tax_included): void',
      'set_total_discount' => 'public function set_total_discount($total_discount): void',
      'set_total_price' => 'public function set_total_price($total_price): void',
      'set_total_tax' => 'public function set_total_tax($total_tax): void',
      'set_tracking' => 'public function set_tracking($tracking): void',
    ),
  ),
  'Omnisend\\SDK\\V1\\Events\\OrderRefunded' => 
  array (
    'kind' => 'class',
    'parent' => 'Omnisend\\SDK\\V1\\Events\\OrderBase',
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
      'EVENT_NAME' => 'order refunded',
    ),
    'methods' => 
    array (
      'add_discount' => 'public function add_discount($discount): void',
      'add_line_item' => 'public function add_line_item($line_item): void',
      'add_refunded_line_item' => 'public function add_refunded_line_item($refunded_line_item): void',
      'add_tag' => 'public function add_tag($tag, $clean_up_tag = true): void',
      'set_address' => 'public function set_address($address): void',
      'set_created_at' => 'public function set_created_at($created_at): void',
      'set_currency' => 'public function set_currency($currency): void',
      'set_fulfillment_status' => 'public function set_fulfillment_status($fulfillment_status): void',
      'set_id' => 'public function set_id($id): void',
      'set_note' => 'public function set_note($note): void',
      'set_number' => 'public function set_number($number): void',
      'set_payment_method' => 'public function set_payment_method($payment_method): void',
      'set_payment_status' => 'public function set_payment_status($payment_status): void',
      'set_shipping_method' => 'public function set_shipping_method($shipping_method): void',
      'set_shipping_price' => 'public function set_shipping_price($shipping_price): void',
      'set_status_url' => 'public function set_status_url($status_url): void',
      'set_subtotal_price' => 'public function set_subtotal_price($subtotal_price): void',
      'set_subtotal_tax_included' => 'public function set_subtotal_tax_included($subtotal_tax_included): void',
      'set_total_discount' => 'public function set_total_discount($total_discount): void',
      'set_total_price' => 'public function set_total_price($total_price): void',
      'set_total_refunded_amount' => 'public function set_total_refunded_amount($total_refunded_amount): void',
      'set_total_refunded_tax_amount' => 'public function set_total_refunded_tax_amount($total_refunded_tax_amount): void',
      'set_total_tax' => 'public function set_total_tax($total_tax): void',
      'set_tracking' => 'public function set_tracking($tracking): void',
      'validate_extra_properties' => 'public function validate_extra_properties(WP_Error $error): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\Events\\PaidForOrder' => 
  array (
    'kind' => 'class',
    'parent' => 'Omnisend\\SDK\\V1\\Events\\OrderBase',
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
      'EVENT_NAME' => 'paid for order',
    ),
    'methods' => 
    array (
      'add_discount' => 'public function add_discount($discount): void',
      'add_line_item' => 'public function add_line_item($line_item): void',
      'add_tag' => 'public function add_tag($tag, $clean_up_tag = true): void',
      'set_address' => 'public function set_address($address): void',
      'set_created_at' => 'public function set_created_at($created_at): void',
      'set_currency' => 'public function set_currency($currency): void',
      'set_fulfillment_status' => 'public function set_fulfillment_status($fulfillment_status): void',
      'set_id' => 'public function set_id($id): void',
      'set_note' => 'public function set_note($note): void',
      'set_number' => 'public function set_number($number): void',
      'set_payment_method' => 'public function set_payment_method($payment_method): void',
      'set_payment_status' => 'public function set_payment_status($payment_status): void',
      'set_shipping_method' => 'public function set_shipping_method($shipping_method): void',
      'set_shipping_price' => 'public function set_shipping_price($shipping_price): void',
      'set_status_url' => 'public function set_status_url($status_url): void',
      'set_subtotal_price' => 'public function set_subtotal_price($subtotal_price): void',
      'set_subtotal_tax_included' => 'public function set_subtotal_tax_included($subtotal_tax_included): void',
      'set_total_discount' => 'public function set_total_discount($total_discount): void',
      'set_total_price' => 'public function set_total_price($total_price): void',
      'set_total_tax' => 'public function set_total_tax($total_tax): void',
      'set_tracking' => 'public function set_tracking($tracking): void',
    ),
  ),
  'Omnisend\\SDK\\V1\\Events\\PlacedOrder' => 
  array (
    'kind' => 'class',
    'parent' => 'Omnisend\\SDK\\V1\\Events\\OrderBase',
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
      'EVENT_NAME' => 'placed order',
    ),
    'methods' => 
    array (
      'add_discount' => 'public function add_discount($discount): void',
      'add_line_item' => 'public function add_line_item($line_item): void',
      'add_tag' => 'public function add_tag($tag, $clean_up_tag = true): void',
      'set_address' => 'public function set_address($address): void',
      'set_created_at' => 'public function set_created_at($created_at): void',
      'set_currency' => 'public function set_currency($currency): void',
      'set_fulfillment_status' => 'public function set_fulfillment_status($fulfillment_status): void',
      'set_id' => 'public function set_id($id): void',
      'set_note' => 'public function set_note($note): void',
      'set_number' => 'public function set_number($number): void',
      'set_payment_method' => 'public function set_payment_method($payment_method): void',
      'set_payment_status' => 'public function set_payment_status($payment_status): void',
      'set_shipping_method' => 'public function set_shipping_method($shipping_method): void',
      'set_shipping_price' => 'public function set_shipping_price($shipping_price): void',
      'set_status_url' => 'public function set_status_url($status_url): void',
      'set_subtotal_price' => 'public function set_subtotal_price($subtotal_price): void',
      'set_subtotal_tax_included' => 'public function set_subtotal_tax_included($subtotal_tax_included): void',
      'set_total_discount' => 'public function set_total_discount($total_discount): void',
      'set_total_price' => 'public function set_total_price($total_price): void',
      'set_total_tax' => 'public function set_total_tax($total_tax): void',
      'set_tracking' => 'public function set_tracking($tracking): void',
    ),
  ),
  'Omnisend\\SDK\\V1\\Events\\StartedCheckout' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
      'EVENT_NAME' => 'started checkout',
    ),
    'methods' => 
    array (
      'add_line_item' => 'public function add_line_item($item): void',
      'set_abandoned_checkout_url' => 'public function set_abandoned_checkout_url($abandoned_checkout_url): void',
      'set_cart_id' => 'public function set_cart_id($cart_id): void',
      'set_currency' => 'public function set_currency($currency): void',
      'set_value' => 'public function set_value($value): void',
      'to_array' => 'public function to_array(): array',
      'validate' => 'public function validate(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\GetCategoryResponse' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      '__construct' => 'public function __construct(WP_Error $wp_error, ?Omnisend\\SDK\\V1\\Category $category = NULL)',
      'get_category' => 'public function get_category(): ?Omnisend\\SDK\\V1\\Category',
      'get_wp_error' => 'public function get_wp_error(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\GetContactResponse' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      '__construct' => 'public function __construct(?Omnisend\\SDK\\V1\\Contact $contact, WP_Error $wp_error)',
      'get_contact' => 'public function get_contact(): Omnisend\\SDK\\V1\\Contact',
      'get_wp_error' => 'public function get_wp_error(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\GetProductResponse' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      '__construct' => 'public function __construct(WP_Error $wp_error, ?Omnisend\\SDK\\V1\\Product $product = NULL)',
      'get_product' => 'public function get_product(): ?Omnisend\\SDK\\V1\\Product',
      'get_wp_error' => 'public function get_wp_error(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\Omnisend' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      'get_client' => 'public static function get_client($plugin, $version, $options = NULL): Omnisend\\Internal\\V1\\Client',
      'is_connected' => 'public static function is_connected(): bool',
    ),
  ),
  'Omnisend\\SDK\\V1\\Options' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      'get_origin' => 'public function get_origin(): string',
      'set_origin' => 'public function set_origin($origin): void',
    ),
  ),
  'Omnisend\\SDK\\V1\\Product' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
      'AVAILABLE_STATUS' => 
      array (
        0 => 'inStock',
        1 => 'outOfStock',
        2 => 'notAvailable',
      ),
      'STATUS_IN_STOCK' => 'inStock',
      'STATUS_NOT_AVAILABLE' => 'notAvailable',
      'STATUS_OUT_OF_STOCK' => 'outOfStock',
    ),
    'methods' => 
    array (
      'add_category_id' => 'public function add_category_id($category_id): void',
      'add_image' => 'public function add_image($image_url): void',
      'add_tag' => 'public function add_tag($tag, $clean_up_tag = true): void',
      'add_variant' => 'public function add_variant($variant): void',
      'get_category_ids' => 'public function get_category_ids(): ?array',
      'get_created_at' => 'public function get_created_at(): ?string',
      'get_currency' => 'public function get_currency(): ?string',
      'get_default_image_url' => 'public function get_default_image_url(): ?string',
      'get_descripton' => 'public function get_descripton(): ?string',
      'get_id' => 'public function get_id(): ?string',
      'get_images' => 'public function get_images(): ?array',
      'get_status' => 'public function get_status(): ?string',
      'get_tags' => 'public function get_tags(): ?array',
      'get_title' => 'public function get_title(): ?string',
      'get_type' => 'public function get_type(): ?string',
      'get_updated_at' => 'public function get_updated_at(): ?string',
      'get_url' => 'public function get_url(): ?string',
      'get_variants' => 'public function get_variants(): ?array',
      'get_vendor' => 'public function get_vendor(): ?string',
      'set_created_at' => 'public function set_created_at($created_at): void',
      'set_currency' => 'public function set_currency($currency): void',
      'set_default_image_url' => 'public function set_default_image_url($default_image_url): void',
      'set_description' => 'public function set_description($description): void',
      'set_id' => 'public function set_id($id): void',
      'set_status' => 'public function set_status($status): void',
      'set_title' => 'public function set_title($title): void',
      'set_type' => 'public function set_type($type): void',
      'set_updated_at' => 'public function set_updated_at($updated_at): void',
      'set_url' => 'public function set_url($url): void',
      'set_vendor' => 'public function set_vendor($vendor): void',
      'to_array' => 'public function to_array(): array',
      'validate' => 'public function validate(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\ProductVariant' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      'add_image' => 'public function add_image($image_url): void',
      'set_default_image_url' => 'public function set_default_image_url($default_image_url): void',
      'set_description' => 'public function set_description($description): void',
      'set_id' => 'public function set_id($id): void',
      'set_price' => 'public function set_price($price): void',
      'set_sku' => 'public function set_sku($sku): void',
      'set_status' => 'public function set_status($status): void',
      'set_strike_through_price' => 'public function set_strike_through_price($strike_through_price): void',
      'set_title' => 'public function set_title($title): void',
      'set_url' => 'public function set_url($url): void',
      'to_array' => 'public function to_array(): array',
      'validate' => 'public function validate(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\SaveContactResponse' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      '__construct' => 'public function __construct(string $contact_id, WP_Error $wp_error)',
      'get_contact_id' => 'public function get_contact_id(): string',
      'get_wp_error' => 'public function get_wp_error(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\SendBatchResponse' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      '__construct' => 'public function __construct(WP_Error $wp_error, string $batch_id = \'\')',
      'get_batch_id' => 'public function get_batch_id(): string',
      'get_wp_error' => 'public function get_wp_error(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\SendCustomerEventResponse' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      '__construct' => 'public function __construct(WP_Error $wp_error)',
      'get_wp_error' => 'public function get_wp_error(): WP_Error',
    ),
  ),
  'Omnisend\\SDK\\V1\\UpdateCategoryResponse' => 
  array (
    'kind' => 'class',
    'parent' => NULL,
    'interfaces' => 
    array (
    ),
    'constants' => 
    array (
    ),
    'methods' => 
    array (
      '__construct' => 'public function __construct(WP_Error $wp_error, string $category_id = \'\')',
      'get_category_id' => 'public function get_category_id(): string',
      'get_wp_error' => 'public function get_wp_error(): WP_Error',
    ),
  ),
);
