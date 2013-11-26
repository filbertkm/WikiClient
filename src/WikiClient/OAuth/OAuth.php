<?php

namespace WikiClient\OAuth;

class OAuth {

	const SIGMETHOD = 'HMAC-SHA1';

	const OVERSION = '1.0';

	protected $config;

	protected $app;

	protected $nonce = null;

	protected $timestamp = null;

	protected $signature = null;

	public function __construct( array $config, $app ) {
		$this->config = $config;
		$this->app = $app;
	}

	public function authorize() {
		$url = $this->getRequestUrl();

		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );

		$results = curl_exec( $ch );

		curl_close( $ch );

		$data = $this->parseResults( $results );

		if ( array_key_exists( 'error', $data ) ) {
			return 'error: ' . $results;
		}

		$this->app['session']->set( 'oauth_token', $data['key'] );
		$this->app['session']->set( 'oauth_secret', $data['secret'] );

		$userTokenUrl = $this->config['oauth']['baseurl']
			. '?title=Special:OAuth/authorize'
			. '&oauth_token=' . $data['key']
			. '&oauth_consumer_key=' . $this->config['oauth']['consumerkey'];

		return "<a href='$userTokenUrl'>authorize</a>";
	}

	public function verify( $accessToken, $verifier ) {
		$url = $this->getAuthUrl( $accessToken, $verifier );
		return "<a href='$url'>confirm</a>";
	}

	protected function parseResults( $results ) {
		return json_decode( $results, true );
	}

	protected function getAuthUrl( $accessToken, $verifier ) {
		$nonce = $this->generateNonce();
		$timestamp = $this->getTimestamp();
		$signature = $this->generateAuthSignature( $accessToken, $verifier, $nonce, $timestamp );

		$fields = array(
			'format' => 'json',
			'oauth_nonce' => $nonce,
			'oauth_timestamp' => $timestamp,
			'oauth_verifier' => $verifier,
			'oauth_consumer_key' => $this->config['oauth']['consumerkey'],
			'oauth_signature_method' => self::SIGMETHOD,
			'oauth_version' => self::OVERSION,
			'oauth_token' => $this->app['session']->get( 'oauth_token' ),
			'oauth_signature' => $signature,
			'title' => 'Special:OAuth/token'
		);

		$fields_string = '';

		foreach( $fields as $key => $value ) {
			$fields_string .= "$key=" . urlencode( $value ) . "&";
		}

		$fields_string = rtrim( $fields_string, '&' );

		return $this->config['oauth']['baseurl'] . '?' . $fields_string;
	}

	protected function getRequestUrl() {
		$nonce = $this->generateNonce();
		$timestamp = $this->getTimestamp();

		$query = $this->getQueryString( $nonce, $timestamp )
			. "&oauth_signature=" . urlencode( $this->getSignature( $nonce, $timestamp ) );

		return $this->config['oauth']['baseurl'] . "?" . $query;
	}

	protected function generateNonce() {
		return md5( microtime() . mt_rand() );
	}

	protected function getTimestamp() {
		return gmdate( 'U' );
	}

	protected function getQueryString( $nonce, $timestamp ) {
		return "format=json&oauth_callback=oob"
			. "&oauth_consumer_key=" . $this->config['oauth']['consumerkey']
			. "&oauth_nonce=" . $nonce
			. "&oauth_signature_method=" . self::SIGMETHOD
			. "&oauth_timestamp=" . $timestamp
			. "&oauth_version=" . self::OVERSION
			. "&title=" . urlencode( 'Special:OAuth/initiate' );
	}

	protected function getSignature( $nonce, $timestamp ) {
		$baseString = $this->getQueryString( $nonce, $timestamp );
		$baseUrl = "GET&" . urlencode( $this->config['oauth']['baseurl'] )."&".urlencode( $baseString );
		$hashkey = $this->config['oauth']['consumersecret'] . '&';

		$signature = base64_encode( hash_hmac( 'sha1', $baseUrl, $hashkey, true ) );

		return $signature;
	}

	protected function generateAuthSignature( $accessToken, $verifier, $nonce, $timestamp ) {
		$basestring = "format=json"
			. "&oauth_consumer_key=" . $this->config['oauth']['consumerkey']
			. "&oauth_nonce=" . $nonce
			. "&oauth_signature_method=" . self::SIGMETHOD
			. "&oauth_timestamp=" . $timestamp
			. "&oauth_token=" . $accessToken
			. "&oauth_verifier=" . $verifier
			. "&oauth_version=" . self::OVERSION
			. "&title=" . urlencode( 'Special:OAuth/token' );

		$basestring = "GET&" . urlencode( $this->config['oauth']['baseurl'] )
			. "&" .urlencode( $basestring );

		$oauth_token_secret = $this->app['session']->get( 'oauth_secret' );
		$hashkey = $this->config['oauth']['consumersecret'] . "&" . $oauth_token_secret;

		return base64_encode( hash_hmac( 'sha1', $basestring, $hashkey, true ) );
	}

}
