//Creazione Oggetto globale calendar
var calendar = null;

//////////////////////////
// Chiamate al database //
//////////////////////////

/** Funzione per ottenere gli eventi dal server.
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

/** Recupera gli incontri dal server e carica il calendario con la risposta.
 *
 * Effettua una richiesta AJAX di tipo GET al file "calendar-helper.php" con il parametro "action" impostato su "get-matches".
 * Se la richiesta ha successo, la risposta viene passata alla funzione "loadCalendar" per caricare il calendario con gli incontri ottenuti.
 * In caso di errore, viene visualizzato un messaggio di errore nella console.
 *
 * @returns {void}
 */
function fetchMatches() {
    jQuery.ajax({
        url: 'http://localhost/calendar/calendar-helper.php?action=get-matches',
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

/** Funzione per ottenere gli eventi di un allenatore dal server.
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
        });
}

/** Funzione per ottenere gli eventi di un responsabile di una società.
 * 
 * La funzione effettua una richiesta AJAX di tipo GET al file "calendar-helper.php" con il parametro "action" impostato su "get-society-event" e il parametro "responsabile" impostato con il valore specificato.
 * Se la richiesta ha successo, i dati dell'evento vengono passati alla funzione "loadCalendar" per caricare il calendario.
 * In caso di errore, viene visualizzato un messaggio di errore nella console e è possibile gestire l'errore o mostrare un messaggio di errore all'utente.
 * 
 * @param {string} coach - L'allenatore per il quale ottenere gli eventi.
 */
function fetchSocietyEvents(responsabile) {
    // Effettua una richiesta AJAX per ottenere gli eventi dell'allenatore
    jQuery.get('http://localhost/calendar/calendar-helper.php?action=get-society-event', {
        responsabile: responsabile
    })
        .done(function (event) {
            // Recupero degli eventi avvenuto con successo
            loadCalendar(event);
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            // Recupero degli eventi fallito
            console.error('Impossibile recuperare gli eventi del responsabile:', textStatus, errorThrown);
        });
}

/** Salva un evento nel calendario dell'utente specificato.
 * Questa funzione invia una richiesta AJAX al server per salvare l'evento utilizzando i dati del modulo '#save-form'
 * e l'ID dell'utente specificato. La funzione esegue una verifica preliminare dei campi richiesti
 * attraverso la funzione 'validateForm()' prima di inviare la richiesta.
 * @param {string} user_id - L'ID dell'utente a cui associare l'evento nel calendario.
 * @return {void}
*/
function saveEvent(email) {
    var formData = $('#save-form').serialize();
    formData += '&author=' + email;

    // Verifica se i campi richiesti sono stati compilati prima di inviare la richiesta
    if (validateForm()) {
        $.ajax({
            url: 'http://localhost/calendar/calendar-helper.php?action=save-event',
            type: 'POST',
            data: formData,
            dataType: 'json'
        })
            .done(function (response) {
                if (response.status === 'success') {
                    $.ajax({ //getUserType che non funziona altrimenti
                        url: 'http://localhost/calendar/calendar-helper.php?action=get-user-type',
                        type: 'POST',
                        data: { email: email },
                        dataType: 'text',
                        success: function (userType) {
                            if (userType === '"manutentore"') { //Essendo la risposa in datatype text è "manutentore" da cercare
                                fetchEvents();
                            } else {
                                fetchCoachEvents(email);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log("Errore AJAX: " + error);
                        }
                    });

                    $.magnificPopup.close();
                    showSuccessAlert();
                }
            })
            .fail(function (xhr, status, error) {
                console.log(xhr.responseText);
            });
    } else {
        $('#error-message').show();
    }
}

/**
 * Effettua una chiamata AJAX per ottenere il tipo di utente associato all'indirizzo email specificato.
 * Il tipo di utente determinerà quale azione deve essere intrapresa successivamente.
 *
 * @param {string} email - L'indirizzo email dell'utente per il quale si desidera ottenere il tipo.
 *
 * @returns {void}
 */
function getUserType(email) {
    $.ajax({
        url: 'http://localhost/calendar/calendar-helper.php?action=get-user-type',
        type: 'POST',
        data: { email: email },
        dataType: 'text',
        success: function (userType) {
            if (userType === '"manutentore"') { //Essendo la risposa in datatype text è "manutentore" da cercare
                fetchEvents();
            } else {
                fetchCoachEvents(email);
            }
        },
        error: function (xhr, status, error) {
            console.log("Errore AJAX: " + error);
        }
    });
}



/** Funzione per visualizzare i dettagli di un obiettivo.
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

/** Funzione per eliminare un evento.
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
                showSuccessAlert();
            }
        },
        error: function (xhr, status, error) {
            console.log(xhr.responseText);
        }
    });
}

/** Mostra il modal per la modifica di un evento.
 */
function ShowForEditEvent() {

    var id = document.getElementById('event-id').value;
    // get evento dal server
    jQuery.get('http://localhost/calendar/calendar-helper.php?action=get-event', {
        id: id
    }, function (event) {
        // Effettua la richiesta per ottenere la nota
        jQuery.get('http://localhost/calendar/calendar-helper.php?action=get-event-info', {
            id: id
        }, function (info) {

            updateEventEditModal(event.title, info.id_squadra, event.start, event.end, event.startTime, event.endTime, info.autore_prenotazione, event.url, info.nota, event.groupID, event.id);

            // Apre il modal
            $.magnificPopup.open({
                items: {
                    src: "#modify-event-modal"
                },
                type: 'inline',
                enableEscapekey: false
            }, 0);
        });
    });
}

/** Aggiorna il modal "choose-cams" con le telecamere selezionate.
 * @param {string} cameras - Il JSON contenente le telecamere selezionate.
 * @param {string} id - L'ID delle telecamere.
 */
function Showcameras() {

    var id = document.getElementById('event-id').value;

    jQuery.ajax({
        url: 'http://localhost/calendar/calendar-helper.php?action=get-cams',
        type: 'GET',
        dataType: 'json',
        data: { id: id },
        success: function (response) {

            updateAddcameras(response, id);
            $.magnificPopup.open({
                items: {
                    src: "#choose-cams"
                },
                type: 'inline',
                enableEscapekey: false
            }, 0);
        },
        error: function (xhr, status, error) {
            console.log(xhr.responseText);
        }
    });
}

/** Aggiorna un evento tramite una richiesta AJAX.
 */
function editEvent() {
    // Verifica se i campi richiesti sono stati compilati prima di inviare la richiesta
    var eventId = document.getElementById('event-id').value;

    jQuery.ajax({
        url: 'http://localhost/calendar/calendar-helper.php?action=edit-event',
        type: 'POST',
        data: {
            id: eventId,
            groupId: $('#group-id-edit').val(),
            startDate: $('#start-date-edit').val(),
            endDate: $('#end-date-edit').val(),
            startTime: $('#start-time-edit').val(),
            endTime: $('#end-time-edit').val(),
            url: $('#url-edit').val(),
            society: $('#society-edit').val(),
            note: $('#description-edit').val()
        },
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                fetchEvents();
                $.magnificPopup.close();
                showSuccessAlert();
            }
        },
        error: function (xhr, status, error) {
            console.log(xhr.responseText);
        }
    });
    
}

/** Salva le telecamere selezionate tramite una chiamata AJAX.
 */
function saveCameras() {
    // Ottieni l'ID delle telecamere
    var eventId = document.getElementById('id-cams').innerText;

    // Ottieni le telecamere selezionate come un array
    var selectedCameras = $('input[name="camera[]"]:checked').map(function () {
        return $(this).val();
    }).get();

    // Effettua la chiamata AJAX per salvare le telecamere
    jQuery.ajax({
        url: 'http://localhost/calendar/calendar-helper.php?action=save-cams',
        type: 'POST',
        data: {
            id: eventId,
            cameras: selectedCameras
        },
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                $.magnificPopup.close();
                showSuccessAlert();
            }
        },
        error: function (xhr, status, error) {
            // In caso di errore, visualizza il messaggio di errore nella console
            console.log(xhr.responseText);
        }
    });
}

