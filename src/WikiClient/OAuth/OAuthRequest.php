<?php

namespace WikiClient\OAuth;

use WikiClient\MediaWiki\ApiClient;
use WikiClient\MediaWiki\Wiki;

class OAuthRequest {

	const SIGMETHOD = 'HMAC-SHA1';

	const OVERSION = '1.0';

	protected $config;

	private $client;

	public function __construct( array $config ) {
		$this->config = $config;
	}

	public function buildParams( array $apiParams, $method ) {
		$oauthParams = array(
			'oauth_consumer_key' => $this->config['oauth']['consumerkey'],
			'oauth_nonce' => $this->generateNonce(),
			'oauth_signature_method' => self::SIGMETHOD,
			'oauth_timestamp' => $this->getTimestamp(),
			'oauth_token' => $this->config['oauth']['token'],
			'oauth_version' => self::OVERSION
		);

		$params = array_merge( $apiParams, $oauthParams );
		ksort( $params );

		$baseString = $this->getBaseString( $params, $method );

		$hashkey = $this->config['oauth']['consumersecret'] . '&'
			. $this->config['oauth']['usersecret'];

		$params['oauth_signature'] = base64_encode( hash_hmac( 'sha1', $baseString, $hashkey, true ) );

		return $params;
	}

	public function edit( Wiki $wiki, array $params ) {
		$client = $this->getClient( $wiki );
		$params = $client->buildEditParams( $params );

		return $this->post( $wiki, $params );
	}

	public function post( Wiki $wiki, array $params ) {
		return $this->request( $wiki, $params, 'post' );
	}

	public function get( Wiki $wiki, array $params ) {
		return $this->request( $wiki, $params, 'get' );
	}

	public function request( Wiki $wiki, array $params, $method ) {
		$params['format'] = 'json';
		$header = $this->makeHeader( $params, strtoupper( $method ) );
		$client = $this->getClient( $wiki );

		if ( $method === 'post' ) {
			$params = http_build_query( $params );
		}

		$data = $client->$method( $params, $header );

		return $data;
	}

	private function getClient( $wiki ) {
		if ( !isset( $this->client ) ) {
			$this->client = new ApiClient( $wiki, '/tmp' );
		}

		return $this->client;
	}

	protected function generateNonce() {
		return md5( microtime() . mt_rand() );
	}

	protected function getTimestamp() {
		return gmdate( 'U' );
	}

	protected function getBaseString( $params, $method ) {
		return "$method&" . urlencode( $this->config['oauth']['apibaseurl'] )
			. "&" .urlencode( http_build_query( $params ) );
	}

	protected function makeHeader( $apiParams, $method ) {
		$oauthParams = $this->buildParams( $apiParams, $method );
		$first = true;
		$out = 'Authorization: OAuth';

		foreach ( $oauthParams as $k => $v ) {
			if ( substr( $k, 0, 5 ) != "oauth" ) continue;

			$out .= ( $first ) ? ' ' : ',';
			$out .= $this->oauthEncode( $k ) .
				'="' .
				$this->oauthEncode( $v ) .
				'"';

			$first = false;
		}

		return $out;
	}

	protected function oauthEncode( $input ) {
		return str_replace( '+', ' ', str_replace( '%7E', '~', rawurlencode( $input ) ) );
	}

}
