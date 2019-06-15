<?php

require '../../vendor/autoload.php';
require '../class/SitesFactory.php';
require '../../config.php';
require '../helpers.php';

$dbh = db($config['dbUser'], $config['dbPassword'], $config['dbName']);

// Fetch sites from endpoint
$sites = new SitesFactory();
$sites->_fillSites();

// Extract site codes
$siteCodes = $sites->getCodeList();
$nbSiteCodes = sizeof($siteCodes);

// Build prepared statement
$query = find_new_sites_query($nbSiteCodes);
$statement = $dbh->prepare($query);

// Fill the parameters
for ($i = 0; $i < $nbSiteCodes; $i += 1) {
	$statement->bindValue($i + 1, $siteCodes[$i], PDO::PARAM_STR);
}

// Get the codes of all unknown sites
$statement->execute();
$new_sites_codes = $statement->fetchAll(PDO::FETCH_COLUMN, 0);
$filledSites = [];

// Fill the coordinates for new sites
foreach($new_sites_codes as $new_site_code) {
	$new_site = $sites->getSiteByCode($new_site_code);

	try {
		$new_site->fillCoords();
		$filledSites[] = $new_site;
	} catch(Exception $e) {
		error_log('Unable to get coords for site: ' . $new_site->getName());
		echo $e->getMessage();
	}

	// Don't hammer the endpoint
	// (each fillCoords() is a HTTP request)
	sleep(2);  
}

$nbNewSites = sizeof($filledSites);

// No new sites, exit normally
if ($nbNewSites === 0) {
	echo "No new sites\n";
	exit(0);
}

// Build prepared statement
$query = insert_sites_query($nbNewSites);
$statement = $dbh->prepare($query);

// Fill the parameters
$i = 0;
foreach($filledSites as $filledSite) {
	$strIdx = (string)$i;

	$statement->bindValue(":code$strIdx", $filledSite->getCode(), PDO::PARAM_STR);
	$statement->bindValue(":name$strIdx", $filledSite->getName(), PDO::PARAM_STR);
	$statement->bindValue(":province$strIdx", $filledSite->getProvince(), PDO::PARAM_STR);
	$statement->bindValue(":lon$strIdx", $filledSite->getCoords()->lon, PDO::PARAM_INT);
	$statement->bindValue(":lat$strIdx", $filledSite->getCoords()->lat, PDO::PARAM_INT);

	$i += 1;
}

// Insert new sites in DB
$statement->execute();

