<?php
use InfluxDB2\Point;

/**
 * la funzione legge il file csv specificato e restituisce un array di istranze della classe Point
 * che rappresentano le righe del file csv
 * @param string $measurment_name il nome della misura che si andrà a salvare 
 * @param string $path_csv il percorso al file csv cercato
 * @param integer $session_number il numero della sessione di registrazione
 * @return array $points l'array di Point che rappresentano le linee del csv; ritorna null il caso di errori
 */
function getPointsFromCsv($measurment_name, $session_number, $path_csv){
    $points = null;
    $file = fopen($path_csv, 'r');
    if ($file != false){
        $points = array();
        $point = new Point($measurment_name);
        $buffer = fgets($file);//LA PRIMA LINEA CONTIENE LE COLONNE, PER ORA NON MI SERVE
        while (($buffer = fgets($file)) !== false) {
            $line = explode(',',$buffer);//estraggo i dati della lina (separati dalla ',')
            $point->addTag("tag_id", $line[0]);
            $point->addTag("session", $session_number);//numero della sessione di registrazione
            /*$point->addField("x_kf", $line[3]);
            $point->addField("y_kf", $line[4]);
            $point->addField("z_kf", $line[5]);*/
            $point->addTag("x_kf", $line[3]);
            $point->addTag("y_kf", $line[4]);
            $point->addTag("z_kf", $line[5]);
            $point->time(strtotime($line[2]));//converto la data in timestamp prima di inserirla in $point 
            $points[] = $point;
        }
    }
    fclose($file);
    return $points;
}

/**
 * inserisce in influxdb i dati specificati
 * @param Client $client la connessione ad influxdb
 * @param string $data i dati da inserire nel db
 * 
 */
function insertNewLineInfluxDb($client, $line){
    $write_api = $client->createWriteApi();
    $write_api->write($line);
}

/**
 * prende il nome del file csv con i dati da inserire dalla richiesta http get con parametro 'filename'
 * inserisce il contenuto del file nel db
 * @param Client $client connessione ad influxdb
 * @param string $measurment_name il nome della nuova misura
 * @param integer $session_number il numero della sessione di registrazione
 * @param string $path_csv il percorso al file csv cercato
 * 
 */
function newData($client, $measurment_name, $session_number, $path_csv){
    if(file_exists($path_csv)){
        $points = getPointsFromCsv($measurment_name, $session_number, $path_csv);
        foreach($points as $point){
            echo $point->toLineProtocol()."<br>";
            insertNewLineInfluxDb($client, $point->toLineProtocol());
        }
    }else{return false;}
}