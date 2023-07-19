<?php
include('../modals/header.php');
require('auth-helper.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require('register-process.php');
}

$userType = $_POST['userType'] ?? '';
$teamcode = $_POST['teamCode'] ?? '';
$societycode = $_POST['societyCode'] ?? '';
?>

<!-- registration scripts -->
<script src="../js/authentication/register.js"></script>

<!-- registration area -->
<section id="register">
    <div class="row m-0">
        <div class="col-lg-4 offset-lg-2">
            <div class="text-center pb-5">
                <h1 id="Registerform" class="login-title text-dark">Register</h1>
                <p class="p-1 m-0 font-ubuntu text-black-50">Register and enjoy additional features</p>
                <span class="font-ubuntu text-black-50">I already have <a href="login.php">Login</a></span>
            </div>
            <div class="upload-profile-image d-flex justify-content-center pb-5">
                <div class="text-center">
                    <div class="d-flex justify-content-center">
                        <img class="camera-icon" src="https://i.imgur.com/38vhLT4.png" alt="camera">
                    </div>
                    <img src="https://i.imgur.com/l6CDPuw.png" style="width: 200px; height: 200px" class="img rounded-circle" alt="profile">
                    <small class="form-text text-black-50">Choose Image</small>
                    <input type="file" form="reg-form" class="form-control-file" name="profileUpload" id="upload-profile">
                </div>
            </div>
            <div class="d-flex justify-content-center">
                <form action="register-process.php" method="post" enctype="multipart/form-data" id="reg-form">
                    <div class="form-row">
                        <div class="col">
                            <input type="text" value="<?php if (isset($_POST['firstName'])) echo $_POST['firstName'];  ?>" name="firstName" id="firstName" class="form-control" placeholder="First Name">
                        </div>
                        <div class="col">
                            <input type="text" value="<?php if (isset($_POST['lastName'])) echo $_POST['lastName'];  ?>" name="lastName" id="lastName" class="form-control" placeholder="Last Name">
                        </div>
                    </div>

                    <div class="form-row my-4">
                        <div class="col">
                            <input type="email" value="<?php if (isset($_POST['email'])) echo $_POST['email'];  ?>" required name="email" id="email" class="form-control" placeholder="Email*">
                        </div>
                    </div>

                    <div class="form-row my-4">
                        <div class="col">
                            <input type="password" minlength="8" required name="password" id="password" class="form-control" placeholder="Password*">
                        </div>
                    </div>

                    <div class="form-row my-4">
                        <div class="col">
                            <input type="password" required name="confirm_pwd" id="confirm_pwd" class="form-control" placeholder="Confirm Password*">
                            <small id="confirm_error" class="text-danger"></small>
                        </div>
                    </div>

                    <div class="form-row my-4">
                        <div class="col">
                            <select required name="userType" id="userType" class="form-control" onchange="handleUserType()">
                                <option value="" selected disabled>Seleziona il tuo ruolo*</option>
                                <option value="società" <?php if ($userType === 'società') echo 'selected'; ?>>Società</option>
                                <option value="allenatore" <?php if ($userType === 'allenatore') echo 'selected'; ?>>Allenatore</option>
                                <option value="giocatore" <?php if ($userType === 'giocatore') echo 'selected'; ?>>Giocatore</option>
                                <option value="altro" <?php if ($userType === 'tifoso') echo 'selected'; ?>>Tifoso</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row my-4" id="userSocietyRow" style="display: none;">
                        <div class="col">
                            <input type="text" name="p_iva" id="p_iva" class="form-control" placeholder="Partita Iva*">
                            <input type="text" name="societyName" id="societyName" class="form-control" placeholder="Nome Società*">
                            <input type="text" name="address" id="address" class="form-control" placeholder="Indirizzo">
                            <p id="societyCodeError" class="error-message">Codice societario non esistente</p>
                            <select name="sportType" id="sportType" class="form-control">
                                <option value="" disabled selected>Tipo Sport*</option>
                                <?php echo getSports(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row my-4" id="teamCodeRow" style="display: none;">
                        <div class="col">
                            <input type="text" value="<?php echo $teamcode; ?>" name="teamCode" id="teamCode" class="form-control" placeholder="Team Code">
                            <p id="teamCodeError" class="error-message">Codice squadra non esistente</p>
                        </div>
                    </div>
                    <div class="form-row my-4" id="societyCodeRow" style="display: none;">
                        <div class="col">
                            <input type="text" value="<?php echo $societycode; ?>" name="societyCode" id="societyCode" class="form-control" placeholder="Society Code">
                            <p id="societyCodeError" class="error-message">Codice societario non esistente</p>
                            <select name="coachType" id="coachType" class="form-control">
                                <option value="" disabled selected>Tipo Allenatore*</option>
                                <?php echo getCoachTypes(); ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row my-4">
                        <div class="col">
                            <input type="text" value="<?php if (isset($_POST['telefono'])) echo $_POST['telefono'];  ?>" name="telefono" id="telefono" class="form-control" placeholder="Telefono">
                        </div>
                    </div>

                    <div class="form-row my-4">
                        <div class="col">
                            <input type="text" value="<?php if (isset($_POST['citta'])) echo $_POST['citta'];  ?>" name="citta" id="citta" class="form-control" placeholder="Città">
                        </div>
                    </div>

                    <div class="form-row my-4">
                        <div class="col">
                            <label for="dataNascita">Data di nascita</label>
                            <input type="date" value="<?php if (isset($_POST['dataNascita'])) echo $_POST['dataNascita']; ?>" name="dataNascita" id="dataNascita" class="form-control" placeholder="Data di nascita">
                        </div>
                    </div>


                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="agreement" id=agreement class="form-check-input" required>
                        <label for="agreement" class="form-check-label font-ubuntu text
                        -black-50">I agree <a href="#">term, conditions, and policy </a>(*) </label>
                    </div>

                    <div class="submit-btn text-center my-5">
                        <button type="submit" class="btn btn-warning rounded-pill text-dark px-5">Continue</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</section>
<!-- #registration area -->

<?php
// footer.php
include('../modals/footer.php');
?>