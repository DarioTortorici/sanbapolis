<?php

require_once '../authentication/db_connection.php';

/** Verifica se la richiesta corrente è una richiesta AJAX.
 *
 * @return bool True se la richiesta è una richiesta AJAX, altrimenti False.
 */
function is_ajax_request()
{
    // Verifica se l'intestazione 'HTTP_X_REQUESTED_WITH' è presente e ha il valore 'xmlhttprequest'
    if (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
    ) {
        return true;
    }

    // La richiesta non è una richiesta AJAX
    return false;
}

/** Salva un training nella tabella "calendar_events" insieme alle informazioni correlate nella tabella "prenotazioni".
 * Inoltre gestisce i parametri di fullcalendar.io che non sono al momento utilizzati
 *
 * @param int $groupId L'ID del gruppo associato all'evento.
 * @param bool $allDay Indica se l'evento dura per l'intera giornata.
 * @param string $startDate La data di inizio dell'evento.
 * @param string $endDate La data di fine del training.
 * @param string|null $daysOfWeek I giorni della settimana in cui si ripete l'evento.
 * @param string|null $startTime L'orario di inizio del training.
 * @param string|null $endTime L'orario di fine del training.
 * @param string|null $startRecur La data di inizio della ricorrenza del training.
 * @param string|null $endRecur La data di fine della ricorrenza del training.
 * @param string $url L'URL associato al training.
 * @param string $society Il nome dell'associazione/società associata al training.
 * @param string $sport Lo sport del training.
 * @param string $coach Il nome dell'allenatore associato al training.
 * @param string $note La nota relativa al training.
 * @param string $eventType Il tipo di evento (es. "match" o altro).
 * @return int L'ID del training salvato (ID calendar_events).
 */
function save_event($groupId, $allDay, $startDate, $endDate, $daysOfWeek, $startTime, $endTime, $startRecur, $endRecur, $url, $society, $sport, $coach, $note, $eventType, $cameras, $sessionId)
{
    // missing premium parameter `resourceEditable`=?, `resourceId`=?, `resourceIds`=?

    $con = get_connection();

    if ($startRecur == "0000-00-00" || $startRecur == null) {
        $startRecur = $startDate;
    }

    if ($endRecur == "0000-00-00" || $endRecur == null) {
        // +1 perché altrimenti non prende giorno finale
        $endRecursive = strtotime($endDate . ' +1 day');
        $endRecur =  date('Y-m-d', $endRecursive);
    }

    // allDay settings
    if ($allDay) {
        $startTime = null;
        $endTime = null;
        $allDay = 1;
    } else {
        $allDay = 0;
    }

    if ($daysOfWeek == "null") {
        $daysOfWeek = null;
    }

    $interactive = true;
    $className = null;

    //disabilitata per aggiungere la gestione della chiamata al db per modificare i valori
    $editable = true;
    $startEditable = false;
    $durationEditable = false;

    $display = true;

    // nella palestra non possono esserci eventi contemporanei
    $overlap = false;

    //$color settato a null perch modifichiamo bordi e background in base al tipo di evento
    $color = null;
    $backgroundColor = getEventColor($sport);
    $textcolor = "white";

    //Camere preselezionate da attivare
    if ($cameras == "null") {
        $cameras = "[]";
    }

    $squadra = getSquadra($society);

    $eventTypeBoolean = ($eventType === 'match') ? 1 : 0;
    if ($eventTypeBoolean) {
        $title = $society . ' | ' . $eventType;
        $borderColor = "black";
    } else {
        $title = $society;
        $borderColor = $backgroundColor;
    }

    $sql = "INSERT INTO calendar_events (`groupId`,`allDay`,`start`,`end`,`daysOfWeek`, `startTime`, `endTime`,`startRecur`, `endRecur`, `title`, `url`,
        `interactive`, `className`, `editable`, `startEditable`, `durationEditable`, `display`, `overlap`, `color`, `backgroundColor`, `borderColor`, `textColor`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $query = $con->prepare($sql);
    $query->execute([
        $groupId, $allDay, $startDate, $endDate, $daysOfWeek, $startTime, $endTime, $startRecur, $endRecur, $title, $url,
        $interactive, $className, $editable, $startEditable, $durationEditable, $display, $overlap, $color, $backgroundColor, $borderColor, $textcolor
    ]);

    $calendar_id = $con->lastInsertId();

    $author = getAuthorEvent($sessionId);
    $data_ora_inizio = accorpaTime($startDate,$startTime);
    $data_ora_fine = accorpaTime($endDate, $endTime);

    $sql = "INSERT INTO prenotazioni (`data_ora_inizio`,`data_ora_fine`, `autore_prenotazione`, `note`, `id_squadra`, `id_calendar_events`, `cams`) 
        VALUES (?,?,?,?,?,?,?)";
    $query = $con->prepare($sql);
    $query->execute([$data_ora_inizio, $data_ora_fine, $author, $note, $squadra['id'], $calendar_id, $cameras]);
    $prenotazioni_id = $con->lastInsertId();

    if ($eventTypeBoolean) { // Partita
        $sport = getSportbyTeam($squadra);
        $event_id = save_match($data_ora_inizio, $data_ora_fine, $squadra, $sport);
        save_prenotazioni_partita($prenotazioni_id, $event_id, $con);
    } else { // Allenamento
        $event_id = save_training($data_ora_inizio, $data_ora_fine, $squadra);
        save_prenotazioni_allenamenti($prenotazioni_id, $event_id, $con);
    }

    return $calendar_id;
}

