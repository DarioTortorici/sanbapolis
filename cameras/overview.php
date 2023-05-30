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

<section id="camera overview">
  <div class="camera-1">
    <iframe src="http://192.168.65.169/wmf/index.html#/uni/channel" width="640" height="480" frameborder="0"></iframe>
  </div>

  <div class="camera-2">
    <iframe src="http://indirizzo_telecamera_2" width="640" height="480" frameborder="0"></iframe>
  </div>

  <div class="camera-3">
    <iframe src="http://indirizzo_telecamera_3" width="640" height="480" frameborder="0"></iframe>
  </div>
</section>

<?php
// footer.php
include('../modals/footer.php');
?>