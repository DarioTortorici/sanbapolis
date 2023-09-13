<?php

include '../php/Curl.php';
include '../php/functions.php';

$pdo = get_connection();

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
            }else {$message['success'] = true;}
        } else {$message['error'] = 'wrong credentials';}
    } else {$message['error'] = 'missing credentials';}
} else {$message['error'] = 'missing session number';}

if($message['success']){
	$url = "{$bucket->getUrl()}/api/v2/query?org={$bucket->getOrg()}&bucket={$bucket->getName()}";

	$query = file_get_contents('php://input');//esteaggo il body della richiesta

	if($query != ''){
		$header = [//header della get
			"Authorization: Token {$bucket->getToken()}",
			"Content-Type: application/vnd.flux",
			"Accept: application/csv"
		];
		
		$curl = new Curl($url, $header, $query, POST);//inoltro la richiesta alle api di influxdb
		$result = $curl->execCurl();
		
		$message['results'] = $result;
		echo json_encode($message);
	}
	else {
		$message['success'] = false;
		$message['error'] = "missing query";
		echo json_encode($message);
	}
} else {
	echo json_encode($message);
}
?>
