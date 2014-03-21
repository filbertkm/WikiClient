<?php

namespace WikiClient\MediaWiki;

class Wiki {

	protected $siteId;

	protected $apiUrl;

	protected $user;

	public function __construct( $siteId, $apiUrl, $user = null ) {
		$this->siteId = $siteId;
		$this->apiUrl = $apiUrl;
		$this->user = $user;
	}

	public function getSiteId() {
		return $this->siteId;
	}

	public function getApiUrl() {
		return $this->apiUrl;
	}

	public function getUser() {
		return $this->user;
	}

}
