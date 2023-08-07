<?php

/**
 * Questa pagina gestisce l'attivazione dell'account utilizzando un codice di attivazione passato nell'URL.
 */

session_start(); // Avvia la sessione per poter utilizzare $_SESSION

/**
 * Verifica e attiva l'account utilizzando il codice di attivazione fornito.
 *
 * @param string $activationCode Il codice di attivazione da verificare.
 * @return bool True se il codice di attivazione è valido, altrimenti False.
 */
function activateAccountUsingCode($activationCode)
{
    if ($_SESSION['token'] == $activationCode) {
        // Codice di attivazione valido, impostiamo la variabile di sessione 'attivato' su true
        $_SESSION['attivato'] = true;
        return true;
    }

    return false;
}

// Verifica se è stato passato il parametro 'code' nell'URL
if (isset($_GET['code'])) {
    $activationCode = $_GET['code'];

    if (activateAccountUsingCode($activationCode)) {
        // Codice di attivazione valido, esegui le azioni necessarie per attivare l'account

        // Redirect verso la pagina di login con il parametro 'verified' impostato su true
        header("Location: ../authentication/login.php?verified=true");
        exit; // Termina l'esecuzione del codice dopo il reindirizzamento
    } else {
        // Codice di attivazione non valido
        echo "Codice di attivazione non valido!";
    }
} else {
    // Nessun codice di attivazione fornito
    echo "Codice di attivazione mancante!";
}