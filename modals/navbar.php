<!-- PHP session init -->
<?php

$user = array();
require(__DIR__ . '/../authentication/db_connection.php');
include(__DIR__ . '/../authentication/auth-helper.php');

if (isset($_COOKIE['email'])) {
    $user_email = $_COOKIE['email'];
    $user = get_user_info($con, $_COOKIE['email']);

    if (!$user['verificato']){
        header("Location: /modals/suggest.php");
    }
}
?>

<!-- Navbar-->
<div class="container">
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/index.php">Sanbapolis</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/index.php">Home</a>
                    </li>
                    <?php if (isset($user_email)) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../calendar/calendar.php">Calendario</a>
                        </li>
                        <?php if (isset($user) && $user['privilegi_cam'] == 1) : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../cameras/overview.php">Livecams</a>
                            </li>
                        <?php endif; ?>
                        <?php if (isset($user) && $user['userType'] == "allenatore") : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../cameras/video_storage.php">Filmati</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../profile/my-team.php">My team</a>
                            </li>
                        <?php endif; ?>
                        <?php if (isset($user) && $user['userType'] == "società") : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../profile/my-staff.php">My staff</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../chi-siamo.php">Chi siamo</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav grid gap-1">
                    <?php if (isset($user_email)) : ?>
                        <a href="../profile/user-dashboard.php"><img class="img rounded-circle" style="width: 20px; height: 20px;" src="<?php echo isset($user['locazione_immagine_profilo']) ? substr($user['locazione_immagine_profilo'], 2) : '../assets/profileimg/beard.png'; ?>" alt=""></a>
                    <?php else : ?>
                        <li> <a class="btn btn-primary" href="/authentication/login.php" role="button">Accedi</a> </li>
                        <li> <a class="btn btn-primary" href="/authentication/register.php" role="button">Iscriviti</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</div>
