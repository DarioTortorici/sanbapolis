<?php

include '../../modals/header.php';
include '../../modals/navbar.php';

include 'video-editing-helper.php';
include '../../classes/Mark.php';
include '../../classes/Screen.php';
include '../../classes/Video.php';


$pdo = get_connection();

setPreviusPage();

$video = null;
$filename = "";

if (isset($_SESSION["video"])) {
    $video = unserialize($_SESSION["video"]);
    $filename = basename($video->getPath(), ".mp4");
} elseif (isset($_GET["video_deleted"])) {
    echo "<p class=\"message\">Video Eliminato correttamente</p>";
} else {
    http_response_code(404);
    include('error.php');
    die();
}

if (isset($_SESSION["person"])) {
    $person = unserialize($_SESSION["person"]);
} else {
    header("Location: ../" . INDEX);
}
?>
<link rel="stylesheet" href="../../css/video/videoList.css">
<div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="video-gallery">
                    <div class="video-player embed-responsive embed-responsive-16by9">
                        <video id="<?php echo $filename ?>" controls muted autoplay>
                            <source src="<?php if ($video != null) {
                                                echo "../{$video->getPath()}";
                                            } ?>" type="video/mp4">
                        </video>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <form action="<?php echo VIDEO_MANAGER ?>?operation=update_video" method="post" onsubmit="return confirm('Confermi?')">
                    <fieldset>
                        <legend>Modifica Nome e Descrizione</legend>
                        <div class="form-group">
                            <label for="video_name">Nome:</label>
                            <input type="text" class="form-control" name="video_name" id="video_name" value="<?php if ($video != null) {
                                                                                                                    echo $video->getName();
                                                                                                                } ?>">
                        </div>
                        <div class="form-group">
                            <label for="video_note">Descrizione:</label>
                            <textarea class="form-control" name="video_note" id="video_note" rows="3"><?php if ($video != null) {
                                                                                                                echo $video->getNote();
                                                                                                            } ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Salva</button>
                        <button type="submit" class="btn btn-danger" formaction="<?php echo VIDEO_MANAGER ?>?operation=delete_video">Elimina Video</button>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>

<div class="container mt-4">
    <br>
    <div class="row mt-4">
        <div class="col-md-3">
            <a href="editing_video.php" class="button btn btn-secondary btn-block">Editing Video</a>
        </div>
        <div class="col-md-3">
            <a href="../clips/clips_list.php" class="button btn btn-secondary btn-block">Gestione clip</a>
        </div>
        <div class="col-md-3">
            <a href="../marks/marks_list.php" class="button btn btn-secondary btn-block">Gestione segnaposti</a>
        </div>
        <div class="col-md-3">
            <a href="../screenshots/screenshots_list.php" class="button btn btn-secondary btn-block">Gestione screenshots</a>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-3">
            <button class="btn btn-warning" type="button" onclick="showMarks()" id="show_marks">Mostra i segnaposti</button>
        </div>
        <div class="col-md-3">
            <button class="btn btn-warning" type="button" onclick="showScreenArea()" id="show_screen_area">Mostra gli screenshot</button>
        </div>
    </div>

</div>
<div id="marks" hidden>
    <table id="list_marks" class="paleBlueRows">
        <tr>
            <th>Minutaggio</th>
            <th>Nome</th>
        </tr>
        <?php
        $marks = getMarksFromVideo($pdo, $video->getPath());
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

<div id="screen_area" class="grid-container" hidden>
    <?php
    $screenshots = getScreenshotsFromVideo($pdo, $video->getPath());
    try {
        foreach ($screenshots as $el) {
            $img_name = ($el->getName() == null) ? basename($el->getPath(), ".jpg") : $el->getName();
            echo <<< END
                        <div class="grid-item">
                            <a href="screen_details.php?id={$el->getId()}">
                                <img id="{$el->getId()}" src="../{$el->getPath()}" alt="$img_name" width="426" height="240">
                            </a>
                            <br>
                            <a href="screen_details.php?id={$el->getId()}">$img_name</a>
                        </div>\n
                    END;
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

    function segnaposto() {
        const xhttp = new XMLHttpRequest();
        var url = "mark_manager.php?timing=" + $('#timing_video').val();
        xhttp.open("GET", url, true);
        xhttp.onreadystatechange = function() {
            if (this.readyState = 4 && this.status === 200) {
                let timing = xhttp.responseText;
                if (timing != "") {
                    $('#timing_mark')[0].value = timing;
                    $('#mark_details')[0].hidden = false;
                    $('#<?php echo $filename ?>')[0].pause();
                }
            }
        }
        xhttp.send();
    }

    window.onload = function() {
        let timing = findGetParameter("timing_screen");
        if (timing != null) {
            timing = parseFloat(timing);
            document.getElementById("<?php echo $filename ?>").currentTime = timing;
        }
    }

    function goToTiming(video, timing) {
        video.currentTime = timing;
        video.pause();
    }
</script>
<script src="../../js/video/video-scripts.js"></script>