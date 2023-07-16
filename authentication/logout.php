<?php
// Elimina il cookie di autenticazione
setcookie('email', '', time() - 3600, '/'); // Imposta il tempo di scadenza al passato per eliminare il cookie

// Reindirizza alla pagina di login
header("Location: login.php");
exit();

?>
