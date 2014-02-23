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

	protected $header;

	/**
	 * @param string $method
	 * @param string $url
	 * @param array $params
	 * @param ? $header
	 */
	public function __construct( $method, $url, $params = null, $header = null ) {
		$this->method = $method;
		$this->url = $url;
		$this->params = $params;
		$this->header = $header;
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

	public function getHeader() {
		return $this->header;
	}

}
