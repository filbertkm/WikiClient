<?php

namespace WikiClient\MediaWiki;

class User {

	/**
	 * @var string
	 */
	private $username;

	/**
	 * @var string
	 */
	private $password;

	/**
	 * @var boolean
	 */
	private $isBot;

	/**
	 * @param string $username
	 * @param string $password
	 * @param boolean $isBot
	 */
	public function __construct( $username, $password, $isBot ) {
		$this->username = $username;
		$this->password = $password;
		$this->isBot = $isBot;
	}

	/**
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @return boolean
	 */
	public function isBot() {
		return $this->isBot;
	}

}
