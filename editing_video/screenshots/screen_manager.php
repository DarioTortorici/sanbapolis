<?php
session_start();

include '../../vendor/autoload.php';
include '../../authentication/db_connection.php';
include '../editing/video-editing-helper.php';
include '../../classes/Screen.php';
include '../../classes/Video.php';
include '../../classes/Person.php';

include '../editing/error-checker.php';


if(isset($video)){
    $filename = basename($video->getPath(), ".mp4");
}

$pdo = get_connection();

if(isset($_GET["operation"])){
    switch ($_GET["operation"]){
        case "get_screen":
            try{                
                $timing_screen_string = $_POST["timing_video"];
                $timing_screen = getIntTimingScreen($timing_screen_string);
                getScreen($video->getPath(), $filename, $timing_screen, $timing_screen_string, $pdo);
            } catch (Exception $e) {echo 'Eccezione: ',  $e->getMessage(), "\n";}
            header("Location: ../editing/editing_video.php?timing_screen=$timing_screen");
            break;
        case "update_screen":
            try{  
                $ok = "true";
                if(!updateScreen($pdo)){
                    $ok = "false";
                }
                header("Location: screen_details.php?id={$_GET["id"]}&updated=$ok");
            } catch (Exception $e) {echo 'Eccezione: ',  $e->getMessage(), "\n";}
            break;
        case "delete_screen":
            if(isset($_GET["id"])){
                deleteScreen($pdo, $_GET["id"]);
            }
            header("Location: screen_details.php?id={$_GET["id"]}&screen_deleted=true");
            break;
        case "multiple_screen_delete":
            if(isset($_POST["id"])){
                multipleDelete($pdo);
            }
            header("Location: ../editing/editing_video.php?update=1");
            break;

    }
}

//funzioni locali, solo per questo file
function getScreen($path_video, $filename, $timing_screen, $timing_screen_string, $pdo){
    $ffmpeg = FFMpeg\FFMpeg::create();
    $video = $ffmpeg->open("../$path_video");
    
    $screen_name = generateScreenName($filename, $timing_screen_string);
    $video
        ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($timing_screen))
        ->save("../" . $screen_name);
    
    $name = basename($screen_name, ".jpg");
    $note = "Screenshots del video $path_video";
    $screen = new Screen($screen_name, $name, $note, NULL, $path_video);
    insertNewScreen($pdo, $screen);
}

function updateScreen($pdo){
    $id = $_GET["id"];
    $name = ($_POST["screen_name"] == "") ? null : $_POST["screen_name"];
    $note = ($_POST["screen_note"] == "") ? null : $_POST["screen_note"];
    $screen = new Screen(null, $name, $note, $id, null);
    return updateScreenFromId($pdo, $screen);
}

function deleteScreen($pdo, $id){
    $path_screen = "";
    $query = "SELECT locazione FROM screenshots WHERE id=$id";
    $statement = $pdo->query($query);
    $publishers = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($publishers) {
        foreach ($publishers as $publisher) {
            $path_screen = $publisher['locazione'];
        }
    }
    deleteScreenFromId($pdo, $id);
    unlink("../$path_screen");
}

function multipleDelete($pdo){
    foreach($_POST["id"] as $el){
        deleteScreen($pdo, $el);
    }
}