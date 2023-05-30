/**
 * Funzione di gestione dell'evento di invio del modulo di accesso (login)
 * @param {Event} event - Oggetto evento generato dall'invio del modulo
*/
document.getElementById('log-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Blocca l'invio del modulo

    // Ottieni i valori dei campi email e password
    var email = document.getElementById('email').value;
    var password = document.getElementById('password').value;

    // Effettua una richiesta AJAX al file PHP per verificare le credenziali
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'login-process.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);

            if (response.success) {
                // Credenziali corrette, reindirizza l'utente alla pagina successiva
                console.log("Reindirizzo");
                window.location.href = 'http://localhost/profile/user-dashboard.php';
            } else {
                // Credenziali errate, mostra un messaggio di errore
                showErrorMessage();
            }
        }
    };
    xhr.send('email=' + email + '&password=' + password);
});

/**
 * Mostra un alert di errore nel caso in cui le credenziali di accesso siano errate.
*/
function showErrorMessage() {
    const alertDiv = document.getElementById('mismatch-credentials')
    alertDiv.style.display = 'block';
}