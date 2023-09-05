<?php
use InfluxDB2\Point;

function generaDati(){
    for($j = 1; $j < 11; $j++){
        $myfile = fopen("./files_csv/session_$j.csv", "w") or die("Unable to open file!");
        for($i = 0; $i < 10; $i++){
            $x = rand(1, 100);
            $y = rand(1, 100);
            $z = rand(1, 100);
            $txt = "misura,session=$j,id=1 x=\"$x\",y=\"$y\",z=\"$z\"\n";
            fwrite($myfile, $txt);
        }
        fclose($myfile);
    }
}

function myVarDump($obj, $messaggio = null){
    if($messaggio != null){
        echo "$messaggio: ";
    }
    var_dump($obj);
    echo "<br><br>";
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
 * la funzione legge il file csv specificato e restituisce un array di istranze della classe Point
 * che rappresentano le righe del file csv
 * @param string $measurment_name il nome della misura che si andrà a salvare 
 * @param string $session_number il numero di sessione di registrazione associata ai dati raccolti
 * @param string $path_csv il percorso al file csv cercato
 * @return array $points l'array di Point che rappresentano le linee del csv; ritorna null il caso di errori
 */
function getPointsFromCsv($measurment_name, $session_number, $path_csv){
    $points = null;
    $file = fopen($path_csv, 'r');
    if ($file != false){
        $points = array();
        $point = new Point($measurment_name);
        $buffer = fgets($file, 4096);//leggo la prima riga che non mi serve: contiene le colonne
        while (($buffer = fgets($file, 4096)) !== false) {
            $line = explode(',',$buffer);//estraggo i dati della lina (separati dalla ',')
            $point->addTag("tag_id", $line[0]);
            $point->addTag("session", $session_number);//numero della sessione di registrazione
            $point->addField("x_kf", $line[2]);
            $point->addField("y_kf", $line[3]);
            $point->addField("z_kf", $line[4]);
            $point->time(strtotime($line[2]));//converto la data in timestamp prima di inserirla in $point 
            $points[] = $point;
        }
    }
    fclose($file);
    return $points;
}