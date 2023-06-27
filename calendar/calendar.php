<!-- Javascripts per gestire il calendario -->
<script src="../js/calendar/calendar-scripts.js"></script>

<!-- PHP session init -->
<?php

include('../modals/calendar-header.php');
include_once("../modals/navbar.php");
include_once('../authentication/auth-helper.php');
include("./calendar-helper.php");

if (!isset($_SESSION['userID'])) {
    header("Location: ../authentication/login.php");
    exit();
}

if ($user['userType'] == "allenatore") {
    // Chiamata alla funzione JavaScript per il calendario degli allenatori
    echo '<script>';
    echo 'fetchCoachEvents("' . $user['email'] . '");';
    echo '</script>';
    $delete = true;
    $modify = false;
    $add = true;
} elseif ($user['userType'] == "manutentore") {
    // Chiamata alla funzione JavaScript per il calendario dei manutentori
    echo '<script>';
    echo 'fetchEvents();';
    echo '</script>';
    $delete = true;
    $modify = true;
    $add = true;
} else {
    // Chiamata alla funzione JavaScript per il calendario generale
    echo '<script>';
    echo 'fetchMatches();';
    echo '</script>';
    $delete = false;
    $modify = false;
    $add = false;
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
            Società:
            <select name="society" required>
                <option value="" disabled selected>Scegli una società</option>
                <?php echo getSociety(); ?>
            </select>
            Evento:
            <select name="event_type">
                <option value="training">Allenamento</option>
                <option value="match">Partita</option>
            </select><br>
            Data inizio: <input id="start-date" type="date" name="start-date" placeholder="Data inizio" autocomplete="off" value="<?= date('Y-m-d') ?>" required /><br>
            Data fine: <input id="end-date" type="date" name="end-date" placeholder="Data fine" autocomplete="off" value="<?= date('Y-m-d') ?>" required /><br>
            Ora inizio: <input type="time" name="startTime" placeholder="Ora inizio" /><br>
            Ora fine: <input type="time" name="endTime" placeholder="Ora fine" /><br>

            <!-- Scelta camere -->
            <div>
                <input type="checkbox" id="camera-checkbox" name="camera-checkbox" onchange="toggleCameraOptions(this)" />
                <label for="camera-checkbox">Seleziona telecamere</label>
            </div>
            <div id="camera-options" style="display: none;">
                <label>
                    <input type="checkbox" name="camera[]" value="1">
                    Camera 1
                </label>
                <label>
                    <input type="checkbox" name="camera[]" value="2">
                    Camera 2
                </label>
                <label>
                    <input type="checkbox" name="camera[]" value="3">
                    Camera 3
                </label>
                <label>
                    <input type="checkbox" name="camera[]" value="4">
                    Camera 4
                </label>
            </div>

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

            <button type="button" id="save-event" onclick="saveEvent(<?php echo $user_id; ?>)">Salva</button>
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

    <?php


    if ($delete) {
        echo ('<button id="delete-button" class="btn btn-danger" onclick="deleteEvent()">Elimina</button>');
    }
    if ($modify) {
        echo ('<button id="edit-button" class="btn btn-primary" onclick="ShowForEditEvent()">Modifica</button>');
    }
    if ($add) {
        echo ('<button id="add-button" class="btn btn-primary" onclick="Showcameras()">Imposta Camere</button>');
    }
    ?>
</div>

<!-- Modale modifica evento -->
<div id="modify-event-modal" class="white-popup-block mfp-hide">
    <p style="height: 30px; background: darkblue; width: 100%;"></p>
    <h2 id=nome-evento>Nome Evento</h2>
    <div style="min-height: 250px;">
        <form id="edit-form">
            <input type="text" id="id-edit" style="display: none;"></p>
            <label for="society-edit">Società:</label>
            <input type="text" name="society-edit" id="society-edit" placeholder="Società*" required /><br>
            <label for="start-date-edit">Data inizio:</label>
            <input id="start-date-edit" type="date" name="start-date-edit" placeholder="Data inizio" autocomplete="off" required /><br>
            <label for="end-date-edit">Data fine:</label>
            <input id="end-date-edit" type="date" name="end-date-edit" placeholder="Data fine" autocomplete="off" required /><br>
            <label for="start-time-edit">Ora inizio:</label>
            <input type="time" name="start-time-edit" id="start-time-edit" placeholder="Ora inizio" /><br>
            <label for="end-time-edit">Ora fine:</label>
            <input type="time" name="end-time-edit" id="end-time-edit" placeholder="Ora fine" /><br>
            <label for="coach-edit">Allenatore:</label>
            <input type="text" name="coach-edit" id="coach-edit" placeholder="Mail allenatore*" required /><br>
            <label for="description-edit">Note:</label><br>
            <textarea cols="55" rows="5" name="description-edit" id="description-edit" placeholder="Note"></textarea><br>
            <label for="url-edit">Url:</label>
            <input type="text" name="url-edit" id="url-edit" placeholder="Url"><br>
            <label for="group-id-edit">GroupId:</label>
            <input type="text" name="group-id-edit" id="group-id-edit" placeholder="GroupId"><br>
            <button type="button" id="save-event" onclick="editEvent()">Salva</button>
        </form>
    </div>
    <div id="error-message" style="color: red; display: none;">Si prega di compilare tutti i campi obbligatori.</div>
</div>

<!-- Modal scelta camere  -->
<div id="choose-cams" class="white-popup-block mfp-hide">
    <p style="height: 30px; background: orangered; width: 100%;"></p>
    <div class="modal-content">
        <p type="text" id="id-cams" style="display: none;"> id </p>
        <h2>Seleziona le telecamere da attivare:</h2>
        <form id="cameraForm">
            <label>
                <input type="checkbox" name="camera[]" value="1">
                Camera 1
            </label>
            <label>
                <input type="checkbox" name="camera[]" value="2">
                Camera 2
            </label>
            <label>
                <input type="checkbox" name="camera[]" value="3">
                Camera 3
            </label>
            <label>
                <input type="checkbox" name="camera[]" value="4">
                Camera 4
            </label>
            <button type="submit" onclick="saveCameras()">Attiva</button>
        </form>
    </div>
</div>


</div>
</div>

<?php

include('../modals/footer.php');

?>