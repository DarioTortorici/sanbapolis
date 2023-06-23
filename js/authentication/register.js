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

    // Verifica il valore selezionato nel tipo di utente
    if (userTypeSelect.value === 'giocatore') {
        // Se il tipo di utente è "giocatore", mostra la riga del codice squadra
        teamCodeRow.style.display = 'block';
        societyCodeRow.style.display = 'none';

        // Rendi il campo del codice squadra obbligatorio
        teamCodeInput.required = true;
        societyCodeInput.required = false;
    } else if (userTypeSelect.value === 'allenatore') {
        // Se il tipo di utente è "allenatore", mostra la riga del codice società
        societyCodeRow.style.display = 'block';
        teamCodeRow.style.display = 'none';

        // Rendi il campo del codice società obbligatorio
        societyCodeInput.required = true;
        teamCodeInput.required = false;
    } else {
        // Altrimenti, nascondi entrambe le righe
        teamCodeRow.style.display = 'none';
        societyCodeRow.style.display = 'none';

        // Rimuovi l'attributo required da entrambi i campi
        teamCodeInput.required = false;
        societyCodeInput.required = false;
    }
}

