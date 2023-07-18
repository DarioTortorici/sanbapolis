<?php

require_once __DIR__ . '/../authentication/db_connection.php';
require_once __DIR__ . '/../authentication/auth-helper.php';
require_once __DIR__ . '/../modals/email-handler.php';

/** Verifica se la richiesta corrente Ã¨ una richiesta AJAX.
 *
 * @return bool True se la richiesta Ã¨ una richiesta AJAX, altrimenti False.
 */
function is_ajax_request()
{
    // Verifica se l'intestazione 'HTTP_X_REQUESTED_WITH' Ã¨ presente e ha il valore 'xmlhttprequest'
    if (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
    ) {
        return true;
    }

    // La richiesta non Ã¨ una richiesta AJAX
    return false;
}

/**
 * Salva un evento nel calendario.
 *
 * Questa funzione salva un nuovo evento nel calendario. Riceve diversi parametri che descrivono l'evento
 * e esegue le operazioni necessarie per salvare l'evento nel database.
 *
 * @param int $groupId L'ID del gruppo di calendari a cui l'evento appartiene.
 * @param bool $allDay Indica se l'evento dura l'intera giornata o ha un'ora specifica.
 * @param string $startDate La data di inizio dell'evento nel formato "YYYY-MM-DD".
 * @param string $endDate La data di fine dell'evento nel formato "YYYY-MM-DD".
 * @param string $daysOfWeek I giorni della settimana in cui si ripete l'evento.
 * @param string $startTime L'ora di inizio dell'evento nel formato "HH:MM:SS".
 * @param string $endTime L'ora di fine dell'evento nel formato "HH:MM:SS".
 * @param string $startRecur La data di inizio della ricorrenza dell'evento nel formato "YYYY-MM-DD".
 * @param string $endRecur La data di fine della ricorrenza dell'evento nel formato "YYYY-MM-DD".
 * @param string $url L'URL associato all'evento.
 * @param string $society Il nome della società  sportiva associata all'evento.
 * @param string $sport Lo sport associato all'evento.
 * @param string $note Le note aggiuntive sull'evento.
 * @param string $eventType Il tipo di evento ("match" per una partita, "training" per un allenamento).
 * @param string $cameras Le telecamere preselezionate da attivare durante l'evento.
 * @return int L'ID dell'evento appena creato nel calendario.
 */