////////////////////////
//    Altri script    //
////////////////////////

/** Funzione per visualizzare il calendario con gli eventi forniti.
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
        views: {
            listMonth: {
                buttonText: 'Lista' // Modifica il testo del pulsante per la visualizzazione "listMonth"
            },
            dayGridMonth: {
                buttonText: 'Mese' // Modifica il testo del pulsante per la visualizzazione "dayGridMonth"
            },
            timeGridWeek: {
                buttonText: 'Settimana' // Modifica il testo del pulsante per la visualizzazione "timeGridWeek"
            },
            timeGridDay: {
                buttonText: 'Giorno' // Modifica il testo del pulsante per la visualizzazione "timeGridWeek"
            }
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
            // Verifica il tipo di utente
            getUserType().then(function (userType) {
                // Gestisci il click sulla data solo se l'usertype è "società" o "manutentore"
                if (userType === 'società' || userType === 'manutentore') {
                    // Gestisce il click su una data nel calendario
                    createCalendarEvent(info.dateStr); // Triggera modal nuovo evento
                    handleDateClick(info); // Aggiunge automaticamente la data cliccata nel form
                }
            }).catch(function (error) {
                console.error(error);
            });
        },
        eventClick: function (info) {
            if (window.location.href != "http://localhost/cameras/video_storage.php") {
                info.jsEvent.preventDefault();
                var currentEvent = info.event.id;
                showGoal(currentEvent); // Triggera modal per visualizzare informazioni evento
            }
        },

    });

    calendar.render(); // Rende visibile il calendario
}

/** Funzione per aggiornare i valori del modal dell'evento.
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

/** Funzione per aggiornare i campi del modal di modifica evento.
 *
 * @param {string} title - Il titolo dell'evento.
 * @param {string} society - La società relativa all'evento.
 * @param {string} startDate - La data di inizio dell'evento.
 * @param {string} endDate - La data di fine dell'evento.
 * @param {string} startTime - L'orario di inizio dell'evento.
 * @param {string} endTime - L'orario di fine dell'evento.
 * @param {string} coach - Il nome dell'allenatore relativo all'evento.
 * @param {string} url - L'URL dell'evento.
 * @param {string} note - Le note relative all'evento.
 * @param {string} groupID - L'ID del gruppo dell'evento.
 * @param {string} id - L'ID dell'evento.
 * @returns {void}
 */
