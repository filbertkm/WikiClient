<?php

namespace WikiClient\MediaWiki;

use Exception;

class WikiFactory {

	private $wikis;

	public function __construct( array $wikis ) {
		$this->wikis = $wikis;
	}

	public function newWiki( $siteId ) {
		if ( !array_key_exists( $siteId, $this->wikis ) ) {
			throw new Exception( "Site $siteId not found in config." );
		}

		$config = $this->wikis[$siteId];

		$wiki = new Wiki( $siteId, $config['api'] );

		return $wiki;
	}

}
