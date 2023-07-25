<?php
require_once __DIR__.'/../authentication/db_connection.php';

/** Ottiene tutte le squadre dal database.
 *
 * @return string JSON contenente i dati delle squadre.
 */
function getTeams()
{
  $con = get_connection();
  $query = "SELECT * FROM squadre";
  $statement = $con->query($query);
  $teams = $statement->fetchAll(PDO::FETCH_ASSOC);
  return json_encode($teams);
}

/** Ottiene i dettagli di una squadra dal database in base all'ID.
 *
 * @param int $id ID della squadra da recuperare.
 * @return string JSON contenente i dettagli della squadra.
 */
function getTeam($id)
{
  $con = get_connection();
  $query = "SELECT * FROM squadre WHERE id = :id";
  $statement = $con->prepare($query);
  $statement->bindParam(':id', $id);
  $statement->execute();
  $team = $statement->fetch(PDO::FETCH_ASSOC);
  return json_encode($team);
}

/** Ottiene i dettagli della squadra di un allenatore dal database in base all'email dell'allenatore.
 *
 * @param string $coach_email Email dell'allenatore.
 * @return string JSON contenente i dettagli della squadra dell'allenatore.
 */
function getTeambyCoach($coach_email)
{
  try {
    $con = get_connection();
    $query = "SELECT squadre.*
    FROM squadre
    INNER JOIN allenatori_squadre ON squadre.id = allenatori_squadre.id_squadra
    INNER JOIN allenatori ON allenatori.email = allenatori_squadre.email_allenatore
    WHERE allenatori.email = :coach";
    $statement = $con->prepare($query);
    $statement->bindParam(':coach', $coach_email);
    $statement->execute();
    $team = $statement->fetch(PDO::FETCH_ASSOC);

    if ($team) {
      // Success response
      $response = [
        'status' => 'success',
        'team' => $team
      ];
    } else {
      // Error response if no team found
      $response = [
        'status' => 'error',
        'message' => 'No team found for the given coach'
      ];
    }

    return json_encode($response);
  } catch (Exception $e) {
    // Error response if an exception occurs
    $response = [
      'status' => 'error',
      'message' => 'An error occurred: ' . $e->getMessage()
    ];
    return json_encode($response);
  }
}


/**
 * Ottiene le società sportive in base all'email del responsabile.
 * 
 * @param {string} $boss - L'email del responsabile da utilizzare come filtro.
 * @return {void} - La funzione restituisce una risposta JSON contenente le società sportive o un messaggio di errore.
 */
function getSocietyByBoss($boss)
{
    try {
        $con = get_connection();
        $query = "SELECT sp.*
                  FROM societa_sportive AS sp
                  WHERE sp.responsabile = :email";
        $statement = $con->prepare($query);
        $statement->bindParam(':email', $boss);
        $statement->execute();
        $society = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($society) {
            // Risposta di successo se sono state trovate società sportive
            $response = [
                'status' => 'success',
                'society' => $society
            ];
        } else {
            // Risposta di errore se nessuna squadra è stata trovata per il responsabile fornito
            $response = [
                'status' => 'error',
                'message' => 'Nessuna società sportiva trovata per il responsabile fornito'
            ];
        }

        echo json_encode($response);
    } catch (Exception $e) {
        // Risposta di errore in caso di eccezione
        $response = [
            'status' => 'error',
            'message' => 'Si è verificato un errore: ' . $e->getMessage()
        ];
        echo json_encode($response);
    }
}


/**
 * Ottiene gli allenatori in base all'email del responsabile.
 * 
 * @param {string} $responsabile_email - L'email del responsabile da utilizzare come filtro.
 * @return {string} - Una stringa JSON contenente i dati degli allenatori o un messaggio di errore.
 */
