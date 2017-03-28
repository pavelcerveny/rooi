<?php

namespace Connect;

use Guzzle\Client as HttpClient;
use Latte\Engine as Latte;
use Connect\Support as Support;

abstract class API {

	protected $cfgHttpClient = array();

	/**
	* @var string header|node|''
	**/
	protected $authType;

	protected $authCredentials;

	protected $requestString;

	protected $latteTempDir;

	protected $pathToLatteTemplates;

	protected $response;

	/**
	* @var array [method:endpoint]
	**/
	protected $endpoints;

	protected $decodedResponse;

	/**
	* only method to call them all
	*/
	abstract protected function handleMethod($method, $request);

	protected fuction setHttpClientCfg($config) {
		$this->cfgHttpClient = $config;
	}

	protected fuction getHttpClientCfg() {
		return $this->cfgHttpClient;
	}

	protected function setAuth($type, $credentials) {
		$this->authType = $type;
		$this->authCredentials = $credentials;
	}

	protected function getAuth() {
		return ['type' => $this->authType, 'credentials' => $this->authCredentials];
	}

	protected function setLatteCfg($pathToTemplates, $tempDir) {
		$this->pathToLatteTemplates = $pathToTemplates;
		$this->latteTempDir = $tempDir;
	}

	protected function setEndpoints($endpoints) {
		$this->endpoints = $endpoints;
	}

	protected function renderRequest($method, $data) {
		$latte = new Latte;
		$latte->setTempDirectory($this->latteTempDir);
		$request = $latte->renderToString("{$this->pathToLatteTemplates}/{$method}.latte", $data);
		$this->renderRequest = $request;

		return $this;
	}

	protected function send($method, $action = 'POST') {
		$endpoint = $this->endpoints[$method];
		$client = new HttpClient;

		if (!empty($this->authType) && $this->authType === 'header') {
			if (count($this->authCredentials !== 2)){
				throw new \ApiException("Error Guzzle auth requires only 2 params", 1);
				
			}
			$this->cfgHttpClient = array_merge($this->cfgHttpClient, ['auth' => [$this->authCredentials]]);
		}

		$response = $client->request($action, $endpoint, $this->cfgHttpClient, $this->renderRequest);
		$this->response = (string) $response->getBody();
		return $this;
	}

	protected function decode() {
		return Support::parseXMLRecursive($this->response);
	}

	protected function execute($method, $data, $action) {
		return $this->renderRequest($method, $data)->send($method, $action)->decode();
	}
}
