<?php
session_start();
include('../modals/header.php');
include('../authentication/auth-helper.php');
$user = array();

if (!isset($_SESSION['userID'])) {
  header("Location: ../authentication/login.php");
  exit();
} else {
  require('../authentication/db_connection.php');
  $user = get_user_info($con, $_SESSION['userID']);
}
?>


<button type="submit" class="btn btn-primary" name="recButton" onclick="getRecordingEvents()">Avvia Rec</button>


<style>
  table {
    width: 100%;
    border-collapse: collapse;
  }

  td {
    padding: 10px;
    border: 1px solid #ccc;
  }

  .video-container {
    position: relative;
  }

  video {
    width: 100%;
    height: auto;
  }

  .expanded-video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
  }

  .video-title {
    text-align: center;
    font-weight: bold;
    margin-bottom: 10px;
  }
</style>

<script src="../js/cameras/cameras-scripts.js"></script>

<section id="camera-overview">
  <table>
    <tr class="main-row">
      <td onclick="expandVideo(this)">
        <div class="video-container">
          <div class="video-title">Camera 1</div>
          <video id="camera1" controls autoplay>
            <source src="www.url.it" type="video/mp4">
          </video>
        </div>
      </td>
      <td onclick="expandVideo(this)">
        <div class="video-container">
          <div class="video-title">Camera 2</div>
          <video id="camera2" controls autoplay>
            <source src="www.url.it" type="video/mp4">
          </video>
        </div>
      </td>
    </tr>
    <tr class="sub-row">
      <td onclick="expandVideo(this)">
        <div class="video-container">
          <div class="video-title">Camera 3</div>
          <video id="camera3" controls autoplay>
            <source src="www.url.it" type="video/mp4">
          </video>
        </div>
      </td>
      <td onclick="expandVideo(this)">
        <div class="video-container">
          <div class="video-title">Camera 4</div>
          <video id="camera4" controls autoplay>
            <source src="www.url.it" type="video/mp4">
          </video>
        </div>
      </td>
    </tr>
  </table>
</section>


<?php
// footer.php
include('../modals/footer.php');
?>