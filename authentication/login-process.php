<?php
require("auth-helper.php");
require ('db_connection.php');

$error = array();

$email = validate_input_email($_POST['email']);
if (empty($email)){
    $error[] = "You forgot to enter your Email";
}

$password = validate_input_text($_POST['password']);
if (empty($password)){
    $error[] = "You forgot to enter your password";
}

if (empty($error)) {
    // Preparazione SQL query e PDO statement 
    $query = "SELECT userID, firstName, lastName, email, password, profileImage FROM user WHERE email=:email";
    $stmt = $con->prepare($query);

    // Imposta parametro email
    $stmt->bindParam(':email', $email);

    // Esegue query
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($row)) {
        // Verifica password
        if (password_verify($password, $row['password'])) {
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

