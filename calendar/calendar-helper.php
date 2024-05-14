<?php

require_once __DIR__ . '/../authentication/db_connection.php';
require_once __DIR__ . '/../authentication/auth-helper.php';
require_once __DIR__ . '/../modals/email-handler.php';

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

/**
 * Salva un nuovo evento nel calendario.
 *
 * Questa funzione inserisce un nuovo evento nel calendario, includendo il salvataggio delle prenotazioni,
 * delle informazioni sulla telecamera e altre impostazioni correlate.
 *
 * @param int $groupId L'ID del gruppo associato all'evento.
 * @param bool $allDay Indica se l'evento dura tutto il giorno.
 * @param string $startDate La data di inizio dell'evento nel formato "Y-m-d".
 * @param string $endDate La data di fine dell'evento nel formato "Y-m-d".
 * @param string|null $daysOfWeek I giorni della settimana in cui l'evento si ripete.
 * @param string|null $startTime L'ora di inizio dell'evento nel formato "H:i:s".
 * @param string|null $endTime L'ora di fine dell'evento nel formato "H:i:s".
 * @param string|null $startRecur La data di inizio della ricorrenza dell'evento nel formato "Y-m-d".
 * @param string|null $endRecur La data di fine della ricorrenza dell'evento nel formato "Y-m-d".
 * @param string $url L'URL della cartella dove verranno salvati i file.
 * @param string $society Il nome della società associata all'evento.
 * @param string $sport Lo sport associato all'evento.
 * @param string $note La nota o descrizione dell'evento.
 * @param string $eventType Il tipo di evento (partita o allenamento).
 * @param string|null $cameras Le telecamere associate all'evento in formato JSON.
 * @param string $author L'autore dell'evento.
 *
 * @return int L'ID dell'evento creato nel calendario.
 */
function save_event($groupId, $allDay, $startDate, $endDate, $daysOfWeek, $startTime, $endTime, $startRecur, $endRecur, $url, $society, $note, $eventType, $dataposCheck, $cameras, $author)
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

    $interactive = 1;
    $className = null;

    //disabilitata per aggiungere la gestione della chiamata al db per modificare i valori
    $editable = 1;
    $startEditable = 0;
    $durationEditable = 1;

    $display = 1;

    // nella palestra non possono esserci eventi contemporanei
    $overlap = 0;

    //$color settato a null perch modifichiamo bordi e background in base al tipo di evento
    $color = null;
    $backgroundColor = getEventColor($society);
    $textcolor = "white";

    //Camere preselezionate da attivare
    if ($cameras == "null") {
        $cameras = "[]";
    }

    // Controllo del check per la registrazione dei dati di posizionamento dei giocatori
    $dataposCheckBoolean = ($dataposCheck === 'on') ? 1 : 0;
    print_r($dataposCheckBoolean);

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

    // Salva le informazioni nella tabella prenotazioni
    $prenotazioni_id = save_prenotazione($con, $data_ora_inizio, $data_ora_fine, $author, $note, $squadra, $calendar_id);

    // Salva le informazioni della telecamera
    save_cameras($cameras, $calendar_id);

    // Salva il check della registrazione dei dati di posizionamento dei giocatori
    //
    // [ISSUE]  Serve implementare un meccanismo che invii i comandi all'orario specificato.
    //          Per il momento, invia entrambi i comandi (START e STOP)
    save_datapos($dataposCheckBoolean, $calendar_id);
    print_r($dataposCheckBoolean);

    // Salva la sessione di registrazione
    save_sessione_rec($author, $data_ora_inizio, $data_ora_fine, $calendar_id);

    echo 'CALENDAR_ID: '.$calendar_id;

    // Salva le telecamere per la sessione
    $video_url = "storage_video/volley_test_2.mp4";
    $nome = "Test Volley 2";
    save_cameras_in_video($note, $author, $nome, $cameras, $calendar_id, $video_url);

    if ($eventTypeBoolean) { // Partita
        $sport = getSportbyTeam($squadra);
        $event_id = save_match($data_ora_inizio, $data_ora_fine, $squadra, $sport, $prenotazioni_id);
    } else { // Allenamento
        $event_id = save_training($data_ora_inizio, $data_ora_fine, $squadra, $prenotazioni_id);
    }

    $userInfo = get_user_info($con, $_COOKIE['email']);

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
 * Salva una nuova prenotazione nel database.
 *
 * Questa funzione inserisce una nuova prenotazione nel database utilizzando le informazioni fornite.
 *
 * @param PDO $con L'oggetto di connessione al database.
 * @param string $data_ora_inizio La data e ora di inizio della prenotazione nel formato "Y-m-d H:i:s".
 * @param string $data_ora_fine La data e ora di fine della prenotazione nel formato "Y-m-d H:i:s".
 * @param string $author L'autore della prenotazione.
 * @param string $note La nota o descrizione della prenotazione.
 * @param array $squadra Le informazioni sulla squadra associata alla prenotazione.
 * @param int $calendar_id L'ID dell'evento nel calendario associato alla prenotazione.
 *
 * @return int L'ID della prenotazione appena inserita nel database.
 *
 * @throws PDOException Se si verifica un errore durante l'esecuzione dell'inserimento.
 */
