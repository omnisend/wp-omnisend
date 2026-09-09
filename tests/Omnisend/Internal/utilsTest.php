<?php

namespace Omnisend\Tests\Unit\Internal;

use Omnisend\Internal\Utils;
use PHPUnit\Framework\TestCase;

require_once( __DIR__ . '/../../dependencies/dependencies.php' );

final class UtilsTest extends TestCase
{
	public function test_custom_property_validation()
	{
		$this->assertTrue( Utils::is_valid_custom_property_name( Utils::clean_up_custom_property_name( 'ABC-_!@#!ĄČ ' ) ) );
	}

	public function test_tag_validation()
	{
		$this->assertTrue( Utils::is_valid_tag( Utils::clean_up_tag( 'ABC-_!@#!ĄČ ' ) ) );
	}

	public function test_api_date_time_validation()
	{
		$this->assertTrue( Utils::is_valid_api_date_time( '2021-01-04T08:30:24Z' ) );
		$this->assertTrue( Utils::is_valid_api_date_time( '2021-01-04T08:30:24.000Z' ) );
		$this->assertFalse( Utils::is_valid_api_date_time( '2021-01-04T08:30:24+02:00' ) );
		$this->assertFalse( Utils::is_valid_api_date_time( '2021-01-04 08:30:24' ) );
		$this->assertFalse( Utils::is_valid_api_date_time( 20210104 ) );
	}

	public function test_api_url_validation()
	{
		$this->assertTrue( Utils::is_valid_api_url( 'https://shop.lt/nuotraukos/žiedas.png' ) );
		$this->assertTrue( Utils::is_valid_api_url( 'http://omnisend.com/media/product.png' ) );
		$this->assertFalse( Utils::is_valid_api_url( 'media/product.png' ) );
		$this->assertFalse( Utils::is_valid_api_url( 'ftp://omnisend.com/product.png' ) );
		$this->assertFalse( Utils::is_valid_api_url( 'https:///product.png' ) );
	}
}