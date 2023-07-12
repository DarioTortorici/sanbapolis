<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

if (isset($_POST['invited-email'])) {
    $invitedEmail = $_POST['invited-email'];
    $teamName = $_POST['hidden-team-name'];
    $code = $_POST['hidden-code'];
    inviteByEmail($invitedEmail, $teamName, $code);
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

?>
