<?php
require_once('db_connection.php');
/**
 *  Accetta una stringa $textValue come input e la valida per assicurarsi che non sia vuota. 
 *  Se la stringa non è vuota, viene effettuata una pulizia dei caratteri illegali tramite la funzione 
 *  filter_var() con l'opzione FILTER_UNSAFE_RAW. 
 *  Infine, la stringa pulita viene restituita come output. Se la stringa è vuota, viene restituita una stringa vuota.
 *  @param string $textValue Il testo da validare.
 *  @return string Il testo validato o una stringa vuota se il testo è vuoto
 */
function validate_input_text($textValue)
{
    if (!empty($textValue)) {
        $trim_text = trim($textValue);
        // rimuove caratteri illegali
        $sanitize_str = filter_var($trim_text, FILTER_UNSAFE_RAW);
        return $sanitize_str;
    }
    return '';
}

/**
 *  Accetta una stringa $emailValue come input e la valida per assicurarsi che non sia vuota. 
 *  Se la stringa non è vuota, viene effettuata una pulizia dei caratteri illegali tramite la funzione
 *  filter_var() con l'opzione FILTER_SANITIZE_EMAIL.
 *  Infine, la stringa pulita viene restituita come output. Se la stringa è vuota, viene restituita una stringa vuota.
 * @param string $emailValue L'indirizzo email da validare.
 * @return string L'indirizzo email validato o una stringa vuota se l'indirizzo email è vuoto.
 */
function validate_input_email($emailValue)
{
    if (!empty($emailValue)) {
        $trim_text = trim($emailValue);
        // rimuove caratteri illegali
        $sanitize_str = filter_var($trim_text, FILTER_SANITIZE_EMAIL);
        return $sanitize_str;
    }
    return '';
}

/**
 * Accetta una stringa $password come input e la verifica rispetto a determinati requisiti:
 * 1. Deve avere una lunghezza minima di 8 caratteri.
 * 2. Deve contenere almeno una lettera maiuscola.
 * 3. Deve contenere almeno un carattere speciale diverso da lettere e numeri.
 * 4. Se la password soddisfa tutti i requisiti, la funzione restituisce true, altrimenti restituisce false.
 * @param string $password La password da validare.
 * @return bool Vero se rispecchia i requisiti, falso altrimenti
 */
function validate_password($password)
{
    // Verifica la lunghezza minima
    if (strlen($password) < 8) {
        return false;
    }

    // Verifica se contiene almeno una lettera maiuscola
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }

    // Verifica se contiene almeno un carattere speciale
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        return false;
    }

    // La password soddisfa tutti i requisiti
    return true;
}

/**
 * Verifica se il codice societario esiste nel database delle società sportive.
 *
 * @param PDO $con La connessione al database.
 * @param string $societyCode Il codice societario da verificare.
 * @return bool True se il codice societario esiste nel database, altrimenti False.
 */
function validate_society_code($con, $societyCode)
{
    try {
        $query = "SELECT COUNT(*) as count FROM societa_sportive WHERE codice = :societyCode";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':societyCode', $societyCode);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'];

        return ($count > 0);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

/**
 * Verifica se il codice squadra esiste nel database delle squadre.
 *
 * @param PDO $con La connessione al database.
 * @param string $teamCode Il codice squadra da verificare.
 * @return bool True se il codice squadra esiste nel database, altrimenti False.
 */

function validate_team_code($con, $teamCode)
{
    try {
        $query = "SELECT COUNT(*) as count FROM squadre WHERE codice = :teamCode";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':teamCode', $teamCode);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'];

        return ($count > 0);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}


/**
 * Gestisce il caricamento di un'immagine del profilo sul server. 
 * Prende due parametri: 
 * $path, che rappresenta la directory di destinazione in cui l'immagine verrà caricata, 
 * $file, che rappresenta l'array dei dati del file inviato tramite il form. 
 * La funzione estrae il nome del file dalla variabile $file, controlla il tipo di file 
 * consentito e, se è valido, sposta il file nella directory di destinazione specificata. 
 * Restituisce il percorso del file caricato se il caricamento è avvenuto con successo, 
 * altrimenti restituisce il percorso predefinito di un'immagine di default.
 * @param string $path Il percorso dell'immagine.
 * @param mixed $file Immagine.
 * @return string percorso del file, se mancante immagine, percorso immagine di default
 */
function upload_profile($path, $file)
{
    $targetDir = $path;
    $default = "beard.png";

    // Ottieni il nome del file
    $filename = basename($file['name']);
    $targetFilePath = $targetDir . $filename;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    if (!empty($filename)) {
        // Consenti solo determinati formati di file
        $allowType = array('jpg', 'png', 'jpeg', 'gif', 'pdf');
        if (in_array($fileType, $allowType)) {
            // Carica il file sul server
            if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                return $targetFilePath;
            }
        }
    }
    // Restituisci l'immagine predefinita
    return $path . $default;
}