function accorpaTime($date, $time)
{
    $datetime = $date . ' ' . $time;
    return $datetime;
}


/** Modifica un training nella tabella "calendar_events" insieme alle informazioni correlate nella tabella "prenotazioni".
 *
 * @param int $groupId L'ID del gruppo associato all'evento.
 * @param string $startDate La data di inizio dell'evento.
 * @param string $endDate La data di fine del training.
 * @param string|null $startTime L'orario di inizio del training.
 * @param string|null $endTime L'orario di fine del training.
 * @param string|null $startRecur La data di inizio della ricorrenza del training.
 * @param string|null $endRecur La data di fine della ricorrenza del training.
 * @param string $url L'URL associato al training.
 * @param string $society Il nome dell'associazione/società associata al training.
 * @param string $sport Lo sport del training.
 * @param string $coach Il nome dell'allenatore associato al training.
 * @param string $note La nota relativa al training.
 * @param int|null $id L'ID del training da modificare.
 * @return int L'ID del training modificato.
 */
function edit_training($groupId, $startDate, $endDate, $startTime, $endTime, $url, $society, $coach, $note, $id)
{
    $con = get_connection();

    // Da modificare altrimenti eseguono l'override di startDate ed endDate
    $startRecur = $startDate;
    $endRecursive = strtotime($endDate . ' +1 day');
    $endRecur =  date('Y-m-d', $endRecursive);

    // Se è presente un ID, esegui l'aggiornamento nelle due tabelle
    if ($id) {
        $sql = "UPDATE calendar_events 
                SET `groupId` = ?, `start` = ?, `end` = ?, `startTime` = ?, `endTime` = ?, `startRecur` = ?, `endRecur` = ?, `url` = ? 
                WHERE id = ?";
        $query = $con->prepare($sql);
        $query->execute([$groupId, $startDate, $endDate, $startTime, $endTime, $startRecur, $endRecur, $url, $id]);

        $sql = "UPDATE prenotazioni SET `society`=?, `coach`=?, `note`=? WHERE id_calendar_events=?";
        $query = $con->prepare($sql);
        $query->execute([$society, $coach, $note, $id]);
        return $id;
    } else {
        echo ("Errore, nessun ID specificato: " . $id);
    }
}

/**
 * Salva le telecamere selezionate nel database per un determinato evento.
 * @param string $cameras - Le telecamere selezionate da salvare (formato JSON o array).
 * @param int $id - L'ID dell'evento a cui associare le telecamere.
 * @return int|null - L'ID dell'evento se l'aggiornamento ha avuto successo, altrimenti null.
 */
