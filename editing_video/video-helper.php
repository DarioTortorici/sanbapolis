<?php
/**
 * converte il timing del video indicato dall'appostio box nel browser in un intero
 * @param string $timing_screen La stringa è nel formato 00:00:000 (minuti:secondi:millesimi di secondi)
 * @return int il numero di secondi relativo al timing idicato 
 */
function getIntTimingScreen($timing_screen){

    $vet_timing = array();  
    $tok = strtok($timing_screen, ":");
    while ($tok !== false) {
        array_push($vet_timing, intval($tok));
        $tok = strtok(":");
    }

    $ris = $vet_timing[0] * 60 + $vet_timing[1] + $vet_timing[2] / 1000; //secondi (sommando anche i minuti)

    return $ris;
}

/**
 * genera il nome del file immagine dello screenshot
 * @param string $filename Il nome del file del video "nomefile.estenzione"
 * @param string $timing_screen La stringa è nel formato 00:00:000 (minuti:secondi:millesimi di secondo)
 * @param string $screen_folder La cartella dove vengono salvati gli screen, di defalut "screen/"
 * @param string $image_format Formato dell'immagine in cui salvare l'immagine, di default jpg
 * @return string la path completa, formattata, di dove salvare l'immagine
 */
function generateScreenName($filename, $timing_screen_string, $screen_folder = "screen/", $image_format = "jpg"){
    $name_video = strtok($filename,'.');
    $timing = "";
    $tok = strtok($timing_screen_string, ":");
    while ($tok !== false) {
        $timing .= $tok;
        $tok = strtok(":");
    }
    $screen_name = $screen_folder . "frame_{$name_video}_{$timing}." . $image_format;
    
    return $screen_name;
}

/**
 * @param string $timing Il timing è formattato minuti:secondi:millesimi di secondo
 * @return string Il fromato richiesto dal db: hh:mm:ss.ssss
*/
function timing_format_db($timing){
    $hh = 0; $mm = 0;
    $db_format = "";

    $tok = strtok($timing, ":");

    //estraggo i minuti
    $mm = intval($tok);
    
    //calcolo il numero di ore
    if($mm >= 60){
        $hh = intdiv($mm, 60);
        $mm -= $hh * 60;//calcolo i min rimanenti (es. 63 min sono 1h e 3 min)
    }

    //concateno le ore
    $db_format .= "$hh:";
    //concateno i minuti
    $db_format .= ($mm < 10) ? "0$mm:" : "$mm:"; // se i minuti sono < 10, aggiungo lo 0 (es. 3 minuti devo aggiungere 03, non solo 3)
    
    //concateno i secondi
    $tok = strtok(":");
    $db_format .= "$tok";   
    
    //concateno i millesimi di secondo
    $tok = strtok(":");
    $db_format .= ".$tok";
    
    return $db_format;
}

/**
 * Riceve in input la stringa che specifica il timing formattato secondo il db (hh:mm:ss.sss) in un intero minuti.secondi
 * @param string $timing La stringa che specifica il timing formattato secondo il db (hh:mm:ss.sss)
 * @return integer timing espresso in secondi.millisecondi
 */
function timing_format_from_db_to_int($timing){
    $ss = 0.0;

    //estraggo le ore e le converto in secondi
    $tok = strtok($timing, ":");
    $ss += intval($tok) * 60 * 60;

    //estraggo i minuti e li converto in secondi
    $tok = strtok(":");
    $ss += intval($tok) * 60;
    
    //estraggo e sommo i secondi
    $tok = strtok(".");
    $ss += (intval($tok));

    //estraggo e sommi i millisecondi
    $tok = strtok("");
    $ss += (intval($tok) / 1000);

    return $ss;
}

/**
 * Restituisce i mark salvati nel db come vettore di istanse della classe Mark
 * @param PDO La connessione al db
 * @return array Un array di istanze della classe Mark, che rappresentano i mark salvati nel db
 */