function save_prenotazione($con, $data_ora_inizio, $data_ora_fine, $author, $note, $squadra, $calendar_id)
{
    try {
        $sql = "INSERT INTO prenotazioni (`data_ora_inizio`, `data_ora_fine`, `autore_prenotazione`, `nota`, `id_squadra`, `id_calendar_events`) 
            VALUES (?, ?, ?, ?, ?, ?)";
        $query = $con->prepare($sql);
        $query->execute([$data_ora_inizio, $data_ora_fine, $author, $note, $squadra['id'], $calendar_id]);

        return $con->lastInsertId();
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il salvataggio della prenotazione: " . $e->getMessage());
    }
}

/**
 * Modifica un allenamento esistente nel calendario.
 *
 * Questa funzione aggiorna le informazioni di un allenamento esistente nel calendario, inclusi i dettagli
 * della prenotazione e altri attributi correlati.
 *
 * @param int $groupId L'ID del gruppo associato all'allenamento.
 * @param string $startDate La nuova data di inizio dell'allenamento nel formato "Y-m-d".
 * @param string $endDate La nuova data di fine dell'allenamento nel formato "Y-m-d".
 * @param string|null $startTime La nuova ora di inizio dell'allenamento nel formato "H:i:s".
 * @param string|null $endTime La nuova ora di fine dell'allenamento nel formato "H:i:s".
 * @param string $url Il nuovo URL associato all'allenamento.
 * @param string $society Il nome della società associata all'allenamento.
 * @param string $note La nuova nota o descrizione dell'allenamento.
 * @param int $id L'ID dell'allenamento da modificare.
 *
 * @return int L'ID dell'allenamento modificato nel calendario.
 *
 * @throws Exception Se l'ID specificato è nullo o se si verifica un errore durante l'esecuzione dell'aggiornamento.
 */
function edit_training($groupId, $startDate, $endDate, $startTime, $endTime, $url, $society, $note, $id)
{
    $con = get_connection();

    // Da modificare altrimenti eseguono l'override di startDate ed endDate
    $startRecur = $startDate;
    $endRecursive = strtotime($endDate . ' +1 day');
    $endRecur = date('Y-m-d', $endRecursive);

    // Se è presente un ID, esegui l'aggiornamento nelle due tabelle
    if ($id) {
        try {
            // Aggiorna le informazioni sull'evento nel calendario
            $sql = "UPDATE calendar_events 
                    SET `groupId` = ?, `start` = ?, `end` = ?, `startTime` = ?, `endTime` = ?, `startRecur` = ?, `endRecur` = ?, `url` = ? 
                    WHERE id = ?";
            $query = $con->prepare($sql);
            $query->execute([$groupId, $startDate, $endDate, $startTime, $endTime, $startRecur, $endRecur, $url, $id]);

            // Aggiorna le informazioni sulla prenotazione
            $sql = "UPDATE prenotazioni SET `data_ora_inizio`=?, `data_ora_fine`=?, `nota`=? WHERE id_calendar_events=?";
            $query = $con->prepare($sql);
            $query->execute([$startDate, $endDate, $note, $id]);

            return $id;
        } catch (PDOException $e) {
            throw new Exception("Errore durante l'aggiornamento dell'allenamento: " . $e->getMessage());
        }
    } else {
        throw new Exception("Errore, nessun ID specificato.");
    }
}

/**
 * Salva le telecamere selezionate nel database per un determinato evento.
 *
 * @param mixed $cameras Le telecamere selezionate da salvare (formato JSON o array).
 * @param int $id L'ID dell'evento a cui associare le telecamere.
 *
 * @return int|null L'ID dell'evento se l'aggiornamento ha avuto successo, altrimenti null.
 *
 * @throws PDOException Se si verifica un errore durante l'aggiornamento delle telecamere.
 */
function save_cameras($cameras, $id)
{
    $con = get_connection();

    try {
        if ($id) {
            // Ottieni l'ID della prenotazione associata all'evento
            $sql = "SELECT id FROM prenotazioni WHERE id_calendar_events=?";
            $query = $con->prepare($sql);
            $query->execute([$id]);
            $prenotazioni_id = $query->fetchColumn();

            // Elimina le telecamere esistenti associate alla prenotazione
            delete_cameras($prenotazioni_id);

            // Converti le telecamere in un array di interi
            $cams_array = is_array($cameras) ? array_map('intval', $cameras) : json_decode($cameras, true);

            // Inserisci le nuove telecamere associate alla prenotazione o modifica se già presenti
            $sql = "INSERT INTO telecamere_prenotazioni (telecamera, prenotazione) VALUES (?, ?) ON DUPLICATE KEY UPDATE telecamera = VALUES(telecamera)";
            $query = $con->prepare($sql);
            foreach ($cams_array as $camera) {
                $query->execute([$camera, $prenotazioni_id]);
            }

            return $id;
        } else {
            throw new PDOException("Errore, nessun ID specificato.");
        }
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il salvataggio delle telecamere: " . $e->getMessage());
    }
}

