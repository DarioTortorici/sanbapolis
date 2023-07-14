<?php

/**
 * Avvia lo streaming live utilizzando FFmpeg.
 *
 * @param string $cameraRTSP L'URL RTSP della videocamera
 * @return void
 */
function ffmpegLive($cameraRTSP) {
    $comando = 'ffmpeg -y -i'. $cameraRTSP.'-c:v libx264 -c:a aac -f dash -seg_duration 10 -window_size 6 -dash_segment_type mp4 output.mpd > /dev/null 2>&1 &';
    exec($comando);
}

/**
 * Registra un video utilizzando FFmpeg.
 *
 * @param string $cameraRTSP L'URL RTSP della videocamera
 * @return void
 */
function ffmpegRec($cameraRTSP) {
    $comando = 'ffmpeg -y -r 25 -rtsp_transport tcp -i' .$cameraRTSP. ' -vcodec copy -b:v 10000k out1.mp4';
    shell_exec($comando);
}

/**
 * Interrompe la registrazione di un video in corso utilizzando FFmpeg.
 *
 * @param string $cameraRTSP L'URL RTSP della videocamera
 * @return void
 */
function ffmpegStopRec($cameraRTSP) {
    $comando = 'pkill -f "ffmpeg -y -r 25 -rtsp_transport tcp -i ' . $cameraRTSP . ' -vcodec copy -b:v 10000k out1.mp4"';
    shell_exec($comando);
}

?>
