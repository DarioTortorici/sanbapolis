<?php
require("auth-helper.php");

$error = array();

/**
 * Valida e sanifica un input di tipo email.
 * @param string $input L'input email da validare e sanificare.
 * @return string|null Restituisce l'email validata e sanificata se è valida, altrimenti restituisce null.
 */
$email = validate_input_email($_POST['email']);
if (empty($email)) {
    $error[] = "You forgot to enter your Email";
}

/**
 * Valida e sanifica un input di tipo testo.
 * @param string $input L'input testo da validare e sanificare.
 * @return string|null Restituisce il testo validato e sanificato se è valido, altrimenti restituisce null.
 */
$password = validate_input_text($_POST['password']);
if (empty($password)) {
    $error[] = "You forgot to enter your password";
}

/**
 * Gestisce l'autenticazione dell'utente controllando che l'email e la password fornite corrispondano nel database.
 * @param string $email L'email fornita dall'utente.
 * @param string $password La password fornita dall'utente.
 * @param object $con L'oggetto di connessione al database PDO.
 * @return void
 */
if (empty($error)) {
    // Preparazione SQL query e PDO statement 
    $query = "SELECT * FROM persone WHERE email=:email";
    $stmt = $con->prepare($query);

    // Imposta parametro email
    $stmt->bindParam(':email', $email);

    // Esegue query
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($row)) {
        // Verifica password
        if (password_verify($password, $row['digest_password'])) {
           
            setcookie('email', $email, time() + 86400, '/'); // Cookie scade in 24 ore

            // Credenziali corrette, reindirizza l'utente alla pagina successiva
            $response = array('success' => true);
            echo json_encode($response);
            exit();
        } else {
            // Credenziali errate
            $response = array('success' => false);
            echo json_encode($response);
            exit();
        }
    } else {
        // Utente non trovato
        $response = array('success' => false);
        echo json_encode($response);
        exit();
    }
} else {
    // Non sono stati compilati tutti i campi
    $response = array('success' => false);
    echo json_encode($response);
    exit();
}
