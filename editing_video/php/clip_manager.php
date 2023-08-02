<?php
session_start();

include '../vendor/autoload.php';
include 'functions.php';
include 'db_connection.php';
include 'classes/Video.php';


$pdo = get_connection();
$video = unserialize($_SESSION["video"]);

if(isset($_GET["operation"])){
    switch($_GET["operation"]){
        case "new_clip":
            if (isset($_POST["start_timing_trim"]) && isset($_POST["end_timing_trim"])){
                $start = $_POST["start_timing_trim"];
                $end = $_POST["end_timing_trim"];

                $start_number = getIntTimingScreen($start);
                $end_number = getIntTimingScreen($end);

                $start = str_replace(":", "", $start);
                $end = str_replace(":", "", $end);
                $filename = basename($video->getPath(), ".mp4");
                $clip_name = "clip_$filename"."_$start"."_$end.mp4";

                getClip($pdo, $start_number, $end_number, $clip_name);

                header("Location: ./clip.php?clip=$clip_name");
            }
            break;  
        case "multiple_clip_delete":
                if(isset($_POST["id"])){
                    multipleDelete($pdo);
                }
                header("Location: clips_list.php");
            break;
        default:
			echo "<p>Opzione non riconosciuta</p>";
			echo "<a href=\"../index.php\">Home</a>";
			break;
    }
}


/**
 * @param float $start Il minutaggio iniziale per estrarre clip
 * @param float $end Il minutaggio finale per estrarre clip
 * @param string $clip_name nome della clip, inclusa di estensione
 **/
function getClip($pdo, $start, $end, $clip_name){
    $clip_path = "video/$clip_name";
    try{
        $ffmpeg = FFMpeg\FFMpeg::create();
        $video = $ffmpeg->open("../{$_SESSION["path_video"]}");
        $clip = $video->clip(FFMpeg\Coordinate\TimeCode::fromSeconds($start), FFMpeg\Coordinate\TimeCode::fromSeconds($end-$start));
        $clip->save(new FFMpeg\Format\Video\X264(), "../$clip_path");
    } catch (Exception $e) {echo 'Eccezione: ',  $e->getMessage(), "\n";}


    //$autore = $_SESSION["email_user"];
    $autore = "vincenzo.italiano@gmail.com";
    $video = new Video(null, $clip_path, basename($clip_name, ".mp4"), "Clip del video{$_SESSION["path_video"]}", $autore);
    return insertNewClip($pdo, $video, $_SESSION["path_video"]);
}

function multipleDelete($pdo){
	foreach($_POST["id"] as $el){
        try{
            $video = getVideoFromId($pdo, $el);
            unlink("../{$video->getPath()}");
            deleteVideoFromId($pdo, $el);
        } catch (Exception $e) {echo 'Eccezione: ',  $e->getMessage(), "\n";}
	}
}