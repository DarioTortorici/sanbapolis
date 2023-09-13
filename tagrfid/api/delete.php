<?php
include '../php/Curl.php';
include '../php/functions.php';

$pdo = get_connection();

/*
NEL TESTO DEL BODY, LO 'START' E 'STOP' SONO OBBLIGATORI specificati in formato RFC3339
È OPZIONALE AGGIUNGERE UN PREDICATO - ATTANZIONE SE NON SPECIFICATO IL PREDICATO, VENGONO ELIMINATI TUTTI I DATI NEL
RANGE TEMPORALE SPECIFICATO
curl --request POST https://us-west-2-1.aws.cloud2.influxdata.com/api/v2/delete?org=example-org&bucket=example-bucket \
  --header 'Authorization: Token YOUR_API_TOKEN' \
  --header 'Content-Type: application/json' \
  --data '{
    "start": "2020-03-01T00:00:00Z",
    "stop": "2020-11-14T00:00:00Z",
    "predicate": "_measurement=\"example-measurement\" AND exampleTag=\"exampleTagValue\""
  }'

*/

//verifico le credenziali
$message = array();
$message['success'] = false;
if(isset($_GET['session'])){
    $session_id = intval($_GET['session']);
    if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
        $email = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
        $logged = loginApi($pdo, $email, $password);
        if($logged){
            $bucket = getBucketFromSession($pdo, $session_id);
            if(!($bucket instanceof Bucket)){
                $message['error'] = $bucket['error'];
            }else{$message['success'] = true;}
        } else{$message['error'] = 'wrong credentials';}
    } else{$message['error'] = 'missing credentials';}
} else{$message['error'] = 'missing session number';}

if($message['success']){
	$url = "{$bucket->getUrl()}/api/v2/delete?org={$bucket->getOrg()}&bucket={$bucket->getName()}";
	
	$query = file_get_contents('php://input');//esteaggo il body della richiesta

	$header = [//header della get
		"Authorization: Token {$bucket->getToken()}",
		"Content-Type: application/json; charset=utf-8",
		"Accept: application/json"
	];

	$curl = new Curl($url, $header, $query, POST);//le api di influx vogliono POST
	$result = $curl->execCurl();

	echo $result;
} else {
	echo json_encode($message);
}