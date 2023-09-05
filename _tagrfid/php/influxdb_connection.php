<?php
include '../vendor/autoload.php';

use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;

# You can generate an API token from the "API Tokens Tab" in the UI - poi da sopostare in config
$token = '2TuOUZfWRd15hx1oQTZdH4SrX7b_SySd551JrwTv1jrSScVYE3I13JjPHIhN8gee5u6uP4tYWV6zSFn9NsDdWQ==';//token creato solo per il php
$org = 'sanbapolis';
$bucket = 'sanbapolis';

define('TOKEN', $token);
define('ORG', $org);
define('BUCKET', $bucket);

function get_influxdb_connection(){
    $client = new Client([
    "url" => "http://localhost:8086",
    "token" => TOKEN,
    "bucket" => BUCKET,
    "precision" => WritePrecision::NS,
    "org" => ORG,
    "debug" => false
    ]);

    return $client;
}