function save_cameras($cameras, $id)
{
    $con = get_connection();

    if ($id) {
        $sql = "UPDATE prenotazioni SET `cams`=? WHERE id_calendar_events=?";
        $query = $con->prepare($sql);
        $query->execute([$cameras, $id]);
        return $id;
    } else {
        echo ("Errore, nessun ID specificato: " . $id);
    }
}

function save_training($inizio, $fine, $squadra)
{
    $con = get_connection();
    $sql = "INSERT INTO allenamenti (`data_ora_inizio`, `data_ora_fine`, `id_squadra`) VALUES (:inizio, :fine, :squadra)";
    $query = $con->prepare($sql);
    $query->bindParam(':inizio', $inizio);
    $query->bindParam(':fine', $fine);
    $query->bindParam(':squadra', $squadra['id']);
    $query->execute();
    return $con->lastInsertId();
}

function save_match($inizio, $fine, $squadra, $sport)
{
    $con = get_connection();
    $sql = "INSERT INTO partite (`data_ora_inizio`, `data_ora_fine`, `id_squadra_casa`, `sport`) VALUES (:inizio, :fine, :squadra, :sport)";
    $query = $con->prepare($sql);
    $query->bindParam(':inizio', $inizio);
    $query->bindParam(':fine', $fine);
    $query->bindParam(':squadra', $squadra['id']);
    $query->bindParam(':sport', $sport);
    $query->execute();
    return $con->lastInsertId();
}

function getSportbyTeam($squadra)
{
    $con = get_connection();
    $sql = "SELECT sport.nome_sport
            FROM sport
            INNER JOIN squadre ON sport.nome_sport = squadre.sport
            WHERE squadre.id = :squadra";
    $query = $con->prepare($sql);
    $query->bindParam(':squadra', $squadra['id']);
    $query->execute();
    $result = $query->fetchColumn();
    return $result;
}

function save_prenotazioni_partita($calendar_id, $event_id, $con)
{
    $sql = "INSERT INTO prenotazioni_partite VALUES (?,?)";
    $query = $con->prepare($sql);
    $query->execute([$calendar_id, $event_id]);
}

function save_prenotazioni_allenamenti($calendar_id, $event_id, $con)
{
    $sql = "INSERT INTO prenotazioni_allenamenti VALUES (?,?)";
    $query = $con->prepare($sql);
    $query->execute([$calendar_id, $event_id]);
}

/** Elimina un training specifico dalla tabella "calendar_events" e le righe figlie correlate nella tabella "prenotazioni".
 *
 * @param int $id_calendar_events L'ID dell'evento da eliminare.
 * @return bool True se l'eliminazione ha avuto successo per entrambe le tabelle, altrimenti False.
 */
function delete_training($id_calendar_events)
{
    $con = get_connection();

    // Elimina le righe figlie nella tabella "prenotazioni"
    $sql_delete_prenotazioni = "DELETE FROM prenotazioni WHERE id_calendar_events = ?";
    $query_delete_prenotazioni = $con->prepare($sql_delete_prenotazioni);
    $query_delete_prenotazioni->execute([$id_calendar_events]);

    // Elimina l'evento dalla tabella "calendar_events"
    $sql_delete_event = "DELETE FROM calendar_events WHERE id = ?";
    $query_delete_event = $con->prepare($sql_delete_event);
    $query_delete_event->execute([$id_calendar_events]);

    // Verifica se le query di eliminazione hanno avuto successo
    if ($query_delete_prenotazioni->rowCount() > 0 && $query_delete_event->rowCount() > 0) {
        return true;
    } else {
        // Almeno una delle eliminazioni non è riuscita
        return false;
    }
}


function getSquadra($society)
{
    $con = get_connection();
    $sql = "SELECT squadre.id FROM squadre INNER JOIN societa_sportive ON societa_sportive.partita_iva = squadre.societa WHERE societa_sportive.nome = ?";
    $query = $con->prepare($sql);
    $query->execute([$society]);
    $squadra = $query->fetch(PDO::FETCH_ASSOC);
    return $squadra;
}

