<?php
include('../modals/header.php');
include_once("../modals/navbar.php");

if (!isset($_COOKIE['email'])) {
  header("Location: ../authentication/login.php");
  exit();
}
?>

<script src="../js/cameras/cameras-scripts.js"></script>
<link href="https://vjs.zencdn.net/8.3.0/video-js.css" rel="stylesheet" />
<script src="https://vjs.zencdn.net/8.3.0/video.min.js"></script>

<div class="container">
<section id="camera-overview">
  <table>
    <tr class="main-row">
      <td>
        <div class="video-container">
          <div class="video-title">Camera 1</div>
          <video id="camera1" class="video-js" controls>
          </video>
        </div>
      </td>
    </tr>
  </table>
</section>
</div>

<?php
include('../modals/footer.php');
?>