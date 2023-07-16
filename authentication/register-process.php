<!-- registration scripts -->
<script src="../js/authentication/register.js"></script>
<?php
require('auth-helper.php');
require('db_connection.php');
require ('../modals/email-handler.php');
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
 * @param string $_POST['userType'] Il ruolo fornito dall'utente.
 * @param string $_POST['dataNascita'] La data di nascita fornita dall'utente.
 * @param string $_POST['citta'] La citta fornita dall'utente.
 * @param string $_POST['telefono'] Il numero di telefono dell'utente
 * @param string $_POST['societyCode'] Il codice associato alla società
 * @param string $_POST['teamCode'] Il codice associato alla squadra
 * @param array $_FILES['profileUpload'] I dettagli dell'immagine del profilo da caricare.
 */
$firstName = validate_input_text($_POST['firstName']);
if (empty($firstName)) {
    $errors[] = "Hai dimenticato di inserire il tuo nome.";
}

$lastName = validate_input_text($_POST['lastName']);
if (empty($lastName)) {
    $errors[] = "Hai dimenticato di inserire il tuo cognome.";
}

$email = validate_input_email($_POST['email']);
if (empty($email)) {
    $errors[] = "Hai dimenticato di inserire il tuo indirizzo email.";
}

$password = validate_input_text($_POST['password']);
if (empty($password)) {
    $errors[] = "Hai dimenticato di inserire una password.";
} elseif (!validate_password($password)) {
    $errors[] = "La password deve contenere almeno 8 caratteri, di cui uno maiuscolo ed uno speciale.";
}

$confirmPwd = validate_input_text($_POST['confirm_pwd']);
if (empty($confirmPwd)) {
    $errors[] = "Hai dimenticato di inserire la conferma della password.";
}

// Verifica che le password corrispondano
if ($password !== $confirmPwd) {
    $errors[] = "Le password non coincidono.";
}

$userType = validate_input_text($_POST['userType']);
if (empty($userType)) {
    $errors[] = "Hai dimenticato di inserire il tuo ruolo.";
}


$dataNascita = $_POST['dataNascita'];
$citta = $_POST['citta'];
$telefono = $_POST['telefono'];
$profileImage = upload_profile("../assets/profileimg/", $_FILES['profileUpload']);

$societyCode = $_POST['societyCode'];
$teamCode = $_POST['teamCode'];

if (!empty($societyCode)) {
    if (!validate_society_code($con, $societyCode)) {
        $errors[] = "Il codice societario non esiste";
    }
} else if (!empty($teamCode)) {
    if (!validate_team_code($con, $teamCode)) {
        $errors[] = "Il codice squadra non esiste";
    }
}

if (empty($errors)) {

    // Registra un nuovo utente
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $activationCode = generateActivationCode(); // Genera un codice di attivazione univoco

    try {
        // Crea una query
        $query = "INSERT INTO persone (nome, cognome, email, data_nascita, citta, indirizzo, telefono, digest_password, locazione_immagine_profilo, data_registrazione,codice_attivazione,verificato)";
        $query .= " VALUES (:firstName, :lastName, :email, :dataNascita, :citta, :indirizzo, :telefono, :password, :profileImage, NOW(), :code,0)";

        // Prepara la dichiarazione
        $stmt = $con->prepare($query);

        // Bind dei parametri
        $stmt->bindParam(':firstName', $firstName);
        $stmt->bindParam(':lastName', $lastName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':dataNascita', $dataNascita);
        $stmt->bindParam(':citta', $citta);
        $stmt->bindParam(':indirizzo', $indirizzo);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':profileImage', $profileImage, PDO::PARAM_LOB);
        $stmt->bindParam(':code', $activationCode, PDO::PARAM_LOB);

        // Esegui la query
        $stmt->execute();

        // Invia mail "Attiva account"
        authEmail($email,$activationCode);

        if ($stmt->rowCount() == 1) {
            
            setcookie('email', $email, time() + 86400, '/'); // Cookie scade in 24 hours

            if ($userType == "allenatore") {
                if(checkPending($con,"allenatori", $email)){
                    addCoach($con, $email, $societyCode);
                }
                else{
                    $errors[] = "Il tuo indirizzo mail non risulta tra gli inviti, contatta la tua società per risolvere il problema.";
                }
            } elseif ($userType == "giocatore") {
                if(checkPending($con,"giocatori", $email)){
                    addPlayer($con, $email, $teamCode);
                }
                else{
                    $errors[] = "Il tuo indirizzo mail non risulta tra gli inviti, contatta il tuo allenatore per risolvere il problema.";
                }
            } elseif ($userType == "società") {
                $p_iva = $_POST['p_iva'];
                $societyName = $_POST['societyName'];
                $address = $_POST['address'];
                addCompany($con, $email, $p_iva, $societyName, $address);
            } else {
                addFan($con, $email);
            }

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
// Mostra gli errori a schermo
if (!empty($errors)) {
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}
