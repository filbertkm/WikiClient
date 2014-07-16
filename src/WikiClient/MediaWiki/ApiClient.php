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

	protected $loggedIn = false;

	protected $isBot = true;

	/**
	 * @var array
	 */
	protected $calls = array();

	/**
	 * @param Wiki $wiki
	 */
	public function __construct( Wiki $wiki, User $user = null ) {
		$this->http = new HttpClient();
		$this->user = $user;
		$this->wiki = $wiki;
	}

	public function getWiki() {
		return $this->wiki;
	}

	public function getUser() {
		return $this->user;
	}

	public function setUser( User $user ) {
		$this->user = $user;
	}

	public function setIsBot( $isBot ) {
		$this->isBot = $isBot;
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
			$this->loggedIn = true;
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

	public function getEditToken() {
		$tokens = $this->getTokens();

		return $tokens['edittoken'];
	}

	/**
	 * @param array $params
	 */
	private function buildEditParams( $params ) {
		if ( !$this->loggedIn ) {
			$this->login();
		}

		$tokens = $this->getTokens();

		$params = $this->buildParams(
			array_merge(
				$params,
				array(
					'assert' => 'bot',
					'bot' => 1,
					'token' => $this->getEditToken()
				)
			)
		);

		if ( $this->isBot ) {
			$params = array_merge(
				$params,
				array(
					'assert' => 'bot',
					'bot' => 1
				)
			);
		}

		return $params;
	}

	/**
	 * @param array $params
	 * @deprecated
	 */
	public function doEdit( $params, $header = null ) {
		return $this->doPost( $params, $header );
	}

	/**
	 * @param array $params
	 */
	public function doPost( $params ) {
		$params = $this->buildEditParams( $params );
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
