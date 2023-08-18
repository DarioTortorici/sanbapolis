<?php

include '../../modals/header.php';
include_once '../../modals/navbar.php';
include_once '../../vendor/autoload.php';
include_once '../editing/video-editing-helper.php';
include '../../classes/Video.php';
include '../editing/error-checker.php';

$pdo = get_connection();



if (isset($_GET["operation"])) {
    switch ($_GET["operation"]) {
        case "new_clip":
            if (isset($_POST["start_timing_trim"]) && isset($_POST["end_timing_trim"])) {
                $clip = newClip($pdo, $video, $person);
                header("Location ../editing/editing_video.php?update=1");
                }
            break;
        case "multiple_clip_delete":
            if (isset($_POST["id"])) {
                multipleDelete($pdo);
            }
            header("Location: ../editing/editing_video.php?update=1");
            break;
        default:
            echo "<p>Opzione non riconosciuta</p>";
            echo "<a href=\"../" . INDEX . "\">Home</a>";
            break;
    }
}


/**
 * Creazione e salvataggio di un nuovo clip video nel sistema.
 *
 * Questa funzione gestisce la creazione di una nuova clip video dal video originale.
 * Vengono forniti il tempo di inizio e fine della clip tramite POST, e vengono calcolati
 * anche i numeri corrispondenti alle schermate di inizio e fine. La nuova clip viene generata,
 * salvata e inserita nel database. Viene restituito l'oggetto Video rappresentante essa.
 *
 * @param PDO $pdo L'oggetto PDO per la connessione al database.
 * @param Video $video L'oggetto Video del video originale.
 * @param Person $person L'oggetto Person che rappresenta l'autore della clip.
 * @return Video L'oggetto Video rappresentante la nuova clip.
 */
function newClip($pdo, $video, $person) {
    $start = $_POST["start_timing_trim"];
    $end = $_POST["end_timing_trim"];
    $start_number = getIntTimingScreen($start);  // Ottiene il numero corrispondente alla schermata di inizio.
    $end_number = getIntTimingScreen($end);  // Ottiene il numero corrispondente alla schermata di fine.

    $start = str_replace(":", "", $start);  // Rimuove i due punti dal tempo di inizio.
    $end = str_replace(":", "", $end);  // Rimuove i due punti dal tempo di fine.

    $filename = basename($video->getPath(), ".mp4");
    $clip_name = "clip_$filename" . "_$start" . "_$end.mp4";  // Crea il nome del nuovo clip.

    getClip($start_number, $end_number, $clip_name, $video);  // Genera la nuova clip.

    // Crea un oggetto Video per la nuova clip.
    $clip = new Video(
        null,
        "storage_video/$clip_name",
        basename($clip_name, ".mp4"),
        "Clip del video {$video->getPath()}",
        $person->getEmail(),
        $video->getSession()
    );

    insertNewClip($pdo, $clip, $video->getPath());  // Inserisce la nuova clip nel database.

    return $clip;  // Restituisce l'oggetto Video rappresentante la nuova clip.
}

/**
 * Generazione di un nuovo clip video.
 *
 * Questa funzione gestisce la generazione di un nuovo clip video a partire dal video originale.
 * Vengono forniti il tempo di inizio e fine della clip, il nome della clip e l'oggetto Video originale.
 * La nuova clip viene creata utilizzando la libreria FFMpeg e salvato nel percorso specificato.
 *
 * @param int $start Il tempo di inizio della clip in secondi.
 * @param int $end Il tempo di fine della clip in secondi.
 * @param string $clip_name Il nome della nuova clip.
 * @param Video $video L'oggetto Video originale.
 * @return void
 */
function getClip($start, $end, $clip_name, $video) {
    $clip_path = "../storage_video/$clip_name";  // Percorso completo per la nuova clip.

    try {
        $ffmpeg = FFMpeg\FFMpeg::create();  // Crea un oggetto FFMpeg.
        $video = $ffmpeg->open("../{$video->getPath()}");  // Apre l'oggetto Video originale.
        
        // Crea la nuova clip utilizzando i tempi di inizio e fine specificati.
        $clip = $video->clip(FFMpeg\Coordinate\TimeCode::fromSeconds($start), FFMpeg\Coordinate\TimeCode::fromSeconds($end - $start));
        
        // Salva la nuova clip nel formato X264 nel percorso specificato.
        $clip->save(new FFMpeg\Format\Video\X264(), $clip_path);
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
            unlink("../{$video->getPath()}");  // Elimina il file del video dal sistema di file.
            deleteVideoFromId($pdo, $el);  // Rimuove il video dal database.
        } catch (Exception $e) {
            echo 'Eccezione: ', $e->getMessage(), "\n";  // Gestisce eventuali eccezioni.
        }
    }
}
