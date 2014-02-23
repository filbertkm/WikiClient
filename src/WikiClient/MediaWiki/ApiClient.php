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
	 * @param Wiki $wiki
	 */
	public function __construct( Wiki $wiki ) {
		$this->http = new HttpClient();
		$this->user = $wiki->getUser();
		$this->wiki = $wiki;
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
	 * @param User $user
	 */
	public function setUser( User $user ) {
		$this->user = $user;
	}

	/**
	 * @return boolean
	 */
	public function login() {
		if ( !isset( $this->user ) ) {
			throw new RuntimeException( 'No user is set for the api client' );
		}

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

	public function buildEditParams( $params ) {
		$this->login();
		$tokens = $this->getTokens();

		$params = $this->buildParams(
			array_merge(
				$params,
				array(
//					'assert' => 'bot',
					'bot' => 1,
					'token' => $tokens['edittoken']
				)
			)
		);

		return $params;
	}

	public function doEdit( $params ) {
		$params = $this->buildEditParams( $params );
		return $this->post( $params );
	}

	public function get( $params, $header = null ) {
		$request = $this->buildRequest( 'get', $params, $header );
		return $this->http->doRequest( $request );
	}

	public function post( $params, $header = null ) {
		$request = $this->buildRequest( 'post', $params, $header );
		return $this->http->doRequest( $request );
	}

	private function buildRequest( $method, $params, $header = null ) {
		$request = new Request(
			$method,
			$this->wiki->getBaseUrl(),
			$params,
			$header
		);

		return $request;
	}

}
