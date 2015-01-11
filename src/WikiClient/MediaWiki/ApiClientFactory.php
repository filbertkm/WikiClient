<?php

namespace WikiClient\MediaWiki;

use Wikibot\Sites\SiteFactory;

class ApiClientFactory {

	private $siteFactory;

	private $userFactory;

	public function __construct( SiteFactory $siteFactory, UserFactory $userFactory ) {
		$this->siteFactory = $siteFactory;
		$this->userFactory = $userFactory;
	}

	public function getClient( $siteId ) {
		$site = $this->siteFactory->getSite( $siteId );
		$user = $this->userFactory->getUserForGroup( $site->getGroup() );

		return new ApiClient( $site, $user );
	}

}
