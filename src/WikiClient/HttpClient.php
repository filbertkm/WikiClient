<?php

namespace WikiClient;

use InvalidArgumentException;

class HttpClient {

	protected $baseUrl;

	protected $userAgent;

	protected $conn;

	protected $cookieDir;

	/**
	 * @param string|null $baseUrl
	 */
	public function __construct( $baseUrl = null ) {
		if ( !is_string( $baseUrl ) ) {
			throw new InvalidArgumentException( '$baseUrl is invalid' );
		}

		$this->baseUrl = $baseUrl;
		$this->userAgent = 'WikiClient framework';
		$this->cookieDir = '/tmp/';

		$this->conn = curl_init();
		$this->init();
	}

	protected function init() {
		$cookieFile = $this->getCookieFilename();

		curl_setopt( $this->conn, CURLOPT_COOKIEFILE, $cookieFile );
		curl_setopt( $this->conn, CURLOPT_COOKIEJAR, $cookieFile );
		curl_setopt( $this->conn, CURLOPT_USERAGENT, $this->userAgent );
		curl_setopt( $this->conn, CURLOPT_SSL_VERIFYPEER, false );
	}

	/**
	 * @return string
	 */
	private function getCookieFilename() {
		$cookieFile = 'cookie.' . dechex( rand( 0, 99999999 ) ) . '.dat';

		return $this->cookieDir . $cookieFile;
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
	 * @param ? $header
	 *
	 * @throws InvalidArgumentException
	 */
	public function get( $url = null, $params = null, $header = null ) {
		$url = $this->resolveUrl( $url );

		if ( is_array( $params ) ) {
			$url = $url . $this->makeQueryString( $params );
		}

		curl_setopt( $this->conn, CURLOPT_URL, $url );
		curl_setopt( $this->conn, CURLOPT_HTTPGET, true );
		curl_setopt( $this->conn, CURLOPT_RETURNTRANSFER, true );

		if ( $header ) {
			curl_setopt( $this->conn, CURLOPT_HEADER, 0 );
			curl_setopt( $this->conn, CURLOPT_HTTPHEADER, array( $header ) );
		}

		$response = curl_exec( $this->conn );

		return $response;
	}

	/**
	 * @param string|null $url - default is $this->baseUrl
	 * @param array|string $params
	 * @param ? $header
	 *
	 * @throws InvalidArgumentException
	 */
	public function post( $url = null, $params, $header = null ) {
		$url = $this->resolveUrl( $url );
		$headers = array( 'Expect:' );

		curl_setopt( $this->conn, CURLOPT_URL, $url );
		curl_setopt( $this->conn, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->conn, CURLOPT_POST, true );
		curl_setopt( $this->conn, CURLOPT_POSTFIELDS, $params );

		if ( $header ) {
			curl_setopt( $this->conn, CURLOPT_HEADER, 0 );
			$headers[] = $header;
		}

		curl_setopt( $this->conn, CURLOPT_HTTPHEADER, $headers );

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
