/**
 * Converte il numero di secondi in un formato di visualizzazione delle ore, dei minuti e dei secondi.
 *
 * @param {number} seconds - Il numero di secondi da convertire.
 * @param {boolean} showHours - Un flag booleano che indica se visualizzare anche le ore.
 * @returns {string} - La stringa nel formato "HH:mm:ss" se showHours è true, altrimenti "mm:ss".
 */
function fromSeconds(seconds, showHours) {
    if (showHours) {
        var hours = Math.floor(seconds / 3600);
        seconds = seconds - hours * 3600;
    }
    var minutes = ("0" + Math.floor(seconds / 60)).slice(-2);
    var seconds = ("0" + parseInt(seconds % 60, 10)).slice(-2);

    if (showHours) {
        var timestring = hours + ":" + minutes + ":" + seconds;
    } else {
        var timestring = minutes + ":" + seconds;
    }
    return timestring;
}

/**
 * Trova e restituisce il valore di un parametro dalla stringa di query dell'URL.
 *
 * Questa funzione analizza la stringa di query dell'URL, estrae il valore associato al parametro
 * specificato e lo restituisce. Se il parametro non viene trovato o non ha un valore associato,
 * la funzione restituirà `null`.
 *
 * @param {string} parameterName - Il nome del parametro da cercare.
 * @returns {string|null} - Il valore del parametro specificato, oppure `null` se il parametro non è presente o non ha un valore.
 */
function findGetParameter(parameterName) {
        var result = null,
            tmp = [];
        location.search
            //.substr(1) modificato perché deprecato
            .slice(1)
            .split("&")
            .forEach(function(item) {
                tmp = item.split("=");
                if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
            });
        return result;
}

/**
 * Visualizza una notifica a comparsa (snackbar) all'interno della pagina web per un breve periodo di tempo.
 *
 * Questa funzione recupera l'elemento con l'ID "snackbar" dal DOM e gli applica una classe "show" per
 * renderlo visibile sulla pagina. Dopo 3 secondi, la classe "show" viene rimossa, nascondendo così la notifica.
 * La funzione può essere utilizzata per fornire feedback all'utente su determinate azioni o eventi.
 */
function showSnackbar() {
    var x = document.getElementById("snackbar");
    //console.log(x);
    x.className = "show";
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
}

/**
 * Converte un tempo nel formato "HH:mm:ss" in un valore numerico totale in secondi.
 *
 * @param {string} timing_screen - Una stringa nel formato "HH:mm:ss" rappresentante un tempo.
 * @returns {number} - Il tempo totale in secondi, rappresentato come un numero con la parte dei millisecondi inclusa.
 */
function getNumberTimingScreen(timing_screen){
    let ris = 0.0;
    let vet_timing = Array();
    substr = timing_screen.split(":");
    substr.forEach(element => {
        vet_timing.push(parseInt(element)); 
    });
    ris = vet_timing[0] * 60 + vet_timing[1] + vet_timing[2] / 1000;

    return ris;
}

/**
 * Imposta il punto di inizio del trim di un video.
 *
 * Questa funzione recupera l'elemento con l'ID "timing_video" dal DOM, che rappresenta il campo
 * per inserire il tempo del video. Quindi, recupera l'elemento con l'ID "start_timing_trim",
 * che rappresenta il campo per impostare il punto di inizio del trim. Il valore del campo "timing_video"
 * viene copiato nel campo "start_timing_trim", in modo da impostare il punto di inizio del trim con lo stesso tempo
 * visualizzato nel campo "timing_video".
 * Infine, la funzione richiama `checkTrimTime(start_trim, end_trim)` per verificare il tempo di trimming.
 */
function getStartTimingTrim(){
    timing = document.getElementById("timing_video");
    start_trim = document.getElementById("start_timing_trim");
    start_trim.value = timing.value;
    end_trim = document.getElementById("end_timing_trim");
    checkTrimTime(start_trim, end_trim);
}

