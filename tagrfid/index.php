<?php
use InfluxDB2\Point;
include './vendor/autoload.php';

include ('../modals/header.php');
?>

<div>
    <h1>Servizio Rest per l'accesso ai dati generati dai sensori</h1>
    <p>Come usare il tutto:</p>
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum</p>
</div>

<?php
    $path_csv = './csv/example.csv';
    $measurment_name = 'sanba';
    $session_number = 1;

    $points = null;
    $file = fopen($path_csv, 'r');
    if ($file != false){
        $points = array();
        $columns = fgetcsv($file);
        while (($buffer = fgetcsv($file)) !== false) {
            
            $point = new Point($measurment_name);

            $point->addTag($columns[1], $buffer[1]);
            $point->addTag("session", $session_number);//numero della sessione di registrazione
            
            $point->addField($columns[3], $buffer[3]);
            $point->addField($columns[4], $buffer[4]);
            $point->addField($columns[5], $buffer[5]);

            $point->time(strtotime($buffer[2]));//converto la data in timestamp prima di inserirla in $point

            $points[] = $point;
        }
    }
    fclose($file);



include ('../modals/footer.php');
?>