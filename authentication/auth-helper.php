<?php
require_once('db_connection.php');

/**
 * Convalida un valore di testo di input.
 *
 * Questa funzione rimuove gli spazi vuoti iniziali e finali dal testo,
 * quindi esegue una convalida di sicurezza per evitare la codifica HTML dei caratteri
 * speciali potenzialmente dannosi nel testo.
 *
 * @param string $textValue Il valore di testo di input da convalidare.
 * @return string Il testo convalidato, oppure una stringa vuota se il valore non è valido.
 */
function validate_input_text($textValue)
{
    // Verifica se il valore non è vuoto
    if (!empty($textValue)) {
        // Rimuovi gli spazi vuoti iniziali e finali dal testo
        $trim_text = trim($textValue);
        // Evita la codifica HTML dei caratteri speciali potenzialmente dannosi nel testo
        $sanitize_str = htmlspecialchars($trim_text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return $sanitize_str;
    }
    return '';
}

/**
 * Convalida un indirizzo email di input.
 *
 * Questa funzione rimuove gli spazi vuoti iniziali e finali dall'indirizzo email,
 * quindi esegue una convalida di sicurezza per verificare che l'indirizzo email sia valido.
 *
 * @param string $emailValue L'indirizzo email di input da convalidare.
 * @return string L'indirizzo email convalidato, oppure una stringa vuota se il valore non è valido.
 */
function validate_input_email($emailValue)
{
    // Verifica se l'indirizzo email non è vuoto
    if (!empty($emailValue)) {
        // Rimuovi gli spazi vuoti iniziali e finali dall'indirizzo email
        $trim_email = trim($emailValue);
        // Convalida l'indirizzo email per rimuovere caratteri illegali e verificare la validità dell'email
        $sanitize_email = filter_var($trim_email, FILTER_SANITIZE_EMAIL);

        // Verifica se l'indirizzo email è valido dopo la convalida
        if (filter_var($sanitize_email, FILTER_VALIDATE_EMAIL)) {
            return $sanitize_email;
        }
    }

    return '';
}

/**
 * Convalida una password secondo i criteri stabiliti.
 *
 * Questa funzione verifica se la password soddisfa i seguenti requisiti:
 * - Deve avere una lunghezza minima di 8 caratteri.
 * - Deve contenere almeno una lettera maiuscola.
 * - Deve contenere almeno un carattere speciale (carattere diverso da lettere e numeri).
 *
 * @param string $password La password da convalidare.
 * @return bool True se la password soddisfa tutti i requisiti, altrimenti False.
 */
function validate_password($password)
{
    // Verifica la lunghezza minima della password
    if (strlen($password) < 8) {
        return false;
    }

    // Verifica se la password contiene almeno una lettera maiuscola
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }

    // Verifica se la password contiene almeno un carattere speciale
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        return false;
    }

    // La password soddisfa tutti i requisiti
    return true;
}

/**
 * Convalida un codice di una società sportiva nel database.
 *
 * Questa funzione verifica se un codice di società esiste nel database delle società sportive.
 *
 * @param PDO $con L'oggetto di connessione al database.
 * @param string $societyCode Il codice della società sportiva da convalidare.
 * @return bool True se il codice della società esiste nel database, altrimenti False.
 */
