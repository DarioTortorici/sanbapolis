<?php

include '../../modals/header.php';
include_once "../../modals/navbar.php";
include '../editing/video-editing-helper.php';
include '../../classes/Video.php';
include '../editing/error-checker.php';

$filename = basename($video->getName(), ".mp4");
$pdo = get_connection();

?>
<script src="../../js/video/video-scripts.js"></script>
<link rel="stylesheet" href="../../css/video/videoList.css">
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="video-gallery">
                <div class="video-player embed-responsive embed-responsive-16by9">
                    <video id="<?php echo $video->getId() ?>" class="player embed-responsive-item" controls muted autoplay>
                        <source src="<?php echo "../{$video->getPath()}"; ?>" type="video/mp4">
                    </video>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <form action="clip_manager.php?operation=new_clip" method="post">
                <input class="form-control" type="text" name="timing_video" id="timing_video" readonly><br>
                <label>Timing inizio clip: </label><input class="form-control" type="text" name="start_timing_trim" id="start_timing_trim" readonly>
                <input class="btn btn-info" type="button" onclick="getStartTimingTrim()" value="Prendi tempo iniziale"><br>
                <label>Timing fine clip: </label><input class="form-control" type="text" name="end_timing_trim" id="end_timing_trim" readonly>
                <input class="btn btn-info" type="button" onclick="getEndTimingTrim()" value="Prendi tempo finale"><br>
                <input class="btn btn-success" type="submit" id="trim_video" value="EstraiClip" disabled>
            </form>
        </div>
    </div>
</div>
<div class="container mt-4">
    <div id="clip" hidden>
        <fieldset>
            <legend>Clip Estratta</legend>
            <video controls muted width="100%" height="auto">
                <?php
                if (isset($_GET["clip"])) {
                    $id = intval($_GET["clip"]);
                    $clip = getVideoFromId($pdo, $id);
                    echo "<source src=\"../{$clip->getPath()}\" type=\"video/mp4\">";
                }
                ?>
            </video>
        </fieldset>
    </div>
</div>

</body>

<div hidden id="snackbar" class>Il tempo iniziale deve essere maggiore al tempo finale</div>

<script>
    //timing video a tempo reale
    var video = $('#<?php echo $video->getId() ?>');
    video.bind("timeupdate", function() {

        var stime = video[0].currentTime;
        stime = stime.toString();
        stime = stime.split(".").pop();
        stime = stime.substr(0, 3);

        $('#timing_video').val(fromSeconds(video[0].currentTime) + ':' + stime);
    });


    var clip = findGetParameter("clip");
    if (clip != null) {
        document.getElementById("clip").hidden = false;
    }
</script>