/**
 * Salva le telecamere selezionate nel database per un determinato evento (all'interno della tabella video).
 */
function save_cameras_in_video($note, $autore, $nome, $cameras, $prenotazioni_id, $video_url) {

    $con = get_connection();

    try {
        if ($prenotazioni_id) {

            // Ottengo l'id della sessione di registrazione
            $sql = "SELECT id FROM sessioni_registrazione WHERE prenotazione=(SELECT id FROM prenotazioni WHERE id_calendar_events=?)";
            $query = $con->prepare($sql);
            $query->execute([$prenotazioni_id]);
            $sessione_id = $query->fetchColumn();

            echo 'QUI = '.$sessione_id;

            // Elimina le telecamere esistenti associate alla prenotazione
            delete_cameras_in_video($sessione_id);

            // Converti le telecamere in un array di interi
            $cams_array = is_array($cameras) ? array_map('intval', $cameras) : json_decode($cameras, true);
            print_r($cams_array);

            // Inserisci le nuove telecamere associate alla sessione o modifica se già presenti
            $sql = "INSERT INTO video (locazione, nome, autore, nota, sessione, telecamera) VALUES (?,?,?,?,?,?)";
            $query = $con->prepare($sql);
            foreach ($cams_array as $camera) {
                $query->execute([$video_url,$nome." [".$camera."]",$autore,$note,$sessione_id,$camera]);
            }

        } else {
            throw new PDOException("Errore, nessun ID specificato.");
        }
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il salvataggio delle telecamere della sessione: " . $e->getMessage());
    }

}

/**
 * Elimina le telecamere associate a una sessione di registrazione
 */
function delete_cameras_in_video($prenotazioni_id) {

    $con = get_connection();

    try {
        if ($prenotazioni_id) {

            // Ottengo l'id della sessione di registrazione
            $sql = "DELETE FROM video WHERE sessione=?";
            $query = $con->prepare($sql);
            $query->execute([$prenotazioni_id]);

        } else {
            throw new PDOException("Errore, nessun ID specificato.");
        }
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il salvataggio delle telecamere della sessione: " . $e->getMessage());
    }

}

/**
 * Salva la sessione di registrazione
 * 
 * @param string $author L'autore della prenotazione.
 * @param string $data_ora_inizio La data e ora di inizio della prenotazione nel formato "Y-m-d H:i:s".
 * @param string $data_ora_fine La data e ora di fine della prenotazione nel formato "Y-m-d H:i:s".
 * @param int $calendar_id L'ID dell'evento nel calendario associato alla prenotazione.
 */
function save_sessione_rec($autore, $data_ora_inizio, $data_ora_fine, $prenotazioni_id) {

    $con = get_connection();

    try {
        if ($prenotazioni_id) {
            // Ottieni l'ID della prenotazione associata all'evento
            $sql = "SELECT id FROM prenotazioni WHERE id_calendar_events=?";
            $query = $con->prepare($sql);
            $query->execute([$prenotazioni_id]);
            $prenotazioni_id = $query->fetchColumn();

            // Elimina il check esistente associato alla prenotazione
            delete_sessione_rec($prenotazioni_id);

            // Inserisci i dati della prenotazione
            $sql = "INSERT INTO sessioni_registrazione (`autore`, `data_ora_inizio`, `data_ora_fine`, `prenotazione`) VALUES (?, ?, ?, ?)";
            $query = $con->prepare($sql);
            $query->execute([$autore, $data_ora_inizio, $data_ora_fine, $prenotazioni_id]);

            return $con->lastInsertId();
        } else {
            throw new PDOException("Errore, nessun ID specificato.");
        }
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il salvataggio della sessione: " . $e->getMessage());
    }

}

/**
 * Elimina la sessione di registrazione
 * 
 * @param int $prenotazioni_id L'ID della prenotazione di cui eliminare la sessione di registrazione
 */
function delete_sessione_rec($prenotazioni_id) {

    $con = get_connection();

    try {
        if ($prenotazioni_id) {
            $sql = "DELETE FROM sessioni_registrazione WHERE prenotazione=?";
            $query = $con->prepare($sql);
            $query->execute([$prenotazioni_id]);
        } else {
            throw new PDOException("Errore, nessun ID specificato.");
        }
    } catch (PDOException $e) {
        throw new PDOException("Errore durante l'eliminazione della sessione: " . $e->getMessage());
    }
}