function validate_society_code(PDO $con, $societyCode)
{
    try {
        $query = "SELECT COUNT(*) as count FROM societa_sportive WHERE codice = :societyCode";
        $stmt = $con->prepare($query);
        $stmt->execute([':societyCode' => $societyCode]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'];

        return ($count > 0);
    } catch (PDOException $e) {
        // Gestione dell'eccezione in caso di errore di connessione o query
        echo "Error: " . $e->getMessage();
        return false;
    }
}

/**
 * Convalida un codice di squadra nel database.
 *
 * Questa funzione verifica se un codice di squadra esiste nel database delle squadre.
 *
 * @param PDO $con L'oggetto di connessione al database.
 * @param string $teamCode Il codice della squadra da convalidare.
 * @return bool True se il codice della squadra esiste nel database, altrimenti False.
 */
function validate_team_code(PDO $con, $teamCode)
{
    try {
        $query = "SELECT COUNT(*) as count FROM squadre WHERE codice = :teamCode";
        $stmt = $con->prepare($query);
        $stmt->execute([':teamCode' => $teamCode]);

        $count = $stmt->fetchColumn();

        return ($count > 0);
    } catch (PDOException $e) {
        // Gestione dell'eccezione in caso di errore di connessione o query
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
 * Aggiunge un nuovo allenatore al database e lo associa a una squadra.
 *
 * Questa funzione aggiunge un nuovo allenatore alla tabella "allenatori" con l'email e il tipo specificati.
 * Quindi associa l'allenatore a una squadra identificata dal codice società fornito.
 *
 * @param PDO $con L'oggetto di connessione al database.
 * @param string $email L'email dell'allenatore da aggiungere.
 * @param string $coachtype Il tipo di allenatore da aggiungere.
 * @param string $code Il codice della società associato alla squadra.
 * @return bool True se l'aggiunta dell'allenatore è riuscita, altrimenti False.
 * @throws Exception Se si verifica un errore durante l'aggiunta dell'allenatore o l'associazione alla squadra.
 */
function addCoach(PDO $con, $email, $coachtype, $code)
{
    try {
        // Valida i dati di input
        if (empty($email) || empty($coachtype) || empty($code)) {
            throw new InvalidArgumentException('I parametri non possono essere vuoti.');
        }

        // Inserisci l'allenatore nella tabella "allenatori"
        $insertCoachQuery = "INSERT INTO allenatori (`email`, `tipo`, `privilegi_cam`) VALUES (:coach_email, :coachtype, 0)";
        $stmt = $con->prepare($insertCoachQuery);
        $stmt->execute([':coach_email' => $email, ':coachtype' => $coachtype]);

        // Ottieni l'ID della squadra associata al codice società
        $getTeamIdQuery = "SELECT id FROM squadre INNER JOIN societa_sportive AS sp ON partita_iva = societa WHERE sp.codice = :code";
        $stmt = $con->prepare($getTeamIdQuery);
        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch();
        $id = $row['id'];

        // Associa l'allenatore alla squadra nella tabella "allenatori_squadre"
        $insertCoachTeamQuery = "INSERT INTO allenatori_squadre (`email_allenatore`, `id_squadra`, `data_inizio`) VALUES (:coach_email, :id, NOW())";
        $stmt = $con->prepare($insertCoachTeamQuery);
        $stmt->execute([':coach_email' => $email, ':id' => $id]);

        return true;
    } catch (PDOException $e) {
        throw new Exception("Errore durante l'aggiunta dell'allenatore: " . $e->getMessage());
    }
}

/**
 * Aggiunge un nuovo giocatore al database e lo associa a una squadra.
 *
 * Questa funzione aggiunge un nuovo giocatore alla tabella "giocatori" con l'email specificata.
 * Quindi associa il giocatore a una squadra identificata dal codice della squadra fornito.
 *
 * @param PDO $con L'oggetto di connessione al database.
 * @param string $email L'email del giocatore da aggiungere.
 * @param string $code Il codice della squadra associata al giocatore.
 * @return bool True se l'aggiunta del giocatore è riuscita, altrimenti False.
 * @throws Exception Se si verifica un errore durante l'aggiunta del giocatore o l'associazione alla squadra.
 */
function addPlayer(PDO $con, $email, $code)
{
    try {
        // Verifica la validità dell'email e del codice
        if (empty($email) || empty($code)) {
            throw new InvalidArgumentException('I parametri non possono essere vuoti.');
        }

        // Inizia una transazione
        $con->beginTransaction();

        // Inserisci il giocatore nella tabella "giocatori"
        $insertPlayerQuery = "INSERT INTO giocatori (email) VALUES (:email)";
        $stmt = $con->prepare($insertPlayerQuery);
        $stmt->execute([':email' => $email]);

        // Ottieni l'ID della squadra associata al codice squadra
        $getTeamIdQuery = "SELECT id FROM squadre WHERE codice = :code";
        $stmt = $con->prepare($getTeamIdQuery);
        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch();
        $id = $row['id'];

        // Associa il giocatore alla squadra nella tabella "giocatori_squadre"
        $insertPlayerTeamQuery = "INSERT INTO giocatori_squadre (email_giocatore, id_squadra, data_inizio) VALUES (:email, :id, NOW())";
        $stmt = $con->prepare($insertPlayerTeamQuery);
        $stmt->execute([':email' => $email, ':id' => $id]);

        // Conferma la transazione
        $con->commit();

        return true;
    } catch (PDOException $e) {
        // In caso di errore rollback
        $con->rollback();
        throw new Exception("Errore durante l'aggiunta del giocatore: " . $e->getMessage());
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

/**
 * Aggiunge una nuova società sportiva al database con la squadra associata.
 *
 * Questa funzione aggiunge una nuova società sportiva alla tabella "societa_sportive" con l'email del responsabile,
 * la partita IVA, il nome della società, l'indirizzo e un codice univoco generato automaticamente.
 * Inoltre, aggiunge una nuova squadra associata alla società nella tabella "squadre" con il nome della società,
 * la partita IVA, lo sport e un altro codice univoco generato automaticamente.
 *
 * @param PDO $con L'oggetto di connessione al database.
 * @param string $email L'email del responsabile della società.
 * @param string $p_iva La partita IVA della società.
 * @param string $societyName Il nome della società sportiva.
 * @param string $sport Lo sport della squadra associata alla società.
 * @param string $address L'indirizzo della società sportiva.
 * @return bool True se l'aggiunta della società è riuscita, altrimenti False.
 * @throws Exception Se si verifica un errore durante l'aggiunta della società o della squadra.
 */
function addCompany(PDO $con, $email, $p_iva, $societyName, $sport, $address)
{
    $code = generateUniqueCode();
    $teamcode = generateUniqueCode();

    try {
        // Inserisci la società sportiva nella tabella "societa_sportive"
        $insertSocietyQuery = "INSERT INTO societa_sportive (responsabile, partita_iva, nome, indirizzo, codice) VALUES (:email, :iva, :nome, :addr, :code)";
        $stmt = $con->prepare($insertSocietyQuery);
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

        // Inserisci la squadra associata alla società nella tabella "squadre"
        $insertTeamQuery = "INSERT INTO squadre (nome, societa, sport, codice) VALUES (:nome, :iva, :sport, :teamcode)";
        $stmt = $con->prepare($insertTeamQuery);
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
        // In caso di errore rollback
        $con->rollback();
        throw new Exception("Errore durante l'aggiunta della società: " . $e->getMessage());
    }
}

/**
 * Genera un codice casuale univoco di 6 caratteri.
 *
 * Questa funzione genera un codice casuale di 6 caratteri, composto da numeri e lettere maiuscole/minuscole.
 * Il codice generato potrebbe non essere univoco nel database, quindi è consigliabile verificare
 * se il codice esiste già prima di utilizzarlo come chiave primaria o codice identificativo.
 *
 * @return string Il codice casuale di 6 caratteri generato.
 */
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
 * Ottiene le informazioni dell'utente corrispondente all'email specificata.
 *
 * Questa funzione recupera le informazioni dell'utente corrispondente all'email fornita dal database.
 * Le informazioni includono dati personali dalla tabella "persone" e, se presenti, il ruolo specifico
 * dell'utente come giocatore, allenatore, manutentore, società sportiva o tifoso.
 *
 * @param PDO $con L'oggetto di connessione al database.
 * @param string $email L'email dell'utente di cui ottenere le informazioni.
 * @return array|bool Un array associativo con le informazioni dell'utente o False se l'utente non esiste.
 */
function get_user_info(PDO $con, $email)
{
    $query = "SELECT p.*, p.nome as 'username', g.email AS giocatore_email, 
            a.email AS allenatore_email, m.email AS manutentore_email, s.*, privilegi_cam 
            FROM persone AS p 
            LEFT JOIN societa_sportive AS s ON p.email = s.responsabile 
            LEFT JOIN allenatori AS a ON p.email = a.email 
            LEFT JOIN giocatori AS g ON p.email = g.email 
            LEFT JOIN manutentori AS m ON p.email = m.email 
            WHERE p.email = :email";
    $stmt = $con->prepare($query);
    $stmt->execute([':email' => $email]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($row)) {
        return false;
    }

    // Aggiungi il campo "userType" al risultato in base al ruolo dell'utente
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

/**
 * Verifica se l'utente specificato ha inviti in sospeso per una determinata tabella.
 *
 * Questa funzione verifica se l'utente con l'email fornita ha inviti in sospeso nella tabella specificata.
 * La tabella in cui cercare gli inviti in sospeso dipende dal tipo di utente, come giocatore, allenatore, manutentore o società sportiva.
 *
 * @param PDO $con L'oggetto di connessione al database.
 * @param string $userType Il tipo di utente per cui verificare gli inviti in sospeso (giocatore, allenatore, manutentore, società).
 * @param string $email L'email dell'utente di cui verificare gli inviti in sospeso.
 * @return bool True se l'utente ha inviti in sospeso nella tabella specificata, altrimenti False.
 */
function checkPending(PDO $con, $userType, $email)
{
    $tabella = 'inviti_' . $userType;

    try {
        $query = "SELECT email FROM $tabella WHERE email = :email";
        $stmt = $con->prepare($query);
        $stmt->execute([':email' => $email]);

        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Errore durante la verifica degli inviti in sospeso: " . $e->getMessage());
    }
}

/**
 * Genera un codice di attivazione dell'account univoco di lunghezza 32.
 *
 * Questa funzione genera un codice di attivazione univoco di lunghezza 32 caratteri,
 * composto da numeri, lettere maiuscole e lettere minuscole.
 *
 * @return string Il codice di attivazione dell'account.
 */
function generateActivationCode()
{
    $length = 32; // Lunghezza del codice
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $activationCode = '';

    for ($i = 0; $i < $length; $i++) {
        $randomIndex = rand(0, strlen($characters) - 1);
        $activationCode .= $characters[$randomIndex];
    }

    return $activationCode;
}

/**
 * Ottiene i tipi di allenatori dal database e li formatta come opzioni per un elemento <select> HTML.
 *
 * Questa funzione recupera i nomi dei tipi di allenatori dalla tabella "tipi_allenatori" nel database
 * e li formatta come opzioni per un elemento <select> HTML.
 *
 * @return string Le opzioni formattate come HTML per un elemento <select> contenente i tipi di allenatori.
 */
function getCoachTypes()
{
    $con = get_connection();
    $query = "SELECT nome_tipo FROM tipi_allenatori";
    $stmt = $con->query($query);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $options = array_map(function ($row) {
        $tipoCoach = $row['nome_tipo'];
        return "<option value='$tipoCoach'>$tipoCoach</option>";
    }, $result);

    return implode('', $options);
}

/**
 * Ottiene i nomi degli sport dal database e li formatta come opzioni per un elemento <select> HTML.
 *
 * Questa funzione recupera i nomi degli sport dalla tabella "sport" nel database
 * e li formatta come opzioni per un elemento <select> HTML.
 *
 * @return string Le opzioni formattate come HTML per un elemento <select> contenente i nomi degli sport.
 */
function getSports()
{
    $con = get_connection();
    $query = "SELECT nome_sport FROM sport";
    $stmt = $con->query($query);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $options = array_map(function ($row) {
        $sport = $row['nome_sport'];
        return "<option value='$sport'>$sport</option>";
    }, $result);

    return implode('', $options);
}
