<!-- Javascripts per gestire il calendario -->
<script src="../js/calendar/calendar-scripts.js"></script>

<!-- PHP session init -->
<?php

include('../modals/calendar-header.php');
include_once("../modals/navbar.php");
require_once('../authentication/auth-helper.php');

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