/**
 * Salva la scelta se registrare i dati di posizionamento dei giocatori
 * 
 * @param int $datapos_check valore del check  dei dati di posizionamento
 * @param int $id L'id dell'evento a cui associare il check dei dati di posizionamento
 */
function save_datapos($datapos_check, $id) {

    $con = get_connection();

    try {
        if ($id) {
            // Ottieni l'ID della prenotazione associata all'evento
            $sql = "SELECT id FROM prenotazioni WHERE id_calendar_events=?";
            $query = $con->prepare($sql);
            $query->execute([$id]);
            $prenotazioni_id = $query->fetchColumn();

            // Elimina il check esistente associato alla prenotazione
            delete_datapos($prenotazioni_id);

            // Inserisci il check associato alla prenotazione
            $sql = "INSERT INTO dati_posizionamento_prenotazioni (datipos, prenotazione) VALUES (:datipos, :prenotazione)";
            $query = $con->prepare($sql);
            $query->bindParam(':datipos', $datapos_check);
            $query->bindParam(':prenotazione', $id);
            $query->execute();

            // Endpoint Remoto
            if ($datapos_check == "1") {
                echo '$prenotazioni_id: '.$prenotazioni_id;
                postStartSessionRecording(true, $prenotazioni_id);
                echo 'Start Recording Sent';
                postEndSessionRecording(true, $prenotazioni_id);
                echo 'End Recording Sent';
            }
            

            return $id;
        } else {
            throw new PDOException("Errore, nessun ID specificato.");
        }
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il salvataggio del check dei dati di posizionamento: " . $e->getMessage());
    }
}

/**
 * Elimina il check per registrare i dati di posizionamento dei giocatori
 * 
 * @param int $prenotazioni_id L'ID della prenotazione di cui eliminare il check
 */
function delete_datapos($prenotazioni_id) {

    $con = get_connection();

    try {
        if ($prenotazioni_id) {
            $sql = "DELETE FROM dati_posizionamento_prenotazioni WHERE prenotazione=?";
            $query = $con->prepare($sql);
            $query->execute([$prenotazioni_id]);
        } else {
            throw new PDOException("Errore, nessun ID specificato.");
        }
    } catch (PDOException $e) {
        throw new PDOException("Errore durante l'eliminazione del check dei dati di posizionamento: " . $e->getMessage());
    }
}

/**
 * Elimina le telecamere associate a una prenotazione dal database.
 *
 * @param int $prenotazioni_id L'ID della prenotazione di cui eliminare le telecamere.
 *
 * @return void
 * 
 * @throws PDOException Se si verifica un errore durante l'eliminazione delle telecamere.
 */
function delete_cameras($prenotazioni_id)
{
    $con = get_connection();

    try {
        if ($prenotazioni_id) {
            $sql = "DELETE FROM telecamere_prenotazioni WHERE prenotazione=?";
            $query = $con->prepare($sql);
            $query->execute([$prenotazioni_id]);
        } else {
            throw new PDOException("Errore, nessun ID specificato.");
        }
    } catch (PDOException $e) {
        throw new PDOException("Errore durante l'eliminazione delle telecamere: " . $e->getMessage());
    }
}

/**
 * Salva un allenamento nel database.
 *
 * Registra un nuovo allenamento nel database con le informazioni specificate.
 *
 * @param string $inizio La data e l'ora di inizio dell'allenamento nel formato 'YYYY-MM-DD HH:mm:ss'.
 * @param string $fine La data e l'ora di fine dell'allenamento nel formato 'YYYY-MM-DD HH:mm:ss'.
 * @param array $squadra L'array contenente l'ID della squadra associata all'allenamento.
 * @param int $prenotazioni_id L'ID della prenotazione associata all'allenamento.
 * @return int L'ID dell'allenamento appena inserito nel database.
 * 
 * @throws PDOException Se si verifica un errore durante l'inserimento dell'allenamento nel database.
 */
function save_training($inizio, $fine, $squadra, $prenotazioni_id)
{
    $con = get_connection();

    try {
        $sql = "INSERT INTO allenamenti (`data_ora_inizio`, `data_ora_fine`, `id_squadra`, `prenotazione`) VALUES (:inizio, :fine, :squadra, :prenotazione)";
        $query = $con->prepare($sql);
        $query->bindParam(':inizio', $inizio);
        $query->bindParam(':fine', $fine);
        $query->bindParam(':squadra', $squadra['id']);
        $query->bindParam(':prenotazione', $prenotazioni_id);
        $query->execute();
        return $con->lastInsertId();
    } catch (PDOException $e) {
        throw new PDOException("Errore durante l'inserimento dell'allenamento nel database: " . $e->getMessage());
    }
}

