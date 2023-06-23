<!-- Javascripts per gestire la pagina squadra -->
<script src="../js/teams/myteam-scripts.js"></script>

<!-- PHP session init -->
<?php

include('../modals/header.php');
include_once("../modals/navbar.php");
include_once('../authentication/auth-helper.php');

if (!isset($_SESSION['userID'])) {
  header("Location: ../authentication/login.php");
  exit();
}


echo '<script>';
echo 'getTeambyCoach("' . $user["email"] . '");';
echo 'getPlayersbyTeam(1);';
echo '</script>';

?>

<div class="container">
  <h2 id="team-name">My team Name</h2>
  <p class="text-left" id="team-code">Code</p>

  <div class="row">
    <div class="col-md-4">
      <div class="card">
        <img src="../assets/profileimg/beard.png" class="card-img-top" alt="Card Image">
        <div class="card-body">
          <h5 class="card-title">Card Title 1</h5>
          <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
          <a href="#" class="btn btn-primary">Dettagli</a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card">
        <img src="../assets/profileimg/Penguin writer.png" class="card-img-top" alt="Card Image">
        <div class="card-body">
          <h5 class="card-title">Card Title 2</h5>
          <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
          <a href="#" class="btn btn-primary">Dettagli</a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card">
        <img src="https://via.placeholder.com/150" class="card-img-top" alt="Card Image">
        <div class="card-body">
          <h5 class="card-title">Card Title 3</h5>
          <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
          <a href="#" class="btn btn-primary">Dettagli</a>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="card">
        <img src="https://via.placeholder.com/150" class="card-img-top" alt="Card Image">
        <div class="card-body">
          <h5 class="card-title">Card Title 4</h5>
          <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
          <a href="#" class="btn btn-primary">Dettagli</a>
        </div>
      </div>
    </div>
  </div>
</div>


</div>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>