<?php

use JetBrains\PhpStorm\Internal\ReturnTypeContract;

require_once '../authentication/db_connection.php';

// verifica se la richiesta è una chiamata AJAX.
function is_ajax_request()
{
    if (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
    ) {
        return true;
    }
    return false;
}

// elimina un evento nel database
function delete_training($event_id)
{
    $con = get_connection();

    // Elimina le righe figlie nella tabella "event_info"
    $sql_delete_event_info = "DELETE FROM event_info WHERE event_id = ?";
    $query_delete_event_info = $con->prepare($sql_delete_event_info);
    $query_delete_event_info->execute([$event_id]);

    // Elimina l'evento dalla tabella "calendar_events"
    $sql_delete_event = "DELETE FROM calendar_events WHERE id = ?";
    $query_delete_event = $con->prepare($sql_delete_event);
    $query_delete_event->execute([$event_id]);

    // Verifica se le query di eliminazione hanno avuto successo
    if ($query_delete_event_info->rowCount() > 0 && $query_delete_event->rowCount() > 0) {
        return true;
    } else {
        // Almeno una delle eliminazioni non è riuscita
        return false;
    }
}

// recupera un singolo evento dal database utilizzando l'ID dell'evento.
function get_one_training($id)
{
    $results = [];
    try {
        $con = get_connection();
        $query = $con->prepare("SELECT * from calendar_events WHERE id=? LIMIT 1");
        $query->execute([$id]);
        $results = $query->fetchAll();
        if (isset($results[0])) {
            $results = $results[0];
        } else {
            $results = [];
        }
    } catch (Exception $e) {
    }
    return $results;
}

// Recupera tutti gli eventi dal database e li restituisce come JSON.
function getEvents()
{
    $con = get_connection();
    $query = "SELECT * FROM calendar_events";
    $statement = $con->query($query);
    $events = $statement->fetchAll(PDO::FETCH_ASSOC);
    return json_encode($events);
}

// Recupera un singolo evento dal database utilizzando l'ID dell'evento e lo restituisce come JSON.
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

// Recupera tutti gli eventi dal database e li restituisce come un array.
function getTrainings()
{
    $results = [];
    try {
        $con = get_connection();
        $query = $con->prepare("SELECT * from calendar_events");
        $query->execute([]);
        $results = $query->fetchAll();
    } catch (Exception $e) {
    }
    return $results;
}

// Restituisce il colore dell'evento in base allo sport specificato.
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

// Gestione GET e POST
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'save-event') { // salvataggio di un evento
        //tabella calendar_event
        $id = isset($_POST['id']) ? $_POST['id'] : null;
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

        $daysOfWeek = json_encode($daysOfWeek);

        //tabella event-info 
        $society = isset($_POST['society']) ? $_POST['society'] : null;
        $sport = isset($_POST['sport']) ? $_POST['sport'] : null;
        $coach = isset($_POST['coach']) ? $_POST['coach'] : null;
        $note = isset($_POST['description']) ? $_POST['description'] : null;
        $eventType = isset($_POST['event_type']) ? $_POST['event_type'] : null;

        //Sono obbligatori society e startdate ed effettuiamo il controllo che esistano
        if ($society && $startDate) {
            $id = save_training($groupId, $allDay, $startDate, $endDate, $daysOfWeek, $startTime, $endTime, $startRecur, $endRecur, $url, $society, $sport, $coach, $note, $eventType, $id);
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
    } elseif ($action == 'save-description') { //salvataggio descrizione
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $description = isset($_POST['description']) ? $_POST['description'] : null;
        $con = get_connection();
        $sql = "UPDATE events SET `description`=? WHERE id=?";
        $query = $con->prepare($sql);
        $query->execute([$description, $id]);
        header('Content-Type: application/json');
        echo json_encode([
            'description' => nl2br($description)
        ]);
    } elseif ($action == 'next-date') {
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $next = isset($_POST['next']) ? $_POST['next'] : null;
        $goal = get_one_training($id);
        $currentDate = $goal['startdate'];
        $nextDate = null;
        if ($next == 'day') {
            $nextDate = date("Y-m-d", strtotime($currentDate . " +1 day"));
        } else if ($next == 'week') {
            $nextDate = date("Y-m-d", strtotime($currentDate . " +1 week"));
        } else if ($next == 'month') {
            $nextDate = date("Y-m-d", strtotime($currentDate . " +1 month"));
        } else if ($next == 'year') {
            $nextDate = date("Y-m-d", strtotime($currentDate . " +1 year"));
        }
        $con = get_connection();
        $sql = "UPDATE events SET `startdate`=? WHERE id=?";
        $query = $con->prepare($sql);
        $query->execute([$nextDate, $id]);
        echo 'good';
    } elseif ($action == 'delete-event') {
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        if (delete_training($id)) {
            $response = array('status' => 'success', 'message' => 'Evento eliminato con successo');
        } else {
            $response = array('status' => 'error', 'message' => 'Richiesta non valida');
        }

        echo json_encode($response);
    } else {
        // Invalid action
    }
} else {
    // Missing 'action' parameter
}
