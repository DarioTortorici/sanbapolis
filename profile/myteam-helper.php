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


function getSocietyByBoss($boss) {
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
          // Success response
          $response = [
              'status' => 'success',
              'society' => $society
          ];
      } else {
          // Error response if no team found
          $response = [
              'status' => 'error',
              'message' => 'No team found for the given coach'
          ];
      }

      echo json_encode($response);
  } catch (Exception $e) {
      // Error response if an exception occurs
      $response = [
          'status' => 'error',
          'message' => 'An error occurred: ' . $e->getMessage()
      ];
      echo json_encode($response);
  }
}


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
      // Success response
      $response = [
        'status' => 'success',
        'coaches' => $coaches
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
}