<?php
include('../modals/header.php');

function logout()
{
    // Codice PHP per eliminare il cookie di autenticazione
    setcookie('email', '', time() - 3600, '/'); // Imposta il tempo di scadenza al passato per eliminare il cookie

    // Reindirizza alla pagina di login
    header("Location: ../authentication/login.php");
    exit();
}

if (isset($_POST['logout'])) {
    logout();
}

?>

<div style="text-align: center; padding: 50px;">
    <h1>Benvenuto!</h1>
    <p>Grazie per esserti registrato. Per poter utilizzare i nostri servizi, devi attivare il tuo account.</p>
    <p>Ti abbiamo inviato una email di conferma all'indirizzo fornito durante la registrazione. Controlla la tua casella di posta elettronica e segui le istruzioni per attivare il tuo account.</p>
    <p>Se non ricevi l'email entro qualche minuto, assicurati di controllare anche nella cartella dello spam.</p>
    <p>Una volta attivato il tuo account, sarai pronto a sfruttare tutti i nostri servizi!</p>
    <p>Se ti sei reso conto di aver fatto un errore nel digitare la mail, puoi effettuare il log out:</p>
    <form method="post">
        <button type="submit" name="logout" class="btn btn-warning">Effettua il log out</button>
    </form>
</div>

<?php
include('../modals/footer.php');
?>