/**
 * Salva una partita nel database.
 *
 * Registra una nuova partita nel database con le informazioni specificate.
 *
 * @param string $inizio La data e l'ora di inizio della partita nel formato 'YYYY-MM-DD HH:mm:ss'.
 * @param string $fine La data e l'ora di fine della partita nel formato 'YYYY-MM-DD HH:mm:ss'.
 * @param array $squadra L'array contenente l'ID della squadra di casa associata alla partita.
 * @param string $sport Il nome dello sport associato alla partita.
 * @param int $prenotazioni_id L'ID della prenotazione associata alla partita.
 * @return int L'ID della partita appena inserita nel database.
 * 
 * @throws PDOException Se si verifica un errore durante l'inserimento della partita nel database.
 */
function save_match($inizio, $fine, $squadra, $sport, $prenotazioni_id)
{
    $con = get_connection();

    try {
        $sql = "INSERT INTO partite (`data_ora_inizio`, `data_ora_fine`, `id_squadra_casa`, `sport`, `prenotazione`) VALUES (:inizio, :fine, :squadra, :sport, :prenotazione)";
        $query = $con->prepare($sql);
        $query->bindParam(':inizio', $inizio);
        $query->bindParam(':fine', $fine);
        $query->bindParam(':squadra', $squadra['id']);
        $query->bindParam(':sport', $sport);
        $query->bindParam(':prenotazione', $prenotazioni_id);
        $query->execute();
        return $con->lastInsertId();
    } catch (PDOException $e) {
        throw new PDOException("Errore durante l'inserimento della partita nel database: " . $e->getMessage());
    }
}

/**
 * Ottiene il nome dello sport associato a una squadra.
 *
 * Recupera il nome dello sport dal database corrispondente all'ID della squadra specificata.
 *
 * @param array $squadra L'array contenente l'ID della squadra.
 * @return string|null Il nome dello sport associato alla squadra, o null se non trovato.
 * 
 * @throws PDOException Se si verifica un errore durante la query al database.
 */
function getSportbyTeam($squadra)
{
    $con = get_connection();

    try {
        $sql = "SELECT sport
                FROM squadre
                WHERE squadre.id = :squadra";
        $query = $con->prepare($sql);
        $query->bindParam(':squadra', $squadra['id']);
        $query->execute();
        $result = $query->fetchColumn();
        return $result;
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il recupero del nome dello sport dalla squadra: " . $e->getMessage());
    }
}

/**
 * Elimina un allenamento e tutti i suoi dati correlati dal database.
 *
 * @param int $id_calendar_events ID dell'evento del calendario relativo all'allenamento da eliminare.
 * @return bool True se l'eliminazione è avvenuta con successo, False altrimenti.
 * 
 * @throws PDOException Se si verifica un errore durante la query al database.
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
    } catch (PDOException $e) {
        // Rollback delle modifiche in caso di errore
        $con->rollBack();
        throw new PDOException("Errore durante l'eliminazione dell'allenamento: " . $e->getMessage());
    }
}


/**
 * Ottiene l'ID della squadra associata a una determinata società sportiva.
 *
 * Recupera dal database l'ID della squadra che è associata alla società sportiva specificata.
 *
 * @param string $society Il nome della società sportiva.
 * @return array|false L'array associativo contenente l'ID della squadra, o false se non trovata o in caso di errore.
 * 
 * @throws PDOException Se si verifica un errore durante la query al database.
 */
function getSquadra($society)
{
    $con = get_connection();

    try {
        $sql = "SELECT squadre.id FROM squadre INNER JOIN societa_sportive ON societa_sportive.partita_iva = squadre.societa WHERE societa_sportive.nome = ?";
        $query = $con->prepare($sql);
        $query->execute([$society]);
        $squadra = $query->fetch(PDO::FETCH_ASSOC);
        return $squadra;
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il recupero dell'ID della squadra associata alla società sportiva: " . $e->getMessage());
    }
}

/**
 * Recupera tutti gli eventi dalla tabella "calendar_events" del database e li restituisce come JSON.
 *
 * @return string Una stringa JSON che rappresenta gli eventi. Se non ci sono eventi, la stringa JSON sarà vuota.
 * 
 * @throws PDOException Se si verifica un errore durante la query al database.
 */
function getEvents()
{
    $con = get_connection();

    try {
        $query = "SELECT * FROM calendar_events";
        $statement = $con->query($query);
        $events = $statement->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($events);
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il recupero degli eventi: " . $e->getMessage());
    }
}

/**
 * Ottiene le informazioni di un singolo evento dalla tabella "calendar_events" in base all'ID fornito e lo restituisce come JSON.
 *
 * @param int $id L'ID dell'evento da recuperare.
 * @return string Una stringa JSON che rappresenta l'evento. Se l'evento non viene trovato, la stringa JSON sarà vuota.
 * 
 * @throws PDOException Se si verifica un errore durante la query al database.
 */
