<?php

namespace WikiClient\MediaWiki;

class WikiFactory {

	public static function newWiki( $config, $siteId ) {
		$user = new User(
			$config[$siteId]['user']['username'],
			$config[$siteId]['user']['password']
		);

		return new Wiki(
			$siteId,
			$config[$siteId]['baseurl'],
			$user
		);
	}

}
