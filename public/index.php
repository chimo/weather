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
$dbh = db($config['dbUser'], $config['dbPassword'], $config['dbName']);

// Get the closest weather station to us
$statement = $dbh->prepare('SELECT code, name, province FROM site order by location <-> ST_MakePoint(:lon, :lat) limit 1');
$statement->bindValue(':lon', $_GET['lon'], PDO::PARAM_INT);
$statement->bindValue(':lat', $_GET['lat'], PDO::PARAM_INT);
$statement->execute();

$row = $statement->fetch(PDO::FETCH_ASSOC);

$site = new Site($row['code'], $row['name'], $row['province']);

$currentConditions = $site->getCurrentConditions();

header('Content-Type: application/json');
echo $currentConditions->toJSON();

