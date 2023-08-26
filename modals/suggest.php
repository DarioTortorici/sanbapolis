<?php
include('../modals/header.php');

/**
 * Funzione per eseguire il logout dell'utente.
 */
function eseguiLogout()
{
    // Codice PHP per cancellare il cookie di autenticazione
    setcookie('email', '', time() - 3600, '/'); // Imposta il tempo al passato per eliminare il cookie

    // Reindirizza alla pagina di accesso
    header("Location: ../authentication/login.php");
    exit();
}

// Esegue al click del bottone di logout la funzione eseguiLogout()
if (isset($_POST['logout'])) {
    eseguiLogout();
}

?>

<!-- Testo -->
<div style="text-align: center; padding: 50px;">
    <h1>Benvenuto!</h1>
    <p>Ti ringraziamo per esserti registrato. Per poter usufruire dei nostri servizi, è necessario attivare il tuo account.</p>
    <p>Abbiamo inviato un'email di conferma all'indirizzo fornito durante la registrazione. Controlla la tua casella di posta e segui le istruzioni per attivare il tuo account.</p>
    <p>Se non ricevi l'email entro pochi minuti, verifica anche nella cartella dello spam.</p>
    <p>Una volta attivato il tuo account, sarai pronto a beneficiare di tutti i servizi che offriamo!</p>
    <p>Se hai commesso un errore nell'inserire l'indirizzo email, puoi effettuare il logout:</p>
    <form method="post">
        <button type="submit" name="logout" class="btn btn-warning">Esegui il logout</button>
    </form>
    <!-- Pulsante per tornare alla Pagina Iniziale -->
    <a href="../index.php" class="btn btn-primary">Vai alla Home Page</a>
</div>

<?php
include('../modals/footer.php');
?>
