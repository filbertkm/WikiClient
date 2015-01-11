<?php

namespace WikiClient\MediaWiki;

class UserFactory {

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @param array $config
	 */
	public function __construct( array $config ) {
		$this->config = $config;
	}

	/**
	 * @param string $group
	 *
	 * @return User
	 */
	public function getUserForGroup( $group ) {
		if ( !array_key_exists( $group, $this->config ) ) {
			throw new InvalidArgumentException( 'Unknown group: ' . $group );
		}

		$user = $this->config[$group];

		return new User( $user['id'], $user['password'], $user['bot'] );
	}

}
