<?php
// Include lo script di registrazione
require('registration_script.php');

// Definisci una funzione di utilità per simulare i dati $_POST e $_FILES
function setPostData($postData, $filesData)
{
    $_POST = $postData;
    $_FILES = $filesData;
}

// Caso di test 1: dati di registrazione validi
$postData = array(
    'firstName' => 'John',
    'lastName' => 'Doe',
    'email' => 'johndoe@example.com',
    'password' => 'Password123!',
    'confirm_pwd' => 'Password123!',
    'userType' => 'allenatore',
    'dataNascita' => '1990-01-01',
    'citta' => 'Città',
    'telefono' => '123456789',
    'societyCode' => 'SOC123',
    'teamCode' => 'TEAM456'
);
$filesData = array(
    'profileUpload' => array(
        'name' => 'profile.jpg',
        'type' => 'image/jpeg',
        'size' => 1000,
        'tmp_name' => '/tmp/profile.jpg',
        'error' => 0
    )
);

setPostData($postData, $filesData);

// Testa il processo di registrazione
ob_start();
register();
$output = ob_get_clean();

// Verifica se l'utente è stato reindirizzato alla dashboard
if (strpos($output, 'Location: ../profile/user-dashboard.php') === false) {
    echo "Caso di test 1 fallito: l'utente non è stato reindirizzato alla dashboard.\n";
}

// Caso di test 2: dati di registrazione non validi (campi obbligatori mancanti)
$postData = array(
    'firstName' => '',
    'lastName' => '',
    'email' => '',
    'password' => '',
    'confirm_pwd' => '',
    'userType' => '',
    'dataNascita' => '',
    'citta' => '',
    'telefono' => '',
    'societyCode' => '',
    'teamCode' => ''
);
$filesData = array(
    'profileUpload' => array(
        'name' => 'profile.jpg',
        'type' => 'image/jpeg',
        'size' => 1000,
        'tmp_name' => '/tmp/profile.jpg',
        'error' => 0
    )
);

setPostData($postData, $filesData);

// Testa il processo di registrazione
ob_start();
register();
$output = ob_get_clean();

// Verifica se gli errori vengono visualizzati correttamente
if (strpos($output, '<li>Hai dimenticato di inserire il tuo nome.</li>') === false ||
    strpos($output, '<li>Hai dimenticato di inserire il tuo cognome.</li>') === false ||
    strpos($output, '<li>Hai dimenticato di inserire il tuo indirizzo email.</li>') === false ||
    strpos($output, '<li>Hai dimenticato di inserire una password.</li>') === false ||
    strpos($output, '<li>La password deve contenere almeno 8 caratteri, di cui uno maiuscolo ed uno speciale.</li>') === false ||
    strpos($output, '<li>Hai dimenticato di inserire la conferma della password.</li>') === false ||
    strpos($output, '<li>Le password non coincidono.</li>') === false ||
    strpos($output, '<li>Hai dimenticato di inserire il tuo ruolo.</li>') === false
) {
    echo "Caso di test 2 fallito: gli errori non vengono visualizzati correttamente.\n";
}

// È possibile aggiungere casi di test aggiuntivi per coprire ulteriori scenari

echo "Unit test completato.\n";
?>
