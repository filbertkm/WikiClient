<?php

namespace WikiClient\MediaWiki;

use RuntimeException;
use WikiClient\HttpClient;
use WikiClient\Request;

class ApiClient {

	/**
	 * @var HttpClient
	 */
	protected $http;

	/**
	 * @var Wiki
	 */
	protected $wiki;

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var array
	 */
	protected $tokens;

	/**
	 * @var array
	 */
	protected $calls = array();

	/**
	 * @param Wiki $wiki
	 */
	public function __construct( Wiki $wiki, User $user ) {
		$this->http = new HttpClient();
		$this->user = $user;
		$this->wiki = $wiki;
	}

	public function getWiki() {
		return $this->wiki;
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	public function buildParams( array $params ) {
		$defaultParams = array(
			'format' => 'json'
		);

		return array_merge( $defaultParams, $params );
	}

	/**
	 * @return boolean
	 */
	public function login() {
		$params = $this->buildParams( array(
			'action' => 'login',
			'lgname' => $this->user->getUserName(),
			'lgpassword' => $this->user->getPassword()
		) );

		$response = $this->post( $params );
		$result = json_decode( $response, true );

		if ( $result['login']['result'] === 'Success' ) {
			$this->tokens = $this->getTokens();
			return true;
		} elseif ( $result['login']['result'] === 'NeedToken' ) {
			$params['lgtoken'] = $result['login']['token'];
			$response = $this->post( $params );

			$params = $this->buildParams( array(
				'action' => 'tokens',
				'type' => 'edit|createaccount'
			) );

			$response = $this->post( $params );
			$this->tokens = $this->getTokens();

			return true;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function getTokens() {
		if ( !isset( $this->tokens ) ) {
			$params = $this->buildParams( array(
				'action' => 'tokens',
				'type' => 'edit|createaccount'
			) );

			$result = $this->post( $params );
			$result = json_decode( $result, true );
			$this->tokens = $result['tokens'];
		}

		return $this->tokens;
	}

	/**
	 * @param array $params
	 */
	private function buildBotParams( $params ) {
		$this->login();
		$tokens = $this->getTokens();

		$params = $this->buildParams(
			array_merge(
				$params,
				array(
					'assert' => 'bot',
					'bot' => 1,
					'token' => $tokens['edittoken']
				)
			)
		);

		return $params;
	}

	/**
	 * @param array $params
	 * @deprecated
	 */
	public function doEdit( $params ) {
		return $this->doPost( $params );
	}

	/**
	 * @param array $params
	 */
	public function doPost( $params ) {
		$params = $this->buildBotParams( $params );
		return $this->post( $params );
	}

	/**
	 * @param array $params
	 */
	public function get( $params, $header = null ) {
		$params = $this->buildParams( $params );
		$request = $this->buildRequest( 'get', $params, $header );

		$this->calls[] = $request;

		return $this->http->doRequest( $request );
	}

	/**
	 * @param array $params
	 */
	public function post( $params, $header = null ) {
		$params = $this->buildParams( $params );
		$request = $this->buildRequest( 'post', $params, $header );

		$this->calls[] = $request;

		return $this->http->doRequest( $request );
	}

	/**
	 * @param string $method
	 * @param array $params
	 *
	 * @return Request
	 */
	private function buildRequest( $method, $params, $header = null ) {
		$request = new Request(
			$method,
			$this->wiki->getApiUrl(),
			$params,
			$header
		);

		return $request;
	}

	public function getCalls() {
		return $this->calls;
	}

}
