<?php

namespace WikiClient\MediaWiki;

class Wiki {

	protected $siteId;

	protected $apiUrl;

	public function __construct( $siteId, $apiUrl ) {
		$this->siteId = $siteId;
		$this->apiUrl = $apiUrl;
	}

	public function getSiteId() {
		return $this->siteId;
	}

	public function getApiUrl() {
		return $this->apiUrl;
	}

}
