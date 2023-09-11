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
            }else{$message['success'] = true;}
        } else{$message['error'] = 'wrong credentials';}
    } else{$message['error'] = 'missing credentials';}
} else{$message['error'] = 'missing session number';}

if($message['success']){//nel caso che il processo di login sia andato a buon fine
	$precision = (getPrecision() == null) ? 'ns' : getPrecision();//se il formato di precision non è valido, imposta quello di defalut di influxdb
	$url = "{$bucket->getUrl()}/api/v2/write?org={$bucket->getOrg()}&bucket={$bucket->getName()}&precision=ns";

	$query = "test1,room=Living\ Room temp=21.1,hum=35.9,co=0i 1641024000\nins,room=Kitchen temp=21.0,hum=35.9,co=0i 1641024000\nins,room=Living\ Room temp=21.4,hum=35.9,co=0i 1641027600";
	$query .= "\ntest2,room=Living\ Room temp=21.1,hum=35.9,co=0i 1641024000\nins,room=Kitchen temp=21.0,hum=35.9,co=0i 1641024000\nins,room=Living\ Room temp=21.4,hum=35.9,co=0i 1641027600";
	//ora devo occuparmi di creare la query
	//per inserire più points devo concatenare con \n
	
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