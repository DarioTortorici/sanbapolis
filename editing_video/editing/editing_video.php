<?php

include '../../modals/header.php';
include_once "../../modals/navbar.php";
include 'video-editing-helper.php';
include '../../classes/Mark.php';
include '../../classes/Screen.php';
include '../../classes/Video.php';
include 'error-checker.php';

$pdo = get_connection();
if (isset($_GET['video']) && $_GET['video'] != '') {
    $video_location = $_GET['video'];
    $video = getVideoFromPath($pdo, $video_location);
    $_SESSION["video"] = serialize($video);
    if (isset($_GET['recording_date']) && $_GET['recording_date'] != '') {
        $recording_date = $_GET['recording_date'];
    }
} else {
    $video = unserialize($_SESSION["video"]);
    $filename = basename($video->getPath(), ".mp4");
    $recording_date = getRecordingDate($video->getPath());
}
setPreviusPage();
?>

<link rel="stylesheet" href="../../css/video/videoList.css">
<div class="video-gallery">
    <div class="video-player container embed-responsive embed-responsive-16by9">
        <video id="<?php echo $filename ?>" class="player embed-responsive-item" controls muted autoplay>
            <source src="<?php echo "../{$video->getPath()}" ?>" type="video/mp4">
        </video>
    </div>
    <div id="playlist" class="playlist">
        <ul id="video-list" class="list-group">
            <form action="<?php echo VIDEO_MANAGER; ?>?operation=multiple_video_delete" method="post" onsubmit="confirm('Sicuro di eliminare i video selezionati?')">
                <?php
                try {
                    $videos = getPlaylist($video->getPath());
                    echo '<table class="table table-striped table-hover">';
                    echo '<thead>
                            <tr>
                                <th></th>
                                <th id="playlist-date">' . $recording_date . '</th>
                            </tr>
                          </thead>';
                    foreach ($videos as $el) {
                        $link = "../".$el->getPath();
                        echo <<<END
                    <tr class='clickable-row'>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="{$el->getId()}" name="id[]" value="{$el->getId()}">
                            </div>
                        </td>        
                        <td data-href='$link'>{$el->getName()}</td>
                    </tr>
                    END;
                    }

                    echo '</table>';
                } catch (Exception $e) {
                    echo 'Eccezione: ',  $e->getMessage(), "\n";
                }

                ?>
                <input type="submit" class="btn btn-danger" value="Elimina">
            </form>
        </ul>
    </div>
</div>
<div class="container mt-4">
    <form action="../screenshots/screen_manager.php?operation=get_screen" method="post" class="mb-4">
        <div class="input-group">
            <input type="text" class="form-control" name="timing_video" id="timing_video" hidden readonly>

            <button class="btn btn-primary mr-2" type="button" id="mark" onclick="segnaposto()">Aggiungi Segnaposto</button>

            <button type="submit" class="btn btn-success mr-2">Screen</button>
        </div>
    </form>

    <a href="<?php if ($video != null) {
                    echo "../clips/clip.php?id=" . $video->getId();
                } ?>" class="btn btn-secondary mr-2">Vai a Estrai Clip</a>
    <a href="../clips/clips_list.php" class="btn btn-secondary mr-2">Gestione clip</a>
    <a href="../marks/marks_list.php" class="btn btn-secondary mr-2">Gestione segnaposti</a>
    <a href="../screenshots/screenshots_list.php" class="btn btn-secondary mr-2">Gestione screenshots</a>
    <a href="video_details.php?id=<?php if ($video != null) {
                                        echo "{$video->getId()}\"";
                                    } ?>" class="btn btn-secondary">Dettagli Video</a>
</div>

<div class="container mt-4">
    <div id="mark_details" hidden>
        <form action="../marks/mark_manager.php?operation=new_mark" method="post">
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

                <button type="submit" class="btn btn-primary">Salva</button>
            </fieldset>
        </form>
    </div>
</div>

<div class="container mt-4" id="marks">
    <table id="list_marks" class="table table-striped">
        <thead class="thead-light">
            <tr>
                <th scope="col">Minutaggio</th>
                <th scope="col">Nome</th>
                <th scope="col">Dettagli</th>
                <th scope="col">Vai al Timing</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $marks = getMarksFromVideo($pdo, $video->getPath());
            try {
                if ($marks != null) {
                    foreach ($marks as $el) {
                        $name = ($el->getName() == null) ? "-" : $el->getName();
                        $timing = timing_format_from_db_to_int($el->getTiming());
                        echo <<< END
                                <tr>
                                    <td>{$el->getTiming()}</td>
                                    <td>$name</td>
                                    <td><a href="../marks/mark_details.php?id={$el->getId()}" class="btn btn-secondary btn-sm">Dettagli</a></td>
                                    <td><a href="javascript:goToTiming(document.getElementById('{$filename}'), '$timing')" class="btn btn-primary btn-sm">Vai al Timing</a></td>
                                </tr>
                            END;
                    }
                }
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
            ?>
        </tbody>
    </table>
</div>




<div id="screen_area" class="grid-container">
    <?php
    $screenshots = getScreenshotsFromVideo($pdo, $video->getPath());
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

<div hidden id="snackbar">Esiste già un segnaposto con quel minutaggio</div>

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
        var url = "../marks/mark_manager.php? timing=" + $('#timing_video').val();
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

        let message = findGetParameter("message");
        if (message == "mark_exists") {
            showSnackbar();
        }
    }

    function goToTiming(video, timing) {
        video.currentTime = timing;
        video.pause();
    }
</script>
<script src="../../js/video/video-scripts.js"></script>
<script>
    // Function to change the video source based on the clicked td
    function changeVideoSource(videoPath) {
        const videoElement = document.getElementById("<?php echo $filename ?>");
        videoElement.pause(); // Pause the current video
        videoElement.src = videoPath; // Change the video source
        videoElement.load(); // Load the new video source
        videoElement.play(); // Start playing the new video
    }

    // Attach click event listeners to all clickable-row <td> elements
    const clickableRows = document.querySelectorAll('.clickable-row td[data-href]');
    clickableRows.forEach(td => {
        td.addEventListener('click', () => {
            const videoPath = td.getAttribute('data-href');
            changeVideoSource(videoPath);
        });
    });
</script>
