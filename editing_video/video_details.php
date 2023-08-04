<?php
session_start();

include('../modals/header.php');
include_once("../modals/navbar.php");
include 'video-helper.php';
include '../classes/Mark.php';
include '../classes/Screen.php';
include '../classes/Video.php';

$pdo = get_connection();
setPreviusPage();

if (!isset($_COOKIE['email'])) {
    header("Location: ../authentication/login.php");
    exit();
}

if (isset($_GET["id"])) {
    try {
        $id = intval($_GET["id"]);
        $videos = getVideosFromUser($pdo, "manutentore@example.com");
        $filename = "C:\xampp\htdocs\editing_video\basket_test_1.mp4";
    } catch (Exception $e) {
        echo "Eccezione: " . $e->getMessage();
    }
}
?>


<body>
    <div class="container embed-responsive embed-responsive-16by9">
        <video class="embed-responsive-item" controls muted autoplay>
            <source src="../basket_test_1.mp4" type="video/mp4">
        </video>
    </div>

    <br>
    <div class="container">
        <a href="editing_video.php" class="button btn btn-info">Editing Video</a>
        <a href="./clips/clips_list.php" class="button btn btn-light">Gestione clip</a>
        <a href="./marks/marks_list.php" class="button btn btn-warning">Gestione segnaposti</a>
        <a href="./screenshots/screenshots_list.php" class="button btn btn-dark">Gestione screenshots</a>
    </div>

    <button type="button" class="btn btn-primary" onclick="showMarks()" id="show_marks">Mostra i segnaposti</button>
    <button type="button" class="btn btn-secondary" onclick="showScreenArea()" id="show_screen_area">Mostra gli screenshot</button>

    <div class="container" id="marks" hidden>
        <table id="list_marks" class="paleBlueRows">
            <tr>
                <th>Minutaggio</th>
                <th>Nome</th>
            </tr>
            <?php
            $marks = getMarksFromVideo($pdo, $_SESSION["path_video"]);
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
        $screenshots = getScreenshotsFromVideo($pdo, $_SESSION["path_video"]);
        try {
            foreach ($screenshots as $el) {
                $img_name = substr($el->getPath(), strpos($el->getPath(), "/") + 1);
                echo <<< END
                        <div class="grid-item">
                            <a href="./screenshots/screen_details.php?id={$el->getId()}">
                                <img id="{$el->getId()}" src="../{$el->getPath()}" alt="$img_name" width="426" height="240">
                            </a>
                            <br>
                            <a href="./screenshots/screen_details.php?id={$el->getId()}&timing_video="">
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