<?php

/*
 * Utility script to create a new, empty SQL `site` table
 */

require '../../config.php';

$query = <<<SQL
CREATE TABLE site(
id serial primary key,
code varchar(12) unique not null,
name varchar(191) not null,
province varchar(10) not null,
location geography
);
SQL;

// Connect to DB
$dbh = new PDO('pgsql:dbname=' . $config['dbName'], $config['dbUser'], $config['dbPassword']);

// Throw exception on error
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create table
$dbh->query($query);

// Create index
$dbh->query('CREATE INDEX idx_site_location ON site USING GIST(location)');