function getAuthorEvent($sessionId)
{
    $con = get_connection();
    $sql = "SELECT email FROM persone WHERE session_id = :session_id";
    $statement = $con->prepare($sql);
    $statement->bindParam(':session_id', $sessionId);
    $statement->execute();
    $author = $statement->fetchColumn(); // Ottieni solo il valore della colonna 'email'
    return $author;
}


/** Recupera tutti gli eventi dalla tabella "calendar_events" del database e li restituisce come JSON.
 *
 * @return string Una stringa JSON che rappresenta gli eventi. Se non ci sono eventi, la stringa JSON sarà vuota.
 */
function getEvents()
{
    $con = get_connection();
    $query = "SELECT * FROM calendar_events";
    $statement = $con->query($query);
    $events = $statement->fetchAll(PDO::FETCH_ASSOC);
    return json_encode($events);
}

/** Ottiene le informazioni di un singolo evento dalla tabella "calendar_events" in base all'ID fornito e lo restituisce come JSON.
 *
 * @param int $id L'ID dell'evento da recuperare.
 * @return string Una stringa JSON che rappresenta l'evento. Se l'evento non viene trovato, la stringa JSON sarà vuota.
 */
function getEvent($id)
{
    $con = get_connection();
    $query = "SELECT * FROM calendar_events WHERE id = :id";
    $statement = $con->prepare($query);
    $statement->bindParam(':id', $id);
    $statement->execute();
    $event = $statement->fetch(PDO::FETCH_ASSOC);
    return json_encode($event);
}

/** Funzione per ottenere le informazioni di un evento dal database.
 * Recupera le informazioni dell'evento corrispondente all'ID specificato dalla tabella "prenotazioni"
 * @param int $id - L'ID dell'evento da recuperare.
 * @return string - Le informazioni dell'evento nel formato JSON.
 */
function getInfoEvent($id)
{
    $con = get_connection();
    $query = "SELECT ei.* FROM calendar_events ce INNER JOIN prenotazioni ei ON ce.id = ei.id_calendar_events WHERE ce.id = :id";
    $statement = $con->prepare($query);
    $statement->bindParam(':id', $id);
    $statement->execute();
    $event = $statement->fetch(PDO::FETCH_ASSOC);
    return json_encode($event);
}

/** Recupera gli incontri dal database.
 * @return string JSON contenente gli incontri recuperati dal database.
 */
function getMatches()
{
    $con = get_connection();
    $query = "SELECT ce.* FROM calendar_events ce INNER JOIN prenotazioni ei ON ce.id = ei.id_calendar_events WHERE ei.training = 0";
    $statement = $con->query($query);
    $events = $statement->fetchAll(PDO::FETCH_ASSOC);
    return json_encode($events);
}

/** Recupera gli eventi per un allenatore specifico dal database.
 * @param string $coach Il nome o l'identificatore dell'allenatore.
 * @return string Stringa JSON contenente gli eventi dell'allenatore.
 */
function getCoachEvents($coach)
{
    $con = get_connection();
    $query = "SELECT ce.* FROM calendar_events ce 
              INNER JOIN prenotazioni ei ON ce.id = ei.id_calendar_events 
              INNER JOIN allenatori_squadre on allenatori_squadre.id_squadra = ei.id_squadra 
              WHERE allenatori_squadre.email_allenatore = :coach";
    $statement = $con->prepare($query);
    $statement->bindParam(':coach', $coach);
    $statement->execute();
    $events = $statement->fetchAll(PDO::FETCH_ASSOC);
    return json_encode($events);
}

/** Recupera la nota associata a un evento dalla tabella "prenotazioni" del database, in base all'ID dell'evento fornito.
 *
 * @param int $id L'ID dell'evento per il quale si desidera recuperare la nota.
 * @return array Un array associativo contenente la nota dell'evento. Se la nota non viene trovata, l'array sarà vuoto.
 */
function getNote($id)
{
    $con = get_connection();
    $query = "SELECT note FROM prenotazioni WHERE id_calendar_events = :id";
    $statement = $con->prepare($query);
    $statement->bindParam(':id', $id);
    $statement->execute();
    $note = $statement->fetch(PDO::FETCH_ASSOC);
    return $note;
}

