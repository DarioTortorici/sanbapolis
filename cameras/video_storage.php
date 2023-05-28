<?php

session_start();
include('../modals/calendar-header.php');
$user = array();
?>


<!-- Calendario "FullCalendar" caricato da JavaScript -->
<div class="container">
    <div id="calendar-video"></div>
</div>

<!-- Modale per visualizzare le informazioni dell'evento-->
<div id="show-video-modal" class="white-popup-block mfp-hide">
    <p style="height: 30px; background: orangered; width: 100%;"></p>
    <div style="display: flex;">
        <h2 id="event-date" style="flex: 1;">Giorno Mese Anno</h2>
        <p id="event-time-init" style="margin-left: 10px; font-size: 24px;">Orario Inizio</p>
        <p id="event-time-spacer" style="margin-left: 10px; font-size: 24px;">-</p>
        <p id="event-time-end" style="margin-left: 10px; font-size: 24px;">Orario Fine</p>
    </div>
    <div style="min-height: 250px;">
        <h3 id="event-name">Titolo</h3>
        <p id="event-url">File video</p>
        <p id="event-id" style="display: none;"> id </p>
    </div>

    <button id="delete-button" class="btn btn-danger" onclick="deleteEvent()">Elimina</button>
</div>



<!-- Javascripts per gestire il calendario -->
<script src="../js/cameras/calendar-scripts.js"></script>

<?php

include('../modals/footer.php');

?>