function getCoachesbyBoss($responsabile_email)
{
    try {
        $con = get_connection();
        $query = "SELECT DISTINCT p.* 
                  FROM persone AS p 
                  INNER JOIN allenatori AS coach ON coach.email = p.email 
                  INNER JOIN allenatori_squadre AS a_s ON a_s.email_allenatore = coach.email
                  INNER JOIN squadre ON a_s.id_squadra = squadre.id 
                  INNER JOIN societa_sportive AS sp ON sp.partita_iva = squadre.societa 
                  WHERE sp.responsabile = :email";
        $statement = $con->prepare($query);
        $statement->bindParam(':email', $responsabile_email);
        $statement->execute();
        $coaches = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($coaches) {
            // Risposta di successo se sono stati trovati allenatori
            $response = [
                'status' => 'success',
                'coaches' => $coaches
            ];
        } else {
            // Risposta di errore se nessun allenatore è stato trovato per il responsabile fornito
            $response = [
                'status' => 'error',
                'message' => 'Nessun allenatore trovato per il responsabile fornito'
            ];
        }

        return json_encode($response);
    } catch (Exception $e) {
        // Risposta di errore in caso di eccezione
        $response = [
            'status' => 'error',
            'message' => 'Si è verificato un errore: ' . $e->getMessage()
        ];
        return json_encode($response);
    }
}


/** Ottiene i dettagli dei giocatori di una squadra dal database in base all'ID della squadra.
 *
 * @param int $teamid ID della squadra.
 * @return string JSON contenente i dettagli dei giocatori della squadra.
 */
function getPlayersbyTeam($teamid)
{
  try {
    $con = get_connection();
    $query = "SELECT DISTINCT persone.* 
    FROM persone, squadre INNER JOIN giocatori_squadre ON squadre.id = giocatori_squadre.id_squadra 
    INNER JOIN giocatori ON giocatori_squadre.email_giocatore = giocatori.email 
    and giocatori.email = email WHERE squadre.id = :id and giocatori.email = persone.email";
    $statement = $con->prepare($query);
    $statement->bindParam(':id', $teamid);
    $statement->execute();
    $players = $statement->fetchAll(PDO::FETCH_ASSOC);

    if ($players) {
      // Success response
      $response = [
        'status' => 'success',
        'players' => $players
      ];
    } else {
      // Error response if no team found
      $response = [
        'status' => 'error',
        'message' => 'No team found for the given coach'
      ];
    }
  } catch (Exception $e) {
    // Error response if an exception occurs
    $response = [
      'status' => 'error',
      'message' => 'An error occurred: ' . $e->getMessage()
    ];
  }
  return json_encode($response);
}

/**
 * Elimina l'associazione di un giocatore da una squadra dal database tramite la sua email.
 *
 * @param string $email Email del giocatore.
 * @return string JSON contenente il successo o meno dell'operazione e la lista aggiornata dei giocatori della squadra.
 */
function deletePlayerbyEmail($email){
  try {
    $con = get_connection();

    // Seleziona l'ID della squadra associata al giocatore
    $query = "SELECT id_squadra FROM giocatori_squadre WHERE email_giocatore = :email";
    $statement = $con->prepare($query);
    $statement->execute([':email' => $email]);
    $teamId = $statement->fetchColumn();

    // Elimina il giocatore dalla tabella giocatori_squadre
    $query = "DELETE FROM giocatori_squadre WHERE email_giocatore = :email";
    $statement = $con->prepare($query);
    $statement->execute([':email' => $email]);

    // Ottieni la lista aggiornata dei giocatori della squadra
    $infoJson = getPlayersbyTeam($teamId);
    $info = json_decode($infoJson, true); // Converti l'oggetto JSON in un array associativo

    $response = [
      'status' => 'success',
      'players' => $info['players']
    ];
    return json_encode($response);
  } catch (Exception $e) {
    // Risposta di errore se si verifica un'eccezione
    $response = [
      'status' => 'error',
      'message' => 'Si è verificato un errore: ' . $e->getMessage()
    ];
  }
  return json_encode($response);
}

