<?php

/**
 *  Accetta una stringa $textValue come input e la valida per assicurarsi che non sia vuota. 
 *  Se la stringa non è vuota, viene effettuata una pulizia dei caratteri illegali tramite la funzione 
 *  filter_var() con l'opzione FILTER_SANITIZE_STRING. 
 *  Infine, la stringa pulita viene restituita come output. Se la stringa è vuota, viene restituita una stringa vuota.
 *  @param string $textValue Il testo da validare.
 *  @return string Il testo validato o una stringa vuota se il testo è vuoto
 */
function validate_input_text($textValue)
{
    if (!empty($textValue)) {
        $trim_text = trim($textValue);
        // rimuove caratteri illegali
        $sanitize_str = filter_var($trim_text, FILTER_SANITIZE_STRING);
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
function get_user_info($con, $userID)
{
    $query = "SELECT firstName, lastName, email, sport, userType, society, profileImage FROM user WHERE userID=:userID";
    $stmt = $con->prepare($query);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return empty($row) ? false : $row;
}