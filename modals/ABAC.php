<?php

require(__DIR__ . '/../authentication/db_connection.php');

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

    $sql = "SELECT COUNT(*) As count, autore_prenotazione FROM prenotazioni WHERE data_ora_inizio >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
    $query = $con->prepare($sql);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    $count = $result['count'];

    if ($count > 0) {
        $sql = "UPDATE allenatori SET cam_privileges = 1 WHERE email = :allenatore";
        $query = $con->prepare($sql);
        $query->bindParam(':allenatore', $result['autore_prenotazione']);
        $query->execute();
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

    $sql = "SELECT COUNT(*) AS count, autore_prenotazione FROM prenotazioni WHERE data_ora_fine <= DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
    $query = $con->prepare($sql);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);


    $count = $result['count'];

    if ($count > 0) {
        $sql = "UPDATE allenatori SET cam_privileges = 0 WHERE email = :allenatore";
        $query = $con->prepare($sql);
        $query->bindParam(':allenatore', $result['autore_prenotazione']);
        $query->execute();
    }
}
