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

$f = fopen("C:/Users/ale/Desktop/data.json","r");
$s = fread($f, filesize("C:/Users/ale/Desktop/data.json"));
$ris = json_decode($s, true);
$data = json_decode($ris['results'], true);
$data = $data['results'];
$data = $data[0];
$data = $data['series'];

foreach($data as $el){
    var_dump($el);
}

include ('../modals/footer.php');
?>