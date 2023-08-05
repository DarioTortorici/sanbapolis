<?php
session_start();

include '../modals/header.php';
include_once "../modals/navbar.php";
include 'video-helper.php';
include '../classes/Mark.php';
include '../classes/Screen.php';
include '../classes/Video.php';

setPreviusPage();

$pdo = get_connection();

// Controlla se video è stato passato come parametro
$video_location = $_GET['video'];
$playedVideo = "/editing_video/" . $video_location;
$filename = basename($playedVideo, ".mp4");
$video = getVideoFromPath($pdo, $playedVideo);
// Store video location in a JavaScript variable for later use
echo '<script>const videoLocation = "' . $video_location . '";</script>';
?>
<!-- CSS che gestisce la lista video -->
<link rel="stylesheet" href="../css/video/videoList.css">
<h1 id=video-name>Video Name</h1>
<h3 id="squadra-name">Estrai Clip</h3>
<div class="video-gallery">
    <div class="video-player container embed-responsive embed-responsive-16by9">
        <video id="<?php echo $filename ?>" class="player embed-responsive-item" controls autoplay>
            <source src="<?php echo "../{$playedVideo}" ?>" type="video/mp4">
        </video>
    </div>
    <div id="playlist" class="playlist">
        <ul id="video-list" class="list-group">
            <!-- Video thumbnails and titles will be added here dynamically -->
        </ul>
    </div>
</div>
<div class="container">
    <form action="screenshots/screen_manager.php?video=<?php echo $video_location; ?>&operation=get_screen" method="post">
        <input type="text" class="form-control" name="timing_video" id="timing_video" readonly hidden>
        <input type="button" class="btn btn-primary" id="mark" onclick="segnaposto()" value="Aggiungi Segnaposto">
        <input type="submit" class="btn btn-success" value="Screen">
    </form>
    <a href="<?php if ($video != null) {
                    echo "./clips/clip.php?id={$video->getId()}\"";
                } ?>" class="button btn btn-info">Vai a Estrai Clip</a>
    <a href="./clips/clips_list.php" class="button btn btn-light">Gestione clip</a>
    <a href="./marks/marks_list.php" class="button btn btn-warning">Gestione segnaposti</a>
    <a href="video_details.php?id=<?php if ($video != null) {
                                        echo "{$video->getId()}\"";
                                    } ?>" id="video-details" class="button btn btn-dark">Dettagli Video</a>
</div>
<div id="mark_details" hidden>
    <form action="marks/mark_manager.php?video=<?php echo $video_location; ?>&operation=new_mark" method="post">
        <fieldset>
            <legend>Segnaposto</legend>

            <div class="form-group">
                <label for="timing_mark">Timing:</label>
                <input type="text" class="form-control" name="timing_mark" id="timing_mark" readonly>
            </div>

            <div class="form-group">
                <label for="mark_name">Nome:</label>
                <input type="text" class="form-control" name="mark_name" id="mark_name">
            </div>

            <div class="form-group">
                <label for="mark_note">Descrizione:</label>
                <textarea class="form-control" id="mark_note" name="mark_note" rows="2" cols="30"></textarea>
            </div>

            <button type="submit" class="btn btn-primary" onclick="document.getElementById('mark_details').hidden = true">
                Salva
            </button>
        </fieldset>
    </form>
</div>


<div id="marks">
    <table id="list_marks" class="paleBlueRows">
        <tr>
            <th>Minutaggio</th>
            <th>Nome</th>
        </tr>
        <?php
        $marks = getMarksFromVideo($pdo, $playedVideo);
        try {
            if ($marks != null) {
                foreach ($marks as $el) {
                    $name = ($el->getName() == null) ? "-" : $el->getName();
                    echo <<< END
                                <div id="{$el->getId()}">
                                    <tr>
                                        <td>{$el->getTiming()}</td>
                                        <td>$name</td>
                                        <td><a href="mark_details.php?id={$el->getId()}">Dettagli</a></td>
                                END;
                    $timing = timing_format_from_db_to_int($el->getTiming());
                    echo "<td><a href=\"javascript:goToTiming(document.getElementById('{$filename}'), '$timing')\">Vai al Timing</a></td>\n\t</tr>\n\t</div>\n";
                }
            }
        } catch (Exception $e) {
            echo 'Eccezione: ',  $e->getMessage(), "\n";
        }
        ?>
    </table>
</div>

<a href="./screenshots/screenshots_list.php" class="button btn btn-secondary">Gestione screenshots</a>

<div id="screen_area" class="grid-container">
    <?php
    $screenshots = getScreenshotsFromVideo($pdo, $playedVideo);
    try {
        foreach ($screenshots as $el) {
            $img_name = substr($el->getPath(), strpos($el->getPath(), "/") + 1);
            echo <<< END
                        <div class="grid-item">
                            <a href="screen_details.php?id={$el->getId()}">
                                <img id="{$el->getId()}" src="../{$el->getPath()}" alt="$img_name" width="426" height="240">
                            </a>
                            <br>
                            <a href="screen_details.php?id={$el->getId()}&timing_video="">
                    END;
            echo ($el->getName() == null) ? $img_name : $el->getName();
            echo "</a>\n\t</div>\n";
        }
    } catch (Exception $e) {
        echo 'Eccezione: ',  $e->getMessage(), "\n";
    }
    ?>
</div>

</body>

</html>
<script>
    //timing video a tempo reale
    var video = $('#<?php echo $filename ?>');
    video.bind("timeupdate", function() {

        var stime = video[0].currentTime;
        stime = stime.toString();
        stime = stime.split(".").pop();
        stime = stime.substr(0, 3);

        $('#timing_video').val(fromSeconds(video[0].currentTime) + ':' + stime);
    });
</script>
<script src="../js/video/video-scripts.js"></script>
<script src="../js/video/videoList-script.js"></script>