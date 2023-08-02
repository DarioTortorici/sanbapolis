<?php

// Definizione delle variabili costanti
define('DB_NAME', 'sanbapolis');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', '127.0.0.1');

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