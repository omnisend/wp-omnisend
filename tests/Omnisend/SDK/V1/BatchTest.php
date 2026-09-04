<?php

namespace Omnisend\SDK\V1;

use Omnisend\Internal\CategoryFactory;
use PHPUnit\Framework\TestCase;

require_once( __DIR__ . '/../../../dependencies/dependencies.php' );

final class BatchTest extends TestCase
{
    public function test_fails_with_undefined_data(): void {
        $batch = new Batch();

        $expected_result = array(
            'items' => array('items is a required property.'),
            'method' => array('method is a required property.')
        );

        $this->assertEquals($expected_result, $batch->validate()->errors);
    }

    public function test_100_items_pass_validation(): void {
        $batch = $this->batch($this->categories(100));

        $this->assertFalse($batch->validate()->has_errors());
    }

    public function test_101_items_fail_validation(): void {
        $batch = $this->batch($this->categories(101));

        $this->assertEquals(
            'Items are empty or batch size limit: 100 was exceeded',
            $batch->validate()->get_error_message('items')
        );
    }

    public function test_unsupported_method_fails_validation(): void {
        $batch = $this->batch($this->categories(1));
        $batch->set_method('PATCH');

        $this->assertEquals(
            'Method must be one of the following: POST,PUT',
            $batch->validate()->get_error_message('method')
        );
    }

    public function test_mixed_items_fail_validation(): void {
        $items = $this->categories(1);
        $items[] = new Product();

        $batch = $this->batch($items);

        $this->assertEquals(
            'Mixed items found, make sure items are of one type: categories,products,contacts,events',
            $batch->validate()->get_error_message('Items')
        );
    }

    public function test_to_array_uses_categories_endpoint(): void {
        $batch = $this->batch($this->categories(1));

        $expected_result = array(
            'endpoint' => 'categories',
            'method' => 'POST',
            'items' => array(
                array(
                    'categoryID' => 'category-0',
                    'title' => 'Beauty products'
                )
            )
        );

        $this->assertEquals($expected_result, $batch->to_array());
    }

    public function test_to_array_includes_origin(): void {
        $batch = $this->batch($this->categories(1));
        $batch->set_origin('omnisend');

        $this->assertEquals('omnisend', $batch->to_array()['origin']);
    }

    private function batch(array $items): Batch {
        $batch = new Batch();
        $batch->set_method(Batch::POST_METHOD);
        $batch->set_items($items);

        return $batch;
    }

    private function categories(int $count): array {
        $categories = array();

        for ($i = 0; $i < $count; $i++) {
            $categories[] = CategoryFactory::create_category(
                array('categoryID' => 'category-' . $i, 'title' => 'Beauty products')
            );
        }

        return $categories;
    }
}