/** Ottiene il colore associato a uno specifico sport.
 *
 * @param string $sport Lo sport per il quale si desidera ottenere il colore.
 * @return string Una stringa che rappresenta il colore corrispondente allo sport specificato. Se lo sport non corrisponde a nessuna delle opzioni predefinite, viene restituito il colore di default.
 */
function getEventColor($sport)
{
    if ($sport == 'calcio') {
        return "purple";
    } else if ($sport == 'pallavolo') {
        return "darkorange";
    } else if ($sport == 'basket') {
        return "darkgreen";
    }
    return '#378006';
}

/** Recupera le telecamere associate ad un evento dalla tabella "prenotazioni" del database, in base all'ID dell'evento fornito.
 *
 * @param int $id L'ID dell'evento per il quale si desidera recuperare la nota.
 * @return array Una stringa JSON contenente la lista di telecamere.
 */
function getCameras($id)
{
    $con = get_connection();
    $query = "SELECT cams FROM prenotazioni WHERE id_calendar_events = :id";
    $statement = $con->prepare($query);
    $statement->bindParam(':id', $id);
    $statement->execute();
    $cams = $statement->fetch(PDO::FETCH_ASSOC);
    return json_encode($cams);
}

function getDatetimeEvent($id)
{
    $con = get_connection();
    $query = "SELECT start,startTime FROM calendar_events WHERE id = :id";
    $statement = $con->prepare($query);
    $statement->bindParam(':id', $id);
    $statement->execute();
    $date = $statement->fetch(PDO::FETCH_ASSOC);
    return json_encode($date);
}

/** Restituisce la data corrente nel formato "YYYY-MM-DD".
 *
 * @return string La data corrente nel formato YYYY-MM-DD, esempio: 2023-06-28.
 */
function currentDate()
{
    return date('YYYY-MM-DD');
}

function getSociety()
{
    $con = get_connection();
    $query = "SELECT nome FROM societa_sportive";
    $stmt = $con->query($query);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $options = '';
    foreach ($result as $row) {
        $nomeSocieta = $row['nome'];
        $options .= "<option value='$nomeSocieta'>$nomeSocieta</option>";
    }

    return $options;
}