/**
 * Imposta il punto di fine del trim di un video.
 *
 * Questa funzione recupera l'elemento con l'ID "timing_video" dal DOM, che rappresenta il campo
 * per inserire il tempo del video. Quindi, recupera l'elemento con l'ID "start_timing_trim" e
 * l'elemento con l'ID "end_timing_trim", che rappresentano rispettivamente il campo per impostare
 * il punto di inizio e di fine del trim. Il valore del campo "timing_video" viene copiato nel campo
 * "end_timing_trim", in modo da impostare il punto di fine del trim con lo stesso tempo visualizzato
 * nel campo "timing_video".
 * Infine, la funzione richiama `checkTrimTime(start_trim, end_trim)` per verificare il tempo di trimming.
 */
function getEndTimingTrim(){
    timing = document.getElementById("timing_video");
    start_trim = document.getElementById("start_timing_trim");
    end_trim = document.getElementById("end_timing_trim");
    end_trim.value = timing.value;
    checkTrimTime(start_trim, end_trim);
}

/**
 * Verifica i tempi di inizio e fine del trim di un video.
 *
 * @param {HTMLInputElement} start_trim - L'elemento HTML rappresentante il campo di inizio trim.
 * @param {HTMLInputElement} end_trim - L'elemento HTML rappresentante il campo di fine trim.
 */
function checkTrimTime(start_trim, end_trim){
    if (start_trim != '' && end_trim != ''){
        let st = getNumberTimingScreen(start_trim.value);
        let et = getNumberTimingScreen(end_trim.value);
        if (st > et){
            showSnackbar();
            disableTrim(true);
        }
        else{
            disableTrim(false);
        }
    }
    else{
        disableTrim(true);
    }
}

/**
 * Abilita o disabilita un elemento HTML rappresentante il controllo di "trimming" di un video.
 *
 * @param {boolean} disabled - Un valore booleano che determina se l'elemento deve essere disabilitato (true) o abilitato (false).
 */
function disableTrim(disabled){
    document.getElementById("trim_video").disabled = disabled;
}

/**
 * Mostra o nasconde un'area nel DOM della pagina web.
 *
 * Questa funzione recupera l'elemento con l'ID "screen_area" e l'elemento con l'ID "show_screen_area"
 * dal DOM. Se l'elemento "screen_area" è nascosto (hidden == true), la funzione lo mostra impostando
 * l'attributo hidden a false e cambia il testo del pulsante "show_screen_area" in "Nascondi gli screenshot".
 * Altrimenti, se l'elemento "screen_area" è già visibile, la funzione lo nasconde impostando l'attributo hidden a true
 * e cambia il testo del pulsante "show_screen_area" in "Mostra gli screenshot".
 */
function showScreenArea() {
    let screen_area = document.getElementById('screen_area');
    let btn = document.getElementById('show_screen_area');

    if (screen_area.hidden == true) {
        screen_area.hidden = false;
        btn.innerHTML = "Nascondi gli screenshot";
    } else {
        screen_area.hidden = true;
        btn.innerHTML = "Mostra gli screenshot";
    }
}

/**
 * Mostra o nasconde un'area nel DOM della pagina web.
 *
 * Questa funzione recupera l'elemento con l'ID "marks" e l'elemento con l'ID "show_marks"
 * dal DOM. Se l'elemento "marks" è nascosto (hidden == true), la funzione lo mostra impostando
 * l'attributo hidden a false e cambia il testo del pulsante "show_marks" in "Nascondi i segnaposti".
 * Altrimenti, se l'elemento "marks" è già visibile, la funzione lo nasconde impostando l'attributo hidden a true
 * e cambia il testo del pulsante "show_marks" in "Mostra i segnaposti".
 */
function showMarks(){
    let screen_area = document.getElementById('marks');
    let btn = document.getElementById('show_marks');
    if (screen_area.hidden == true){
        screen_area.hidden = false;
        btn.innerHTML = "Nascondi i segnaposti";
    }
    else{
        screen_area.hidden = true;
        btn.innerHTML = "Mostra i segnaposti";
    }
}