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
    function updateEventModal(date, startTime, endTime, title, url) {
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
            $.ajax({
                url: 'calendar-helper.php?action=save-event',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status == 'success') {
                        fetchEvents();
                        $.magnificPopup.close();
                    }
                },
                error: function(xhr, status, error) {
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


    var calendar = null;


    // Funzione per ottenere gli eventi dal server
    function fetchEvents() {
        jQuery.ajax({
            url: 'calendar-helper.php?action=get-events',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                loadCalendar(response);
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    }

    // Inizializza il calendario quando il documento è pronto
    $(document).ready(function() {
        fetchEvents();
    });

    function loadCalendar(data) {
        var calendarEl = document.getElementById('calendar');
        var calendar;

        if (calendar) {
            calendar.destroy();
        }

        calendar = new FullCalendar.Calendar(calendarEl, {
            events: data,
            plugins: ['interaction', 'dayGrid', 'timeGrid', 'list'],
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            navLinks: true,
            dateClick: function(info) {
                createGoal(info.dateStr);
                handleDateClick(info);
            },

            eventClick: function(info) {
                var currentEvent = info.event.id;
                showGoal(currentEvent);
            },
            dayMaxEventRows: true, // for all non-TimeGrid views
            views: {
                timeGrid: {
                    dayMaxEventRows: 6 // adjust to 6 only for timeGridWeek/timeGridDay
                }
            }
        });

        calendar.render();

    }

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
        // get event from server
        $.get('calendar-helper.php?action=get-event', {
            id: id
        }, function(event) {

            updateEventModal(event.start, event.startTime, event.endTime, event.title, event.url)

            // then we open the modal
            $.magnificPopup.open({
                items: {
                    src: "#show-event-modal"
                },
                type: 'inline',
                enableEscapekey: false
            }, 0);
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