<?php

namespace WikiClient\MediaWiki;

use Exception;

class UserFactory {

	private $users;

	public function __construct( array $users ) {
		$this->users = $users;
	}

	public function newUser( $username ) {
		if ( !array_key_exists( $username, $this->users ) ) {
			throw new Exception( "User $username not found in config." );
		}

		$info = $this->users[$username];

		$user = new User( $info['username'], $info['password'], $info['wikis'] );

		return $user;
	}

}
