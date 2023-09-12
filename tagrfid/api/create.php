<?php
use InfluxDB2\Point;
include '../vendor/autoload.php';

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
            }else{$message['success'] = true;}
        } else{$message['error'] = 'wrong credentials';}
    } else{$message['error'] = 'missing credentials';}
} else{$message['error'] = 'missing session number';}

if($message['success']){//nel caso che il processo di login sia andato a buon fine
	$precision = (getPrecision() == null) ? 'ns' : getPrecision();//se il formato di precision non è valido, imposta quello di defalut di influxdb
	$url = "{$bucket->getUrl()}/api/v2/write?org={$bucket->getOrg()}&bucket={$bucket->getName()}&precision=ns";

	if(isset($_POST['filename'])){
		if(isset($_POST['measurment'])){
			$filename = $_POST['filename'];
			$measurment = $_POST['measurment'];

			$points = getPointsFromCsv($measurment, $session_id, "../csv/$filename");
			$query = "";
			foreach($points as $el){
				$query .= $el->toLineProtocol() . "\n";
				//echo $el->toLineProtocol() . "<br>";
			}
		} else {$message['error'] = 'missing measurment name';}
	} else {$message['error'] = 'missing filename';}

	$f = fopen("C:/Users/ale/Desktop/tmp.txt", "w");
	//fwrite($f, $query);

	$header = [//header della get
		"Authorization: Token {$bucket->getToken()}",
		"Content-Type: text/plain; charset=utf-8",
		"Accept: application/json"
	];

	$curl = new Curl($url, $header, $query, POST);
	$result = $curl->execCurl();

	echo $result;
} else{
	echo json_encode($message);
}