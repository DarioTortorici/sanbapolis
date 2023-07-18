<?php

// Includi il file header.php
include('../modals/header.php');
include "auth-helper.php";
?>

<?php
// Inizializza l'array dell'utente
$user = array();

// Controlla se è stata impostata la variabile di sessione per l'ID dell'utente
if (isset($_SESSION['userID'])) {
    // Ottieni le informazioni dell'utente dal database
    $user = get_user_info($con, $_SESSION['userID']);
}

// Controlla se la richiesta è di tipo POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Includi il file login-process.php per elaborare il login
    require('login-process.php');
}
?>

<!-- Area di registrazione -->
<section id="login-form">
    <div class="row m-0">
        <div class="col-lg-4 offset-lg-2">
            <div class="text-center pb-5">
                <h1 class="login-title text-dark">Login</h1>
                <p class="p-1 m-0 font-ubuntu text-black-50">Accedi e goditi funzionalità aggiuntive</p>
                <span class="font-ubuntu text-black-50">Crea un nuovo <a href="register.php">account</a></span>
            </div>
            <div class="upload-profile-image d-flex justify-content-center pb-5">
                <div class="text-center">
                    <img src="<?php echo isset($user['profileImage']) ? $user['profileImage'] : "https://i.imgur.com/l6CDPuw.png"; ?>" style="width: 200px; height: 200px" class="img rounded-circle" alt="profilo">
                </div>
            </div>
            <div class="d-flex justify-content-center">
                <form action="login-process.php" method="post" enctype="multipart/form-data" id="log-form">

                    <div class="form-row my-4">
                        <div class="col">
                            <input type="email" required name="email" id="email" class="form-control" placeholder="Email*">
                        </div>
                    </div>

                    <div class="form-row my-4">
                        <div class="col">
                            <input type="password" minlength="8" required name="password" id="password" class="form-control" placeholder="Password*">
                        </div>
                    </div>

                    <div class="submit-btn text-center my-5">
                        <button type="submit" class="btn btn-warning rounded-pill text-dark px-5">Login</button>
                    </div>

                    <div id="mismatch-credentials" class="alert alert-danger" role="alert" style="display: none;">
                        Le credenziali di accesso non sono corrette. Riprova.
                    </div>


                </form>
            </div>
        </div>
    </div>
</section>
<!-- #Area di registrazione -->

<script src="../js/authentication/auth.js"></script>


<?php
// Includi il file footer.php
include('../modals/footer.php');
?>