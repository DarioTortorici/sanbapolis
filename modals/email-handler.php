<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';
require_once '../authentication/db_connection.php';

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

function insertInvitedEmail($userType, $invitedEmail)
{
    $con = get_connection();
    $tabella = 'inviti_'.$userType;
    if ($userType == "allenatori") {
        $query = "INSERT INTO ".$tabella." (email) VALUES (:email)";

        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $invitedEmail);
        $stmt->execute();
    }
}

function authEmail($userEmail, $activationCode)
{
    $activationLink = 'https://istar.disi.unitn.it/authentication/activation.php?code=' . urlencode($activationCode); // URL della pagina di attivazione con il codice come parametro

    // Create an instance of PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings for Sendinblue
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp-relay.sendinblue.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sporttech76@gmail.com';
        $mail->Password   = 'sGIHcrNLDbfMKAvZ';
        $mail->Port       = 587;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        // Recipients
        $mail->setFrom('sporttech76@gmail.com', 'SportTech');
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Attiva l'account Sanbapolis";
        $mail->Body    = 'Per poter usufruire di tutti i nostri servizi, clicca su <a href="' . $activationLink . '">questo link</a>';
        $mail->AltBody = 'Per poter usufruire di tutti i nostri servizi, copia e incolla il seguente link nel tuo browser: ' . $activationLink;

        $mail->send();
        echo 'Message has been sent';

        header('Location: ../profile/user-dashboard.php');

        exit();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function inviteCoachByEmail($userEmail, $teamName, $code)
{
    // Create an instance of PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings for Sendinblue
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp-relay.sendinblue.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sporttech76@gmail.com';
        $mail->Password   = 'sGIHcrNLDbfMKAvZ';
        $mail->Port       = 587;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        // Recipients
        $mail->setFrom('sporttech76@gmail.com', 'SportTech');
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Invito alla Sanbapolis Platform';
        $mail->Body    = 'Unisciti a ' . $teamName . ', clicca su istar.disi.unitn.it/authentication/register.php?userType=allenatore&teamcode=' . $code;
        $mail->AltBody = 'Unisciti a ' . $teamName . ', clicca su istar.disi.unitn.it/authentication/register.php?userType=allenatore&teamcode=' . $code;

        $mail->send();
        echo 'Message has been sent';

        // Redirect back to the calling page
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function invitePlayerByEmail($userEmail, $teamName, $code)
{
    // Create an instance of PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings for Sendinblue
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp-relay.sendinblue.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sporttech76@gmail.com';
        $mail->Password   = 'sGIHcrNLDbfMKAvZ';
        $mail->Port       = 587;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        // Recipients
        $mail->setFrom('sporttech76@gmail.com', 'SportTech');
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Invito alla Sanbapolis Platform';
        $mail->Body    = 'Unisciti a ' . $teamName . ', clicca su istar.disi.unitn.it/authentication/register.php?userType=giocatore&teamcode=' . $code;
        $mail->AltBody = 'Unisciti a ' . $teamName . ', clicca su istar.disi.unitn.it/authentication/register.php?userType=giocatore&teamcode=' . $code;

        $mail->send();
        echo 'Message has been sent';

        // Redirect back to the calling page
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function authEvent($manutentore, $author, $startDate, $endDate, $startTime, $endTime, $cameras)
{
    // Create an instance of PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings for Sendinblue
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp-relay.sendinblue.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sporttech76@gmail.com';
        $mail->Password   = 'sGIHcrNLDbfMKAvZ';
        $mail->Port       = 587;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        // Recipients
        $mail->setFrom('sporttech76@gmail.com', 'SportTech');

        // Mail ad ogni manutentore
        foreach ($manutentore as $email) {
            $mail->addAddress($email);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Richiesta Evento';
        $mail->Body    = 'La tua struttura è richiesta da ' . $author .
            'Dal ' . $startDate . 'al ' . $endDate .
            "dall'ora" . $startTime . "alle " . $endTime .
            "Utilizzando le seguenti telecamere: " . $cameras;
        $mail->AltBody = 'La tua struttura è richiesta da ' . $author .
            'Dal ' . $startDate . 'al ' . $endDate .
            "dall'ora" . $startTime . "alle " . $endTime .
            "Utilizzando le seguenti telecamere: " . $cameras;

        $mail->send();
        echo 'Message has been sent';

        // Redirect back to the calling page
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
