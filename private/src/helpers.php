<?php

function db($user, $pass, $db_name) {
	// Connect to DB
	$dbh = new PDO('pgsql:dbname=' . $db_name, $user, $pass);

	// Throw exception on error
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	return $dbh;	
}

function find_new_sites_query($nbCodes) {
	$subs = [];

	for ($i = 0; $i < $nbCodes; $i += 1) {
		$subs[] = '(?)';
	}

	$subs = implode(',', $subs);

	$query = <<<SQL
WITH tmp (code) as (values $subs)
SELECT tmp.code FROM tmp LEFT JOIN site ON tmp.code = site.code WHERE site.code IS NULL;
SQL;

	return $query;
}

function upsert_current_conditions_query($currentConditions, $site_id) {
	$humidity = $currentConditions->humidity;
	$condition = $currentConditions->condition;
	$temperature = $currentConditions->temperature;

	$query = <<<SQL
INSERT INTO current_conditions (site_id, humidity,
condition, temperature, updated_at) VALUES($site_id,
'$humidity', '$condition', '$temperature', NOW())
ON CONFLICT (site_id) DO UPDATE SET
updated_at = NOW(),
humidity = EXCLUDED.humidity,
condition = EXCLUDED.condition,
temperature = EXCLUDED.temperature;
SQL;

	return $query;
}

function insert_sites_query($nbSites) {
	$subs = [];

	for($i = 0; $i < $nbSites; $i += 1) {
		$strIdx = (string)$i;

		$subs[] = "(:code$strIdx, :name$strIdx, :province$strIdx, ST_MakePoint(:lon$strIdx,:lat$strIdx))";
	}

	$subs = implode(',', $subs);

	$query = 'INSERT INTO site (code, name, province, location) VALUES ' . $subs;

	return $query;
}

