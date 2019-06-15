<?php

class Coords {
	public $lat;
	public $lon;

	public function __construct($lat, $lon) {
		if (
			!is_string($lat) || empty($lat) ||
			!is_string($lon) || empty($lon)
		) {
			throw new Exception('Garbage coords');
		}

		$this->lat = $this->format($lat);
		$this->lon = $this->format($lon);
	}

	// Remove direction and make number negative if appropriate...
	public function format($val) {
		// Remove last character (direction), keep the number part
		$coord = substr($val, 0, -1);
		// Keep only the last character (direction)
		$direction = substr($val, -1);

		// If the direction is South or West, then the number should be negative
		if ($direction === 'S' || $direction === 'W') {
			$coord = '-' . $coord;
		}

		return $coord;
	}

	public function toSQL() {
		return "$this->lon, $this->lat";
	}
}

