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
    var calendarEl = document.getElementById('calendar-video');

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
        

    });

    calendar.render(); // Rende visibile il calendario

}
