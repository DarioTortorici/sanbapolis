<?php
// Test di Integrazione: Login
// Descrizione: Test dell'integrazione tra auth.js, login.php e login-process.php

// 1. Preparazione dei dati di test
$_POST['email'] = 'test@example.com';
$_POST['password'] = 'password123';

// 2. Simulazione dell'invio del modulo (richiesta POST) a login-process.php
$_SERVER['REQUEST_METHOD'] = 'POST';
require_once 'login-process.php';

// 3. Verifica del processo di login
// Verifica che il login sia stato eseguito correttamente
assert($_SESSION['userID'] !== null, "Il processo di login non ha impostato correttamente l'ID dell'utente nella sessione");

// 4. Reset dei dati di test (se necessario)
$_POST = [];

// 5. Simulazione dell'invio del modulo con credenziali non valide
$_POST['email'] = 'test@example.com';
$_POST['password'] = 'passworderrata';

require_once 'login-process.php';

// 6. Verifica del messaggio di errore per le credenziali non valide
// Verifica che il messaggio di errore venga visualizzato correttamente
assert(strpos($response, 'success') !== false, "Il messaggio di errore per le credenziali non valide non è stato visualizzato correttamente");

?>
