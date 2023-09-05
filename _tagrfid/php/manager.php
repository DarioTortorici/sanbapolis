<?php

use InfluxDB2\Point;

include '../vendor/autoload.php';
include './functions.php';
include './influxdb_connection.php';

$client = get_influxdb_connection();

if(isset($_GET['operation'])){
    switch ($_GET['operation']){
        case 'new_data':
            newData($client);
            break;

        default:
            break;
    }
}

function newData($client){
    $measurment_name = "test_measure";
    $session = 1;//numero della sessione di registrazione

    if(isset($_GET['filename'])){
        $filename = $_GET['filename'];
        $points = getPointsFromCsv($measurment_name,$session, "../files/".$filename);
        foreach($points as $point){
            insertNewLineInfluxDb($client, $points[0]->toLineProtocol());
        }
    }else{return false;}
}

/*
$columns = fgetcsv($file);//estraggo le colonne dal csv
            array_shift($columns);//eliminio il primo elemento dell'array: string vuota
//preparo le colonne della tabella
$start_field = false;//mi serve per sapere quando iniziano i field del Point
$tags = array();
$fields = array();
$columns = fgetcsv($file);//estraggo le colonne dal csv
foreach($columns as $el){
    if($el != ""){//la prima colonna è vuota allora la escludo
        if(!$start_field){
            if($el != "x_kf"){//z_kf è la prima colonna dei field
                $tags[] = $el;
            }
            else{
                $fields[] = $el;
                $start_field = true;//cambio l'array di salvataggio
            }
        }
        else{
            $fields[] = $el;
        }
    }
}
*/