function getMarksFromVideo($pdo, $video){
    $marks = array();
    $query = "SELECT * FROM segnaposti WHERE video=\"$video\"";
    $statement = $pdo->query($query);
    $publishers = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($publishers) {
        foreach ($publishers as $publisher) {
            try{                
                $id = $publisher['id'];
                $timing = $publisher['minutaggio'];
                $name = $publisher['nome'];
                $note = $publisher['nota'];
                $video = $publisher['video'];
                $mark = new Mark($timing, $name, $note, $video, $id);
                array_push($marks, $mark);
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
        }
    }

    return $marks;
}

/**
 * Restituisce un mark salvato nel db con l'id specificato
 * @param PDO La connessione al db
 * @param integer $id l'id che identifica il mark
 * @return Mark il mark cercato, come istanza della classe Mark
 */
function getMarkFromId($pdo, $id){
    $mark = null;
    $query = "SELECT * FROM segnaposti WHERE id=$id";
    $statement = $pdo->query($query);
    $publishers = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($publishers) {
        foreach ($publishers as $publisher) {
            try{                
                $id = $publisher['id'];
                $timing = $publisher['minutaggio'];
                $name = $publisher['nome'];
                $note = $publisher['nota'];
                $mark = new Mark($timing, $name, $note, null, $id);
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
        }
    }

    return $mark;
}

/**
 * Inserisce nel db una nuovo record nella tabella 'Segnaposti'
 * @param PDO La connessione al db
 * @param Mark $mark Istanza della classe Mark, che contiene i valori da inserie
 * @return bool true se l'inserimento ha successo, altrimenti false
 */
function insertNewMark($pdo, $mark){
    $ris = null;
    try{
        $query = 'INSERT INTO segnaposti(minutaggio, video, nome, nota) VALUES (:minutaggio, :video, :nome, :note)';
        $statement = $pdo->prepare($query);
        $ris = $statement->execute([
            ':minutaggio' => $mark->getTiming(),
            ':video' => $mark->getPathVideo(),
            ':nome' => $mark->getName(),
            ':note' => $mark->getNote(),
        ]);
    } catch (Exception $e){echo "Eccezione:" . $e->getMessage();}
    return $ris;
}

/**
 * Aggiorna un segnaposto nel db con l'id specificato nell'istanza della classe Mark
 * @param PDO La connessione al db
 * @param Mark $mark Istanza della classe Mark, che contiene i valori da aggiornare
 * @return bool true se l'aggiornamento ha successo, altrimenti false
 */
function updateMarkFromId($pdo, $mark){
   $query = "UPDATE segnaposti SET nome=:nome, nota=:nota WHERE id=:id";
   $statement = $pdo->prepare($query);
   $ris = $statement->execute([
       ':nome' => $mark->getName(),
       ':nota' => $mark->getNote(),
       ':id' => $mark->getId(),
    ]);
    
   return $ris;
}

/**
 * Elimina il segnaposto dal db con l'id specificato
 * @param PDO La connessione al db
 * @param integer $id indica l'id del segnaposto
 */
function deleteMarkFromId($pdo, $id){
    $query = "DELETE FROM segnaposti WHERE id = \"$id\"";
    $pdo->query($query);
}

/**
 * Restituisce i mark salvati nel db come vettore di istanse della classe Mark
 * @param PDO La connessione al db
 * @param string Il percorso del video
 * @return array Un array di istanze della classe Screen, che rappresentano gli screenshots salvati nel db
 */
function getScreenshotsFromVideo($pdo, $path_video){
    $screenshots = array();
    $query = "SELECT * FROM screenshots WHERE video=\"$path_video\"";
    $statement = $pdo->query($query);
    $publishers = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($publishers) {
        foreach ($publishers as $publisher) {
            try{                
                $id = $publisher['id'];
                $path = $publisher['locazione'];
                $name = $publisher['nome'];
                $note = $publisher['nota'];
                $path_video = $publisher['video'];
                $screen = new Screen($path, $name, $note, $id, $path_video);
                array_push($screenshots, $screen);
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
        }
    }

    return $screenshots;
}

/**
 * Restituisce uno screen salvato nel db con l'id specificato
 * @param PDO La connessione al db
 * @param integer $id l'id che identifica lo screen
 * @return Screem lo screen cercato, come istanza della classe Screen
 */
function getScreenfromId($pdo, $id){
    $screen = null;
    $query = "SELECT * FROM screenshots WHERE id=$id";
    $statement = $pdo->query($query);
    $publishers = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($publishers) {
        foreach ($publishers as $publisher) {
            try{                
                $id = $publisher['id'];
                $path = $publisher['locazione'];
                $name = $publisher['nome'];
                $note = $publisher['nota'];
                $path_video = $publisher['video'];
                $screen = new Screen($path, $name, $note, $id, $path_video);
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
        }
    }

    return $screen;
}

/**
 * Aggiorna uno screen nel db con l'id specificato nell'istanza della classe Screen
 * @param PDO La connessione al db
 * @param Screen $screen Istanza della classe Screen, che contiene i valori da aggiornare
 * @return bool true se l'aggiornamento ha successo, altrimenti false
 */
function updateScreenFromId($pdo, $screen){
    $query = "UPDATE screenshots SET nome=:nome, nota=:nota WHERE id=:id";
    $statement = $pdo->prepare($query);
    $ris = $statement->execute([
        ':nome' => $screen->getName(),
        ':nota' => $screen->getNote(),
        ':id' => $screen->getId(),
     ]);

     return $ris;
}

/**
 * Elimina lo screen dal db con l'id specificato
 * @param PDO La connessione al db
 * @param integer $id indica l'id dello screen
 */
function deleteScreenFromId($pdo, $id){
    $query = "DELETE FROM screenshots WHERE id = \"$id\"";
    $pdo->query($query);
}

/**
 * Inserisce nel db un nuovo video
 * @param PDO La connessione al db
 * @param Video $video Istanza della classe Video, che contiene i valori da inserie
 * @return bool true se l'inserimento ha successo, altrimenti false
 */
function insertNewVideo($pdo, $video){
    $ris = null;
    try{
        $query = 'INSERT INTO video(locazione, nome, autore, nota) VALUES (:locazione, :nome, :autore, :nota)';
        $statement = $pdo->prepare($query);
        $ris = $statement->execute([
            ':locazione' => $video->getPath(),
            ':nome' => $video->getName(),
            ':autore' => $video->getAuthor(),
            ':nota' => $video->getNote(),
        ]);
    } catch (Exception $e){echo "Eccezione:" . $e->getMessage();}

    return $ris;
}

/**
 * @param PDO La connessione al db
 * @param Video $video Istanza della classe Video, che contiene i valori da inserie
 * @param string $path_original_video locazione del video da cui è stata estratta la clip
 * @return bool true se l'inserimento ha successo, altrimenti false
 */
function insertNewClip($pdo, $clip, $path_original_video){
    $ris = null;
    if(insertNewVideo($pdo, $clip)){
        try{
            $query = 'INSERT INTO clips_video(locazione_video_originale, locazione_clip) VALUES (:originale, :clip)';
            $statement = $pdo->prepare($query);
            $ris = $statement->execute([
                ':originale' => $path_original_video,
                ':clip' => $clip->getPath(),
            ]);
        } catch (Exception $e){echo "Eccezione:" . $e->getMessage();}
    }

    return $ris;
}

/**
 * @param PDO La connessione al db
 * @param string $path_video locazione del video originale da cui estrarre le clip
 * @return array array con le istanse dei video, della classe Video
 */
function getClipsFromVideo($pdo, $path_video){
    $videos = array();
    $query = "SELECT V.id, V.locazione, V.nome, V.autore, V.nota FROM video V INNER JOIN clips_video CV ON V.locazione = CV.locazione_clip WHERE CV.locazione_video_originale = '$path_video'";
    $statement = $pdo->query($query);
    $publishers = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($publishers) {
        foreach ($publishers as $publisher) {
            try{                
                $id = $publisher['id'];
                $path = $publisher['locazione'];
                $name = $publisher['nome'];
                $author = $publisher['autore'];
                $note = $publisher['nota'];
                $video = new Video($id, $path, $name, $note, $author);
                array_push($videos, $video);
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
        }
    }

    return $videos;
}



/**
 * Elimina il video dal db con l'id specificato
 * @param PDO La connessione al db
 * @param integer $id indica l'id del video
 */
function deleteVideoFromId($pdo, $id){
    $query = "DELETE FROM video WHERE id = $id";
    $pdo->query($query);
}

/** Restitiusce un video dall'id specificato
 * @param PDO La connessione al db
 * @param integer $id indica l'id del video
 * @return Video il video cercato, null se non trovato
 */
function getVideoFromId($pdo, $id){
    $video = null;
    $query = "SELECT * FROM video WHERE id = $id";
    $statement = $pdo->query($query);
    $publishers = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($publishers) {
        foreach ($publishers as $publisher) {
            try{                
                $id = $publisher['id'];
                $path = $publisher['locazione'];
                $name = $publisher['nome'];
                $author = $publisher['autore'];
                $note = $publisher['nota'];
                $video = new Video($id, $path, $name, $note, $author);
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
        }
    }

    return $video;
}

/** Restitiusce un video dalla locazione specificata
 * @param PDO La connessione al db
 * @param string $path indica la locazione del video
 * @return Video il video cercato, null se non trovato
 */
function getVideoFromPath($pdo, $path){
    $video = null;
    $query = "SELECT * FROM video WHERE locazione = '$path'";
    $statement = $pdo->query($query);
    $publishers = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($publishers) {
        foreach ($publishers as $publisher) {
            try{                
                $id = $publisher['id'];
                $path = $publisher['locazione'];
                $name = $publisher['nome'];
                $author = $publisher['autore'];
                $note = $publisher['nota'];
                $video = new Video($id, $path, $name, $note, $author);
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
        }
    }

    return $video;
}


/** Restitiusce la persona con l'email specificata
 * @param PDO La connessione al db
 * @param string $email 
 * @return Person $person la persona cercata
 */
function getPersonaFromEmail($pdo, $email){
    $person = null;
    $query = "SELECT * FROM persone WHERE email = '$email'";
    $statement = $pdo->query($query);
    $publishers = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($publishers) {
        foreach ($publishers as $publisher) {
            try{                
                //$id = $publisher['id'];
                $email = $publisher['email'];
                $name = $publisher['nome'];
                $surname = $publisher['cognome'];
                $birthday = $publisher['data_nascita'];
                $city = $publisher['citta'];
                $address = $publisher['indirizzo'];
                $telephone_number = $publisher['telefono'];
                $person = new Person(null, $email, $name, $surname, $birthday, $city, $address, $telephone_number);
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
        }
    }

    return $person;
}

/**
 * restiuisce tutti i viedo associati alla persona specificata
 * @param PDO $pdo La connessione al db
 * @param string $email della persona
 * @return Array $videos i video cercati
 */
function getVideosFromUser($pdo, $email){
    $videos = array();
    $query = "SELECT * FROM video WHERE autore = '$email'";
    $statement = $pdo->query($query);
    $publishers = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($publishers) {
        foreach ($publishers as $publisher) {
            try{                
                $id = $publisher['id'];
                $path = $publisher['locazione'];
                $name = $publisher['nome'];
                $author = $publisher['autore'];
                $note = $publisher['nota'];
                $video = new Video($id, $path, $name, $note, $author);
                array_push($videos, $video);
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
        }
    }

    return $videos;
}

/**
 * Aggiorna un video nel db con l'id specificato nell'istanza della classe
 * @param PDO La connessione al db
 * @param Video $mark Istanza della classe Video, che contiene i valori da aggiornare
 * @return bool true se l'aggiornamento ha successo, altrimenti false
 */
function updateVideo($pdo, $video){
    $query = "UPDATE video SET nome=:nome, nota=:nota WHERE id=:id";
    $statement = $pdo->prepare($query);
    $ris = $statement->execute([
        ':nome' => $video->getName(),
        ':nota' => $video->getNote(),
        ':id' => $video->getId(),
     ]);

     return $ris;
}



/**
 * @return string restituisce il link alla pagina corrente
 */
function getCurentUrl(){
    $actual_link = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    return $actual_link;
}

/**
 * salva in memoria (in $_SESSION) la pagina attuale, che verrà usata come pagina precedente
 */
function setPreviusPage(){
    $_SESSION["previus_page"] = getCurentUrl();
}

/**
 * @return string restituisce il link alla pagina precedente
 */
function getPreviusPage(){
    return $_SESSION["previus_page"];
}

