<?php

require realpath(dirname(__FILE__)) . '/Coords.php';
require realpath(dirname(__FILE__)) . '/CurrentConditions.php';

class Site {
	private $id;
	private $code;
	private $coords;
	private $name;
	private $province;

	public function __construct($code, $name, $province, $coords = null) {
		$this->code = (string)$code;
		$this->coords = $coords;
		$this->name = (string)$name;
		$this->province = (string)$province;
	}

	public function getName() {
		return $this->name;
	}

	public function getCode() {
		return $this->code;
	}

	public function getProvince() {
		return $this->province;
	}

	public function getCoords() {
		return $this->coords;
	}

	public function fillCoords() {
		$code = $this->code;
		$province = $this->province;

		$urlSegments = [$code, $province];
		$baseUrl = "http://dd.weatheroffice.ec.gc.ca/citypage_weather/xml";
		$endpoint = $baseUrl . "/{$province}/{$code}_e.xml";

		$client = new \GuzzleHttp\Client();
		$response = $client->get($endpoint);

		$xml = $response->getBody();

		$location = $this->getLocationInfo($xml);

		$coords = new Coords((string)$location['lat'], (string)$location['lon']);

		$this->coords = $coords;

		return $this;
	}

	public function getLocationInfo($xmlstr) {
		// There are two lat/lon pairs in the XML:
		// 1) $xml->currentConditions->station
		// 2) $xml->location->name
		//
		// "currentCondition" isn't always populated though,
		// so use the pair in "location" instead

		$xml = new SimpleXMLElement($xmlstr);

		return $xml->location->name;
	}

	public function getCurrentConditions() {
		$province = $this->province;
		$code = $this->code;

		$baseUrl = "http://dd.weatheroffice.ec.gc.ca/citypage_weather/xml";
		$endpoint = $baseUrl . "/{$province}/{$code}_e.xml";

		$client = new \GuzzleHttp\Client();
		$response = $client->get($endpoint);

		$xml = $response->getBody();

		return $this->parseCurrentConditions($xml);
	}

	public function parseCurrentConditions($xmlstr) {
		$data = new SimpleXMLElement($xmlstr);

		$currentConditions = $data->currentConditions;

		$condition = (string)$currentConditions->condition;
		$temperature = (string)$currentConditions->temperature;
		$humidity = (string)$currentConditions->relativeHumidity;

		return new CurrentConditions($condition, $temperature, $humidity);
	}
}

