<?php

// Includi il file config.php per accedere alle variabili
require __DIR__.'/../modals/config.php';

// Definizione delle variabili costanti
define('DB_NAME', $databaseName);
define('DB_USER', $databaseUser);
define('DB_PASSWORD', $databasePassword);
define('DB_HOST', $databaseHost);

try {
    // Creazione della connessione PDO
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
    $con = new PDO($dsn, DB_USER, DB_PASSWORD);

    // Impostazione della modalità di gestione degli errori su eccezione
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $ex) {
    // Gestione degli errori di connessione
    echo "Si è verificata un'eccezione. Messaggio: " . $ex->getMessage();
} catch (Error $e) {
    // Gestione di altri errori
    echo "Il sistema è occupato. Riprova più tardi";
}

// Funzione che restituisce la connessione PDO con il server
function get_connection()
{
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
    $con = new PDO($dsn, DB_USER, DB_PASSWORD);
    return $con;
}
