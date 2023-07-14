<?php

require 'db_connection.php';
// Verifica se è stato passato il parametro 'code' nell'URL
if (isset($_GET['code'])) {
    $activationCode = $_GET['code'];

    // Verifica il codice di attivazione nel database o in altre fonti di dati
    $isValidCode = checkActivationCode($activationCode);

    if ($isValidCode) {
        // Codice di attivazione valido, esegui le azioni necessarie per attivare l'account
        activateAccount($activationCode);

        // Mostra un messaggio di conferma
        echo "Account attivato con successo!";
    } else {
        // Codice di attivazione non valido
        echo "Codice di attivazione non valido!";
    }
} else {
    // Nessun codice di attivazione fornito
    echo "Codice di attivazione mancante!";
}

function checkActivationCode($activationCode) {
    $con = get_connection();
    $query = "SELECT * FROM persone WHERE activation_code = :code";
    $stmt = $con->prepare($query);
    $stmt->execute([':code' => $activationCode]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($row !== false);
}

function activateAccount($activationCode) {
    $con = get_connection();
    $query = "UPDATE persone SET is_active = 1 WHERE activation_code = :code";
    $stmt = $con->prepare($query);
    $stmt->bindParam(':code', $activationCode);
    $stmt->execute();
}

?>
