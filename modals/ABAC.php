<?php

require(__DIR__ . '/../authentication/db_connection.php');
include(__DIR__ . '/ffmpeg-sender.php');
// Funzioni di ABAC che si attivano per il controllo delle telecamere
checkRecordtoStart();
checkRecordtoEnd();


/**
 * Controlla e avvia la registrazione delle prenotazioni per le telecamere.
 *
 * Questa funzione verifica le prenotazioni attive e avvia la registrazione dei flussi video
 * per le telecamere associate a ciascuna prenotazione. Aggiorna anche i permessi dei
 * membri dello staff (allenatori) per visualizzare le telecamere registrate.
 *
 * @return void
 */
function checkRecordtoStart()
{
    $con = get_connection();

    $sql = "SELECT p.*, GROUP_CONCAT(t.indirizzo_ipv4) as IPv4, COUNT(*) as count 
            FROM prenotazioni as p 
            INNER JOIN telecamere_prenotazioni as tp ON tp.prenotazione = p.id 
            INNER JOIN telecamere as t on t.id = tp.telecamera
            WHERE p.data_ora_inizio >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            GROUP BY p.id";

    $query = $con->prepare($sql);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
        $count = $row['count'];
        $squadra = $row['id_squadra'];
        $cams = explode(",", $row['IPv4']);
        $data = $row['data_ora_inizio'];

        if ($count > 0) {
            // Calcola la durata in secondi
            $duration = calculateTimeDifferenceInSeconds($data, $row['data_ora_fine']);

            // Retrieve allenatori
            $sql = "SELECT email_allenatore FROM allenatori_squadre WHERE id_squadra = :squadra";
            $query = $con->prepare($sql);
            $query->bindParam(':squadra', $squadra);
            $query->execute();
            $coaches = $query->fetchAll(PDO::FETCH_COLUMN);

            // FFMpeg: avvia registrazione video per ogni telecamera
            foreach ($cams as $Camerartsp) {
                $rtsp = 'rtsp://istar:password@' . $Camerartsp . '/profile2/media.smp';
                ffmpegRec($rtsp, $squadra, $data, $duration);
            }

            // Aggiorna permessi dello staff per ogni allenatore
            foreach ($coaches as $coach) {
                $sql = "UPDATE allenatori SET privilegi_cam = 1 WHERE email = :allenatore";
                $query = $con->prepare($sql);
                $query->bindParam(':allenatore', $coach);
                $query->execute();
            }
        }
    }
}


/**
 * Controlla e gestisce le prenotazioni scadute per chiudere le registrazioni video.
 * 
 * La funzione recupera le prenotazioni con data di fine inferiore a 5 minuti fa ed
 * aggiorna i permessi dello staff e aggiunge i dettagli dei video registrati nel database.
 * 
 * @return void
 */
function checkRecordtoEnd()
{
    $con = get_connection();

    // Query per recuperare le prenotazioni scadute e le relative telecamere
    $sql = "SELECT p.*, GROUP_CONCAT(tp.telecamera) as telecamere, COUNT(*) as count 
        FROM prenotazioni as p 
        INNER JOIN telecamere_prenotazioni as tp ON tp.prenotazione = p.id 
        WHERE p.data_ora_fine <= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        GROUP BY p.id";
    $query = $con->prepare($sql);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
        // Estrai le informazioni dalla riga risultante
        $count = $row['count'];
        $squadra = $row['id_squadra'];
        $cams = $row['telecamere']; // $cams conterrà un elenco di telecamere separate da virgole
        $data = $row['data_ora_inizio'];
        $idCalendar = $row['id_calendar_events'];
    }

    if ($count > 0) {
        // Recupera gli allenatori associati alla squadra
        $sql = "SELECT email_allenatore FROM allenatori_squadre WHERE id_squadra = :squadra";
        $query = $con->prepare($sql);
        $query->bindParam(':squadra', $squadra);
        $query->execute();
        $coaches = $query->fetchAll(PDO::FETCH_COLUMN); // Utilizziamo FETCH_COLUMN per ottenere solo la colonna 'email_allenatore'

        // Recupera gli indirizzi RTSP delle telecamere selezionate [TO DO]
        $cameraRTSP = $cams; // Simuliamo $cams come array di indirizzi RTSP delle telecamere

        // Aggiorna i permessi dello staff
        foreach ($coaches as $coach) {
            $sql = "UPDATE allenatori SET privilegi_cam = 0 WHERE email = :allenatore";
            $query = $con->prepare($sql);
            $query->bindParam(':allenatore', $coach);
            $query->execute();
        }
        
        $path = "../video_editing/storage_video/". $squadra . '/' . $data . '/';
        // Aggiorna l'URL nel calendario dell'evento con la pagina per visualizzare i file
        $watchCams = "/editing_video/video_list.php?video_location=./storage_video/" . $path . "&register_date=" . $data;
        $sql = "UPDATE calendar_events SET url = :dir WHERE id = :idCalendar";
        $query = $con->prepare($sql);
        $query->bindParam(':dir', $watchCams);
        $query->bindParam(':idCalendar', $idCalendar);
        $query->execute();

        // Registra i file video nella tabella 'video'
        $query = "INSERT INTO video (locazione) VALUES (:dir)";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':dir', $path);
        $stmt->execute();
    }
}

/**
 * Calcola la differenza in secondi tra due date.
 *
 * Questa funzione accetta due date nel formato "YYYY-MM-DD HH:MM:SS" e calcola
 * la differenza in secondi tra le due date, considerando giorni, ore, minuti e secondi.
 *
 * @param string $date1 La prima data nel formato "YYYY-MM-DD HH:MM:SS".
 * @param string $date2 La seconda data nel formato "YYYY-MM-DD HH:MM:SS".
 * @return int La differenza in secondi tra le due date.
 */
function calculateTimeDifferenceInSeconds($date1, $date2)
{
    $dateTime1 = new DateTime($date1);
    $dateTime2 = new DateTime($date2);
    $interval = $dateTime1->diff($dateTime2);

    $secondsDifference = $interval->days * 24 * 60 * 60 +
        $interval->h * 60 * 60 +
        $interval->i * 60 +
        $interval->s;

    return $secondsDifference;
}
