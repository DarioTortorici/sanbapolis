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
 * legge le linee di un file e restituisce un array
 */
function getFileLines($path_file){
    $lines = array();
    $fn = fopen($path_file,"r");
    while(! feof($fn))  {
    $result = fgets($fn);
        $lines[] = $result;
    }
    fclose($fn);
    return $lines;
}