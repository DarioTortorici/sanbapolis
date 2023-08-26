<?php
session_start(); // Avvia la sessione per poter utilizzare $_SESSION

// Include il file di connessione al database o qualsiasi altra operazione necessaria

/**
 * Verifica e attiva l'account utilizzando il codice di attivazione fornito.
 *
 * @param string $activationCode Il codice di attivazione da verificare.
 * @return bool True se il codice di attivazione è valido, altrimenti False.
 */
function activateAccountUsingCode($activationCode)
{
    return ($_SESSION['token'] == $activationCode);
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
    $query = "UPDATE persone SET verificato = 1 WHERE email = :email";
    $stmt = $con->prepare($query);
    $stmt->execute([':email' => $_COOKIE['email']]);
}

// Verifica se è stato passato il parametro 'code' nell'URL
if (isset($_GET['code'])) {
    $activationCode = $_GET['code'];

    if (activateAccountUsingCode($activationCode)) { // Codice di attivazione valido
        // Imposta la variabile di sessione e attiva l'account nel database
        $_SESSION['attivato'] = true;
        activateAccount($activationCode);

        // Redirect verso la pagina di login con il parametro 'verified' impostato su true
        header("Location: ../authentication/login.php?verified=true");
        exit;
    } else {
        // Codice di attivazione non valido
        echo "Codice di attivazione non valido!";
    }
} else {
    // Nessun codice di attivazione fornito
    echo "Codice di attivazione mancante!";
}
?>
