<!-- Javascripts per gestire il calendario -->
<script src="../js/calendar/calendar-scripts.js"></script>

<!-- PHP session init -->
<?php

include('../modals/calendar-header.php');
include_once("../modals/navbar.php");
include_once('../authentication/auth-helper.php');
include("./calendar-helper.php");

if (!isset($_COOKIE['email'])) {
    header("Location: ../authentication/login.php");
    exit();
}

$userType = $user['userType']; // Ottenere il tipo di utente dalla variabile $user['userType']

// Impostazioni predefinite
$delete = false;
$modify = false;
$add = false;

// Mappa il tipo di utente alle azioni e alla chiamata JavaScript
$actions = array(
    'allenatore' => array(
        'fetchFunction' => 'fetchCoachEvents',
        'args' => '"' . $user['email'] . '"',
        'add' => true,
        'delete'=> true,
    ),
    'società' => array(
        'fetchFunction' => 'fetchSocietyEvents',
        'args' => '"' . $user['email'] . '"',
        'add' => true,
        'delete'=> true,
    ),
    'manutentore' => array(
        'fetchFunction' => 'fetchEvents',
        'args' => '',
        'modify' => true,
        'add' => true,
        'delete'=> true,
    ),
    'tifoso' => array(
        'args' => '',
        'fetchFunction' => 'fetchMatches',
    ),
    // Tipo di utente non gestito
    'altro' => array(
        'fetchFunction' => 'fetchMatches',
    ),
);

if (isset($actions[$userType])) {
    $action = $actions[$userType];
    echo '<script>';
    echo $action['fetchFunction'] . '(' . $action['args'] . ');';
    echo '</script>';

    $delete = isset($action['delete']) ? $action['delete'] : $delete;
    $modify = isset($action['modify']) ? $action['modify'] : $modify;
    $add = isset($action['add']) ? $action['add'] : $add;
}
?>

<style>
    /* Style for the time inputs */
    input[type="time"] {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
    }

    /* Style for the input fields */
    input[type="date"] {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
    }

    .day-icon {
        width: 20px;
        height: 20px;
        margin-right: 5px;
    }

    /* Style for the circle icon */
    .circle-icon {
        width: 14px;
        height: 14px;
        background: url('https://api.iconify.design/cil:circle.svg') no-repeat center center;
        background-size: cover;
        display: inline-block;
        position: relative;
        top: 5px;
        /* Adjust the vertical position as needed */
        left: 5px;
        /* Adjust the horizontal position as needed */
    }

    /* Hide the actual checkboxes */
    .day-checkbox input[type="checkbox"] {
        display: none;
    }

    /* Style for the checked icons */
    .day-checkbox input[type="checkbox"]:checked+.day-icon {
        filter: grayscale(0%);
    }
</style>

<!-- Calendario "FullCalendar" caricato da JavaScript -->
<div class="container">
    <div id="alert"></div>
    <div id="calendar"></div>
</div>

