<?php
session_start();

include('../../modals/header.php');
include_once("../../modals/navbar.php");
include '../../vendor/autoload.php';
include '../video-helper.php';
include '../../classes/Screen.php';

if(isset($_GET["video"])){
    $videoPath = $_GET["video"];
}

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
                getScreen($videoPath, $filename, $timing_screen, $timing_screen_string, $pdo);
            } catch (Exception $e) {echo 'Eccezione: ',  $e->getMessage(), "\n";}
            header("Location: ../editing_video.php?video=" . $videoPath . "&timing_screen=" . urlencode($timing_screen));
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

/**
 * Crea uno screenshot da un video e salva le informazioni nel database.
 *
 * Questa funzione riceve come parametri il percorso del video, il nome del file, il timing
 * dello screenshot in secondi, la rappresentazione in formato stringa del timing, e un
 * oggetto PDO per la connessione al database. Utilizza la libreria FFMpeg per aprire il
 * video e creare uno screenshot al tempo specificato. Quindi, genera un nome per lo
 * screenshot utilizzando la funzione "generateScreenName" e salva l'immagine nel server.
 * Successivamente, salva le informazioni dello screenshot nel database, inclusa la
 * locazione del file e il nome dell'immagine.
 *
 * @param string $videoPath - Il percorso del video da cui estrarre lo screenshot.
 * @param string $filename - Il nome del file video.
 * @param float $timing_screen - Il timing dello screenshot in secondi.
 * @param string $timing_screen_string - Il timing dello screenshot come stringa.
 * @param PDO $pdo - L'oggetto PDO utilizzato per la connessione al database.
 */
function getScreen($videoPath, $filename, $timing_screen, $timing_screen_string, $pdo){
    $ffmpeg = FFMpeg\FFMpeg::create();
    $video = $ffmpeg->open($videoPath);
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
        ':video' => $videoPath
    ]);
}

/**
 * Aggiorna un record di screenshot nel database con i nuovi dati forniti.
 *
 * Questa funzione riceve come parametro un oggetto PDO per la connessione al database.
 * Recupera l'ID dello screenshot da aggiornare dalla query string ($_GET) e i nuovi
 * dati dello screenshot dai dati inviati tramite il metodo POST. Viene quindi creato un
 * nuovo oggetto "Screen" con i dati forniti e l'ID recuperato. Infine, la funzione
 * "updateScreenFromId" viene richiamata per eseguire l'aggiornamento nel database.
 *
 * @param PDO $pdo - L'oggetto PDO utilizzato per la connessione al database.
 * @return bool - True se l'aggiornamento è avvenuto con successo, altrimenti false.
 */
function updateScreen($pdo){
    $id = $_GET["id"];
    $name = ($_POST["screen_name"] == "") ? null : $_POST["screen_name"];
    $note = ($_POST["screen_note"] == "") ? null : $_POST["screen_note"];
    $screen = new Screen(null, $name, $note, $id, null);
    return updateScreenFromId($pdo, $screen);
}

/**
 * Elimina uno screenshot dal database e il file corrispondente dal server.
 *
 * Questa funzione riceve come parametro un oggetto PDO per la connessione al database
 * e l'ID dello screenshot da eliminare. Recupera la posizione del file dello screenshot
 * dal database e quindi richiama la funzione "deleteScreenFromId" per eliminare l'elemento
 * dal database. Infine, elimina anche il file dello screenshot dal server utilizzando la
 * posizione del file recuperata dal database.
 *
 * @param PDO $pdo - L'oggetto PDO utilizzato per la connessione al database.
 * @param int $id - L'ID dello screenshot da eliminare.
 * 
 * @see deleteScreenFromId
 */
function deleteScreen($pdo, $id) {
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


/**
 * Elimina più elementi dal database utilizzando l'ID fornito.
 *
 * Questa funzione accetta un array di ID di elementi da eliminare. Per ciascun ID nell'array,
 * viene richiamata la funzione "deleteScreen" per eliminare l'elemento corrispondente dal database.
 *
 * @param PDO $pdo - L'oggetto PDO utilizzato per la connessione al database.
 * @param array $idArray - Un array contenente gli ID degli elementi da eliminare.
 *
 */
function multipleDelete($pdo, $idArray) {
    foreach ($idArray as $el) {
        deleteScreen($pdo, $el);
    }
}