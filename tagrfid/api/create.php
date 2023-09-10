<?php
include '../php/Curl.php';
include '../php/functions.php';

/*
curl --request POST \
"$INFLUX_HOST/api/v2/write?org=$INFLUX_ORG&bucket=get-started&precision=s" \
  --header "Authorization: Token $INFLUX_TOKEN" \
  --header "Content-Type: text/plain; charset=utf-8" \
  --header "Accept: application/json" \
  --data-binary "
home,room=Living\ Room temp=21.1,hum=35.9,co=0i 1641024000
home,room=Kitchen temp=21.0,hum=35.9,co=0i 1641024000
home,room=Living\ Room temp=21.4,hum=35.9,co=0i 1641027600
*/

$INFLUX_HOST = "http://localhost:8086";
$INFLUX_ORG = "sanbapolis";
$INFLUX_TOKEN = "UtctBnnDWVHAmkT3VK2pCOnL362JD2w0OQ8ASOwOUOd9DH_wRc6RUzKayJvXmhfrgeREdAXFAUkYi4fxX3mUhg==";

$database = 'get-started';
$bucket = 'get-started';
$url = "$INFLUX_HOST/api/v2/write?org=$INFLUX_ORG&bucket=$bucket&precision=s";

$query = "test1,room=Living\ Room temp=21.1,hum=35.9,co=0i 1641024000\nins,room=Kitchen temp=21.0,hum=35.9,co=0i 1641024000\nins,room=Living\ Room temp=21.4,hum=35.9,co=0i 1641027600";
$query .= "\ntest2,room=Living\ Room temp=21.1,hum=35.9,co=0i 1641024000\nins,room=Kitchen temp=21.0,hum=35.9,co=0i 1641024000\nins,room=Living\ Room temp=21.4,hum=35.9,co=0i 1641027600";
//ora devo occuparmi di creare la query
//per inserire più points devo concatenare con \n

echo $query;

$header = [
    "Authorization: Token $INFLUX_TOKEN",
    "Content-Type: text/plain; charset=utf-8",
    "Accept: application/json"
];//header della get



$curl = new Curl($url, $header, $query, POST);
$result = $curl->execCurl();

echo $result;