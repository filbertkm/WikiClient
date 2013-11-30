<?php

namespace WikiClient;

use InvalidArgumentException;

class HttpClient {

	protected $conn;

	/**
	 * @var string cookie jar directory
	 */
	public function __construct( $cookieJar ) {
		$this->conn = curl_init();
		$this->init( $cookieJar );
	}

	/**
	 * @var string cookie jar directory
	 */
	protected function init( $cookieJar ) {
		$cookieFile = $this->makeCookieFile();

		curl_setopt( $this->conn, CURLOPT_COOKIEFILE, $cookieFile );
		curl_setopt( $this->conn, CURLOPT_COOKIEJAR, $cookieJar . '/' . $cookieFile );
		curl_setopt( $this->conn, CURLOPT_USERAGENT, 'WikiClient framework' );
		curl_setopt( $this->conn, CURLOPT_SSL_VERIFYPEER, false );
	}

	private function makeCookieFile() {
		return 'cookie.' . dechex( rand( 0, 99999999 ) ) . '.dat';
	}

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	public function makeQueryString( array $params ) {
		$queryString = '';

		foreach( $params as $key => $value ) {
			$queryString .= ( $queryString === '' ) ? '?' : '&';
			$queryString .= urlencode( $key ) . '=' . urlencode( $value );
		}

		return $queryString;
	}

	/**
	 * @param string $url
	 * @param array $params
	 *
	 * @throws InvalidArgumentException
	 */
	public function get( $url, $params = null ) {
		if ( !is_string( $url ) ) {
			throw new InvalidArgumentException( '$url must be a string' );
		}

		if ( is_array( $params ) ) {
			$url = $url . $this->makeQueryString( $params );
		}

		curl_setopt( $this->conn, CURLOPT_URL, $url );
		curl_setopt( $this->conn, CURLOPT_HTTPGET, true );
		curl_setopt( $this->conn, CURLOPT_RETURNTRANSFER, true );

		$response = curl_exec( $this->conn );

		return $response;
	}

	/**
	 * @param string $url
	 * @param array $params
	 *
	 * @throws InvalidArgumentException
	 */
	public function post( $url, array $params ) {
		if ( !is_string( $url ) ) {
			throw new InvalidArgumentException( '$url must be a string' );
		}

		curl_setopt( $this->conn, CURLOPT_URL, $url );
		curl_setopt( $this->conn, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->conn, CURLOPT_POST, true );
		curl_setopt( $this->conn, CURLOPT_POSTFIELDS, $params );
		curl_setopt( $this->conn, CURLOPT_HTTPHEADER, array( 'Expect:' ) );

		$response = curl_exec( $this->conn );

		return $response;
	}

}
