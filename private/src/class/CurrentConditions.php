<?php

class CurrentConditions {
	private $condition;
	private $humidity;	// percent
	private $temperature;	// celcius

	public function __construct($condition, $temperature, $humidity) {
		$this->condition = $condition;
		$this->temperature = $temperature;
		$this->humidity = $humidity;
	}


	public function getCondition() {
		return $this->condition;
	}

	public function getTemperature() {
		return $this->temperature;
	}

	public function getHumidity() {
		return $this->humidity;
	}

	public function toJSON() {
		$data = [
			'humidity' => $this->humidity,
			'temperature' => $this->temperature,
			'condition' => $this->condition
		];

		return json_encode($data);
	}
}

