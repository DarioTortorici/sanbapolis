<?php

require(__DIR__ . '/../authentication/db_connection.php');
include(__DIR__ . '/ffmpeg-sender.php');
// Funzioni di ABAC che si attivano per il controllo delle telecamere
checkRecordtoStart();
checkRecordtoEnd();


/**
 * Verifica se ci sono record nella tabella 'prenotazioni' che hanno raggiunto il loro orario di inizio
 * e aggiorna il campo 'cam_privileges' degli allenatori corrispondenti a 1.
 * @return void
 */
function checkRecordtoStart()
{
    $con = get_connection();

    $sql = "SELECT *, COUNT(*) as count FROM prenotazioni WHERE data_ora_inizio >= DATE_SUB(NOW(), INTERVAL 5 MINUTE);";
    $query = $con->prepare($sql);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    $count = $result['count'];
    $squadra = $result['id_squadra'];
    $cams = $result['cams'];
    $data = $result['data_ora_inizio'];

    if ($count > 0) {

        //Retrieve allenatori
        $sql = "SELECT email_allenatore FROM allenatori_squadre WHERE id_squadra = :squadra";
        $query = $con->prepare($sql);
        $query->bindParam(':squadra', $squadra);
        $query->execute();
        $coaches = $query->fetchAll(PDO::FETCH_COLUMN); // Utilizziamo FETCH_COLUMN per ottenere solo la colonna 'email_allenatore'

        //Retrieve ip selected cameras [TO DO]
        $cameraRTSP = $cams; // Simulo $cams come array deglii indirizzi RTSP delle telecamere

        //ffmpeg parte video-rec per ogni telecamera
        foreach ($cameraRTSP as $rtsp) {
            ffmpegRec($rtsp,$squadra,$data);
        }

        // Aggiorna permessi dello staff per ogni allenatore
        foreach ($coaches as $coach) {
            $sql = "UPDATE allenatori SET cam_privileges = 1 WHERE email = :allenatore";
            $query = $con->prepare($sql);
            $query->bindParam(':allenatore', $coach);
            $query->execute();
        }
    }
}

/**
 * Verifica se ci sono record nella tabella 'prenotazioni' che hanno raggiunto il loro orario di fine
 * e aggiorna il campo 'cam_privileges' degli allenatori corrispondenti a 0.
 * @return void
 */
function checkRecordtoEnd()
{
    $con = get_connection();

    $sql = "SELECT *, COUNT(*) as count FROM prenotazioni WHERE data_ora_fine <= DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
    $query = $con->prepare($sql);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    $count = $result['count'];
    $squadra = $result['id_squadra'];
    $cams = $result['cams'];
    $data = $result['data_ora_inizio'];
    $idCalendar = $result['id_calendar_events'];

    if ($count > 0) {
        //Retrieve allenatori
        $sql = "SELECT email_allenatore FROM allenatori_squadre WHERE id_squadra = :squadra";
        $query = $con->prepare($sql);
        $query->bindParam(':squadra', $squadra);
        $query->execute();
        $coaches = $query->fetchAll(PDO::FETCH_COLUMN); // Utilizziamo FETCH_COLUMN per ottenere solo la colonna 'email_allenatore'

        //Retrieve ip selected cameras [TO DO]
        $cameraRTSP = $cams; // Simulo $cams come array deglii indirizzi RTSP delle telecamere

        //ffmpeg parte video-rec per ogni telecamera
        foreach ($cameraRTSP as $rtsp) {
            $directory = ffmpegStopRec($rtsp,$squadra,$data);
        }

        // Aggiorna permessi dello staff per ogni allenatore
        foreach ($coaches as $coach) {
            $sql = "UPDATE allenatori SET cam_privileges = 0 WHERE email = :allenatore";
            $query = $con->prepare($sql);
            $query->bindParam(':allenatore', $coach);
            $query->execute();
        }

        //Imposta la directory dove sono presenti i file
        $sql = "UPDATE calendar_events SET url = :dir WHERE id = :idCalendar";
            $query = $con->prepare($sql);
            $query->bindParam(':dir', $directory);
            $query->bindParam(':idCalendar', $idCalendar);
            $query->execute();
    }
}
