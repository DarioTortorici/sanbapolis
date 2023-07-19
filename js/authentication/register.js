/** Gestisce la selezione del tipo di utente e mostra/nasconde le righe dei codici assegnazione.
 */

function handleUserType() {
    // Ottieni il riferimento all'elemento select per il tipo di utente
    var userTypeSelect = document.getElementById('userType');

    // Ottieni il riferimento alle righe del form
    var teamCodeRow = document.getElementById('teamCodeRow');
    var societyCodeRow = document.getElementById('societyCodeRow');
    var userSocietyRow = document.getElementById('userSocietyRow');

    // Ottieni i campi input del form
    var teamCodeInput = document.getElementById('teamCode');
    var societyCodeInput = document.getElementById('societyCode');
    var pIvaInput = document.getElementById('p_iva');
    var societyNameInput = document.getElementById('societyName');
    var sportInput = document.getElementById('sportType');
    var coachTypeInput = document.getElementById('coachType');

    // Nascondi tutte le righe del form
    teamCodeRow.style.display = 'none';
    societyCodeRow.style.display = 'none';
    userSocietyRow.style.display = 'none';

    // Imposta tutti i campi input come non obbligatori
    teamCodeInput.required = false;
    societyCodeInput.required = false;
    pIvaInput.required = false;
    societyNameInput.required = false;
    sportInput.required = false;
    coachTypeInput.required = false;

    // Verifica il valore selezionato nel tipo di utente
    switch (userTypeSelect.value) {
        case 'giocatore':
            // Se il tipo di utente è "giocatore", mostra la riga del codice squadra
            teamCodeRow.style.display = 'block';
            teamCodeInput.required = true;
            break;
        case 'allenatore':
            // Se il tipo di utente è "allenatore", mostra la riga del codice società
            societyCodeRow.style.display = 'block';
            societyCodeInput.required = true;
            coachTypeInput.required = true;
            break;
        case 'società':
            // Se il tipo di utente è "società", mostra la riga dell'IVA e del nome società
            userSocietyRow.style.display = 'block';
            pIvaInput.required = true;
            societyNameInput.required = true;
            sportInput.required = true;
            break;
        default:
            // Altrimenti, non mostrare alcuna riga e non rendere obbligatorio nessun campo
            break;
    }
}

/**
 * Event listener che controlla che la password inserita rispetti i vincoli
 * @param {string} password - la password da controlalre.
 * @returns {boolean} - True se rispetta i vincoli, False altrimenti.
 */
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('reg-form');
    var passwordInput = document.getElementById('password');
    var passwordError = document.getElementById('confirm_error');

    form.addEventListener('submit', function (event) {
        if (!isPasswordValid(passwordInput.value)) {
            event.preventDefault(); // Impedisce l'invio del modulo
            displayErrorMessage('La password non rispetta i vincoli richiesti.');
        }
    });

    function displayErrorMessage(message) {
        passwordError.textContent = message;
        passwordError.style.display = 'block'; // Rendi visibile il messaggio di errore
    }
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