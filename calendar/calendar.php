<!-- Javascripts per gestire il calendario -->
<script src="../js/calendar/calendar-scripts.js"></script>

<!-- PHP session init -->
<?php
session_start();

require('../authentication/db_connection.php');
include('../modals/calendar-header.php');
include('../authentication/auth-helper.php');

$user = array();

if (!isset($_SESSION['userID'])) {
    header("Location: ../authentication/login.php");
    exit();
} else {
    $user = get_user_info($con, $_SESSION['userID']);
}

if ($user['userType'] == "allenatore") {
    // Chiamata alla funzione JavaScript per il calendario degli allenatori
    echo '<script>';
    echo 'fetchCoachEvents("' . $user['email'] . '");';
    echo '</script>';
} elseif ($user['userType'] == "manutentore") {
    // Chiamata alla funzione JavaScript per il calendario dei manutentori
    echo '<script>';
    echo 'fetchEvents();';
    echo '</script>';
} else {
    // Chiamata alla funzione JavaScript per il calendario generale
    echo '<script>';
    echo 'fetchMatches();';
    echo '</script>';
}
?>


<!-- Calendario "FullCalendar" caricato da JavaScript -->
<div class="container">
    <div id="calendar"></div>
</div>

<!-- Modale aggiunta nuovo evento -->
<div id="add-event-modal" class="white-popup-block mfp-hide">
    <p style="height: 30px; background: darkblue; width: 100%;"></p>
    <h2>Nuovo Evento</h2>
    <div style="min-height: 250px;">
        <form id="save-form">
            <input type="hidden" name="id" />
            Società: <input type="text" name="society" placeholder="Società*" required /><br>
            Evento:
            <select name="event_type">
                <option value="training">Allenamento</option>
                <option value="match">Partita</option>
            </select><br>
            Data inizio: <input id="start-date" type="date" name="start-date" placeholder="Data inizio" autocomplete="off" value="<?= date('Y-m-d') ?>" required /><br>
            Data fine: <input id="end-date" type="date" name="end-date" placeholder="Data fine" autocomplete="off" value="<?= date('Y-m-d') ?>" required /><br>
            Ora inizio: <input type="time" name="startTime" placeholder="Ora inizio" /><br>
            Ora fine: <input type="time" name="endTime" placeholder="Ora fine" /><br>
            Allenatore: <input type="text" name="coach" placeholder="Allenatore*" required /><br>
            Sport:
            <select name="sport">
                <option value="calcio">Calcio a 5</option>
                <option value="pallavolo">Pallavolo</option>
                <option value="basket">Basket</option>
            </select><br>
            <!-- Ripetizione settimanale -->
            Ripetizione settimanale:<br>
            <input type="checkbox" name="repeatWeekly" onchange="toggleWeeklyRepeat(this)"> Si ripete ogni:<br>
            <div id="weeklyRepeat" style="display: none;">
                Data di inizio ripetizione: <input id="startRecur" type="date" name="startRecur" placeholder="Data di inizio ripetizione" autocomplete="off"><br>
                Data di fine ripetizione: <input id="endRecur" type="date" name="endRecur" placeholder="Data di fine ripetizione" autocomplete="off"><br>
                Giorni della settimana:<br>
                <input type="checkbox" name="daysOfWeek[]" value="1"> Lunedì<br>
                <input type="checkbox" name="daysOfWeek[]" value="2"> Martedì<br>
                <input type="checkbox" name="daysOfWeek[]" value="3"> Mercoledì<br>
                <input type="checkbox" name="daysOfWeek[]" value="4"> Giovedì<br>
                <input type="checkbox" name="daysOfWeek[]" value="5"> Venerdì<br>
                <input type="checkbox" name="daysOfWeek[]" value="6"> Sabato<br>
                <input type="checkbox" name="daysOfWeek[]" value="0"> Domenica<br>
            </div>

            <!-- Altre opzioni -->
            <label><input type="checkbox" name="showMoreOptions" onchange="toggleMoreOptions(this)"> Altre opzioni</label><br>
            <div id="moreOptions" style="display: none;">
                Note:<br>
                <textarea cols="55" rows="5" name="description" placeholder="Note"></textarea><br>
                Url: <input type="text" name="url" placeholder="Url"><br>
                GroupId: <input type="value" name="groupId" placeholder="GroupId"><br>
                Tutto il giorno: <input type="checkbox" name="allDay" placeholder="allday"><br>
            </div>

            <button type="button" id="save-event" onclick="saveEvent()">Salva</button>
        </form>
    </div>
    <div id="error-message" style="color: red; display: none;">Si prega di compilare tutti i campi obbligatori.</div>
</div>

<!-- Modale per visualizzare le informazioni dell'evento-->
<div id="show-event-modal" class="white-popup-block mfp-hide">
    <p style="height: 30px; background: orangered; width: 100%;"></p>
    <div style="display: flex;">
        <h2 id="event-date" style="flex: 1;">Giorno Mese Anno</h2>
        <p id="event-time-init" style="margin-left: 10px; font-size: 24px;">Orario Inizio</p>
        <p id="event-time-spacer" style="margin-left: 10px; font-size: 24px;">-</p>
        <p id="event-time-end" style="margin-left: 10px; font-size: 24px;">Orario Fine</p>
    </div>
    <div style="min-height: 250px;">
        <h3 id="event-name">Titolo</h3>
        <p id="event-note">Note evento</p>
        <p id="event-id" style="display: none;"> id </p>
    </div>

    <button id="delete-button" class="btn btn-danger" onclick="deleteEvent()">Elimina</button>
</div>


<?php

include('../modals/footer.php');

?>