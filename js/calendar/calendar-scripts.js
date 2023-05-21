// Funzione per far apparire le funzionalità avanzate
function toggleMoreOptions(checkbox) {
    var moreOptions = document.getElementById("moreOptions");
    moreOptions.style.display = checkbox.checked ? "block" : "none";
}

// Funzione per far apparire i giorni della settimana
function toggleWeeklyRepeat(checkbox) {
    var weeklyRepeatSection = document.getElementById("weeklyRepeat");
    if (checkbox.checked) {
        weeklyRepeatSection.style.display = "block";
    } else {
        weeklyRepeatSection.style.display = "none";
    }
}

// Funzione per modificare i valori del event-modal
function updateEventModal(date, startTime, endTime, title, url, id) {
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
    document.getElementById("event-url").innerText = url;

    // Aggiorna l'id dell'evento
    document.getElementById("event-id").value = id;
}

// Funzione formattare DD-MM-YYYY
function formatDate(date) {
    var eventDate = new Date(date);
    var day = eventDate.getDate();
    var month = eventDate.toLocaleString('default', {
        month: 'long'
    });
    var year = eventDate.getFullYear();

    return day + ' ' + month + ' ' + year;
}

// Funzione formattare HH:MM
function formatTime(time) {
    var eventTime = new Date('1970-01-01T' + time);
    var hours = eventTime.getHours().toString().padStart(2, '0');
    var minutes = eventTime.getMinutes().toString().padStart(2, '0');

    return hours + ':' + minutes;
}


function saveEvent() {
    var formData = $('#save-form').serialize();

    // Verifica se i campi richiesti sono stati compilati prima di inviare la richiesta
    if (validateForm()) {
        jQuery.ajax({
            url: 'calendar-helper.php?action=save-event',
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

// Funzione che controlla che tutti i campi fondamentali siano stati compilati
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


// Funzione per ottenere gli eventi dal server
function fetchEvents() {
    jQuery.ajax({
        url: 'calendar-helper.php?action=get-events',
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

// Inizializza il calendario quando il documento è pronto
$(document).ready(function () {
    fetchEvents();
});

//creazione Oggetto globale calendar
var calendar = null;

//Carica il calendario e lo fa visualizzare
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
            right: 'dayGridMonth,timeGridWeek,timeGridDay' // Elementi del header a destra
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
            var currentEvent = info.event.id;
            showGoal(currentEvent); // Triggera modal per visualizzare informazioni evento
        },

    });

    calendar.render(); // Rende visibile il calendario

}


// Apre modal nuovo evento
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


function showGoal(id) {
    // get evento dal server
    jQuery.get('calendar-helper.php?action=get-event', {
        id: id
    }, function (event) {

        updateEventModal(event.start, event.startTime, event.endTime, event.title, event.url, event.id)

        // Apre il modal
        $.magnificPopup.open({
            items: {
                src: "#show-event-modal"
            },
            type: 'inline',
            enableEscapekey: false
        }, 0);
    });
}


function deleteEvent() {
    var eventId = document.getElementById('event-id').value;

    // Remove the event from the calendar
    calendar.getEventById(eventId).remove();

    jQuery.ajax({
        url: 'calendar-helper.php?action=delete-event',
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

// Funzione per gestire il click su una data nel calendario
function handleDateClick(date) {
    var clickedDate = date.dateStr;
    $('#start-date').val(clickedDate);
    $('#end-date').val(clickedDate);

    // Aggiorna il form con la data selezionata
    $('#save-form input[name="start-date"]').val(clickedDate);
    $('#save-form input[name="end-date"]').val(clickedDate);
}