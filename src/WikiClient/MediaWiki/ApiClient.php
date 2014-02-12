<?php

namespace WikiClient\MediaWiki;

use RuntimeException;
use WikiClient\HttpClient;

class ApiClient {

	/**
	 * @var HttpClient
	 */
	protected $http;

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @param Wiki $wiki
	 * @param string $cookiejar
	 */
	public function __construct( $wiki, $cookiejar ) {
		$this->http = new HttpClient( $cookiejar, $wiki->getBaseUrl() );
		$this->user = $wiki->getUser();
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

		$response = $this->http->post( $params );
		$result = json_decode( $response, true );

		if ( $result['login']['result'] === 'Success' ) {
			$this->editToken = $this->getEditToken();

			return true;
		} elseif ( $result['login']['result'] === 'NeedToken' ) {
			$params['lgtoken'] = $result['login']['token'];
			$response = $this->http->post( $params );

			$params = $this->buildParams( array(
				'action' => 'tokens',
				'type' => 'edit|createaccount'
			) );

			$response = $this->http->post( $params );
			$this->tokens = $this->getTokens();

			return true;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function getTokens() {
		if ( $this->tokens === null ) {
			$params = $this->buildParams( array(
				'action' => 'tokens',
				'type' => 'edit|createaccount'
			) );

			$result = $this->http->post( $params );
			$result = json_decode( $result, true );
			$this->tokens = $result['tokens'];
		}

		return $this->tokens;
	}

	public function get( $params ) {
		return $this->http->get( null, $params );
	}

	public function post( $params ) {
		return $this->http->post( null, $params );
	}
}