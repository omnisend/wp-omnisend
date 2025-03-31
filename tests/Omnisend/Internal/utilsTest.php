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
}