function getEvent($id)
{
    $con = get_connection();

    try {
        $query = "SELECT * FROM calendar_events WHERE id = :id";
        $statement = $con->prepare($query);
        $statement->bindParam(':id', $id);
        $statement->execute();
        $event = $statement->fetch(PDO::FETCH_ASSOC);
        return json_encode($event);
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il recupero dell'evento: " . $e->getMessage());
    }
}

/**
 * Funzione per ottenere le informazioni di un evento dal database.
 * Recupera le informazioni dell'evento corrispondente all'ID specificato dalla tabella "prenotazioni".
 *
 * @param int $id L'ID dell'evento da recuperare.
 * @return string Le informazioni dell'evento nel formato JSON. Se l'evento non viene trovato, la stringa JSON sarà vuota.
 * 
 * @throws PDOException Se si verifica un errore durante la query al database.
 */
function getInfoEvent($id)
{
    $con = get_connection();

    try {
        $query = "SELECT ei.* FROM calendar_events ce INNER JOIN prenotazioni ei ON ce.id = ei.id_calendar_events WHERE ce.id = :id";
        $statement = $con->prepare($query);
        $statement->bindParam(':id', $id);
        $statement->execute();
        $event = $statement->fetch(PDO::FETCH_ASSOC);
        return json_encode($event);
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il recupero dell'evento: " . $e->getMessage());
    }
}

/**
 * Recupera gli incontri dal database.
 *
 * @return string JSON contenente gli incontri recuperati dal database. Se non ci sono incontri, la stringa JSON sarà vuota.
 * 
 * @throws PDOException Se si verifica un errore durante la query al database.
 */
function getMatches()
{
    $con = get_connection();

    try {
        $query = "SELECT ce.* FROM calendar_events ce 
                    INNER JOIN prenotazioni ei ON ce.id = ei.id_calendar_events
                    INNER JOIN partite ON partite.prenotazione = ei.id";
        $statement = $con->query($query);
        $events = $statement->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($events);
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il recupero degli incontri: " . $e->getMessage());
    }
}

/**
 * Recupera gli eventi per un allenatore specifico dal database.
 *
 * @param string $coach Il nome o l'identificatore dell'allenatore.
 * @return string Stringa JSON contenente gli eventi dell'allenatore. Se non ci sono eventi, la stringa JSON sarà vuota.
 * 
 * @throws PDOException Se si verifica un errore durante la query al database.
 */
function getCoachEvents($coach)
{
    $con = get_connection();

    try {
        $query = "SELECT ce.* FROM calendar_events ce 
                  INNER JOIN prenotazioni ei ON ce.id = ei.id_calendar_events 
                  INNER JOIN allenatori_squadre on allenatori_squadre.id_squadra = ei.id_squadra 
                  WHERE allenatori_squadre.email_allenatore = :coach";
        $statement = $con->prepare($query);
        $statement->bindParam(':coach', $coach);
        $statement->execute();
        $events = $statement->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($events);
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il recupero degli eventi dell'allenatore: " . $e->getMessage());
    }
}

/**
 * Recupera gli eventi associati alle società sportive gestite da un responsabile (manager).
 *
 * Questa funzione accetta come parametro l'indirizzo email del responsabile e restituisce
 * tutti gli eventi presenti nel calendario associati alle squadre delle società gestite da tale responsabile.
 * Gli eventi vengono restituiti nel formato JSON.
 *
 * @param string $manager L'indirizzo email del responsabile.
 * @return string|null Una stringa JSON contenente gli eventi associati alle società sportive gestite dal responsabile.
 *                    Restituisce NULL in caso di errori o nessun risultato.
 * @throws PDOException Se si verifica un errore durante l'esecuzione della query.
 */
function getSocietyEvents($manager)
{
    $con = get_connection();

    try {
        $query = "SELECT ce.* FROM calendar_events ce
                  INNER JOIN prenotazioni ei ON ce.id = ei.id_calendar_events
                  INNER JOIN squadre s ON ei.id_squadra = s.id
                  INNER JOIN societa_sportive sp ON s.societa = sp.partita_iva
                  WHERE sp.responsabile = :manager";
        $statement = $con->prepare($query);
        $statement->bindParam(':manager', $manager);
        $statement->execute();
        $events = $statement->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($events);
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il recupero degli eventi dell'allenatore: " . $e->getMessage());
    }
}

/**
 * Recupera la nota associata a un evento dalla tabella "prenotazioni" del database, in base all'ID dell'evento fornito.
 *
 * @param int $id L'ID dell'evento per il quale si desidera recuperare la nota.
 * @return array Un array associativo contenente la nota dell'evento. Se la nota non viene trovata, l'array sarà vuoto.
 * 
 * @throws PDOException Se si verifica un errore durante la query al database.
 */
