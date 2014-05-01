<?php

namespace WikiClient\MediaWiki;

class User {

	protected $username;

	protected $password;

	protected $siteIds;

	public function __construct( $username, $password, $siteIds ) {
		$this->username = $username;
		$this->password = $password;
		$this->siteIds = $siteIds;
	}

	public function getUsername() {
		return $this->username;
	}

	public function getPassword() {
		return $this->password;
	}

	public function getSiteIds() {
		return $this->siteIds;
	}

}
