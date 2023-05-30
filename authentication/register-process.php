<?php
require('auth-helper.php');

// Array per gli errori
$errors = array();

/**
 * Gestisce la validazione dei campi dell'utente e l'upload dell'immagine del profilo.
 * In caso di errori di validazione, i messaggi di errore vengono aggiunti all'array $errors.
 * @param string $_POST['firstName'] Il nome fornito dall'utente.
 * @param string $_POST['lastName'] Il cognome fornito dall'utente.
 * @param string $_POST['email'] L'indirizzo email fornito dall'utente.
 * @param string $_POST['password'] La password fornita dall'utente.
 * @param string $_POST['confirm_pwd'] La conferma della password fornita dall'utente.
 * @param string $_POST['sport'] Lo sport fornito dall'utente. 
 * @param string $_POST['userType'] Il ruolo fornito dall'utente.
 * @param string $_POST['society'] La società fornita dall'utente.
 * @param array $_FILES['profileUpload'] I dettagli dell'immagine del profilo da caricare.
*/
$firstName = validate_input_text($_POST['firstName']);
if (empty($firstName)){
    $errors[] = "Hai dimenticato di inserire il tuo nome.";
}

$lastName = validate_input_text($_POST['lastName']);
if (empty($lastName)){
    $errors[] = "Hai dimenticato di inserire il tuo cognome.";
}

$email = validate_input_email($_POST['email']);
if (empty($email)){
    $errors[] = "Hai dimenticato di inserire il tuo indirizzo email.";
}

$password = validate_input_text($_POST['password']);
if (empty($password)){
    $errors[] = "Hai dimenticato di inserire una password.";
} elseif (!validate_password($password)){
    $errors[] = "La password deve contenere almeno 8 caratteri, di cui uno maiuscolo ed uno speciale.";
}

$confirmPwd = validate_input_text($_POST['confirm_pwd']);
if (empty($confirmPwd)){
    $errors[] = "Hai dimenticato di inserire la conferma della password.";
}

// Verifica che le password corrispondano
if ($password !== $confirmPwd) {
    $errors[] = "Le password non coincidono.";
}

$sport = validate_input_text($_POST['sport']);
if (empty($sport)){
    $errors[] = "Hai dimenticato di inserire il tuo sport.";
}

$userType = validate_input_text($_POST['userType']);
if (empty($userType)){
    $errors[] = "Hai dimenticato di inserire il tuo ruolo.";
}

$society = $_POST['society'];

$profileImage = upload_profile("../assets/profileimg/",$_FILES['profileUpload']);

/**
 * Registra un nuovo utente nel database se non ci sono errori di validazione.
 * La password fornita viene criptata utilizzando la funzione password_hash prima di essere memorizzata nel database.
 * @param string $firstName Il nome fornito dall'utente.
 * @param string $lastName Il cognome fornito dall'utente.
 * @param string $email L'indirizzo email fornito dall'utente.
 * @param string $password La password fornita dall'utente.
 * @param string $sport Lo sport fornito dall'utente.
 * @param string $userType Il ruolo fornito dall'utente. 
 * @param string $society La società fornita dall'utente.
 * @param string $profileImage Il percorso dell'immagine del profilo caricata dall'utente.
*/
if (empty($errors)){
    // Registra un nuovo utente
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    try {
        require('db_connection.php');

        // Crea una query
        $query = "INSERT INTO user (firstName, lastName, email, password, sport, userType, society, profileImage, registerDate)";
        $query .= " VALUES (:firstName, :lastName, :email, :password, :sport, :userType, :society, :profileImage, NOW())";

        // Prepara la dichiarazione
        $stmt = $con->prepare($query);

        // Bind dei parametri
        $stmt->bindParam(':firstName', $firstName);
        $stmt->bindParam(':lastName', $lastName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':sport', $sport);
        $stmt->bindParam(':userType', $userType);
        $stmt->bindParam(':society', $society);
        $stmt->bindParam(':profileImage', $profileImage,PDO::PARAM_LOB);
    
        // Esegui la query
        $stmt->execute();

        if ($stmt->rowCount() == 1){
            // Inizia una nuova sessione
            session_start();

            // Crea la variabile di sessione
            $_SESSION['userID'] = $con->lastInsertId();
            header('Location: ../profile/user-dashboard.php');
            exit();
        } else {
            print "Error while registration...!";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    } finally {
        $con = null; // Chiudi la connessione PDO
    }
} else {
    // Aggiungi l'errore specifico della password all'array degli errori
    $passwordError = "";
    foreach ($errors as $error) {
        if (strpos($error, "La password") !== false) {
            $passwordError = $error;
            break;
        }
    }
    
    echo $passwordError;
}
