<?php

include '../../vendor/autoload.php';
include '../../modals/header.php';
include_once '../../modals/navbar.php';
include 'video-editing-helper.php';;
include '../../classes/Video.php';

include 'error-checker.php';

$pdo = get_connection();

/*
$video = null;
$person = null;

if(isset($_SESSION["video"])){
    $video = unserialize($_SESSION["video"]);
}
if(isset($_SESSION["person"])){
    $person = unserialize($_SESSION["person"]);
    //myVarDump($person);
}
else{
    header("Location: ../".INDEX);
}*/

if(isset($_GET["operation"])){
    switch($_GET["operation"]){
        case "select_video":
            select($pdo);
            header("Location: ".VIDEO_DETAILS);
            break;
        case "new_video":
            break;  
        case "update_video":
            update($pdo, $video, $person);
            header("Location: editing_video.php?update=1" );
            break;
        case "delete_video":
            delete($pdo, $video);
            header("Location: " . getPreviusPage() . "?video_deleted=true");
            break;
        case "multiple_video_delete":
            multipleDelete($pdo);
            header("Location: " . getPreviusPage() . "?videos_deleted=true");
            break;
        default:
			break;
    }
}

function select($pdo){
    if(isset($_GET["id"])){
        try{
            $id = $_GET["id"];
            $video = getVideoFromId($pdo, $id);
            $_SESSION["video"] = serialize($video);
        } catch (Exception $e) {echo 'Eccezione: ',  $e->getMessage(), "\n";}
    }
}

function update($pdo, $video, $person){
    try{
        $name = ($_POST["video_name"] == "") ? null : $_POST["video_name"];
        $note = ($_POST["video_note"] == "") ? null : $_POST["video_note"];
        $video = new Video($video->getId(), $video->getPath(), $name, $note, $person->getEmail(), $video->getSession());
        updateVideo($pdo, $video);
    } catch (Exception $e) {echo 'Eccezione: ',  $e->getMessage(), "\n";}
}

function delete($pdo, $video){
    try{
        if (deleteFile("../{$video->getPath()}")){
            deleteVideoFromId($pdo, $video->getId());
            $_SESSION["video"] = null;
        }
    } catch (Exception $e) {echo 'Eccezione: ',  $e->getMessage(), "\n";}
}

function multipleDelete($pdo){
	foreach($_POST["id"] as $el){
        try{
            $video = getVideoFromId($pdo, $el);
            if (deleteFile("../{$video->getPath()}")){
                deleteVideoFromId($pdo, $el);
            }
        } catch (Exception $e) {echo 'Eccezione: ',  $e->getMessage(), "\n";}
	}
}