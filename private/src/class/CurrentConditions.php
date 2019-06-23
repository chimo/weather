<?php

class CurrentConditions extends Model {
	public function toJSON() {
		$data = [
			'humidity' => $this->humidity,
			'temperature' => $this->temperature,
			'condition' => $this->condition
		];

		return json_encode($data);
	}
}

