<?php

namespace Omnisend\Tests\Integration;

use RuntimeException;

/**
 * Answers outgoing WordPress HTTP requests from queued responses and records them.
 * A request without a queued response fails the test, so nothing reaches a real server.
 */
final class HttpStub {

	private array $responses = array();
	private array $requests  = array();

	public function register(): void {
		add_filter( 'pre_http_request', array( $this, 'handle' ), 10, 3 );
	}

	public function respond( string $method, string $url, int $status, ?array $body = null ): void {
		$this->responses[] = array(
			'method'   => $method,
			'url'      => $url,
			'response' => array(
				'headers'  => array(),
				'body'     => null === $body ? '' : wp_json_encode( $body ),
				'response' => array(
					'code'    => $status,
					'message' => get_status_header_desc( $status ),
				),
				'cookies'  => array(),
				'filename' => null,
			),
		);
	}

	public function requests(): array {
		return $this->requests;
	}

	public function handle( $preempt, array $args, string $url ): array {
		$method           = strtoupper( $args['method'] ?? 'GET' );
		$this->requests[] = array(
			'method'  => $method,
			'url'     => $url,
			'headers' => $args['headers'] ?? array(),
			'body'    => $args['body'] ?? null,
		);

		foreach ( $this->responses as $index => $stub ) {
			if ( $stub['method'] === $method && $stub['url'] === $url ) {
				unset( $this->responses[ $index ] );
				return $stub['response'];
			}
		}

		throw new RuntimeException( "No stubbed response for {$method} {$url}" );
	}
}
