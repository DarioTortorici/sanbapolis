//Creazione Oggetto globale calendar
var calendar = null;

//////////////////////////
// Chiamate al database //
//////////////////////////

/**
 * Funzione per ottenere gli eventi dal server.
 * 
 * La funzione invia una richiesta AJAX di tipo GET al file "calendar-helper.php" con il parametro "action" impostato su "get-events".
 * La risposta viene interpretata come JSON, e se la richiesta ha successo, i dati degli eventi vengono passati alla funzione "loadCalendar" per caricare il calendario.
 * In caso di errore, viene visualizzato il messaggio di errore nella console.
 */
function fetchEvents() {
    jQuery.ajax({
        url: 'http://localhost/calendar/calendar-helper.php?action=get-events',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            loadCalendar(response);
        },
        error: function (xhr, status, error) {
            console.log(xhr.responseText);
        }
    });
}

/**
 * Funzione per ottenere gli eventi di un allenatore dal server.
 * 
 * La funzione effettua una richiesta AJAX di tipo GET al file "calendar-helper.php" con il parametro "action" impostato su "get-coach-event" e il parametro "coach" impostato con il valore specificato.
 * Se la richiesta ha successo, i dati dell'evento vengono passati alla funzione "loadCalendar" per caricare il calendario.
 * In caso di errore, viene visualizzato un messaggio di errore nella console e è possibile gestire l'errore o mostrare un messaggio di errore all'utente.
 * 
 * @param {string} coach - L'allenatore per il quale ottenere gli eventi.
 */
function fetchCoachEvents(coach) {
    // Effettua una richiesta AJAX per ottenere gli eventi dell'allenatore
    jQuery.get('http://localhost/calendar/calendar-helper.php?action=get-coach-event', {
        coach: coach
    })
        .done(function (event) {
            // Recupero degli eventi avvenuto con successo
            loadCalendar(event);
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            // Recupero degli eventi fallito
            console.error('Impossibile recuperare gli eventi del coach:', textStatus, errorThrown);
            // Gestisci l'errore o visualizza un messaggio di errore all'utente
        });
}

/**
 * Funzione per salvare un evento.
 * 
 * Serializza i dati del modulo per inviarli al database. 
 * Se i campi sono validi, invia una richiesta AJAX di tipo POST.
 * La risposta viene interpretata come JSON, se la richiesta è andata a buon fine, vengono aggiornati gli eventi.
 * In caso di errore, viene visualizzato il messaggio di errore nella console.
 */
function saveEvent() {
    var formData = $('#save-form').serialize();

    // Verifica se i campi richiesti sono stati compilati prima di inviare la richiesta
    if (validateForm()) {
        jQuery.ajax({
            url: 'http://localhost/calendar/calendar-helper.php?action=save-event',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status == 'success') {
                    fetchEvents();
                    $.magnificPopup.close();
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    } else {
        $('#error-message').show();
    }
}

/**
 * Funzione per visualizzare i dettagli di un obiettivo.
 * 
 * La funzione effettua richiesta al server per ottenere i dettagli dell'obiettivo con l'ID specificato.
 * Una volta ottenuti apre una finestra modale utilizzando il plugin Magnific Popup mostrando i dettagli ricevuti.
 * 
 * @param {string} id - L'ID dell'obiettivo da visualizzare.
 */
function showGoal(id) {
    // get evento dal server
    jQuery.get('http://localhost/calendar/calendar-helper.php?action=get-event', {
        id: id
    }, function (event) {
        // Effettua la richiesta per ottenere la nota
        jQuery.get('http://localhost/calendar/calendar-helper.php?action=get-note', {
            id: id
        }, function (note) {
            // Aggiorna con i dettagli appena ricevuti il modal show-event-modal
            updateEventModal(event.start, event.startTime, event.endTime, event.title, note, event.id);

            // Apre il modal
            $.magnificPopup.open({
                items: {
                    src: "#show-event-modal"
                },
                type: 'inline',
                enableEscapekey: false
            }, 0);
        });
    });
}

/**
 * Funzione per eliminare un evento.
 * 
 * Recupera l'ID dell'evento e lo rimuove dal calendario e dal database".
 */
function deleteEvent() {
    var eventId = document.getElementById('event-id').value;

    // Rimuove l'evento (visivamente) dal calendario
    calendar.getEventById(eventId).remove();

    jQuery.ajax({
        url: 'http://localhost/calendar/calendar-helper.php?action=delete-event',
        type: 'POST',
        data: { id: eventId },
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                $.magnificPopup.close()
            }
        },
        error: function (xhr, status, error) {
            console.log(xhr.responseText);
        }
    });
}

