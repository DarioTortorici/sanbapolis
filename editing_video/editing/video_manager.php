<?php

include '../../vendor/autoload.php';
include '../../modals/header.php';
include_once '../../modals/navbar.php';
include 'video-editing-helper.php';;
include '../../classes/Video.php';

include 'error-checker.php';

$pdo = get_connection();

if(isset($_GET["operation"])){
    switch($_GET["operation"]){
        case "select_video":
            select($pdo);
            header("Location: ".EDITING_VIDEO);
            exit();
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

/**
 * Selezione del video tramite ID e memorizzazione nella sessione.
 *
 * Questa funzione verifica se è presente il parametro 'id' nella query string.
 * Se il parametro è presente, viene utilizzato per ottenere l'oggetto Video
 * corrispondente dall'ID fornito e l'oggetto Video viene serializzato e memorizzato nella sessione.
 *
 * @param PDO $pdo L'oggetto PDO per la connessione al database.
 * @return void
 */
function select($pdo) {
    if (isset($_GET["id"])) {
        try {
            $id = $_GET["id"];  // Ottiene l'ID del video dalla query string.
            $video = getVideoFromId($pdo, $id);  // Ottiene l'oggetto Video corrispondente all'ID.
            $_SESSION["video"] = serialize($video);  // Serializza e memorizza l'oggetto Video nella sessione.
        } catch (Exception $e) {
            echo 'Eccezione: ', $e->getMessage(), "\n";  // Gestisce eventuali eccezioni.
        }
    }
}

/**
 * Aggiornamento delle informazioni del video nel database.
 *
 * Questa funzione gestisce l'aggiornamento delle informazioni del video nel database.
 * Vengono prese le nuove informazioni fornite tramite POST, come il nome e le note del video.
 * Se i campi sono vuoti, vengono impostati a null. Successivamente, l'oggetto Video viene
 * aggiornato con le nuove informazioni e il database viene aggiornato con la nuova versione dell'oggetto.
 *
 * @param PDO $pdo L'oggetto PDO per la connessione al database.
 * @param Video $video L'oggetto Video da aggiornare.
 * @param Person $person L'oggetto Person che rappresenta l'autore del video.
 * @return void
 */
function update($pdo, $video, $person) {
    try {
        $name = ($_POST["video_name"] == "") ? null : $_POST["video_name"];  // Ottiene il nome del video dalla richiesta POST.
        $note = ($_POST["video_note"] == "") ? null : $_POST["video_note"];  // Ottiene le note del video dalla richiesta POST.
        
        // Crea un nuovo oggetto Video con le informazioni aggiornate.
        $updatedVideo = new Video($video->getId(), $video->getPath(), $name, $note, $person->getEmail(), $video->getSession());
        
        updateVideo($pdo, $updatedVideo);  // Aggiorna il video nel database con le nuove informazioni.
    } catch (Exception $e) {
        echo 'Eccezione: ', $e->getMessage(), "\n";  // Gestisce eventuali eccezioni.
    }
}

/**
 * Eliminazione del video dal database e dal sistema di file.
 *
 * Questa funzione gestisce l'eliminazione del video sia dal sistema di file che dal database.
 * Vengono prese le informazioni dell'oggetto Video e il percorso del file viene utilizzato per eliminarlo.
 * Successivamente, l'oggetto Video viene rimosso dal database e la variabile di sessione relativa al video viene resettata.
 *
 * @param PDO $pdo L'oggetto PDO per la connessione al database.
 * @param Video $video L'oggetto Video da eliminare.
 * @return void
 */
function delete($pdo, $video) {
    try {
        // Elimina il file dal sistema di file utilizzando il percorso.
        if (deleteFile("../{$video->getPath()}")) {
            deleteVideoFromId($pdo, $video->getId());  // Rimuove il video dal database.
            $_SESSION["video"] = null;  // Resetta la variabile di sessione relativa al video.
        }
    } catch (Exception $e) {
        echo 'Eccezione: ', $e->getMessage(), "\n";  // Gestisce eventuali eccezioni.
    }
}

/**
 * Eliminazione multipla di video dal database e dal sistema di file.
 *
 * Questa funzione gestisce l'eliminazione di video multipli sia dal sistema di file che dal database.
 * Utilizza un array di ID video forniti tramite POST per individuare e eliminare i video corrispondenti.
 * Per ciascun ID, viene ottenuto l'oggetto Video e il percorso del file viene utilizzato per eliminarlo.
 * Successivamente, i video vengono rimossi dal database.
 *
 * @param PDO $pdo L'oggetto PDO per la connessione al database.
 * @return void
 */
function multipleDelete($pdo) {
    foreach ($_POST["id"] as $el) {
        try {
            $video = getVideoFromId($pdo, $el);  // Ottiene l'oggetto Video corrispondente all'ID.
            
            // Elimina il file dal sistema di file utilizzando il percorso.
            if (deleteFile("../{$video->getPath()}")) {
                deleteVideoFromId($pdo, $el);  // Rimuove il video dal database.
            }
        } catch (Exception $e) {
            echo 'Eccezione: ', $e->getMessage(), "\n";  // Gestisce eventuali eccezioni.
        }
    }
}