<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';
require_once '../authentication/db_connection.php';

global $smtpHost; 
global $smtpUser;
global $smtpPassword;

if (isset($_POST['invited-email'])) {
    $invitedEmail = $_POST['invited-email'];

    if (isset($_POST['hidden-society-name']) and isset($_POST['hidden-society-code'])) {
        $teamName = $_POST['hidden-society-name'];
        $code = $_POST['hidden-society-code'];
        insertInvitedEmail("allenatori",$invitedEmail);
        inviteCoachByEmail($invitedEmail, $teamName, $code);
    } elseif (isset($_POST['hidden-team-name']) and isset($_POST['hidden-team-code'])) {
        $teamName = $_POST['hidden-team-name'];
        $code = $_POST['hidden-team-code'];
        insertInvitedEmail("giocatori",$invitedEmail);
        invitePlayerByEmail($invitedEmail, $teamName, $code);
    } else {
        echo "Impossibile inviare la mail";
    }
}

/**
 * Inserisce un'email invitata per un determinato tipo di utente nella tabella degli inviti.
 * 
 * @param {string} $userType - Il tipo di utente ("allenatori" o "giocatori").
 * @param {string} $invitedEmail - L'email dell'utente invitato.
 * @return {boolean} - True se l'inserimento è avvenuto con successo, False altrimenti.
 * @throws {InvalidArgumentException} - Viene lanciata un'eccezione se il tipo di utente non è valido.
 */
function insertInvitedEmail($userType, $invitedEmail)
{
    // Verifica se il $userType è valido
    $validUserTypes = array("allenatori", "giocatori");
    if (!in_array($userType, $validUserTypes)) {
        throw new InvalidArgumentException("Tipo di utente non valido.");
    }

    $con = get_connection();

    // Costruisce il nome della tabella dinamicamente
    $tabella = 'inviti_' . $userType;

    // Utilizzo della stessa query per entrambi i tipi di utente
    $query = "INSERT INTO " . $tabella . " (email) VALUES (:email)";
    $stmt = $con->prepare($query);
    $stmt->bindParam(':email', $invitedEmail);

    try {
        // Esegue l'inserimento dell'email nella tabella
        $stmt->execute();
        return true; // Ritorno true in caso di successo
    } catch (PDOException $e) {
        // Puoi gestire l'errore in base alle tue esigenze, es. log del messaggio di errore
        error_log("Errore nell'inserimento dell'email: " . $e->getMessage());
        return false; // Ritorno false in caso di errore
    }
}

/**
 * Invia un'email di autenticazione con il codice di attivazione per attivare l'account.
 * 
 * @param {string} $userEmail - L'email dell'utente a cui inviare l'email di autenticazione.
 * @param {string} $activationCode - Il codice di attivazione da includere nell'email di autenticazione.
 * @return {void} - La funzione non restituisce alcun valore.
 */
