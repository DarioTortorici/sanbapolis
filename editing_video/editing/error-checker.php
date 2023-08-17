<?php
if (!isset($_COOKIE['email'])) {
    header("Location: ../../authentication/login.php");
    exit();
}

if (isset($_SESSION["video"])) {
    $video = unserialize($_SESSION["video"]);
} else {
    echo '<div class="alert alert-danger" role="alert" id="errorAlert" style="display: none;">
    Purtoppo qualcosa è andato storto.
</div>';
}