function updateEventEditModal(title, society, startDate, endDate, startTime, endTime, coach, url, note, groupID, id) {

    // Formatta la data per essere presa in input correttamente
    var formattedStartDate = formatDateYYYYMMDD(startDate);
    var formattedEndDate = formatDateYYYYMMDD(endDate);


    // Formatta gli orari di inizio e fine (hh:mm)
    var formattedStartTime = formatTime(startTime);
    var formattedEndTime = formatTime(endTime);


    // Aggiorna la data dell'evento
    $('#start-date-edit').val(formattedStartDate);
    $('#end-date-edit').val(formattedEndDate);

    // Aggiorna gli orari di inizio e fine
    document.getElementById("start-time-edit").value = formattedStartTime;
    document.getElementById("end-time-edit").value = formattedEndTime;

    // Aggiorna le informazioni evento
    document.getElementById("nome-evento").innerText = title;
    document.getElementById("selected-option").value = society;

    // Aggiorna il titolo e l'URL dell'evento
    document.getElementById("url-edit").value = url;
    document.getElementById("description-edit").value = note;

    // Se presente mostra l'id altrimenti lascia il campo vuoto
    if (groupID) {
        document.getElementById("group-id-edit").value = groupID;
    }
    // Aggiorna l'id dell'evento
    document.getElementById("id-edit").value = id;
}

/** Aggiorna il modal "choose-cams" con le telecamere selezionate.
 * @param {Array} response - JSON con l'array delle telecamere selezionate
 * @param {string} id - L'ID delle telecamere.
 */
function updateAddcameras(response, id) {
    // Imposta l'ID delle telecamere
    document.getElementById("id-cams").innerText = id;

    var selectedCameras = response.map(function (item) {
        return item.telecamera.toString(); // Convert to string to ensure correct comparison
    });

    // Seleziona le checkbox corrispondenti alle telecamere preselezionate
    var checkboxes = document.querySelectorAll('input[type="checkbox"][name="camera[]"]');
    checkboxes.forEach(function (checkbox) {
        if (selectedCameras.includes(checkbox.value)) {
            checkbox.checked = true;
        } else {
            checkbox.checked = false; // Ensure unchecked if not found in the response
        }
    });
}


/** Funzione per validare il modulo eventi.
 * 
 * La funzione controlla che tutti i campi fondamentali siano stati compilati.
 * Restituisce true se tutti i campi richiesti hanno un valore non vuoto, altrimenti restituisce false.
 * 
 * @returns {boolean} True se il modulo è valido, altrimenti false.
 */
