<?php

include('../modals/header.php');
include_once("../modals/navbar.php");

if (!isset($_COOKIE['email'])) {
  header("Location: ../authentication/login.php");
  exit();
}
?>

<section id="main-site">
    <div class="container py-5">
        <div class="row">
            <div class="col-4 offset-4 shadow py-4">
                <div class="upload-profile-image d-flex justify-content-center pb-5">
                    <div class="text-center">
                        <img class="img rounded-circle" style="width: 200px; height: 200px;" src="<?php echo isset($user['locazione_immagine_profilo']) ? $user['locazione_immagine_profilo'] : './assets/profile/beard.png'; ?>" alt="">
                        <h4 class="py-3">
                            <?php
                            if (isset($user['nome'])) {
                                printf('%s %s', $user['nome'], $user['cognome']);
                            }
                            ?>
                        </h4>
                    </div>
                </div>

                <div class="user-info px-3">
                    <ul class="font-ubuntu navbar-nav">
                        <li class="nav-link"><b>First Name: </b><span><?php echo $user['nome']; ?></span></li>
                        <li class="nav-link"><b>Last Name: </b><span><?php echo $user['cognome']; ?></span></li>
                        <li class="nav-link"><b>Email: </b><span><?php echo $user['email']; ?></span></li>
                        <li class="nav-link"><b>User Type: </b><span><?php echo $user['userType']; ?></span></li>
                        <li class="nav-link"><a type="button" class="btn btn-dark" role="button" href="../authentication/logout.php">Log out</a></span></li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</section>

<?php
include "../modals/footer.php";
?>