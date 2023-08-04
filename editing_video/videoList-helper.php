<?php

require_once __DIR__ . '/../authentication/db_connection.php';

function getPlaylist($videoUrl)
{
    $videoUrl = "./storage_video/basket_test_1.mp4";
    $con = get_connection();

    $sql = "SELECT video
            FROM sessioni_registrazione
            WHERE id = (SELECT id FROM sessioni_registrazione WHERE video = ?)";
    $query = $con->prepare($sql);
    $query->execute([$videoUrl]);
    $result = $query->fetchAll();

    return $result;
}

///////////////////////////
// GET e POST Management //
///////////////////////////

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'get-playlist') {
        $videoUrl = isset($_GET['video']) ? $_GET['video'] : null;

        $playlist = getPlaylist($videoUrl);

        if ($playlist !== false && $playlist !== null) {
            header('Content-Type: application/json');
            echo json_encode($playlist);
        } else {
            // Playlist non trovata o vuota, restituisci un messaggio d'errore
            header('Content-Type: application/json');
            echo json_encode(array('error' => 'Playlist non trovata o vuota.'));
        }
    }
}
?>