function getNote($id)
{
    $con = get_connection();

    try {
        $query = "SELECT nota FROM prenotazioni WHERE id_calendar_events = :id";
        $statement = $con->prepare($query);
        $statement->bindParam(':id', $id);
        $statement->execute();
        $note = $statement->fetch(PDO::FETCH_ASSOC);
        return $note;
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il recupero della nota dell'evento: " . $e->getMessage());
    }
}

/** Ottiene il colore associato a uno specifico sport.
 *
 * @param string $sport Lo sport per il quale si desidera ottenere il colore.
 * @return string Una stringa che rappresenta il colore corrispondente allo sport specificato. Se lo sport non corrisponde a nessuna delle opzioni predefinite, viene restituito il colore di default.
 */
function getEventColor($society)
{
    $con = get_connection();

    try {
        $query = "SELECT sport FROM squadre INNER JOIN societa_sportive sp ON sp.partita_iva = squadre.societa WHERE sp.Nome = :societa";
        $statement = $con->prepare($query);
        $statement->bindParam(':societa', $society);
        $statement->execute();
        $sport = $statement->fetchColumn();
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il recupero della data e dell'ora dell'evento: " . $e->getMessage());
    }

    if ($sport == 'Calcio') {
        return "purple";
    } else if ($sport == 'Pallavolo') {
        return "darkorange";
    } else if ($sport == 'Basket') {
        return "darkgreen";
    }
    return 'lightblue';
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
 * Recupera il tipo di utente (userType) associato all'utente corrente o all'email fornita.
 *
 * @param string|null $email L'email dell'utente per cui recuperare il tipo di utente. Se non fornita, viene utilizzata l'email memorizzata nel cookie.
 * @return string Il tipo di utente dell'utente corrente o dell'utente con l'email fornita.
 * 
 * @throws PDOException Se si verifica un errore durante la query al database.
 */
function getUserType($email = null)
{
    $con = get_connection();

    try {
        if ($email === null) {
            // Se l'email non è fornita, utilizza l'email memorizzata nel cookie
            $email = $_COOKIE['email'];
        }

        $userInfo = get_user_info($con, $email);
        return $userInfo['userType'];
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il recupero del tipo di utente: " . $e->getMessage());
    }
}

/**
 * Recupera la data e l'ora di un evento specifico dal database.
 *
 * Recupera dal database la data di inizio e l'ora di un evento corrispondente all'ID specificato.
 * Restituisce i dati in formato JSON.
 *
 * @param int $id L'ID dell'evento di cui ottenere la data e l'ora.
 * @return string I dati della data e dell'ora dell'evento in formato JSON.
 * 
 * @throws PDOException Se si verifica un errore durante la query al database.
 */
function getDatetimeEvent($id)
{
    $con = get_connection();

    try {
        $query = "SELECT start, startTime FROM calendar_events WHERE id = :id";
        $statement = $con->prepare($query);
        $statement->bindParam(':id', $id);
        $statement->execute();
        $date = $statement->fetch(PDO::FETCH_ASSOC);
        return json_encode($date);
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il recupero della data e dell'ora dell'evento: " . $e->getMessage());
    }
}


/**
 * Ottiene elenco delle società sportive dal database.
 *
 * Recupera dal database l'elenco dei nomi delle società sportive e li restituisce come opzioni
 * per un elemento di selezione HTML.
 *
 * @return string Le opzioni HTML per l'elemento di selezione delle società sportive.
 *
 * @throws PDOException Se si verifica un errore durante la query al database.
 */
function getSocieties()
{
    $con = get_connection();

    try {
        $query = "SELECT nome FROM societa_sportive";
        $stmt = $con->query($query);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $options = '';
        foreach ($result as $row) {
            $nomeSocieta = $row['nome'];
            $options .= "<option value='$nomeSocieta'>$nomeSocieta</option>";
        }

        return $options;
    } catch (PDOException $e) {
        throw new PDOException("Errore durante il recupero dell'elenco delle società sportive: " . $e->getMessage());
    }
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

///////////////////////////
// GET e POST Management //
///////////////////////////

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'save-event') { // salvataggio di un evento
        try {
            $data = [
                // Tabella calendar_events
                'groupId' => $_POST['groupId'] ?? null,
                'allDay' => $_POST['allDay'] ?? null,
                'startDate' => $_POST['start-date'] ?? null,
                'endDate' => $_POST['end-date'] ?? null,
                'daysOfWeek' => json_encode($_POST['daysOfWeek'] ?? null),
                'startTime' => $_POST['startTime'] ?? null,
                'endTime' => $_POST['endTime'] ?? null,
                'startRecur' => $_POST['startRecur'] ?? null,
                'endRecur' => $_POST['endRecur'] ?? null,
                'url' => $_POST['url'] ?? null,

                // Tabella prenotazioni
                'society' => $_POST['society'] ?? null,
                'note' => $_POST['description'] ?? null,
                'eventType' => $_POST['event_type'] ?? null,

                // Tabella check della registrazione dei dati di posizionamento dei giocatori
                'dataposCheck' => $_POST['datapos-checkbox'] ?? null,

                // Tabella telecamere
                'cameras' => json_encode($_POST['camera'] ?? null),
                'author' => $_POST['author'] ?? null,
            ];

            // Converti gli elementi vuoti in null, necessario per alcuni webserver
            foreach ($data as $key => $value) {
                print_r('FIELD= ' . $data[$key] . ' ');
                if ($value === '') {
                    $data[$key] = null;
                }
            }

            // Controllo che esistano campi obbligatori (society e startDate)
            if ($data['society'] && $data['startDate']) {
                $id = save_event($data['groupId'], $data['allDay'], $data['startDate'], $data['endDate'], $data['daysOfWeek'], $data['startTime'], $data['endTime'], $data['startRecur'], $data['endRecur'], $data['url'], $data['society'], $data['note'], $data['eventType'], $data['dataposCheck'], $data['cameras'], $data['author']);
                $response = array('status' => 'success', 'id' => $id);
            } else {
                $response = array('status' => 'error', 'message' => 'Missing required fields');
            }
        } catch (Exception $e) {
            // Stampa l'errore nel log del server e/o visualizzalo nel browser
            print_r('Errore: ' . $e->getMessage());
            $response = array('status' => 'error', 'message' => 'An error occurred. Please check the server logs for more information.');
        }

        header('Content-Type: application/json');
        echo json_encode($response);
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
    } elseif ($action == 'get-society-event') { //recupero tutti gli eventi da calendar_event dove allena coach
        $manager = isset($_GET['responsabile']) ? $_GET['responsabile'] : null;
        if ($manager) {
            header('Content-Type: application/json');
            echo getSocietyEvents($manager);
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
    } elseif ($action == 'edit-event') { // modifica evento già esistente
        $data = array(
            // Tabella Calendar Event
            'id' => $_POST['id'] ?? null,
            'groupId' => $_POST['groupId'] ?? null,
            'startDate' => $_POST['startDate'] ?? null,
            'endDate' => $_POST['endDate'] ?? null,
            'startTime' => $_POST['startTime'] ?? null,
            'endTime' => $_POST['endTime'] ?? null,
            // Tabella prenotazioni
            'url' => $_POST['url'] ?? null,
            'society' => $_POST['society'] ?? null,
            'note' => $_POST['note'] ?? null,
        );

        // Converti gli elementi vuoti in null, necessario per alcuni webserver
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }
            
        // Controllo che esistano campi obbligatori (society e startDate)
        if ($data['society'] && $data['startDate']) {
            $id = edit_training($data['groupId'], $data['startDate'], $data['endDate'], $data['startTime'], $data['endTime'], $data['url'], $data['society'], $data['note'], $data['id']);
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
    } elseif ($action == 'get-user-type') { // recupero utente
        header('Content-Type: application/json');

        // Decodifica il payload JSON della richiesta POST
        $email = isset($_POST['email']) ? $_POST['email'] : null;

        // Verifica se è stata fornita l'email come parametro nella richiesta POST
        if ($email) {
            // Se è presente l'email nel payload JSON, passa l'email alla funzione getUserType()
            $response = getUserType($email);
        } else {
            // Se l'email non è presente, utilizza l'email memorizzata nel cookie come prima
            $response = getUserType();
        }

        echo json_encode($response);
    } else {
        // Parametro 'action' mancante
    }
}

/**
 * Funzione che ottiene lo status dell'endpoint che contiene i dati dell'IoT
 */
function getEndpointStatus() {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_PORT => "7000",
        CURLOPT_URL => "http://10.218.20.28:7000/?=",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        return $response;
    }
}

/**
 * Funzione che ottiene la lista dei file che possono essere scaricati
 */
function getEndpointDownloadList() {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_PORT => "7000",
        CURLOPT_URL => "http://10.218.20.28:7000/done",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        return $response;
    }
}

/**
 * Funzione che fa iniziare la registrazione della sessione
 */
function postStartSessionRecording($isTest, $sessione_id) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_PORT => "7000",
        CURLOPT_URL => "http://10.218.20.28:7000/start?=",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{\n\t\"session_id\": \"test-sessione_".$sessione_id."\",\n\t\"test\": ".$isTest."\n}",
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        return $response;
    }
}

/**
 * Funzione che ferma la registrazione della sessione
 */
function postEndSessionRecording($isTest, $sessione_id) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_PORT => "7000",
        CURLOPT_URL => "http://10.218.20.28:7000/stop?=",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{\n\t\"session_id\": \"test-sessione_".$sessione_id."\",\n\t\"test\": ".$isTest."\n}",
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        return $response;
    }
}

/**
 * Funzione che cancella la sessione passata per parametro
 */
function deleteSessionRecording($session) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_PORT => "7000",
        CURLOPT_URL => "http://10.218.20.28:7000/done/".$session,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        return $response;
    }
}