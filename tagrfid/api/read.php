<?php
include '../php/Curl.php';

//ad oggi la pagina read funziona solo con la get

/*esempio di richietsa
curl --get "$INFLUX_HOST/query?org=$INFLUX_ORG&bucket=get-started" \
  --header "Authorization: Token $INFLUX_TOKEN" \
  --data-urlencode "db=get-started" \
  --data-urlencode "rp=autogen" \
  --data-urlencode "q=SELECT co,hum,temp,room FROM home WHERE time >= '2022-01-01T08:00:00Z' AND time <= '2022-01-01T20:00:00Z'"
*/

$INFLUX_HOST = "http://localhost:8086";
$INFLUX_ORG = "sanbapolis";
$INFLUX_TOKEN = "UtctBnnDWVHAmkT3VK2pCOnL362JD2w0OQ8ASOwOUOd9DH_wRc6RUzKayJvXmhfrgeREdAXFAUkYi4fxX3mUhg==";

$database = 'get-started';
$bucket = 'get-started';
$url = "$INFLUX_HOST/query?org=$INFLUX_ORG&bucket=$bucket";

if(isset($_GET['query'])){
	$query = $_GET['query'];
	$header = ["Authorization: Token $INFLUX_TOKEN"];//header della get
	$params = array(//parametri della get
		'db' => $database,
		'rp' => 'autogen',
		'q' => $query
	);

	$curl = new Curl($url, $header, $params, GET);
	$result = $curl->execCurl();
	echo $result;
}
else{
	echo "query non presente";
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