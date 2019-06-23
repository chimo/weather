<?php

require '../private/vendor/autoload.php';

require '../private/config.php';
require '../private/src/helpers.php';
require '../private/src/class/Site.php';

if (!isset($_SERVER['HTTP_SECRET']) || $_SERVER['HTTP_SECRET'] !== $config['secret']) {
	header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
	exit(0);
}

if (!isset($_GET['lat']) || !isset($_GET['lon'])) {
	header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
	exit(0);
}

// DB Connection
ORM::configure('pgsql:dbname=' . $config['dbName']);
ORM::configure('username', $config['dbUser']);
ORM::configure('password', $config['dbPassword']);

// Get the closest weather station to us
$site = Site::raw_query(
	'SELECT * FROM site order by location <-> ST_MakePoint(:lon, :lat)',
	array('lon' => $_GET['lon'], 'lat' => $_GET['lat']))->find_one();

$currentConditions = $site->getCurrentConditions();

header('Content-Type: application/json');
echo $currentConditions->toJSON();

