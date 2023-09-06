<!-- registration scripts -->
<script src="../js/authentication/register.js"></script>
<?php
require('auth-helper.php');
require_once('db_connection.php');
require('../modals/email-handler.php');

session_start(); // Avvia la sessione per poter utilizzare $_SESSION
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
if ($userType == "allenatore") {
    $coachType = validate_input_text($_POST['coachType']);
    if (!$coachType) {
        addError($errors, "Hai dimenticato di selezionare il tipo di allenatore.");
    }
} elseif ($userType == "giocatore") {
    $teamCode = $_POST['teamCode'];
    if (empty($teamCode) || !validate_team_code($con, $teamCode)) {
        addError($errors, "Il codice squadra non esiste.");
    }
} elseif ($userType == "società") {
    $p_iva = validate_input_text($_POST['p_iva']);
    $societyName = validate_input_text($_POST['societyName']);
    $address = validate_input_text($_POST['address']);
    $sportType = validate_input_text($_POST['sportType']);

    // Controllo campi fondamentali siano inseriti
    if (!$p_iva || !$societyName || !$sportType) {
        addError($errors, "Alcuni campi dati per la società mancano o sono invalidi.");
    }
}

// Verifica la presenza di altri campi dati comuni a tutti i tipi di utente, altrimenti impostati a null
$dataNascita = !empty($_POST['dataNascita']) ? $_POST['dataNascita'] : null;
$citta = !empty($_POST['citta']) ? $_POST['citta'] : null;
$telefono = !empty($_POST['telefono']) ? $_POST['telefono'] : null;
$profileImage = upload_profile("../assets/profileimg/", $_FILES['profileUpload']);

$societyCode = $_POST['societyCode'];

if ($societyCode && !validate_society_code($con, $societyCode)) {
    addError($errors, "Il codice societario non esiste.");
}

if (empty($errors)) {
    // Registra un nuovo utente
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $activationCode = generateActivationCode(); // Genera un codice di attivazione univoco

    $_SESSION['token'] = $activationCode;
    try {
        // Crea una query
        $query = "INSERT INTO persone (nome, cognome, email, data_nascita, citta, indirizzo, telefono, digest_password, locazione_immagine_profilo, data_ora_registrazione, verificato)";
        $query .= " VALUES (:firstName, :lastName, :email, :dataNascita, :citta, :indirizzo, :telefono, :password, :profileImage, NOW(), 0)";

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
                    addError($errors, "Il tuo indirizzo mail non risulta tra gli inviti, contatta la tua società per risolvere il problema.");
                }
            } elseif ($userType == "giocatore") {
                if (checkPending($con, "giocatori", $email)) {
                    addPlayer($con, $email, $teamCode);
                } else {
                    addError($errors,"Il tuo indirizzo mail non risulta tra gli inviti, contatta il tuo allenatore per risolvere il problema.");
                }
            } elseif ($userType == "società") {
                $p_iva = $_POST['p_iva'];
                $societyName = $_POST['societyName'];
                $address = $_POST['address'];
                $sport = $_POST['sportType'];
                addError($errors,"Sono entrato brotha");
                addCompany($con, $email, $p_iva, $societyName, $sport, $address);
            } else {
                addError($errors,"Sono fan brotha idk y");
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
    // Prepara gli errori come query string per passarli a register.php
    $errorString = implode("|", $errors); // Converte l'array di errori in una stringa separata da "|"
    $redirectURL = "register.php?errors=" . urlencode($errorString);
    
    // Esegue il reindirizzamento alla pagina login.php con gli errori come parte della query string
    header("Location: $redirectURL");
    exit(); // Assicura che lo script si interrompa dopo aver eseguito il reindirizzamento
}