function save_event($groupId, $allDay, $startDate, $endDate, $daysOfWeek, $startTime, $endTime, $startRecur, $endRecur, $url, $society, $sport, $note, $eventType, $cameras, $author)
{
    // missing premium parameter `resourceEditable`=?, `resourceId`=?, `resourceIds`=?

    $con = get_connection();

    if ($startRecur == "0000-00-00" || $startRecur == null) {
        $startRecur = $startDate;
    }

    if ($endRecur == "0000-00-00" || $endRecur == null) {
        // +1 perchÃ© altrimenti non prende giorno finale
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

    $data_ora_inizio = accorpaTime($startDate, $startTime);
    $data_ora_fine = accorpaTime($endDate, $endTime);

    $sql = "INSERT INTO prenotazioni (`data_ora_inizio`,`data_ora_fine`, `autore_prenotazione`, `nota`, `id_squadra`, `id_calendar_events`) 
        VALUES (?,?,?,?,?,?)";
    $query = $con->prepare($sql);
    $query->execute([$data_ora_inizio, $data_ora_fine, $author, $note, $squadra['id'], $calendar_id]);
    $prenotazioni_id = $con->lastInsertId();

    // Salva le informazioni della telecamera
    save_cameras($cameras, $calendar_id);

    if ($eventTypeBoolean) { // Partita
        $sport = getSportbyTeam($squadra);
        $event_id = save_match($data_ora_inizio, $data_ora_fine, $squadra, $sport,$prenotazioni_id);
    } else { // Allenamento
        $event_id = save_training($data_ora_inizio, $data_ora_fine, $squadra,$prenotazioni_id);
    }

    $userInfo = get_user_info($con,$_COOKIE['email']);

    if ($userInfo['userType'] != "manutentore") {
        $query = "SELECT * FROM manutentori";
        $stmt = $con->query($query);
        $manutentore = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Invio la mail al manutentore
        authEvent($manutentore, $author, $startDate, $endDate, $startTime, $endTime, $cameras);
    }

    return $calendar_id;
}

/**
 * Accorpa la data e l'ora in un unico valore di data e ora.
 *
 * Prende una data nel formato "YYYY-MM-DD" e un'ora nel formato "HH:MM:SS"
 * e le unisce in un unico valore di data e ora nel formato "YYYY-MM-DD HH:MM:SS".
 *
 * @param string $date La data nel formato "YYYY-MM-DD".
 * @param string $time L'ora nel formato "HH:MM:SS".
 * @return string Il valore di data e ora accorpato nel formato "YYYY-MM-DD HH:MM:SS".
 */
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
 * @param string $society Il nome dell'associazione/società  associata al training.
 * @param string $sport Lo sport del training.
 * @param string $coach Il nome dell'allenatore associato al training.
 * @param string $note La nota relativa al training.
 * @param int|null $id L'ID del training da modificare.
 * @return int L'ID del training modificato.
 */
function edit_training($groupId, $startDate, $endDate, $startTime, $endTime, $url, $society, $note, $id)
{
    $con = get_connection();

    // Da modificare altrimenti eseguono l'override di startDate ed endDate
    $startRecur = $startDate;
    $endRecursive = strtotime($endDate . ' +1 day');
    $endRecur = date('Y-m-d', $endRecursive);

    // Calcolo squadra
    $squadra = getSquadra($society);

    // Se Ã¨ presente un ID, esegui l'aggiornamento nelle due tabelle
    if ($id) {
        $sql = "UPDATE calendar_events 
                SET `groupId` = ?, `start` = ?, `end` = ?, `startTime` = ?, `endTime` = ?, `startRecur` = ?, `endRecur` = ?, `url` = ? 
                WHERE id = ?";
        $query = $con->prepare($sql);
        $query->execute([$groupId, $startDate, $endDate, $startTime, $endTime, $startRecur, $endRecur, $url, $id]);

        $sql = "UPDATE prenotazioni SET `id_squadra`=?, `data_ora_inizio`=?, `data_ora_fine`=?, `nota`=? WHERE id_calendar_events=?";
        $query = $con->prepare($sql);
        $query->execute([$squadra['id'], $startDate, $endDate, $note, $id]);

        return $id;
    } else {
        throw new Exception("Errore, nessun ID specificato.");
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
        $sql = "SELECT id FROM prenotazioni WHERE id_calendar_events=?";
        $query = $con->prepare($sql);
        $query->execute([$id]);
        $prenotazioni_id = $query->fetchColumn();
        delete_cameras($prenotazioni_id);
        $arrayInt = json_decode($cameras);
        $cams_array = array_map('intval', $arrayInt);

        $sql = "INSERT INTO telecamere_prenotazioni (telecamera, prenotazione) VALUES (?,?)";
        $query = $con->prepare($sql);
        foreach ($cams_array as $camera) {
            $query->execute([$camera, $prenotazioni_id]);
        }

        return $prenotazioni_id;
    } else {
        echo ("Errore, nessun ID specificato: " . $id);
    }
}

function delete_cameras($prenotazioni_id)
{
    $con = get_connection();

    if ($prenotazioni_id) {
        $sql = "DELETE FROM telecamere_prenotazioni WHERE prenotazione=?";
        $query = $con->prepare($sql);
        $query->execute([$prenotazioni_id]);
    } else {
        echo ("Errore, nessun ID specificato: " . $prenotazioni_id);
    }
}

/**
 * Salva un allenamento nel database.
 *
 * Registra un nuovo allenamento nel database con le informazioni specificate.
 *
 * @param string $inizio La data e l'ora di inizio dell'allenamento.
 * @param string $fine La data e l'ora di fine dell'allenamento.
 * @param array $squadra L'array contenente l'ID della squadra associata all'allenamento.
 * @return int L'ID dell'allenamento appena inserito nel database.
 */
function save_training($inizio, $fine, $squadra,$prenotazioni_id)
{
    $con = get_connection();
    $sql = "INSERT INTO allenamenti (`data_ora_inizio`, `data_ora_fine`, `id_squadra`, `prenotazione`) VALUES (:inizio, :fine, :squadra,:prenotazione)";
    $query = $con->prepare($sql);
    $query->bindParam(':inizio', $inizio);
    $query->bindParam(':fine', $fine);
    $query->bindParam(':squadra', $squadra['id']);
    $query->bindParam(':prenotazione',$prenotazioni_id);
    $query->execute();
    return $con->lastInsertId();
}

/**
 * Salva una partita nel database.
 *
 * Registra una nuova partita nel database con le informazioni specificate.
 *
 * @param string $inizio La data e l'ora di inizio della partita.
 * @param string $fine La data e l'ora di fine della partita.
 * @param array $squadra L'array contenente l'ID della squadra di casa.
 * @param string $sport Il nome dello sport associato alla partita.
 * @return int L'ID della partita appena inserita nel database.
 */
function save_match($inizio, $fine, $squadra, $sport,$prenotazioni_id)
{
    $con = get_connection();
    $sql = "INSERT INTO partite (`data_ora_inizio`, `data_ora_fine`, `id_squadra_casa`, `sport`,`prenotazione`) VALUES (:inizio, :fine, :squadra, :sport, :prenotazione)";
    $query = $con->prepare($sql);
    $query->bindParam(':inizio', $inizio);
    $query->bindParam(':fine', $fine);
    $query->bindParam(':squadra', $squadra['id']);
    $query->bindParam(':sport', $sport);
    $query->bindParam(':prenotazione',$prenotazioni_id);
    $query->execute();
    return $con->lastInsertId();
}

/**
 * Ottiene il nome dello sport associato a una squadra.
 *
 * Recupera il nome dello sport dal database corrispondente all'ID della squadra specificata.
 *
 * @param array $squadra L'array contenente l'ID della squadra.
 * @return string Il nome dello sport associato alla squadra.
 */
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

/**
 * Elimina un allenamento e tutti i suoi dati correlati dal database.
 *
 * @param int $id_calendar_events ID dell'evento del calendario relativo all'allenamento da eliminare.
 * @return bool True se l'eliminazione Ã¨ avvenuta con successo, False altrimenti.
 */
function delete_training($id_calendar_events)
{
    $con = get_connection();

    $con->beginTransaction();

    try {
        // Cerca id prenotazione
        $sql_search_prenotazioni = "SELECT id FROM prenotazioni WHERE id_calendar_events = ?";
        $query_search_prenotazioni = $con->prepare($sql_search_prenotazioni);
        $query_search_prenotazioni->execute([$id_calendar_events]);
        $id_prenotazioni = $query_search_prenotazioni->fetchColumn();

        // Elimina le righe figlie nella tabella "allenamenti"
        $sql_delete_allenamenti = "DELETE FROM allenamenti WHERE prenotazione = ?";
        $query_delete_allenamenti = $con->prepare($sql_delete_allenamenti);
        $query_delete_allenamenti->execute([$id_prenotazioni]);

        // Elimina le righe figlie nella tabella "prenotazioni"
        $sql_delete_prenotazioni = "DELETE FROM prenotazioni WHERE id_calendar_events = ?";
        $query_delete_prenotazioni = $con->prepare($sql_delete_prenotazioni);
        $query_delete_prenotazioni->execute([$id_calendar_events]);

        // Elimina l'evento dalla tabella "calendar_events"
        $sql_delete_event = "DELETE FROM calendar_events WHERE id = ?";
        $query_delete_event = $con->prepare($sql_delete_event);
        $query_delete_event->execute([$id_calendar_events]);

        // Commit delle modifiche al database
        $con->commit();

        return true;
    } catch (Exception $e) {
        // Rollback delle modifiche in caso di errore
        $con->rollBack();
        return false;
    }
}


/**
 * Ottiene l'ID della squadra associata a una determinata società  sportiva.
 *
 * Recupera dal database l'ID della squadra che Ã¨ associata alla società  sportiva specificata.
 *
 * @param string $society Il nome della società  sportiva.
 * @return array|false L'array associativo contenente l'ID della squadra, o false in caso di errore.
 */
function getSquadra($society)
{
    $con = get_connection();
    $sql = "SELECT squadre.id FROM squadre INNER JOIN societa_sportive ON societa_sportive.partita_iva = squadre.societa WHERE societa_sportive.nome = ?";
    $query = $con->prepare($sql);
    $query->execute([$society]);
    $squadra = $query->fetch(PDO::FETCH_ASSOC);
    return $squadra;
}


/** Recupera tutti gli eventi dalla tabella "calendar_events" del database e li restituisce come JSON.
 *
 * @return string Una stringa JSON che rappresenta gli eventi. Se non ci sono eventi, la stringa JSON sarÃ  vuota.
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
 * @return string Una stringa JSON che rappresenta l'evento. Se l'evento non viene trovato, la stringa JSON sarÃ  vuota.
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
    $query = "SELECT ce.* FROM calendar_events ce INNER JOIN prenotazioni ei ON ce.id = ei.id_calendar_events";
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
 * @return array Un array associativo contenente la nota dell'evento. Se la nota non viene trovata, l'array sarÃ  vuoto.
 */
function getNote($id)
{
    $con = get_connection();
    $query = "SELECT nota FROM prenotazioni WHERE id_calendar_events = :id";
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
}

function getUserType()
{
    $con = get_connection();
    $userInfo = get_user_info($con, $_COOKIE['email']);
    return $userInfo['userType'];
}

/**
 * Ottiene la data e l'ora di un evento specifico dal database.
 *
 * Recupera dal database la data di inizio e l'ora di un evento corrispondente all'ID specificato.
 * Restituisce i dati in formato JSON.
 *
 * @param int $id L'ID dell'evento di cui ottenere la data e l'ora.
 * @return string I dati della data e dell'ora dell'evento in formato JSON.
 */
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

/**
 * Ottiene elenco delle società  sportive dal database.
 *
 * Recupera dal database l'elenco dei nomi delle società  sportive e li restituisce come opzioni
 * per un elemento di selezione HTML.
 *
 * @return string Le opzioni HTML per l'elemento di selezione delle società  sportive.
 */
function getSocieties()
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
        $note = isset($_POST['description']) ? $_POST['description'] : null;
        $eventType = isset($_POST['event_type']) ? $_POST['event_type'] : null;
        $cameras = isset($_POST['camera']) ? $_POST['camera'] : null;
        $cameras = json_encode($cameras);

        //GetAuthor parametro necessario
        $author = isset($_POST['author']) ? $_POST['author'] : null;

        //Sono obbligatori society e startdate ed effettuiamo il controllo che esistano
        if ($society && $startDate) {
            $id = null;
            $id = save_event($groupId, $allDay, $startDate, $endDate, $daysOfWeek, $startTime, $endTime, $startRecur, $endRecur, $url, $society, $sport, $note, $eventType, $cameras, $author);
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
                echo $note['nota'];
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
        $note = isset($_POST['note']) ? $_POST['note'] : null;

        //Sono obbligatori society e startdate ed effettuiamo il controllo che esistano
        if ($society && $startDate) {
            $id = edit_training($groupId, $startDate, $endDate, $startTime, $endTime, $url, $society, $note, $id);
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
    } elseif ($action == 'get-user-type') { // recupero il tipo di utente
        header('Content-Type: application/json');
        $response = getUserType();
        echo json_encode($response);
    } else {
        // Missing 'action' parameter
    }
}
