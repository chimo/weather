<?php

require realpath(dirname(__FILE__)) . '/Site.php';

class SitesFactory {
	private $sites;

	public function __construct() {
		$this->sites = [];
	}

	public function getSiteByCode($code) {
		return $this->sites[$code];
	}

	public function _fillSites() {
		$xml = file_get_contents('siteList.xml');

		$sites = $this->parseSites($xml);

		$this->sites = $sites;

		return $this;	
	}

	public function fillSites() {
		$endpoint = 'https://dd.weather.gc.ca/citypage_weather/xml/siteList.xml';

		$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', $endpoint);

		$xml = $response->getBody();

		$sites = $this->parseSites($xml);

		$this->sites = $sites;

		return $this;
	}

	public function getCodeList() {
		return array_keys($this->sites);
	}

	public function parseSites($xmlstr) {
		$sites = new SimpleXMLElement($xmlstr);
		$arr = [];

		foreach($sites->site as $site) {
			$siteCode = (string)$site['code'];

			$arr[$siteCode] = new Site($siteCode, $site->nameEn, $site->provinceCode);
		}

		return $arr;
	}
}

