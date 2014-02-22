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

	public function buildParams( array $apiParams, $method, $token, $secret ) {
		$oauthParams = array(
			'oauth_consumer_key' => $this->config['oauth']['consumerkey'],
			'oauth_nonce' => $this->generateNonce(),
			'oauth_signature_method' => self::SIGMETHOD,
			'oauth_timestamp' => $this->getTimestamp(),
			'oauth_token' => $token, //$this->config['oauth']['token'],
			'oauth_version' => self::OVERSION
		);

		$params = array_merge( $apiParams, $oauthParams );
		ksort( $params );

		$baseString = $this->getBaseString( $params, $method );
		$hashkey = $this->config['oauth']['consumersecret'] . '&' . $secret;

		$params['oauth_signature'] = base64_encode( hash_hmac( 'sha1', $baseString, $hashkey, true ) );

		return $params;
	}

	private function getEditToken( Wiki $wiki, $token, $secret ) {
		$params = array(
			'action' => 'tokens',
			'type' => 'edit'
		);

		$json = $this->request( $wiki, $params, 'post', $token, $secret );
		$result = json_decode( $json, true );

		return $result['tokens']['edittoken'];
	}

	public function edit( Wiki $wiki, array $params, $token, $secret ) {
		$editToken = $this->getEditToken( $wiki, $token, $secret );

		$apiParams = array_merge(
			array(
				'token' => $editToken,
				'bot' => 1
			),
			$params
		);

		$result = $this->request( $wiki, $apiParams, 'post', $token, $secret );

		return $result;
	}

	public function post( Wiki $wiki, array $params, $token, $secret ) {
		return $this->request( $wiki, $params, 'post', $token, $secret );
	}

	public function get( Wiki $wiki, array $params, $token, $secret ) {
		return $this->request( $wiki, $params, 'get', $token, $secret );
	}

	public function request( Wiki $wiki, array $params, $method, $token, $secret ) {
		$params['format'] = 'json';
		$header = $this->makeHeader( $params, strtoupper( $method ), $token, $secret );
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
		$string = '';

		foreach( $params as $key => $value ) {
			$string .= $this->encode( $key ) . '=' . $this->encode( $value ) . "&";
		}

		$string = rtrim( $string, '&' );

		$baseString = $this->encode( $method )
			. '&' . $this->encode( $this->config['oauth']['apibaseurl'] )
			. '&' . $this->encode( $string );

		return $baseString;
	}

	protected function encode( $string ) {
		return str_replace( '+', ' ', str_replace( '%7E', '~', rawurlencode( $string ) ) );
	}

	protected function makeHeader( $apiParams, $method, $token, $secret ) {
		$oauthParams = $this->buildParams( $apiParams, $method, $token, $secret );
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
