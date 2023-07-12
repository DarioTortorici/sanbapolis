/** Gestisce la selezione del tipo di utente e mostra/nasconde le righe dei codici assegnazione.
 */
function handleUserType() {
    // Ottieni il riferimento all'elemento select per il tipo di utente
    var userTypeSelect = document.getElementById('userType');

    // Ottieni il riferimento alla riga del codice squadra
    var teamCodeRow = document.getElementById('teamCodeRow');
    var teamCodeInput = document.getElementById('teamCode');

    // Ottieni il riferimento alla riga del codice società
    var societyCodeRow = document.getElementById('societyCodeRow');
    var societyCodeInput = document.getElementById('societyCode');

    // Ottieni il riferimento alla riga dell'IVA e del nome società
    var userSocietyRow = document.getElementById('userSocietyRow');
    var pIvaInput = document.getElementById('p_iva');
    var societyNameInput = document.getElementById('societyName');

    // Verifica il valore selezionato nel tipo di utente
    if (userTypeSelect.value === 'giocatore') {
        // Se il tipo di utente è "giocatore", mostra la riga del codice squadra
        teamCodeRow.style.display = 'block';
        societyCodeRow.style.display = 'none';
        userSocietyRow.style.display = 'none';

        // Rendi il campo del codice squadra obbligatorio
        teamCodeInput.required = true;
        societyCodeInput.required = false;
        pIvaInput.required = false;
        societyNameInput.required = false;
    } else if (userTypeSelect.value === 'allenatore') {
        // Se il tipo di utente è "allenatore", mostra la riga del codice società
        societyCodeRow.style.display = 'block';
        teamCodeRow.style.display = 'none';
        userSocietyRow.style.display = 'none';

        // Rendi il campo del codice società obbligatorio
        societyCodeInput.required = true;
        teamCodeInput.required = false;
        pIvaInput.required = false;
        societyNameInput.required = false;
    } else if (userTypeSelect.value === 'società') {
        // Se il tipo di utente è "società", mostra la riga dell'IVA e del nome società
        societyCodeRow.style.display = 'none';
        teamCodeRow.style.display = 'none';
        userSocietyRow.style.display = 'block';

        // Rendi il campo dell'IVA e del nome società obbligatori
        pIvaInput.required = true;
        societyNameInput.required = true;
        teamCodeInput.required = false;
        societyCodeInput.required = false;
    } else {
        // Altrimenti, nascondi entrambe le righe
        teamCodeRow.style.display = 'none';
        societyCodeRow.style.display = 'none';
        userSocietyRow.style.display = 'none';

        // Rimuovi l'attributo required da tutti i campi
        teamCodeInput.required = false;
        societyCodeInput.required = false;
        pIvaInput.required = false;
        societyNameInput.required = false;
    }
}


/**
 * Event listener che controlla che la password inserita rispetti i vincoli
 * @param {string} password - la password da controlalre.
 * @returns {boolean} - True se rispetta i vincoli, False altrimenti.
 */
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('reg-form');
    var passwordInput = document.getElementById('password');
    var passwordError = document.getElementById('confirm_error');

    form.addEventListener('submit', function(event) {
        if (!isPasswordValid(passwordInput.value)) {
            event.preventDefault(); // Impedisce l'invio del modulo
            passwordError.textContent = 'La password non rispetta i vincoli richiesti.';
            passwordError.style.display = 'block'; // Rendi visibile il messaggio di errore
            
        }
    });
});


/**
 * Controlla che la password inserita rispetti i vincoli
 * 1. Deve avere una lunghezza minima di 8 caratteri.
 * 2. Deve contenere almeno una lettera maiuscola.
 * 3. Deve contenere almeno un carattere speciale diverso da lettere e numeri.
 * @param {string} password - la password da controlalre.
 * @returns {boolean} - True se rispetta i vincoli, False altrimenti.
 */
    function isPasswordValid(password) {
        var minLength = 8;
        var hasUppercase = /[A-Z]/.test(password);
        var hasSpecialChar = /[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]/.test(password);

        return (
            password.length >= minLength &&
            hasUppercase &&
            hasSpecialChar
        );
    }