function validateForm() {
    var requiredFields = ['society', 'start-date'];

    for (var i = 0; i < requiredFields.length; i++) {
        var field = document.querySelector('[name="' + requiredFields[i] + '"]');
        if (field.value === '') {
            return false;
        }
    }

    return true;
}

/** Toggle la visibilità più opzioni in base alla checkbox.
 * @param {HTMLElement} checkbox - L'elemento della checkbox che attiva la modifica.
 * 
 */
function toggleMoreOptions(checkbox) {
    var moreOptions = document.getElementById("moreOptions");
    moreOptions.style.display = checkbox.checked ? "block" : "none";
}

/** Modifica la visibilità della sezione di ripetizione settimanale in base allo stato della checkbox.
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

/** Modifica la visibilità delle opzioni della telecamera in base allo stato della casella di controllo.
 * @param {HTMLElement} checkbox - L'elemento della casella di controllo che attiva la scelta delle telecamere.
*/
function toggleCameraOptions(checkbox) {
    var cameraOptions = document.getElementById("camera-options");
    if (checkbox.checked) {
        cameraOptions.style.display = "block";
    } else {
        cameraOptions.style.display = "none";
    }
}

function confirmSaveEvent(email) {
    // Mostra il popup di conferma
    if (confirm("Sei sicuro di voler salvare l'evento?")) {
        // Se l'utente ha cliccato su "OK", esegui la funzione saveEvent()
        saveEvent(email);
    }
}

function confirmdeleteEvent() {
    // Mostra il popup di conferma
    if (confirm("Sei sicuro di voler eliminare l'evento?")) {
        deleteEvent();
    }
}

function showSuccessAlert() {
    // Crea l'elemento alert di Bootstrap per il messaggio di successo
    var alertElement = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        'Evento modificato con successo !' +
        '<span aria-hidden="true">&times;</span></button></div>');
  
    // Inserisci l'alert all'interno del div con classe "container" e id "alert"
    $('.container #alert').prepend(alertElement);
  
    // Nascondi l'alert dopo qualche secondo (es. 5 secondi)
    setTimeout(function () {
      alertElement.alert('close');
    }, 5000);
  }  

/** Funzione per formattare una data nel formato "DD mese YYYY".
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

/** Formatta una data nel formato "YYYY-MM-DD".
 * 
 * @param {string} dateString - La stringa rappresentante una data.
 * @returns {string} - La data formattata nel formato "YYYY-MM-DD".
*/
function formatDateYYYYMMDD(dateString) {
    var date = new Date(dateString);
    var year = date.getFullYear();
    var month = ('0' + (date.getMonth() + 1)).slice(-2);
    var day = ('0' + date.getDate()).slice(-2);

    return year + '-' + month + '-' + day;
}

/** Funzione per formattare un'orario nel formato "ore minuti".
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

/** Funzione per creare un nuovo evento nel calendario.
 * 
 * La funzione apre una finestra modale utilizzando il plugin Magnific Popup, visualizzando il contenuto con l'ID "add-event-modal".
 * 
 * @param {string} currentDate - La data corrente da utilizzare come valore predefinito per la data di inizio del nuovo obiettivo.
 */
function createCalendarEvent(currentDate) {
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

// ...

/** Funzione per gestire il click su una data nel calendario.
 * 
 * La funzione viene chiamata quando un utente abilitato fa clic su una data nel calendario.
 * Prende come parametro l'oggetto `date` che contiene informazioni sulla data clickata.
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

/**
 * Ottiene il tipo di utente dell'utente corrente tramite una chiamata AJAX a un'API.
 *
 * @return {Promise} Una Promise che restituisce il tipo di utente dell'utente corrente.
 *                   Se la chiamata AJAX ha successo, la Promise viene risolta con il tipo di utente.
 *                   Se si verifica un errore durante la chiamata AJAX, la Promise viene rifiutata con l'errore.
 */
function getUserType() {
    return new Promise(function (resolve, reject) {
        // Effettua una chiamata AJAX per ottenere il tipo di utente dell'utente corrente
        jQuery.ajax({
            url: 'http://localhost/calendar/calendar-helper.php?action=get-user-type',
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                // La chiamata AJAX ha avuto successo, risolvi la Promise con il tipo di utente
                resolve(response);
            },
            error: function (xhr, status, error) {
                // Si è verificato un errore durante la chiamata AJAX, rifiuta la Promise con l'errore
                console.log(xhr.responseText);
                reject(error);
            }
        });
    });
}