<!-- Modale aggiunta nuovo evento -->
<div id="add-event-modal" class="white-popup-block mfp-hide">

    <p style="height: 30px; background: #8FB3FF; width: 100%;"></p>
    <h2>Nuovo Evento</h2>
    <div style="min-height: 250px;">
        <form id="save-form">

            <script>
                
                // TEST:    ottengo il JSON di risposta da un indirizzo remoto
                //          la prova in locale non funziona
                getEndpointStatus();
                
            </script>

            <input type="hidden" name="id" />
            Societa:
            <select name="society" required>
                <option value="" disabled selected>Scegli una societa</option>
                <?php echo getSocieties(); ?>
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

            <!-- Per comodità, questo dovrà rimanere nascosto-->
            <div id="camera-options" style="display: none;">
                <label> <input type="checkbox" name="camera[]" value="1"> Camera 1 </label>
                <label> <input type="checkbox" name="camera[]" value="2"> Camera 2 </label>
                <label> <input type="checkbox" name="camera[]" value="3"> Camera 3 </label>
                <label> <input type="checkbox" name="camera[]" value="4"> Camera 4 </label>
                <label> <input type="checkbox" name="camera[]" value="5"> Camera 5 </label>
                <label> <input type="checkbox" name="camera[]" value="6"> Camera 6 </label>
                <label> <input type="checkbox" name="camera[]" value="7"> Camera 7 </label>
                <label> <input type="checkbox" name="camera[]" value="8"> Camera 8 </label>
                <label> <input type="checkbox" name="camera[]" value="9"> Camera 9 </label>
                <label> <input type="checkbox" name="camera[]" value="10"> Camera 10 </label>
                <label> <input type="checkbox" name="camera[]" value="11"> Camera 11 </label>
                <label> <input type="checkbox" name="camera[]" value="12"> Camera 12 </label>
                <label> <input type="checkbox" name="camera[]" value="13"> Camera 13 </label>
            </div>

            <!-- Mappa per la scelta delle telecamere: mostra i cerchi colorati -->
            <div id="map_container" style="position: relative; display: none;">
                <img src="/assets/images/basketball-court-floor.png" usemap="#camera_map" width="100%"/>
                <div class="circle" style="top: 23px; left: 25px; pointer-events: none;"></div>     <!-- 1 -->
                <div class="circle" style="top: 167px; left: 25px; pointer-events: none;"></div>    <!-- 2 -->
                <div class="circle" style="top: 316px; left: 25px; pointer-events: none;"></div>    <!-- 3 -->

                <div class="circle" style="top: 23px; left: 172px; pointer-events: none;"></div>     <!-- 4 -->
                <div class="circle" style="top: 169px; left: 172px; pointer-events: none;"></div>    <!-- 5 -->
                <div class="circle" style="top: 314px; left: 172px; pointer-events: none;"></div>    <!-- 6 -->

                <div class="circle" style="top: 169px; left: 271px; pointer-events: none;"></div>     <!-- 7 -->

                <div class="circle" style="top: 23px; left: 369px; pointer-events: none;"></div>    <!-- 8 -->
                <div class="circle" style="top: 169px; left: 369px; pointer-events: none;"></div>    <!-- 9 -->
                <div class="circle" style="top: 314px; left: 369px; pointer-events: none;"></div>     <!-- 10 -->

                <div class="circle" style="top: 23px; left: 515px; pointer-events: none;"></div>    <!-- 11 -->
                <div class="circle" style="top: 169px; left: 515px; pointer-events: none;"></div>    <!-- 12 -->
                <div class="circle" style="top: 314px; left: 515px; pointer-events: none;"></div>    <!-- 13 -->
            </div>

            <map name="camera_map">
                <area class="cam_1" shape="circle" coords="54,45,30" href="">       <!-- 1 -->
                <area class="cam_2" shape="circle" coords="54,188,30" href="">      <!-- 2 -->
                <area class="cam_3" shape="circle" coords="54,338,30" href="">      <!-- 3 -->

                <area class="cam_4" shape="circle" coords="201,45,30" href="">      <!-- 4 -->
                <area class="cam_5" shape="circle" coords="201,189,30" href="">     <!-- 5 -->
                <area class="cam_6" shape="circle" coords="201,338,30" href="">     <!-- 6 -->

                <area class="cam_7" shape="circle" coords="300,191,30" href="">      <!-- 7 -->

                <area class="cam_8" shape="circle" coords="398,45,30" href="">     <!-- 8 -->
                <area class="cam_9" shape="circle" coords="398,189,30" href="">     <!-- 9 -->
                <area class="cam_10" shape="circle" coords="398,338,30" href="">     <!-- 10 -->

                <area class="cam_11" shape="circle" coords="544,45,30" href="">    <!-- 11 -->
                <area class="cam_12" shape="circle" coords="544,189,30" href="">    <!-- 12 -->
                <area class="cam_13" shape="circle" coords="544,338,30" href="">    <!-- 13 -->
            </map>

            <script>
                $(".cam_1").on("click", function(e){
                    e.preventDefault();
                    var checkboxes = document.querySelectorAll('input[type=checkbox][value="1"]');
                    var circles = document.getElementsByClassName('circle');
                    checkboxes[0].checked = !checkboxes[0].checked;
                    if (circles[0].style.backgroundColor == "") {circles[0].style.backgroundColor = "green";} else {circles[0].style.backgroundColor = "";}
                });
                $(".cam_2").on("click", function(e){
                    e.preventDefault();
                    var checkboxes = document.querySelectorAll('input[type=checkbox][value="2"]');
                    var circles = document.getElementsByClassName('circle');
                    checkboxes[0].checked = !checkboxes[0].checked;
                    if (circles[1].style.backgroundColor == "") {circles[1].style.backgroundColor = "green";} else {circles[1].style.backgroundColor = "";}
                });
                $(".cam_3").on("click", function(e){
                    e.preventDefault();
                    var checkboxes = document.querySelectorAll('input[type=checkbox][value="3"]');
                    var circles = document.getElementsByClassName('circle');
                    checkboxes[0].checked = !checkboxes[0].checked;
                    if (circles[2].style.backgroundColor == "") {circles[2].style.backgroundColor = "green";} else {circles[2].style.backgroundColor = "";}
                });
                $(".cam_4").on("click", function(e){
                    e.preventDefault();
                    var checkboxes = document.querySelectorAll('input[type=checkbox][value="4"]');
                    var circles = document.getElementsByClassName('circle');
                    checkboxes[0].checked = !checkboxes[0].checked;
                    if (circles[3].style.backgroundColor == "") {circles[3].style.backgroundColor = "green";} else {circles[3].style.backgroundColor = "";}
                });
                $(".cam_5").on("click", function(e){
                    e.preventDefault();
                    var checkboxes = document.querySelectorAll('input[type=checkbox][value="5"]');
                    var circles = document.getElementsByClassName('circle');
                    checkboxes[0].checked = !checkboxes[0].checked;
                    if (circles[4].style.backgroundColor == "") {circles[4].style.backgroundColor = "green";} else {circles[4].style.backgroundColor = "";}
                });
                $(".cam_6").on("click", function(e){
                    e.preventDefault();
                    var checkboxes = document.querySelectorAll('input[type=checkbox][value="6"]');
                    var circles = document.getElementsByClassName('circle');
                    checkboxes[0].checked = !checkboxes[0].checked;
                    if (circles[5].style.backgroundColor == "") {circles[5].style.backgroundColor = "green";} else {circles[5].style.backgroundColor = "";}
                });
                $(".cam_7").on("click", function(e){
                    e.preventDefault();
                    var checkboxes = document.querySelectorAll('input[type=checkbox][value="7"]');
                    var circles = document.getElementsByClassName('circle');
                    checkboxes[0].checked = !checkboxes[0].checked;
                    if (circles[6].style.backgroundColor == "") {circles[6].style.backgroundColor = "green";} else {circles[6].style.backgroundColor = "";}
                });
                $(".cam_8").on("click", function(e){
                    e.preventDefault();
                    var checkboxes = document.querySelectorAll('input[type=checkbox][value="8"]');
                    var circles = document.getElementsByClassName('circle');
                    checkboxes[0].checked = !checkboxes[0].checked;
                    if (circles[7].style.backgroundColor == "") {circles[7].style.backgroundColor = "green";} else {circles[7].style.backgroundColor = "";}
                });
                $(".cam_9").on("click", function(e){
                    e.preventDefault();
                    var checkboxes = document.querySelectorAll('input[type=checkbox][value="9"]');
                    var circles = document.getElementsByClassName('circle');
                    checkboxes[0].checked = !checkboxes[0].checked;
                    if (circles[8].style.backgroundColor == "") {circles[8].style.backgroundColor = "green";} else {circles[8].style.backgroundColor = "";}
                });
                $(".cam_10").on("click", function(e){
                    e.preventDefault();
                    var checkboxes = document.querySelectorAll('input[type=checkbox][value="10"]');
                    var circles = document.getElementsByClassName('circle');
                    checkboxes[0].checked = !checkboxes[0].checked;
                    if (circles[9].style.backgroundColor == "") {circles[9].style.backgroundColor = "green";} else {circles[9].style.backgroundColor = "";}
                });
                $(".cam_11").on("click", function(e){
                    e.preventDefault();
                    var checkboxes = document.querySelectorAll('input[type=checkbox][value="11"]');
                    var circles = document.getElementsByClassName('circle');
                    checkboxes[0].checked = !checkboxes[0].checked;
                    if (circles[10].style.backgroundColor == "") {circles[10].style.backgroundColor = "green";} else {circles[10].style.backgroundColor = "";}
                });
                $(".cam_12").on("click", function(e){
                    e.preventDefault();
                    var checkboxes = document.querySelectorAll('input[type=checkbox][value="12"]');
                    var circles = document.getElementsByClassName('circle');
                    checkboxes[0].checked = !checkboxes[0].checked;
                    if (circles[11].style.backgroundColor == "") {circles[11].style.backgroundColor = "green";} else {circles[11].style.backgroundColor = "";}
                });
                $(".cam_13").on("click", function(e){
                    e.preventDefault();
                    var checkboxes = document.querySelectorAll('input[type=checkbox][value="13"]');
                    var circles = document.getElementsByClassName('circle');
                    checkboxes[0].checked = !checkboxes[0].checked;
                    if (circles[12].style.backgroundColor == "") {circles[12].style.backgroundColor = "green";} else {circles[12].style.backgroundColor = "";}
                });
            </script>

            <!-- Scelta per i dati di posizionamento -->
            <div>
                <input type="checkbox" id="datapos-checkbox" name="datapos-checkbox"/>
                <label for="datapos-checkbox">Registra i dati di posizionamento</label>
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
                Url: <input type="text" name="url" placeholder="Url" value="/editing_video/editing/editing_video.php?video=storage_video/volley_test_2.mp4"><br>
                GroupId: <input type="value" name="groupId" placeholder="GroupId"><br>
                Tutto il giorno: <input type="checkbox" name="allDay" placeholder="allday"><br>
            </div>

            <button type="button" id="save-event" onclick="confirmSaveEvent('<?php echo $_COOKIE['email']; ?>')">Salva</button>
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
        echo ('<button id="delete-button" class="btn btn-danger" onclick="confirmdeleteEvent()">Elimina</button>');
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
    <p style="height: 30px; background: #8FB3FF; width: 100%;"></p>
    <h2 id=nome-evento>Nome Evento</h2>
    <div style="min-height: 250px;">
        <form id="edit-form">
            <input type="text" id="id-edit" style="display: none;"></p>
            <select name="society-edit" required>
                <option value="" id="selected-option" selected>Scegli una società</option>
                <?php echo getSocieties(); ?>
            </select>
            <label for="start-date-edit">Data inizio:</label>
            <input id="start-date-edit" type="date" name="start-date-edit" placeholder="Data inizio" autocomplete="off" required /><br>
            <label for="end-date-edit">Data fine:</label>
            <input id="end-date-edit" type="date" name="end-date-edit" placeholder="Data fine" autocomplete="off" required /><br>
            <label for="start-time-edit">Ora inizio:</label>
            <input type="time" name="start-time-edit" id="start-time-edit" placeholder="Ora inizio" /><br>
            <label for="end-time-edit">Ora fine:</label>
            <input type="time" name="end-time-edit" id="end-time-edit" placeholder="Ora fine" /><br>
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
            <label> <input type="checkbox" name="new_camera[]" value="1"> Camera 1 </label>
            <label> <input type="checkbox" name="new_camera[]" value="2"> Camera 2 </label>
            <label> <input type="checkbox" name="new_camera[]" value="3"> Camera 3 </label>
            <label> <input type="checkbox" name="new_camera[]" value="4"> Camera 4 </label>
            <label> <input type="checkbox" name="new_camera[]" value="5"> Camera 5 </label>
            <label> <input type="checkbox" name="new_camera[]" value="6"> Camera 6 </label>
            <label> <input type="checkbox" name="new_camera[]" value="7"> Camera 7 </label>
            <label> <input type="checkbox" name="new_camera[]" value="8"> Camera 8 </label>
            <label> <input type="checkbox" name="new_camera[]" value="9"> Camera 9 </label>
            <label> <input type="checkbox" name="new_camera[]" value="10"> Camera 10 </label>
            <label> <input type="checkbox" name="new_camera[]" value="11"> Camera 11 </label>
            <label> <input type="checkbox" name="new_camera[]" value="12"> Camera 12 </label>
            <label> <input type="checkbox" name="new_camera[]" value="13"> Camera 13 </label>
            <button type="submit" onclick="saveCameras()">Attiva</button>
        </form>
    </div>
</div>


</div>
</div>

<?php

include('../modals/footer.php');

?>