function authEmail($userEmail, $activationCode)
{
    $activationLink = $_SERVER['DOCUMENT_ROOT'].'/authentication/activation.php?code=' . urlencode($activationCode); // URL della pagina di attivazione con il codice come parametro

    // Crea istanza di PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings di Sendinblue
        $mail->isSMTP();                                            // Mandato via SMTP
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpName;
        $mail->Password   = $smtpPassword;
        $mail->Port       = 587;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        // Recipients
        $mail->setFrom($smtpName, 'SportTech');
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Attiva l'account Sanbapolis";
        $mail->Body    = 'Per poter usufruire di tutti i nostri servizi, clicca su <a href="' . $activationLink . '">questo link</a>';
        $mail->AltBody = 'Per poter usufruire di tutti i nostri servizi, copia e incolla il seguente link nel tuo browser: ' . $activationLink;

        $mail->send();
        // L'email è stata inviata con successo, quindi esegui un reindirizzamento alla dashboard dell'utente.
        header('Location: ../profile/user-dashboard.php');
        

    } catch (Exception $e) {
        // In caso di errore nell'invio dell'email, mostra un messaggio di errore.
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

/**
 * Invia un'email di invito a un allenatore specifico.
 * 
 * @param {string} $userEmail - L'email dell'allenatore a cui inviare l'invito.
 * @param {string} $teamName - Il nome della squadra a cui l'allenatore è invitato.
 * @param {string} $code - Il codice di invito associato all'allenatore.
 * @return {void} - La funzione non restituisce alcun valore.
 */
function inviteCoachByEmail($userEmail, $teamName, $code)
{
    // Crea instanza di PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings for Sendinblue
        $mail->isSMTP();                                            // Mandato via SMTP
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpName;
        $mail->Password   = $smtpPassword;
        $mail->Port       = 587;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        // Recipients
        $mail->setFrom($smtpName, 'SportTech');
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Invito alla Sanbapolis Platform';
        $invitationLink = $_SERVER['DOCUMENT_ROOT'].'/authentication/register.php?userType=allenatore&scoietyCode=' . $code;
        $mail->Body    = 'Unisciti a ' . $teamName . ', clicca su <a href="' . $invitationLink . '">questo link</a>';
        $mail->AltBody = 'Unisciti a ' . $teamName . ', copia e incolla il seguente link nel tuo browser: ' . $invitationLink;

        $mail->send();
        // L'email è stata inviata con successo, quindi esegui un reindirizzamento alla pagina precedente.
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit(); // Termina l'esecuzione dello script dopo il reindirizzamento.

    } catch (Exception $e) {
        // In caso di errore nell'invio dell'email, mostra un messaggio di errore.
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

/**
 * Invia un'email di invito a un giocatore specifico.
 * 
 * @param {string} $userEmail - L'email del giocatore a cui inviare l'invito.
 * @param {string} $teamName - Il nome della squadra a cui il giocatore è invitato.
 * @param {string} $code - Il codice di invito associato al giocatore.
 * @return {void} - La funzione non restituisce alcun valore.
 */
function invitePlayerByEmail($userEmail, $teamName, $code)
{
    // Crea instanza di PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings di Sendinblue
        $mail->isSMTP();                                            // Mandato via SMTP
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpName;
        $mail->Password   = $smtpPassword;
        $mail->Port       = 587;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        // Recipients
        $mail->setFrom($smtpName, 'SportTech');
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Invito alla Sanbapolis Platform';
        $invitationLink = $_SERVER['DOCUMENT_ROOT'].'/authentication/register.php?userType=giocatore&teamCode=' . $code;
        $mail->Body    = 'Unisciti a ' . $teamName . ', clicca su <a href="' . $invitationLink . '">questo link</a>';
        $mail->AltBody = 'Unisciti a ' . $teamName . ', copia e incolla il seguente link nel tuo browser: ' . $invitationLink;

        $mail->send();
        // L'email è stata inviata con successo, quindi esegui un reindirizzamento alla pagina precedente.
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit(); // Termina l'esecuzione dello script dopo il reindirizzamento.

    } catch (Exception $e) {
        // In caso di errore nell'invio dell'email, mostra un messaggio di errore.
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

/**
 * Invia una richiesta di evento ai manutentori specificati.
 * 
 * @param {array} $manutentore - Un array contenente gli indirizzi email dei manutentori a cui inviare la richiesta.
 * @param {string} $author - Il nome dell'autore della richiesta evento.
 * @param {string} $startDate - La data di inizio dell'evento.
 * @param {string} $endDate - La data di fine dell'evento.
 * @param {string} $startTime - L'ora di inizio dell'evento.
 * @param {string} $endTime - L'ora di fine dell'evento.
 * @param {string} $cameras - Un elenco delle telecamere utilizzate nell'evento.
 * @return {void} - La funzione non restituisce alcun valore.
 */
function authEvent($manutentore, $author, $startDate, $endDate, $startTime, $endTime, $cameras)
{
    // Crea istanza di PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings di Sendinblue
        $mail->isSMTP();                                            // Mandato via SMTP
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpName;
        $mail->Password   = $smtpPassword;
        $mail->Port       = 587;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        // Recipients
        $mail->setFrom($smtpName, 'SportTech');

        // Aggiungi gli indirizzi email dei manutentori come destinatari
        foreach ($manutentore as $email) {
            // Verifica se l'indirizzo email è una stringa valida
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Aggiungi l'indirizzo email come destinatario
                $mail->addAddress($email);
            }
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Richiesta Evento';
        $mail->Body    = 'La tua struttura è richiesta da ' . $author .
            ' dal ' . $startDate . ' al ' . $endDate .
            " dalle ore " . $startTime . " alle " . $endTime .
            " utilizzando le seguenti telecamere: " . $cameras;
        $mail->AltBody = 'La tua struttura è richiesta da ' . $author .
            ' dal ' . $startDate . ' al ' . $endDate .
            " dalle ore " . $startTime . " alle " . $endTime .
            " utilizzando le seguenti telecamere: " . $cameras;

        $mail->send();
        // L'email è stata inviata con successo, quindi esegui un reindirizzamento alla pagina precedente.
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit(); // Termina l'esecuzione dello script dopo il reindirizzamento.

    } catch (Exception $e) {
        // In caso di errore nell'invio dell'email, mostra un messaggio di errore.
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
