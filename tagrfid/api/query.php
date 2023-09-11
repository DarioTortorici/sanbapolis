<?php /*LA PAGINA FUNGE SIA DA READ, SIA DA UPDATE POICHè IL TUTTO DIPENDE DAL TESTO DELLA QUERY */

include '../php/Curl.php';
include '../php/functions.php';

$pdo = get_connection();

//ad oggi la pagina read funziona solo con la get
/*esempio di richietsa
curl --get "$INFLUX_HOST/query?org=$INFLUX_ORG&bucket=get-started" \
  --header "Authorization: Token $INFLUX_TOKEN" \
  --data-urlencode "db=get-started" \
  --data-urlencode "rp=autogen" \
  --data-urlencode "q=SELECT co,hum,temp,room FROM home WHERE time >= '2022-01-01T08:00:00Z' AND time <= '2022-01-01T20:00:00Z'"
*/
/*$INFLUX_HOST = "http://localhost:8086";
$INFLUX_ORG = "sanbapolis";
$INFLUX_TOKEN = "UtctBnnDWVHAmkT3VK2pCOnL362JD2w0OQ8ASOwOUOd9DH_wRc6RUzKayJvXmhfrgeREdAXFAUkYi4fxX3mUhg==";
$database = 'get-started';
$bucket = 'get-started';*/



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
	$url = "{$bucket->getUrl()}/query?org={$bucket->getOrg()}&bucket={$bucket->getName()}";
	
	$query = "q=SELECT co,hum,temp,room FROM home WHERE time >= '2022-01-01T08:00:00Z' AND time <= '2022-01-01T20:00:00Z'";

	if(isset($_GET['query'])){
		$query = $_GET['query'];
		$header = ["Authorization: Token {$bucket->getToken()}"];//header della get
		$params = array(//parametri della get
			'db' => $bucket->getDb(),
			'rp' => 'autogen',
			'q' => $query
		);
	
		$curl = new Curl($url, $header, $params, GET);
		$result = $curl->execCurl();

		myVarDump($result);
		
		$message['result'] = $result;
		echo json_encode($message);
	}
	else{
		$message['success'] = false;
		$message['error'] = "missing query";
		echo json_encode($message);
	}
} else {
	echo json_encode($message);
}


/*
$curlSES=curl_init();//inizializzo la curl
curl_setopt($curlSES, CURLOPT_URL, $url);//passo l'url
curl_setopt($curlSES, CURLOPT_RETURNTRANSFER,true);//setto la risposta come stringa da salvare in una variabile
curl_setopt($curlSES, CURLOPT_HEADER, false);//non considero gli header della risposta
curl_setopt($curlSES, CURLOPT_HTTPHEADER, $header);//setto gli header della richiesra
curl_setopt($curlSES, CURLOPT_POSTFIELDS, $params);//setto i paramentri della richiesra
$result=curl_exec($curlSES);//eseguo la richiesta
curl_close($curlSES);//chiudo la curl
echo $result;
*/
?>