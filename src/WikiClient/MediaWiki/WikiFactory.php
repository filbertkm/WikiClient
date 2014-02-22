<?php

namespace WikiClient\MediaWiki;

class WikiFactory {

	public static function newWiki( $config, $siteId, $user = null ) {
		$username = $user ? $user->getUsername() : $config[$siteId]['user']['username'];
		$password = $user ? $user->getApiPassword() : $config[$siteId]['user']['password'];

		$user = new User( $username, $password );

		return new Wiki(
			$siteId,
			$config[$siteId]['baseurl'],
			$user
		);
	}

}
