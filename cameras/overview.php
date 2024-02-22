<!-- Javascripts per gestire il calendario -->
<script src="../js/calendar/calendar-scripts.js"></script>

<!-- PHP session init -->
<?php

include('../modals/header.php');
include_once('../modals/navbar.php');
include("./overview-helper.php");

if (!isset($_COOKIE['email'])) {
  header('Location: ../authentication/login.php');
  exit();
}

// Definisci gli ID dei video
$video_ids = [
  'RTc0Iq6pexw?mute=1&start=',
];

// Verifico se ci sono eventi in corso
// In caso di evento in corso, carico le relative telecamere assegnate
// $date_dummy = '2024-02-16';
// $hour_dummy = '14:30:00';

$date = date('Y-m-d', time());
$hour = date('H:i:s', time());
?>

<style>
  .video-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    grid-gap: 20px;
  }

  .video-item {
    text-align: center;
  }

  .video-title {
    font-size: 18px;
    margin-bottom: 10px;
  }

  iframe {
    width: 100%;
    max-width: 560px;
    height: 315px;
  }
</style>

<div class="container">
  <section id="camera-overview">
    <div class="video-grid">
      <?php

      // Ottengo le informazioni dell'evento corrente
      $curr_data = getCurrentEvent($date,$hour);

      // Controllo se ho trovato un evento
      if ($curr_data !== '[]') {
        $curr_date_data = json_decode($curr_data);

        // Ottengo i secondi precisi in cui devo far iniziare lo streaming (&start=)
        // Dovrei fare la sottrazione dell'ora corrente con l'ora di inizio, e convertirla in secondi
        // (controllo che non ecceda l'ora di fine)
        $start_streaming_time = $curr_date_data[0]->startTime;
        $end_streaming_time = $curr_date_data[0]->endTime;
        $curr_time_to_start = curStreamingStart($start_streaming_time, $hour, $end_streaming_time);

        // Controllo se sono fuori range di tempo.
        if (isset($curr_time_to_start) && $curr_time_to_start !== '') {

          // Ottengo le telecamere dell'evento corrente
          $curr_cameras_data = json_decode(getCameras($curr_date_data[0]->id));

          // Itera attraverso gli ID dei video e crea la griglia
          for ($x = 0; $x < count($curr_cameras_data); $x++) {
            echo '<div class="video-item">';
            echo '<div class="video-title">Camera ' . $curr_cameras_data[$x]->telecamera . '</div>';
            echo '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $video_ids[0] . $curr_time_to_start .'&autoplay=1" frameborder="0" allowfullscreen></iframe>';
            echo '</div>';
          }
        }
      } else {
        echo '<h2> Non ci sono partite o allenamenti in corso! </h2>';
      }
      ?>
    </div>
  </section>
</div>


<?php
// Includi il file footer
include('../modals/footer.php');
?>