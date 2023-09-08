<?php
include ('../modals/header.php');
include './php/Curl.php';
include './php/functions.php';
?>

<div>
    <h1>Servizio Rest per l'accesso ai dati generati dai sensori</h1>
    <p>Come usare il tutto:</p>
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum</p>
</div>


<?php
echo "<a href='./php/create.php'>Manager Example csv</a><br>";
?>

<?php

$pars=array(
    'nome' => 'pippo',
    'cognome' => 'disney',
    'email' => 'pippo@paperino.com',
);

//step1
$curlSES=curl_init(); 
//step2
curl_setopt($curlSES,CURLOPT_URL,"http://www.miosito.it");
curl_setopt($curlSES,CURLOPT_RETURNTRANSFER,true);
curl_setopt($curlSES,CURLOPT_HEADER, false); 
curl_setopt($curlSES, CURLOPT_POST, true);
curl_setopt($curlSES, CURLOPT_POSTFIELDS,$pars);
curl_setopt($curlSES, CURLOPT_CONNECTTIMEOUT,10);
curl_setopt($curlSES, CURLOPT_TIMEOUT,10);
//step3
$result=curl_exec($curlSES);

myVarDump(curl_getinfo($curlSES));

//step4
curl_close($curlSES);
//step5
echo $result;
?>

<?php
include ('../modals/footer.php');
?>