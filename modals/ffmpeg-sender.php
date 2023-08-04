<?php

//Global variable per aiutare nel refactoring
$cartellaStorage = "../video_editing/storage_video/";

/**
 * Avvia lo streaming live utilizzando FFmpeg.
 *
 * @param string $cameraRTSP L'URL RTSP della videocamera
 * @return void
 */
function ffmpegLive($cameraRTSP)
{
    $comando = 'ffmpeg -y -i' . $cameraRTSP . '-c:v libx264 -c:a aac -f dash -seg_duration 10 -window_size 6 -dash_segment_type mp4 ../cameras/live/output.mpd > /dev/null 2>&1 &';
    exec($comando);
}

/**
 * Avvia la registrazione di un video utilizzando FFmpeg e salva il file di output nella cartella specificata.
 *
 * @param string $cameraRTSP L'URL RTSP della videocamera
 * @param string $squadra Il nome della squadra
 * @param string $data La data della registrazione
 * @return void
 */
function ffmpegRec($cameraRTSP, $squadra, $data)
{
    global $cartellaStorage;
    $outputFolder = $cartellaStorage.$squadra.'/'.$data.'/';

    // Verifica se la cartella di output esiste
    if (!is_dir($outputFolder)) {
        // Crea la cartella di output se non esiste
        mkdir($outputFolder, 0777, true);
    }

    // Specifica il percorso completo del file di output
    $outputPath = $outputFolder . $cameraRTSP . '.mp4';

    // Comando FFmpeg con la cartella di output corretta
    $comando = 'ffmpeg -y -r 25 -rtsp_transport tcp -i ' . $cameraRTSP . ' -vcodec copy -b:v 10000k ' . $outputPath;
    shell_exec($comando);
}

/**
 * Interrompe la registrazione di un video in corso utilizzando FFmpeg e restituisce il percorso del file di output.
 *
 * @param string $cameraRTSP L'URL RTSP della videocamera
 * @param string $squadra Il nome della squadra
 * @param string $data La data della registrazione
 * @return string Il percorso completo del file di output
 */
function ffmpegStopRec($cameraRTSP, $squadra, $data)
{
    global $cartellaStorage;
    $outputFolder = $cartellaStorage . $squadra . '/' . $data . '/';
    $outputPath = $outputFolder . $cameraRTSP . '.mp4';
    $comando = 'pkill -f "ffmpeg -y -r 25 -rtsp_transport tcp -i ' . $cameraRTSP . ' -vcodec copy -b:v 10000k ' . $outputPath . '"';
    shell_exec($comando);
    return $outputPath;
}

