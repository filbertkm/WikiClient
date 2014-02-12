<?php

namespace WikiClient\OAuth;

use WikiClient\MediaWiki\Wiki;

class OAuthRequest {

	const SIGMETHOD = 'HMAC-SHA1';

	const OVERSION = '1.0';

	protected $config;

	public function __construct( array $config ) {
		$this->config = $config;
	}

	public function buildParams( array $apiParams ) {
		$oauthParams = array(
			'oauth_consumer_key' => $this->config['oauth']['consumerkey'],
			'oauth_nonce' => $this->generateNonce(),
			'oauth_signature_method' => self::SIGMETHOD,
			'oauth_timestamp' => $this->getTimestamp(),
			'oauth_token' => $this->config['oauth']['token'],
			'oauth_version' => self::OVERSION
		);

		$apiParams = $client->buildParams( $apiParams );
		$params = array_merge( $apiParams, $oauthParams );
		ksort( $params );

		$baseString = $this->getBaseString( $params );

		$hashkey = $this->config['oauth']['consumersecret'] . '&'
			. $this->config['oauth']['usersecret'];

		$params['oauth_signature'] = base64_encode( hash_hmac( 'sha1', $baseString, $hashkey, true ) );

		return $params;
	}

	public function post( Wiki $wiki, array $apiParams ) {
		$params = $this->buildParams( $apiParams );
		$header = array( $this->makeHeader( $params ) );

		$client = new ApiClient( $wiki, '/tmp' );
		$data = $client->post( $params, $header );

		return $data;
	}

	public function get( Wiki $wiki, array $apiParams ) {
		$params = $this->buildParams( $apiParams );
		$header = array( $this->makeHeader( $params ) );

		$client = new ApiClient( $wiki, '/tmp' );
		$data = $client->get( $params, $header );

		return $data;
	}
/*
		$url = $this->config['oauth']['apibaseurl']  . '?' . http_build_query( $apiParams );

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, '/tmp/cookie3333.txt' );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );

		$data = curl_exec( $ch );
*/

	protected function generateNonce() {
		return md5( microtime() . mt_rand() );
	}

	protected function getTimestamp() {
		return gmdate( 'U' );
	}

	protected function getBaseString( $params ) {
		$method = 'GET';

		return "$method&" . urlencode( $this->config['oauth']['apibaseurl'] )
			. "&" .urlencode( http_build_query( $params ) );
	}

	protected function makeHeader( $oauthParams ) {
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
