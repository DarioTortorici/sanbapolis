<?php

/** DA FINIRE DI FARE - SICCOME DEVO CAMBIARE LA STRUTTURA DEL DB ASPETTO DI FINIRE
 * Gestisce l'autenticazione dell'utente controllando che l'email e la password fornite corrispondano nel database.
 * @param string $email L'email fornita dall'utente.
 * @param string $password La password fornita dall'utente.
 * @param PDO $con L'oggetto di connessione al database PDO.
 * @return string 
 */
function checkCredentials($pdo, $email, $password){
    $logged = false;
    if($email != null && $password != null){
        if (empty($error)) {
            $query = "SELECT * FROM persone WHERE email=:email";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($row)) {
                if (password_verify($password, $row['digest_password'])) {
                $logged = true;
                }
            }
        }
    }
    return $logged;
}