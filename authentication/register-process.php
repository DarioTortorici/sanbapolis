<!-- registration scripts -->
<script src="../js/authentication/register.js"></script>
<?php
require('auth-helper.php');
require_once('db_connection.php');
require('../modals/email-handler.php');


// Array per gli errori
$errors = array();

// Funzione per aggiungere un errore all'array degli errori
function addError(&$errors, $error)
{
    $errors[] = $error;
}

// Funzione per verificare l'esistenza e validità di un parametro POST
function validatePostParameter($paramName)
{
    return isset($_POST[$paramName]) && !empty(trim($_POST[$paramName]));
}

$firstName = validate_input_text($_POST['firstName']);
if (!$firstName) {
    addError($errors, "Hai dimenticato di inserire il tuo nome.");
}

$lastName = validate_input_text($_POST['lastName']);
if (!$lastName) {
    addError($errors, "Hai dimenticato di inserire il tuo cognome.");
}

$email = validate_input_email($_POST['email']);
if (!$email) {
    addError($errors, "Hai dimenticato di inserire il tuo indirizzo email.");
}

$password = validate_input_text($_POST['password']);
$confirmPwd = validate_input_text($_POST['confirm_pwd']);

if (!$password) {
    addError($errors, "Hai dimenticato di inserire una password.");
} elseif (!validate_password($password)) {
    addError($errors, "La password deve contenere almeno 8 caratteri, di cui uno maiuscolo ed uno speciale.");
}

if (!$confirmPwd) {
    addError($errors, "Hai dimenticato di inserire la conferma della password.");
}

// Verifica che le password corrispondano
if ($password !== $confirmPwd) {
    addError($errors, "Le password non coincidono.");
}

$userType = validate_input_text($_POST['userType']);
if (!$userType) {
    addError($errors, "Hai dimenticato di inserire il tuo ruolo.");
}

// Verifica la presenza di altri campi dati specifici in base al tipo di utente
if ($userType === "allenatore") {
    $coachType = validate_input_text($_POST['coachType']);
    if (!$coachType) {
        addError($errors, "Hai dimenticato di selezionare il tipo di allenatore.");
    }
} elseif ($userType === "giocatore") {
    $teamCode = $_POST['teamCode'];
    if (empty($teamCode) || !validate_team_code($con, $teamCode)) {
        addError($errors, "Il codice squadra non esiste.");
    }
} elseif ($userType === "società") {
    $p_iva = validate_input_text($_POST['p_iva']);
    $societyName = validate_input_text($_POST['societyName']);
    $address = validate_input_text($_POST['address']);
    $sportType = validate_input_text($_POST['sportType']);

    // Controllo campi fondamentali siano inseriti
    if (!$p_iva || !$societyName || !$sportType) {
        addError($errors, "Alcuni campi dati per la società mancano o sono invalidi.");
    }
}

// Verifica la presenza di altri campi dati comuni a tutti i tipi di utente
$dataNascita = $_POST['dataNascita'];
$citta = $_POST['citta'];
$telefono = $_POST['telefono'];
$profileImage = upload_profile("../assets/profileimg/", $_FILES['profileUpload']);

$societyCode = $_POST['societyCode'];

if ($societyCode && !validate_society_code($con, $societyCode)) {
    addError($errors, "Il codice societario non esiste.");
}

if (empty($errors)) {
    // Registra un nuovo utente
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $activationCode = generateActivationCode(); // Genera un codice di attivazione univoco

    try {
        // Crea una query
        $query = "INSERT INTO persone (nome, cognome, email, data_nascita, citta, indirizzo, telefono, digest_password, locazione_immagine_profilo, data_ora_registrazione,codice_attivazione,verificato)";
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
                if (checkPending($con, "allenatori", $email)) {
                    $coachtype = $_POST['coachType'];
                    addCoach($con, $email, $coachtype, $societyCode);
                } else {
                    $errors[] = "Il tuo indirizzo mail non risulta tra gli inviti, contatta la tua società per risolvere il problema.";
                }
            } elseif ($userType == "giocatore") {
                if (checkPending($con, "giocatori", $email)) {
                    addPlayer($con, $email, $teamCode);
                } else {
                    $errors[] = "Il tuo indirizzo mail non risulta tra gli inviti, contatta il tuo allenatore per risolvere il problema.";
                }
            } elseif ($userType == "società") {
                $p_iva = $_POST['p_iva'];
                $societyName = $_POST['societyName'];
                $address = $_POST['address'];
                $sport = $_POST['sportType'];
                addCompany($con, $email, $p_iva, $societyName, $sport, $address);
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
    // Mostra gli errori a schermo
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}