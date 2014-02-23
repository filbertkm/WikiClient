<?php

namespace WikiClient;

class Request {

	/**
	 * @var string
	 */
	protected $method;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var array
	 */
	protected $params;

	/**
	 * @param string $method
	 * @param string $url
	 * @param array $params
	 */
	public function __construct( $method, $url, $params = null ) {
		$this->method = $method;
		$this->url = $url;
		$this->params = $params;
	}

	/**
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @return array|null
	 */
	public function getParams() {
		return $this->params;
	}

}
