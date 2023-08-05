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

if (isset($_GET['recording_date']) && !empty($_GET['recording_date'])) {
    // Sanitize the input to prevent any potential security issues
    $data = $_GET['recording_date'];
} 
echo '<script>const videoLocation = "' . $video_location . '";</script>';
?>
<link rel="stylesheet" href="../css/video/videoList.css">

<section class="main-site">
    <h1 id=video-name>Video Name</h1>
    <h2 id="data-sessione"><?php echo $data; ?></h2>
    <div class="video-gallery">
        <div class="video-player container embed-responsive embed-responsive-16by9">
            <video class="player embed-responsive-item" controls autoplay>
                <!-- Your video source here -->
                <source src="<?php echo $video_location; ?>" type="video/mp4">
            </video>
        </div>
        <div id="playlist" class="playlist">
            <ul id="video-list" class="list-group">
                <!-- Video thumbnails and titles will be added here dynamically -->
            </ul>
        </div>
    </div>

    <div class="container">
        <!-- Link dinamicamente in JS -->
        <a href="editing_video.php?video=" id="editing-button" class=" button btn btn-info">Editing Video</a>
        <a href="./clips/clips_list.php" class="button btn btn-light">Gestione clip</a>
        <a href="./marks/marks_list.php" class="button btn btn-warning">Gestione segnaposti</a>
        <a href="./screenshots/screenshots_list.php" class="button btn btn-dark">Gestione screenshots</a>
    </div>
</section>

<script src="../js/video/videoList-script.js"></script>

<?php
include "../modals/footer.php";
?>