///////////////////////////
// GET e POST Management //
///////////////////////////

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'save-event') { // salvataggio di un evento
        //tabella calendar_event
        $groupId = isset($_POST['groupId']) ? $_POST['groupId'] : null;
        $allDay = isset($_POST['allDay']) ? $_POST['allDay'] : null;
        $startDate = isset($_POST['start-date']) ? $_POST['start-date'] : null;
        $endDate = isset($_POST['end-date']) ? $_POST['end-date'] : null;
        $daysOfWeek = isset($_POST['daysOfWeek']) ? $_POST['daysOfWeek'] : null;
        $startTime = isset($_POST['startTime']) ? $_POST['startTime'] : null;
        $endTime = isset($_POST['endTime']) ? $_POST['endTime'] : null;
        $startRecur = isset($_POST['startRecur']) ? $_POST['startRecur'] : null;
        $endRecur = isset($_POST['endRecur']) ? $_POST['endRecur'] : null;
        $url = isset($_POST['url']) ? $_POST['url'] : null;
        // parsato daysOfWeek in JSON in modo da salvarlo nel database come stringa
        $daysOfWeek = json_encode($daysOfWeek);

        //tabella prenotazioni
        $society = isset($_POST['society']) ? $_POST['society'] : null;
        $sport = isset($_POST['sport']) ? $_POST['sport'] : null;
        $coach = isset($_POST['coach']) ? $_POST['coach'] : null;
        $note = isset($_POST['description']) ? $_POST['description'] : null;
        $eventType = isset($_POST['event_type']) ? $_POST['event_type'] : null;
        $cameras = isset($_POST['camera']) ? $_POST['camera'] : null;
        $cameras = json_encode($cameras);

        //GetAuthor parametro necessario
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;

        //Sono obbligatori society e startdate ed effettuiamo il controllo che esistano
        if ($society && $startDate) {
            $id = null;
            $id = save_event($groupId, $allDay, $startDate, $endDate, $daysOfWeek, $startTime, $endTime, $startRecur, $endRecur, $url, $society, $sport, $coach, $note, $eventType, $cameras, $user_id);
            echo json_encode(array('status' => 'success', 'id' => $id));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Missing required fields'));
        }
    } elseif ($action == 'get-events') { // recupero tutti gli eventi da calendar_event
        header('Content-Type: application/json');
        echo getEvents();
    } elseif ($action == 'get-event') { // recupero evento da calendar_event con id specifico
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        header('Content-Type: application/json');
        if ($id) {
            echo getEvent($id);
        }
    } elseif ($action == 'get-note') { // recupero descrizione evento (se esiste) da prenotazioni
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            $note = getNote($id);
            if ($note) {
                echo $note['note'];
            } else {
                return " ";
            }
        }
    } elseif ($action == 'get-coach-event') { //recupero tutti gli eventi da calendar_event dove allena coach
        $coach = isset($_GET['coach']) ? $_GET['coach'] : null;
        if ($coach) {
            header('Content-Type: application/json');
            echo getCoachEvents($coach);
        }
    } elseif ($action == 'delete-event') { // Elimino evento con quell'id
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        if (delete_training($id)) {
            $response = array('status' => 'success', 'message' => 'Evento eliminato con successo');
        } else {
            $response = array('status' => 'error', 'message' => 'Richiesta non valida');
        }

        echo json_encode($response);
    } elseif ($action == 'get-matches') { // recupero tutti gli eventi segnati come match
        header('Content-Type: application/json');
        echo getMatches();
    } elseif ($action == 'get-event-info') { // recupero evento da prenotazioni con id (di calendar_events) specifico
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        header('Content-Type: application/json');
        if ($id) {
            echo getInfoEvent($id);
        } else { // invalid action
        }
    } elseif ($action == 'edit-event') { // salvataggio di un evento

        //tabella calendar_event
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $groupId = isset($_POST['groupId']) ? $_POST['groupId'] : null;
        $startDate = isset($_POST['startDate']) ? $_POST['startDate'] : null;
        $endDate = isset($_POST['endDate']) ? $_POST['endDate'] : null;
        $startTime = isset($_POST['startTime']) ? $_POST['startTime'] : null;
        $endTime = isset($_POST['endTime']) ? $_POST['endTime'] : null;
        $url = isset($_POST['url']) ? $_POST['url'] : null;

        //tabella event-info 
        $society = isset($_POST['society']) ? $_POST['society'] : null;
        $coach = isset($_POST['coach']) ? $_POST['coach'] : null;
        $note = isset($_POST['note']) ? $_POST['note'] : null;

        //Sono obbligatori society e startdate ed effettuiamo il controllo che esistano
        if ($society && $startDate) {
            $id = edit_training($groupId, $startDate, $endDate, $startTime, $endTime, $url, $society, $coach, $note, $id);
            echo json_encode(array('status' => 'success', 'id' => $id));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Missing required fields'));
        }
    } elseif ($action == 'get-cams') { // recupero telecamere attive per l'evento con id specifico

        $id = isset($_GET['id']) ? $_GET['id'] : null;
        header('Content-Type: application/json');
        if ($id) {
            echo getCameras($id);
        }
    } elseif ($action == 'save-cams') { //salvo le telecamere da atticare per l'evento
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $cameras = isset($_POST['cameras']) ? $_POST['cameras'] : null;
        $cameras = json_encode($cameras);
        $id = save_cameras($cameras, $id);
        if ($id) {
            echo json_encode(array('status' => 'success', 'id' => $id));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Missing required fields'));
        }
    } elseif ($action == 'get-time') { // recupero il datetime dell'evento

        $id = isset($_POST['id']) ? $_POST['id'] : null;
        header('Content-Type: application/json');

        if ($id) {
            echo getDatetimeEvent($id);
        }
    } else {
        // Missing 'action' parameter
    }
}
