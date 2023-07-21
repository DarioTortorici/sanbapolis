<?php

/**
 * Questa pagina gestisce l'attivazione dell'account utilizzando un codice di attivazione passato nell'URL.
 */

require 'db_connection.php';

/**
 * Verifica e attiva l'account utilizzando il codice di attivazione fornito.
 *
 * @param string $activationCode Il codice di attivazione da verificare.
 * @return bool True se il codice di attivazione è valido, altrimenti False.
 */
function activateAccountUsingCode($activationCode)
{
    $isValidCode = checkActivationCode($activationCode);

    if ($isValidCode) {
        activateAccount($activationCode);
        return true;
    }

    return false;
}

/**
 * Verifica se il codice di attivazione esiste nel database.
 *
 * @param string $activationCode Il codice di attivazione da verificare.
 * @return bool True se il codice di attivazione è presente nel database, altrimenti False.
 */
function checkActivationCode($activationCode)
{
    $con = get_connection();
    $query = "SELECT COUNT(*) FROM persone WHERE codice_attivazione = :code";
    $stmt = $con->prepare($query);
    $stmt->execute([':code' => $activationCode]);
    $rowCount = $stmt->fetchColumn();
    return ($rowCount > 0);
}

/**
 * Attiva l'account impostando il flag 'verificato' a 1 nel database.
 *
 * @param string $activationCode Il codice di attivazione dell'account da attivare.
 * @return void
 */
function activateAccount($activationCode)
{
    $con = get_connection();
    $query = "UPDATE persone SET verificato = 1 WHERE codice_attivazione = :code";
    $stmt = $con->prepare($query);
    $stmt->execute([':code' => $activationCode]);
}

// Verifica se è stato passato il parametro 'code' nell'URL
if (isset($_GET['code'])) {
    $activationCode = $_GET['code'];

    if (activateAccountUsingCode($activationCode)) {
        // Codice di attivazione valido, esegui le azioni necessarie per attivare l'account
        echo "Account attivato con successo!";
        header("Location: ../authentication/login.php");
        exit; // Termina l'esecuzione del codice dopo la reindirizzamento
    } else {
        // Codice di attivazione non valido
        echo "Codice di attivazione non valido!";
    }
} else {
    // Nessun codice di attivazione fornito
    echo "Codice di attivazione mancante!";
}

?>