////////////////////////
//    Altri script    //
////////////////////////

/**
 * Funzione per visualizzare il calendario con gli eventi forniti.
 * 
 * La funzione crea un oggetto `calendar` utilizzando il plugin FullCalendar. 
 * Se esiste già un calendario presente, viene distrutto prima di creare il nuovo calendario. 
 * Il calendario viene creato nel elemento con l'ID "calendarEl" e viene configurato con le opzioni specificate.
 * 
 * @param {Array} data - Gli eventi da visualizzare nel calendario.
 */
function loadCalendar(data) {
    var calendarEl = document.getElementById('calendar');

    if (calendar) {
        calendar.destroy(); // Se esiste già un calendario, distruggilo
    }

    calendar = new FullCalendar.Calendar(calendarEl, {
        events: data, // Eventi da visualizzare nel calendario
        plugins: ['interaction', 'dayGrid', 'timeGrid', 'list'], // Plugin aggiuntivi da utilizzare
        header: {
            left: 'prev,next today', // Elementi del header a sinistra
            center: 'title', // Elemento del header al centro
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth' // Elementi del header a destra
        },
        eventTimeFormat: { // Formattazione orario
            hour: '2-digit',
            minute: '2-digit',
            omitZeroMinute: true,
            hour12: false
        },
        dayMaxEventRows: 4, // Numero massimo di righe per evento nei calendari non basati su TimeGrid
        firstDay: 1, // Inizializza il calendario a Lunedì
        navLinks: true, // Abilita la navigazione ai giorni/settimane/mesi
        dateClick: function (info) {
            // Gestisce il click su una data nel calendario
            createGoal(info.dateStr); // Triggera modal nuovo evento
            handleDateClick(info); //Aggiunge automaticamente la data cliccata nel form
        },
        eventClick: function (info) {
            info.jsEvent.preventDefault();
            var currentEvent = info.event.id;
            showGoal(currentEvent); // Triggera modal per visualizzare informazioni evento
        },

    });

    calendar.render(); // Rende visibile il calendario

}

/**
 * Funzione per aggiornare i valori del modal dell'evento.
 * 
 * @param {string} date - La data dell'evento.
 * @param {string} startTime - L'orario di inizio dell'evento.
 * @param {string} endTime - L'orario di fine dell'evento.
 * @param {string} title - Il titolo dell'evento.
 * @param {string} note - La nota o descrizione dell'evento.
 * @param {string} id - L'ID dell'evento.
 */
function updateEventModal(date, startTime, endTime, title, note, id) {
    // Formatta la data (giorno mese anno)
    var formattedDate = formatDate(date);

    // Formatta gli orari di inizio e fine (hh:mm)
    var formattedStartTime = formatTime(startTime);
    var formattedEndTime = formatTime(endTime);

    // Aggiorna la data dell'evento
    document.getElementById("event-date").innerText = formattedDate;

    // Aggiorna gli orari di inizio e fine
    document.getElementById("event-time-init").innerText = formattedStartTime;
    document.getElementById("event-time-end").innerText = formattedEndTime;

    // Aggiorna il titolo e l'URL dell'evento
    document.getElementById("event-name").innerText = title;
    document.getElementById("event-note").innerText = note;

    // Aggiorna l'id dell'evento
    document.getElementById("event-id").value = id;
}

