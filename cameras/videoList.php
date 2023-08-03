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
<link rel="stylesheet" href="videoList.css">

<section class="main-site">
    <h1>Lista Video</h1>
    <h2>GG-MM-YYYY</h2>
    <h3>Squadra</h3>
    <div class="video-gallery">
        <div class="video-player">
            <video id="player" controls autoplay>
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
</section>
<script src="videoList-script.js"></script>

<?php
include "../modals/footer.php";
?>
