<?php
// Testa l'integrazione tra register.php, register-process.php e register.js

require_once '../authentication/register.php';
require_once '../authentication/register-process.php';

// 2. Prepara i dati di test
$_POST['firstName'] = 'Mario';
$_POST['lastName'] = 'Rossi';
$_POST['email'] = 'mario.rossi@example.com';
$_POST['password'] = 'Password123!';
$_POST['confirm_pwd'] = 'Password123!';
$_POST['userType'] = 'giocatore';
$_POST['teamCode'] = 'BSKTTN';
$_POST['telefono'] = '123456789';
$_POST['citta'] = 'Trento';
$_POST['dataNascita'] = '1990-01-01';
$_POST['agreement'] = 'on';


$_SERVER['REQUEST_METHOD'] = 'POST';
register();

// 4. Verifica il processo di registrazione
assert(strpos($_SERVER['REQUEST_URI'], 'success.php') !== false, 'Registrazione fallita');

// 5. Reimposta i dati di test 
$_POST = [];

// Simula l'invio del modulo con password non valida
$_POST['firstName'] = 'Luigi';
$_POST['lastName'] = 'Bianchi';
$_POST['email'] = 'luigi.bianchi@example.com';
$_POST['password'] = 'password';  // Password non valida, non rispetta i vincoli
$_POST['confirm_pwd'] = 'password';
$_POST['userType'] = 'allenatore';
$_POST['societyCode'] = 'EAGLEB';
$_POST['telefono'] = '987654321';
$_POST['citta'] = 'Trento';
$_POST['dataNascita'] = '1995-01-01';
$_POST['agreement'] = 'on';

$_SERVER['REQUEST_METHOD'] = 'POST';
register();

// Verifica che il processo di registrazione restiusca il giusto errore
assert(strpos($output, 'La password non rispetta i vincoli richiesti.') !== false, 'Invalid password error message not displayed');

?>