/**
 * Funzione per validare il modulo eventi.
 * 
 * La funzione controlla che tutti i campi fondamentali siano stati compilati.
 * Restituisce true se tutti i campi richiesti hanno un valore non vuoto, altrimenti restituisce false.
 * 
 * @returns {boolean} True se il modulo è valido, altrimenti false.
 */
function validateForm() {
    var requiredFields = ['society', 'start-date', 'coach'];

    for (var i = 0; i < requiredFields.length; i++) {
        var field = document.querySelector('[name="' + requiredFields[i] + '"]');
        if (field.value === '') {
            return false;
        }
    }

    return true;
}

/**
 * Toggle la visibilità in base alla checkbox.
 * @param {HTMLElement} checkbox - L'elemento della checkbox che attiva la modifica.
 * 
 */
function toggleMoreOptions(checkbox) {
    var moreOptions = document.getElementById("moreOptions");
    moreOptions.style.display = checkbox.checked ? "block" : "none";
}

/**
 * Modifica la visibilità della sezione di ripetizione settimanale in base allo stato della checkbox.
 * @param {HTMLElement} checkbox - L'elemento della checkbox che attiva la modifica.
 */
function toggleWeeklyRepeat(checkbox) {
    var weeklyRepeatSection = document.getElementById("weeklyRepeat");
    if (checkbox.checked) {
        weeklyRepeatSection.style.display = "block";
    } else {
        weeklyRepeatSection.style.display = "none";
    }
}

/**
 * Funzione per formattare una data nel formato "giorno mese anno".
 * 
 * @param {string} date - La data da formattare (nel formato accettato dal costruttore Date() di JavaScript).
 * @returns {string} La data formattata nel formato "giorno mese anno".
 */
function formatDate(date) {
    var eventDate = new Date(date);
    var day = eventDate.getDate();
    var month = eventDate.toLocaleString('default', {
        month: 'long'
    });
    var year = eventDate.getFullYear();

    return day + ' ' + month + ' ' + year;
}

/**
 * Funzione per formattare un'orario nel formato "ore minuti".
 * 
 * @param {string} date - La data da formattare (nel formato accettato dal costruttore Date() di JavaScript).
 * @returns {string} La data formattata nel formato "ore minuti".
 */
function formatTime(time) {
    var eventTime = new Date('1970-01-01T' + time);
    var hours = eventTime.getHours().toString().padStart(2, '0');
    var minutes = eventTime.getMinutes().toString().padStart(2, '0');

    return hours + ':' + minutes;
}

/**
 * Funzione per creare un nuovo obiettivo.
 * 
 * La funzione apre una finestra modale utilizzando il plugin Magnific Popup, visualizzando il contenuto con l'ID "add-event-modal".
 * 
 * @param {string} currentDate - La data corrente da utilizzare come valore predefinito per la data di inizio del nuovo obiettivo.
 */
function createGoal(currentDate) {
    $('#save-form').trigger('reset');
    $('#save-form input[name=id]').val("");
    $('#sd').val(currentDate);
    $.magnificPopup.open({
        items: {
            src: "#add-event-modal"
        },
        type: 'inline',
        enableEscapekey: false
    }, 0);
}

/**
 * Funzione per gestire il click su una data nel calendario.
 * 
 * La funzione viene chiamata quando l'utente fa clic su una data nel calendario.
 * Prende come parametro l'oggetto `date` che contiene informazioni sulla data clickata.
 * Con questo aggiorna i valori dei campi "start-date", "end-date" e "startRecur" nel modulo
 * @param {object} date - L'oggetto che contiene informazioni sulla data selezionata.
 */
function handleDateClick(date) {
    var clickedDate = date.dateStr;
    $('#start-date').val(clickedDate);
    $('#end-date').val(clickedDate);

    // Aggiorna il form con la data selezionata
    $('#save-form input[name="start-date"]').val(clickedDate);
    $('#save-form input[name="end-date"]').val(clickedDate);
    $('#save-form input[name="startRecur"]').val(clickedDate);

}