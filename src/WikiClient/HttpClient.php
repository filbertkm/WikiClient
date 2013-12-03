<?php

namespace WikiClient;

use InvalidArgumentException;

class HttpClient {

	protected $baseUrl;

	protected $conn;

	/**
	 * @param string cookie jar directory
	 * @param string|null $baseUrl
	 */
	public function __construct( $cookieJar, $baseUrl = null ) {
		if ( !is_string( $baseUrl ) ) {
			throw new InvalidArgumentException( '$baseUrl is invalid' );
		}

		$this->baseUrl = $baseUrl;

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
	 * @param string|null $url - default is $this->baseUrl
	 * @param array|null $params
	 *
	 * @throws InvalidArgumentException
	 */
	public function get( $url = null, $params = null ) {
		$url = $this->resolveUrl( $url );

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
	 * @param string|null $url - default is $this->baseUrl
	 * @param array $params
	 *
	 * @throws InvalidArgumentException
	 */
	public function post( $url = null, array $params ) {
		$url = $this->resolveUrl( $url );

		curl_setopt( $this->conn, CURLOPT_URL, $url );
		curl_setopt( $this->conn, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->conn, CURLOPT_POST, true );
		curl_setopt( $this->conn, CURLOPT_POSTFIELDS, $params );
		curl_setopt( $this->conn, CURLOPT_HTTPHEADER, array( 'Expect:' ) );

		$response = curl_exec( $this->conn );

		return $response;
	}

	/**
	 * @param string|null $url
	 *
	 * @throws InvalidArgumentException
	 * @return string
	 */
	private function resolveUrl( $url = null ) {
		if ( $url === null && isset( $this->baseUrl ) ) {
			$url = $this->baseUrl;
		}

		if ( !$url || !is_string( $url ) ) {
			throw new InvalidArgumentException( '$url param is invalid' );
		}

		return $url;
	}

}
