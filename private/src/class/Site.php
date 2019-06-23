<?php

require realpath(dirname(__FILE__)) . '/Coords.php';
require realpath(dirname(__FILE__)) . '/CurrentConditions.php';

class Site extends Model {
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

		$location = new Coords((string)$location['lat'], (string)$location['lon']);

		$this->location = $location;

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

	public function getRemoteLastModified($endpoint) {
		$client = new \GuzzleHttp\Client();

		$response = $client->request('HEAD', $endpoint);

		if ($response->hasHeader('Last-Modified')) {
			$lastModified = $response->getHeader('Last-Modified')[0];
		} else {
			$lastModified = 0;
		}

		return $lastModified;
	}

	public function getCurrentConditions() {
		$province = $this->province;
		$code = $this->code;

		$baseUrl = "http://dd.weatheroffice.ec.gc.ca/citypage_weather/xml";
		$endpoint = $baseUrl . "/{$province}/{$code}_e.xml";

		// Get remote 'last modified' date
		$remoteLastModified = strtotime($this->getRemoteLastModified($endpoint));

		// Get local 'last updated' date
		$lastCurrentConditions = CurrentConditions::where('site_id', $this->id)
								  ->find_one();

		// If our data is more recent than the remote's "last modified"
		// return our data instead of making a request to the endpoint.
		if (
			$lastCurrentConditions !== false &&
			strtotime($lastCurrentConditions->updated_at) >= $remoteLastModified
		) {
			$currentConditions = $lastCurrentConditions;
		} else {
			$client = new \GuzzleHttp\Client();
			$response = $client->get($endpoint);

			$xml = $response->getBody();

			$currentConditions = $this->parseCurrentConditions($xml);

			// upsert
			$query = upsert_current_conditions_query($currentConditions,
				$this->id);

			// "find_one()" is needed to trigger the query, but we don't
			// really care about the result since it's an INSERT
			CurrentConditions::raw_query($query)->find_one();
		}

		return $currentConditions;
	}

	public function parseCurrentConditions($xmlstr) {
		$data = new SimpleXMLElement($xmlstr);

		$_currentConditions = $data->currentConditions;

		$condition = (string)$_currentConditions->condition;
		$temperature = (string)$_currentConditions->temperature;
		$humidity = (string)$_currentConditions->relativeHumidity;

		$currentConditions = CurrentConditions::create();
		$currentConditions->condition = $condition;
		$currentConditions->temperature = $temperature;
		$currentConditions->humidity = $humidity;

		return $currentConditions;
	}
}

