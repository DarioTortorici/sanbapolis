<?php
include '../vendor/autoload.php';

use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;

//QUESTA PAGINA è TEMPORANEA MI SERVE SOLO PER GLI ESPERIMENTI
//LA CONN AD INFLUXDB DEVE POI CAMBIARE

# You can generate an API token from the "API Tokens Tab" in the UI - poi da sopostare in config
$token = 'UtctBnnDWVHAmkT3VK2pCOnL362JD2w0OQ8ASOwOUOd9DH_wRc6RUzKayJvXmhfrgeREdAXFAUkYi4fxX3mUhg==';
$org = 'sanbapolis';
$bucket = 'test';

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