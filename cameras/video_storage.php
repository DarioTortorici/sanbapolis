<!-- Javascripts per gestire il calendario -->
<script src="../js/calendar/calendar-scripts.js"></script>

<!-- PHP session init -->
<?php

include('../modals/calendar-header.php');
include_once("../modals/navbar.php");
include_once('../authentication/auth-helper.php');

if (!isset($_COOKIE['userID'])) {
  header("Location: ../authentication/login.php");
  exit();
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
