(status: alpha/brain-dump)

# Weather

Simple private API to get current weather information at a lon/lat position
from Environment Canada (EC).

## Requirements

* webserver (tested with nginx and php-fpm)
* php7 (tested with PHP 7.2)
* php7-simplexml
* composer
* postgresql (tested with 11.3)
* postgis (postgresql extension)

## How does it work

The list of weather stations[1] published by Environment Canada and their
lat/lon locations[2] are imported into the database.

When we pass a lat/lon pair to our API, we use PostGIS to find the closest
weather station, and then query the proper EC endpoint to get current
weather[2] from that weather station.

[1] http://dd.weatheroffice.ec.gc.ca/citypage_weather/xml/siteList.xml  
[2] example: http://dd.weatheroffice.ec.gc.ca/citypage_weather/xml/BC/s0000671_e.xml

## Configure

* Run `composer install` in the "private" folder
* Copy "config.dist.php" to "config.php" and edit the values

If you want to bootstrap the database with the weather station information, use
`pg_restore` with the dump provided in "private/weather.sql.dump"

If you want to import the weather station data yourself:
* Create a new database
* Enable the postgis features on it
* Run the "create_table.php" script in "private/src/scripts"
* Run the "import_sites.php" script in "private/src/scripts" (this takes a while)

## Request

`curl -H "secret: xxx" https://example.org?lat=45.00&lon=-75.00`

## Response

```
{
    "condition": "Sunny",
    "temperature": "16",
    "humidity": "12"
}
```

