<?php
require_once '../authentication/auth-helper.php';
require_once '../classes/Person.php';
require_once '../classes/Team.php';
require_once '../classes/Session.php';
require_once '../classes/Reservation.php';
require_once '../classes/Bucket.php';

use InfluxDB2\Point;

function generaDati(){
    for($j = 1; $j < 11; $j++){
        $myfile = fopen("./files_csv/session_$j.csv", "w") or die("Unable to open file!");
        for($i = 0; $i < 10; $i++){
            $x = rand(1, 100);
            $y = rand(1, 100);
            $z = rand(1, 100);
            $txt = "misura,session=$j,id=1 x=\"$x\",y=\"$y\",z=\"$z\"\n";
            fwrite($myfile, $txt);
        }
        fclose($myfile);
    }
}

function myVarDump($obj, $messaggio = null){
    if($messaggio != null){
        echo "$messaggio: ";
    }
    var_dump($obj);
    echo "<br><br>";
}

/**
 * legge le linee di un file e restituisce un array
 */
function getFileLines($path_file){
    $lines = array();
    $fn = fopen($path_file,"r");
    while(! feof($fn))  {
    $result = fgets($fn);
        $lines[] = $result;
    }
    fclose($fn);
    return $lines;
}

/**
 * Controlla che le credenziali inviate tramite Http Basic access authentication siano corrette
 * @param PDO $pdo La connessione al db
 * @param string $email
 * @param string $password
 * @return true|false true in caso di credenziali corrette, false altrimenti 
 */
function loginApi($pdo, $email, $password){
    $response = false;

    $error = array();
    /**
     * Valida e sanifica un input di tipo email.
     * @param string $input L'input email da validare e sanificare.
     * @return string|null Restituisce l'email validata e sanificata se è valida, altrimenti restituisce null.
     */
    $email = validate_input_email($email);
    if (empty($email)) {$error[] = "You forgot to enter your Email";}

    /**
     * Valida e sanifica un input di tipo testo.
     * @param string $input L'input testo da validare e sanificare.
     * @return string|null Restituisce il testo validato e sanificato se è valido, altrimenti restituisce null.
     */
    $password = validate_input_text($password);
    if (empty($password)) {$error[] = "You forgot to enter your password";}

    if (empty($error)) {
        $query = "SELECT * FROM persone WHERE email=:email";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($row)) {
            if (password_verify($password, $row['digest_password'])) {
                $response = true;
            }
        } 
    }

    return $response;
}

/** Restitiusce la squadra relativa all'id specificato
 * @param PDO La connessione al db
 * @param integer $id 
 * @return Team la squadra cercata
 */
function getTeamFromId($pdo, $id){
    $team = null;
    $query = "SELECT * FROM squadre WHERE id = '$id'";
    $statement = $pdo->query($query);
    $publishers = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($publishers) {
        foreach ($publishers as $publisher) {
            try{                
                $id = $publisher['id'];
                $name = $publisher['nome'];
                $society = $publisher['societa'];
                $sport = $publisher['sport'];
                $code = $publisher['codice'];
                $team = new Team($id, $name, $society, $sport, $code);
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
        }
    }
    return $team;
}


/** Restitiusce le sessioni relative all'id specificato
 * @param PDO La connessione al db
 * @param string $id l'id specificato
 * @return Session la sessione
 */
function getSessionFromId($pdo, $id){
    $session = null;
    $query = "SELECT * FROM sessioni_registrazione WHERE id = '$id'";
    $statement = $pdo->query($query);
    $publishers = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($publishers) {
        foreach ($publishers as $publisher) {
            try{                
                $id = $publisher['id'];
                $autore = $publisher['autore'];
                $data_ora_inizio = $publisher['data_ora_inizio'];
                $data_ora_fine = $publisher['data_ora_fine'];
                $prenotazione = $publisher['prenotazione'];
                $session = new Session($id, $autore, $data_ora_inizio, $data_ora_fine, $prenotazione);
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
        }
    }
    return $session;
}

/** Restitiusce la prenotazione relativa all'id specificato
 * @param PDO La connessione al db
 * @param integer $id 
 * @return Reservation la prenotazione cercata
 */
function getReservationFromId($pdo, $id){
    $reservation = null;
    $query = "SELECT * FROM prenotazioni WHERE id = '$id'";
    $statement = $pdo->query($query);
    $publishers = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($publishers) {
        foreach ($publishers as $publisher) {
            try{                
                $id = $publisher['id'];
                $autore = $publisher['autore_prenotazione'];
                $data_ora_inizio = $publisher['data_ora_inizio'];
                $data_ora_fine = $publisher['data_ora_fine'];
                $team = $publisher['id_squadra'];
                $calendar_event = $publisher['id_calendar_events'];
                $note = $publisher['nota'];
                $reservation = new Reservation($id, $autore, $data_ora_inizio, $data_ora_fine, $team, $calendar_event, $note);
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
        }
    }
    return $reservation;
}

/**
 * Restituisce un'stza della classe Bucket che rappresenta un bucket del database influxdb
 * @param PDO La connessione al db
 * @param integer l'id della squadra specificata
 * @return Bucket il bucket cercato, altrimenti null
 */
function getBucketFromTeam($pdo, $team){
    $bucket = null;
    $query = "SELECT * FROM buckets_influxdb WHERE squadra = '$team'";
    $statement = $pdo->query($query);
    $publishers = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($publishers) {
        foreach ($publishers as $publisher) {
            try{                
                $url = $publisher['locazione'];
                $name = $publisher['nome'];
                $token = $publisher['token'];
                $id_team = $publisher['squadra'];
                $bucket = new Bucket($url, $name, $token, $id_team);
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
        }
    }
    return $bucket;
}

/**
 * Verifica se esiste la sessione di registrazione specificata
 * in caso positivo vinene restituita un'istanza della classe Bucket, altrimenti un messaggio di errore in json
 * @param PDO $pdo La connessione al db
 * @param integer $id l'id della sessione specificata
 * @return mixed Il bucket in caso di successo, un array con il messaggio di errore altrimenti
 */
function getBucketFromSession($pdo, $session_id){
    $message = array();
    
    $session = getSessionFromId($pdo, $session_id);
    if($session != null){
        $reservation = getReservationFromId($pdo, $session->getReservation());
        if($reservation != null){
            $team = getTeamFromId($pdo, $reservation->getTeam());
            if($team != null){
                $bucket = getBucketFromTeam($pdo, $team->getId());
                $message = $bucket;
            }
        }else{$message['error'] = 'recording session not found';}
    }
    else{$message['error'] = 'recording session not found';}

    return $message;
}