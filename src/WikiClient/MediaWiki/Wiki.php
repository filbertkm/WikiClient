<?php

namespace WikiClient\MediaWiki;

class Wiki {

	protected $siteId;

	protected $baseUrl;

	protected $user;

	public function __construct( $siteId, $baseUrl, $user = null ) {
		$this->siteId = $siteId;
		$this->baseUrl = $baseUrl;
		$this->user = $user;
	}

	public function getSiteId() {
		return $this->siteId;
	}

	public function getBaseUrl() {
		return $this->baseUrl;
	}

	public function getUser() {
		return $this->user;
	}

}
