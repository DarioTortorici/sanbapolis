<?php

include('../modals/header.php');
include_once("../modals/navbar.php");

if (!isset($_COOKIE['email'])) {
    header("Location: ../authentication/login.php");
    exit();
}

// Check if the 'video_location' GET parameter is set and not empty
if (isset($_GET['video_location']) && !empty($_GET['video_location'])) {
    // Sanitize the input to prevent any potential security issues
    $video_location = htmlspecialchars($_GET['video_location']);
} else {
    // If the 'video_location' parameter is not provided, you can set a default location or redirect to an error page
    // For this example, I'll set a default location
    $video_location = "../videos/default_folder/";
}
echo '<script>const videoLocation = "' . $video_location . '";</script>';
?>
<link rel="stylesheet" href="../css/video/videoList.css">

<section class="main-site">
    <h1>Lista Video</h1>
    <h2 id="data-sessione">GG-MM-YYYY</h2>
    <h3 id="squadra-name">Squadra</h3>
    <div class="video-gallery">
        <div class="video-player container embed-responsive embed-responsive-16by9">
            <video id="player" class="embed-responsive-item" controls autoplay>
                <!-- Your video source here -->
                <source src="<?php echo $video_location; ?>" type="video/mp4">
            </video>
        </div>
        <div id="playlist" class="playlist">
            <ul id="video-list">
                <!-- Video thumbnails and titles will be added here dynamically -->
            </ul>
        </div>
    </div>

    <div class="container">
        <a href="editing_video.php" class="button btn btn-info">Editing Video</a>
        <a href="./clips/clips_list.php" class="button btn btn-light">Gestione clip</a>
        <a href="./marks/marks_list.php" class="button btn btn-warning">Gestione segnaposti</a>
        <a href="./screenshots/screenshots_list.php" class="button btn btn-dark">Gestione screenshots</a>
    </div>
</section>

<script src="../js/video/videoList-script.js"></script>

<?php
include "../modals/footer.php";
?>
