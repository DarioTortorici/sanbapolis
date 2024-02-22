<?php

require_once __DIR__ . '/../authentication/db_connection.php';
require_once __DIR__ . '/../authentication/auth-helper.php';
require_once __DIR__ . '/../modals/email-handler.php';

/**
 * Recupera l'evento attualmente in corso dalla tabella "calendar_events" del database.
 *
 * @param string $date La data del giorno corrente
 * @param string $hour L'ora corrente
 * @return string Una stringa JSON che rappresenta l'evento. Se non ci sono eventi, la stringa JSON sarà vuota.
 * 
 * @throws PDOException Se si verifica un errore durante la query al database.
 */
function getCurrentEvent($date,$hour)
{
    $con = get_connection();

    try {
        $query = "SELECT id, startRecur, startTime, endTime FROM calendar_events WHERE startRecur = :startRecur AND startTime <= :curhour AND endTime > :curhour limit 1";
        $statement = $con->prepare($query);
        $statement->bindParam(':startRecur', $date);
        $statement->bindParam(':curhour', $hour);
        $statement->execute();
        $events = $statement->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($events);
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il recupero degli eventi: " . $e->getMessage());
    }
}

/**
 * Recupera le telecamere associate ad un evento dalla tabella "prenotazioni" del database, in base all'ID dell'evento fornito.
 *
 * @param int $id L'ID dell'evento per il quale si desidera recuperare le telecamere.
 * @return string Una stringa JSON contenente la lista di telecamere associate all'evento.
 * 
 * @throws PDOException Se si verifica un errore durante la query al database.
 */
function getCameras($id)
{
    $con = get_connection();

    try {
        $sql = "SELECT id FROM prenotazioni WHERE id_calendar_events=?";
        $query = $con->prepare($sql);
        $query->execute([$id]);
        $prenotazioni_id = $query->fetchColumn();

        // Controlla se $prenotazioni_id ha un valore valido prima di procedere
        if ($prenotazioni_id) {
            $query = "SELECT telecamera FROM telecamere_prenotazioni WHERE prenotazione = :id";
            $statement = $con->prepare($query);
            $statement->bindParam(':id', $prenotazioni_id);
            $statement->execute();
            $cams = $statement->fetchAll(PDO::FETCH_ASSOC);

            return json_encode($cams);
        } else {
            return json_encode([]);
        }
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il recupero delle telecamere dell'evento: " . $e->getMessage());
    }
}

/**
 * Ottengo i secondi precisi con cui devo iniziare lo streaming
 * Controllo anche se sono fuori range o meno
 * 
 * @param string $start_streaming_time Indica l'orario di inizio
 * @param string $curr_streaming_time Indica l'orario corrente con cui ho iniziato a vedere la stream
 * @param string $end_streaming_time Indica l'orario di fine
 */
function curStreamingStart($start_streaming_time, $curr_streaming_time, $end_streaming_time)
{
    $start = strtotime($start_streaming_time);
    $curr = strtotime($curr_streaming_time);
    $end = strtotime($end_streaming_time);

    // Controllo se lo stream è già iniziato e se sono in range
    if (($curr >= $start) && ($curr < $end)) {
        return ($curr - $start);
    } else return '';
}
?>