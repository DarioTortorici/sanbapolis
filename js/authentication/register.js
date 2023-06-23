/** Gestisce la selezione del tipo di utente e mostra/nasconde la riga del codice squadra.
 */
function handleUserType() {
    // Ottieni il riferimento all'elemento select per il tipo di utente
    var userTypeSelect = document.getElementById('userType');

    // Ottieni il riferimento alla riga del codice squadra
    var teamCodeRow = document.getElementById('teamCodeRow');

    // Verifica il valore selezionato nel tipo di utente
    if (userTypeSelect.value === 'giocatore') {
        // Se il tipo di utente è "giocatore", mostra la riga del codice squadra
        teamCodeRow.style.display = 'block';
    } else {
        // Altrimenti, nascondi la riga del codice squadra
        teamCodeRow.style.display = 'none';
    }
}