/**
 * Aggiunge un allenatore alla tabella "allenatori" e crea una relazione con una squadra nella tabella "allenatori_squadre".
 *
 * @param PDO $con Connessione al database
 * @param string $email Email dell'allenatore
 * @param string $code Codice della squadra
 * @return bool True se l'inserimento è avvenuto con successo, altrimenti False
 */
function addCoach($con, $email, $coachtype, $code)
{
    try {
        // Valida i dati di input
        if (empty($email) || empty($coachtype) || empty($code)) {
            throw new InvalidArgumentException('I parametri non possono essere vuoti.');
        }

        // Inserisci l'allenatore nella tabella "allenatori"
        $query = "INSERT INTO allenatori (`email`, `tipo`, `privilegi_cam`) VALUES (:coach_email, :coachtype, 0)";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':coach_email', $email);
        $stmt->bindParam(':coachtype', $coachtype);
        $stmt->execute();

        // Ottieni l'ID della squadra associata al codice società
        $query = "SELECT id FROM squadre INNER JOIN societa_sportive as sp ON partita_iva = societa WHERE sp.codice = :code";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $row = $stmt->fetch();
        $id = $row['id'];

        // Associa l'allenatore alla squadra nella tabella "allenatori_squadre"
        $query = "INSERT INTO allenatori_squadre (`email_allenatore`, `id_squadra`, `data_inizio`) VALUES (:coach_email, :id, NOW())";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':coach_email', $email);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        throw new Exception("Errore durante l'aggiunta dell'allenatore: " . $e->getMessage());
        return false;
    }
}

/**
 * Aggiunge un giocatore alla tabella "giocatori" e crea una relazione con una squadra nella tabella "giocatori_squadre".
 *
 * @param PDO $con Connessione al database
 * @param string $email Email del giocatore
 * @param string $code Codice della squadra
 * @return bool True se l'inserimento è avvenuto con successo, altrimenti False
 */
function addPlayer($con, $email, $code)
{
    try {
        // Verifica la validità dell'email e del codice
        if (empty($email) || empty($code)) {
            throw new InvalidArgumentException('I parametri non possono essere vuoti.');
        }

        // Inserisci il giocatore nella tabella "giocatori"
        $query = "INSERT INTO giocatori (email) VALUES (:email)";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Ottieni l'ID della squadra associata al codice squadra
        $query = "SELECT id FROM squadre WHERE codice = :code";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $row = $stmt->fetch();
        $id = $row['id'];

        // Associa il giocatore alla squadra nella tabella "giocatori_squadre"
        $query = "INSERT INTO giocatori_squadre (email_giocatore, id_squadra, data_inizio) VALUES (:email, :id, NOW())";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        // In caso di errore rollback
        $con->rollback();
        throw new Exception("Errore durante l'aggiunta del giocatore: " . $e->getMessage());
        return false;
    }
}

/**
 * Aggiunge un nuovo tifoso alla tabella 'tifosi'.
 *
 * @param PDO $con L'oggetto di connessione al database.
 * @param string $email L'email del giocatore da aggiungere.
 * @return bool Restituisce true se il tifoso è stato aggiunto con successo, false altrimenti.
 */
