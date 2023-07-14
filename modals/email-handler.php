<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';
require_once '../authentication/db_connection.php';
require '../authentication/auth-helper.php';

if (isset($_POST['invited-email'])) {
    $invitedEmail = $_POST['invited-email'];
    $teamName = $_POST['hidden-title-name'];
    $code = $_POST['hidden-code'];

    $con = get_connection();
    $query = "INSERT INTO pending (email) VALUES (:email)";

    $stmt = $con->prepare($query);
    $stmt->bindParam(':email', $invitedEmail);
    $stmt->execute();
    inviteByEmail($invitedEmail, $teamName, $code);
}

function authEmail($userEmail)
{
    $activationCode = generateActivationCode(); // Genera un codice di attivazione univoco
    $activationLink = 'https://istar.disi.unitn.it/activation.php?code=' . urlencode($activationCode); // URL della pagina di attivazione con il codice come parametro

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

        // Redirect back to the calling page
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}


function inviteByEmail($userEmail, $teamName, $code)
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
