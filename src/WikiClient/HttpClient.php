<?php

namespace WikiClient;

use UnexpectedValueException;

class HttpClient {

	/**
	 * @var string
	 */
	protected $userAgent;

	/**
	 * @var string
	 */
	protected $cookieDir;

	protected $conn;

	/**
	 * @param string $cookieDir (optional)
	 */
	public function __construct( $cookieDir = null ) {
		$this->cookieDir = $cookieDir ? $cookieDir : '/tmp/';
		$this->userAgent = 'WikiClient framework';
	}

	protected function getConn() {
		if ( !isset( $this->conn ) ) {
			$conn = curl_init();

			$cookieFile = $this->getCookieFilename();

			curl_setopt( $conn, CURLOPT_COOKIEFILE, $cookieFile );
			curl_setopt( $conn, CURLOPT_COOKIEJAR, $cookieFile );
			curl_setopt( $conn, CURLOPT_USERAGENT, $this->userAgent );
			curl_setopt( $conn, CURLOPT_SSL_VERIFYPEER, false );

			$this->conn = $conn;
		}

		return $this->conn;
	}

	/**
	 * @param Request $request
	 *
	 * @throws UnexpectedValueException
	 */
	public function doRequest( Request $request ) {
		if ( $request->getMethod() === 'post' ) {
			return $this->post( $request );
		} elseif ( $request->getMethod() === 'get' ) {
			return $this->get( $request );
		}

		throw new UnexpectedValueException( 'Unexpected request method' );
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
	 * @param Request $request
	 */
	public function get( Request $request ) {
		$params = $request->getParams();
		$url = $request->getUrl();
		$header = $request->getHeader();

		if ( is_array( $params ) ) {
			$url = $url . $this->makeQueryString( $params );
		}

		$conn = $this->getConn();

		curl_setopt( $conn, CURLOPT_URL, $url );
		curl_setopt( $conn, CURLOPT_HTTPGET, true );
		curl_setopt( $conn, CURLOPT_RETURNTRANSFER, true );

		if ( $header ) {
			curl_setopt( $conn, CURLOPT_HEADER, 0 );
			curl_setopt( $conn, CURLOPT_HTTPHEADER, array( $header ) );
		}

		$response = curl_exec( $conn );

		if ( $response === false ) {
			$error = curl_error( $conn );
			return $error;
		}

//		curl_close( $conn );

		return $response;
	}

	/**
	 * @param Request $request
	 */
	public function post( Request $request ) {
		$url = $request->getUrl();
		$params = $request->getParams();
		$header = $request->getHeader();

		$headers = array( 'Expect:' );

		$conn = $this->getConn();

		curl_setopt( $conn, CURLOPT_URL, $url );
		curl_setopt( $conn, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $conn, CURLOPT_POST, true );
		curl_setopt( $conn, CURLOPT_POSTFIELDS, $params );

		if ( $header ) {
			curl_setopt( $conn, CURLOPT_HEADER, 0 );
			$headers[] = $header;
		}

		curl_setopt( $conn, CURLOPT_HTTPHEADER, $headers );

		$response = curl_exec( $conn );

		if ( $response === false ) {
			$error = curl_error( $conn );
			return $error;
		}

//		curl_close( $conn );

		return $response;
	}

}
