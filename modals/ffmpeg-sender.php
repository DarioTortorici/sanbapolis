<?php

require 'vendor/autoload.php';
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;

//Global variable per aiutare nel refactoring
$cartellaStorage = "../video_editing/storage_video/";

/**
 * Cattura un flusso RTSP da una fotocamera e lo salva come file multimediale in formato MPD.
 *
 * @param string $cameraRTSP URL del flusso RTSP della fotocamera.
 * @return void
 */
function ffmpegLive($cameraRTSP)
{
    $ffmpeg = FFMpeg::create();

    $input = $ffmpeg->open($cameraRTSP);
    
    $format = new X264('aac', 'libx264');
    
    $outputFile = '../cameras/live/output.mpd';

    $input->save($format, $outputFile);
}

/**
 * Registra un flusso RTSP utilizzando FFMpeg.
 *
 * Questa funzione cattura un flusso RTSP da una telecamera e lo registra
 * in un file video MP4. Il file di output verrà salvato nella cartella
 * di archiviazione specificata all'interno di una sottocartella
 * corrispondente alla squadra e alla data.
 *
 * @param string $cameraRTSP URL del flusso RTSP della telecamera.
 * @param string $squadra Nome della squadra associata alla registrazione.
 * @param string $data Data in cui è stata effettuata la registrazione (formato: YYYY-MM-DD).
 * @param int $durationToRecord Durata della registrazione in secondi.
 * @return string Il percorso completo del file video MP4 registrato.
 */
function ffmpegRec($cameraRTSP, $squadra, $data, $durationToRecord)
{
    // Crea un'istanza di FFMpeg
    $ffmpeg = FFMpeg::create();
    
    // Ottieni il percorso della cartella di archiviazione dalla variabile globale
    global $cartellaStorage;

    // Costruisci il percorso completo della cartella di output
    $outputFolder = $cartellaStorage . $squadra . '/' . $data . '/';

    // Verifica e crea la cartella di output se non esiste
    if (!is_dir($outputFolder)) {
        mkdir($outputFolder, 0777, true);
    }

    // Specifica il percorso completo del file di output
    $outputPath = $outputFolder . $cameraRTSP . '.mp4';

    // Apri il flusso RTSP come input
    $input = $ffmpeg->open($cameraRTSP);
    
    // Crea un formato di output utilizzando il codec video libx264
    $format = new X264('aac', 'libx264');

    // Inizia la registrazione del flusso nel file di output specificato
    $input->save($format, $outputPath, function () use ($durationToRecord) {
        return [
            '-t', $durationToRecord // Specifica la durata della registrazione
        ];
    });

    // Restituisci il percorso del file di output
    return $outputPath;
}

