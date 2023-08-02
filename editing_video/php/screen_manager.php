<?php
session_start();

include '../vendor/autoload.php';
include 'functions.php';
include 'db_connection.php';
include 'classes/Screen.php';

$path_video = "../" . $_SESSION["path_video"];
$filename = $_SESSION["name_file_video"];
/*var_dump($_SESSION["video"]);
$video = unserialize($_SESSION["video"]);
var_dump($video);
$filename = basename($video->getPath(), ".mp4");*/

$pdo = get_connection();

if(isset($_GET["operation"])){
    switch ($_GET["operation"]){
        case "get_screen":
            try{                
                $timing_screen_string = $_POST["timing_video"];
                $timing_screen = getIntTimingScreen($timing_screen_string);
                getScreen($path_video, $filename, $timing_screen, $timing_screen_string, $pdo);
            } catch (Exception $e) {echo 'Eccezione: ',  $e->getMessage(), "\n";}
            header("Location: editing_video.php?timing_screen=$timing_screen");
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
            header("Location: ./screenshots_list.php");
            break;

    }
}

//funzioni locali, solo per questo file
function getScreen($path_video, $filename, $timing_screen, $timing_screen_string, $pdo){
    $ffmpeg = FFMpeg\FFMpeg::create();
    $video = $ffmpeg->open($path_video);
    $screen_name = generateScreenName($filename, $timing_screen_string);
    $video
        ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($timing_screen))
        ->save("../" . $screen_name);
     
    //$img_name = substr($screen_name, strpos($screen_name, "/") + 1);
    $img_name = basename($screen_name, ".jpg");
    $query = 'INSERT INTO screenshots(locazione, nome, video) VALUES (:locazione, :nome, :video)';
    $statement = $pdo->prepare($query);
    $statement->execute([
        ':locazione' => $screen_name,
        ':nome' => $img_name,
        ':video' => $_SESSION["path_video"],
    ]);
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