/**
 * Elimina l'associazione di un giocatore da una squadra dal database tramite la sua email.
 *
 * @param string $email Email del giocatore.
 * @return string JSON contenente il successo o meno dell'operazione e la lista aggiornata dello staff della squadra.
 */
function deleteStaffbyEmail($email, $boss)
{
    try {
        $con = get_connection();
        $con->beginTransaction();

        // Elimina l'allenatore dalla squadra
        $query = "DELETE a_s FROM allenatori_squadre AS a_s
                  INNER JOIN squadre AS s ON a_s.id_squadra = s.id
                  INNER JOIN societa_sportive AS sp ON s.societa = sp.partita_iva
                  WHERE a_s.email_allenatore = :email AND sp.responsabile = :boss";
        $statement = $con->prepare($query);
        $statement->execute([':email' => $email, ':boss' => $boss]);

        // Ottieni la lista aggiornata dello staff della squadra
        $infoJson = getCoachesbyBoss($boss);
        $info = json_decode($infoJson, true); // Converti l'oggetto JSON in un array associativo

        // Controlla se la chiave 'coaches' è presente nell'array
        if (isset($info['coaches'])) {
            $coaches = $info['coaches'];
        } else {
            $coaches = []; // Se la chiave 'coaches' non è presente, assegna un array vuoto
        }

        $con->commit();

        $response = [
            'status' => 'success',
            'coaches' => $coaches
        ];
    } catch (Exception $e) {
        // Rollback in caso di eccezione
        $con->rollBack();

        // Risposta di errore con messaggio dettagliato
        $response = [
            'status' => 'error',
            'message' => 'Si è verificato un errore durante l\'eliminazione dello staff: ' . $e->getMessage()
        ];
    }

    return json_encode($response);
}



///////////////////////////
// GET e POST Management //
///////////////////////////

if (isset($_GET['action'])) {
  $action = $_GET['action'];

  if ($action == 'get-teams') { // Richiesta per ottenere tutte le squadre
    header('Content-Type: application/json');
    echo getTeams();
  } elseif ($action == 'get-team') { // Richiesta per ottenere i dettagli di una squadra specifica
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    header('Content-Type: application/json');
    if ($id) {
      echo getTeam($id);
    }
  } elseif ($action == 'get-team-by-coach') { // Richiesta per ottenere la squadra di un allenatore specifico
    $coach = isset($_GET['coach']) ? $_GET['coach'] : null;
    if ($coach) {
      header('Content-Type: application/json');
      echo getTeambyCoach($coach);
    }
  } elseif ($action == 'get-players-by-team') { // Richiesta per ottenere i giocatori di una squadra specifica
    header('Content-Type: application/json');
    $team = isset($_GET['team']) ? $_GET['team'] : null;
    echo getPlayersbyTeam($team);
  } elseif ($action == 'get-coaches-by-boss') { // Richiesta per ottenere gli allenatori di un responsabile specifico
    header('Content-Type: application/json');
    $email = isset($_GET['boss_email']) ? $_GET['boss_email'] : null;
    echo getCoachesbyBoss($email);
  }
  elseif ($action == 'get-society-by-boss') { // Richiesta per ottenere la società di un responsabile specifico
    header('Content-Type: application/json');
    $email = isset($_GET['boss']) ? $_GET['boss'] : null;
    echo getSocietyByBoss($email);
  }
  elseif ($action == 'delete-player') { // Richiesta per eliminare una associazione giocatore squadra
    header('Content-Type: application/json');
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    echo deletePlayerbyEmail($email);
  }
  elseif ($action == 'delete-staff') { // Richiesta per eliminare una associazione allenatore squadra
    header('Content-Type: application/json');
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $boss = isset($_POST['boss_email']) ? $_POST['boss_email'] : null;
    echo deleteStaffbyEmail($email,$boss);
  }
}