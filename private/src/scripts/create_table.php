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

// Create site table
$dbh->query($query);

// Create index
$dbh->query('CREATE INDEX idx_site_location ON site USING GIST(location)');

$query = <<<SQL
CREATE TABLE current_conditions(
id serial primary key,
site_id integer REFERENCES site(id) unique,
humidity varchar(12),
condition varchar(191),
temperature varchar(12),
updated_at TIMESTAMP WITH TIME ZONE NOT NULL
);
SQL;

// Create 'current_conditions' table
$dbh->query($query);