function addFan($con, $email)
{
    try {
        $query = "INSERT INTO tifosi (email) VALUES (:email)";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

function addCompany($con, $email, $p_iva, $societyName, $sport, $address)
{
    $code = generateUniqueCode();
    $teamcode = generateUniqueCode();

    try {

        $query = "INSERT INTO societa_sportive (responsabile, partita_iva, nome, indirizzo, codice) VALUES (:email, :iva, :nome, :addr, :code)";
        $stmt = $con->prepare($query);
        $result = $stmt->execute([
            ':email' => $email,
            ':iva' => $p_iva,
            ':nome' => $societyName,
            ':addr' => $address,
            ':code' => $code
        ]);

        if (!$result) {
            throw new Exception("Errore durante l'inserimento dei dati nella tabella societa_sportive.");
        }

        $query = "INSERT INTO squadre (nome, societa, sport, codice) VALUES (:nome, :iva, :sport , :teamcode)";
        $stmt = $con->prepare($query);
        $result = $stmt->execute([
            ':nome' => $societyName,
            ':iva' => $p_iva,
            ':teamcode' => $teamcode,
            ':sport' => $sport
        ]);

        if (!$result) {
            throw new Exception("Errore durante l'inserimento dei dati nella tabella squadre.");
        }

        return true;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}



function generateUniqueCode()
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';

    for ($i = 0; $i < 6; $i++) {
        $randomIndex = rand(0, strlen($characters) - 1);
        $code .= $characters[$randomIndex];
    }

    return $code;
}


/**
 * Recupera le informazioni dell'utente dal database. 
 * Richiede due parametri: 
 * $con, che rappresenta l'oggetto di connessione al database,
 * $userID, che rappresenta l'ID dell'utente di cui si desiderano ottenere le informazioni. 
 * La funzione esegue una query SQL per selezionare gli attributi dell'utente corrispondente all'ID fornito. 
 * Restituisce un array associativo con le informazioni dell'utente se esiste una corrispondenza nel database, altrimenti restituisce false.
 * @param mixed $con connessione PDO al database
 * @param mixed $userID Id utente selezionato
 * @return mixed colonna del record se presente, false altrimenti
 */
function get_user_info($con, $email)
{
    $query = "SELECT p.*, p.nome as 'username', g.email AS giocatore_email, 
            a.email AS allenatore_email, m.email AS manutentore_email, s.*, privilegi_cam 
            FROM persone AS p LEFT JOIN societa_sportive AS s ON p.email = s.responsabile 
            LEFT JOIN allenatori AS a ON p.email = a.email LEFT JOIN giocatori AS g ON p.email = g.email 
            LEFT JOIN manutentori AS m ON p.email = m.email 
            WHERE p.email = :email";
    $stmt = $con->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($row)) {
        return false;
    }

    // Aggiungi il campo "userType" al risultato in base alla presenza dell'ID dell'allenatore
    if (!empty($row['allenatore_email'])) {
        $row['userType'] = 'allenatore';
    } else if (!empty($row['giocatore_email'])) {
        $row['userType'] = 'giocatore';
    } else if (!empty($row['manutentore_email'])) {
        $row['userType'] = 'manutentore';
    } else if (!empty($row['responsabile'])) {
        $row['userType'] = 'società';
    } else {
        $row['userType'] = 'tifoso';
    }

    return $row;
}

function checkPending($con, $userType, $email)
{
    $tabella = 'inviti_' . $userType;
    $query = "SELECT email FROM " . $tabella . " WHERE email = :email";
    $stmt = $con->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    return $stmt->rowCount() > 0;
}

function generateActivationCode()
{
    // Genera un codice di attivazione univoco
    $length = 32; // Lunghezza del codice
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $activationCode = '';
    for ($i = 0; $i < $length; $i++) {
        $randomIndex = rand(0, strlen($characters) - 1);
        $activationCode .= $characters[$randomIndex];
    }
    return $activationCode;
}

function getCoachTypes()
{
    $con = get_connection();
    $query = "SELECT nome_tipo FROM tipi_allenatori";
    $stmt = $con->query($query);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $options = '';
    foreach ($result as $row) {
        $tipoCoach = $row['nome_tipo'];
        $options .= "<option value='$tipoCoach'>$tipoCoach</option>";
    }

    return $options;
}

function getSports()
{
    $con = get_connection();
    $query = "SELECT nome_sport FROM sport";
    $stmt = $con->query($query);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $options = '';
    foreach ($result as $row) {
        $sport = $row['nome_sport'];
        $options .= "<option value='$sport'>$sport</option>";
    }

    return $options;
}