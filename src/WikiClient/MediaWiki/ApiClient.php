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
	 * @param string $cookiejar
	 * @param string $baseUrl
	 * @parma User $user
	 */
	public function __construct( $cookiejar, $baseUrl, User $user = null ) {
		$this->http = new HttpClient( $cookiejar, $baseUrl );
		$this->user = $user;
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

		if ( $this->verbose ) {
			echo "logging in...\n";
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

			if ( $this->verbose ) {
				echo "logged in\n";
			}

			return true;
		} elseif ( $result['login']['result'] === 'NeedToken' ) {
			if ( $this->verbose ) {
				echo "need token\n";
			}

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

}
