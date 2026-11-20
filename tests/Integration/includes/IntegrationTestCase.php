<?php

namespace Omnisend\Tests\Integration;

use WP_UnitTestCase;

abstract class IntegrationTestCase extends WP_UnitTestCase {

	protected HttpStub $http;

	public function set_up(): void {
		parent::set_up();

		$this->http = new HttpStub();
		$this->http->register();
	}
}
