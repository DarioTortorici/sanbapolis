<?php
session_start();

include '../editing/video-editing-helper.php';
include '../../authentication/db_connection.php';
include '../../classes/Mark.php';
include '../../classes/Video.php';
include '../../classes/Person.php';

include '../editing/error-checker.php';

$pdo = get_connection();

if(isset($_GET["timing"])){
    echo $_GET["timing"];
}

if(isset($_GET["operation"])){
	switch ($_GET["operation"]){
		case "new_mark":
			if(isset($_POST["timing_mark"])){
				if (!newMark($pdo, $video, $person)){
					header("Location: ".EDITING_VIDEO."?message=mark_exists");
					exit();
				}
			}
			break;
		case "update_mark":
			if(isset($_POST["timing_mark"])){
				$timing = $_POST["timing_mark"];
				$timing = timing_format_db($timing);//fromato corretto per db
				$name = ($_POST["mark_name"] == "") ? null : $_POST["mark_name"];
				$note = ($_POST["mark_note"] == "") ? null : $_POST["mark_note"];
				$video = $_SESSION["path_video"];
				$id = $_GET["id"];
				$mark = new Mark($timing, $name, $note, $video, $id);
				echo updateMarkFromId($pdo, $mark);
			}
			break;
		case "delete_mark":
			if(isset($_GET["id"])){
				$id = $_GET["id"];
				deleteMarkFromId($pdo, $id);
				header("Location: " . getPreviusPage());
			}
			break;
		case "multiple_mark_delete":
			if(isset($_POST["id"])){
				multipleDelete($pdo);
				header("Location: ../editing/editing_video.php?update=1");
			}
			break;
		default:
			echo "<p>Opzione non riconosciuta</p>";
			echo "<a href=\"../index.php\">Home</a>";
			break;
	}

	$tmp = "";
	if(isset($_POST["timing_mark"])){
		$timing = getIntTimingScreen($_POST["timing_mark"]);
		$tmp = "?timing_screen=$timing";
		header("Location: ../editing/".EDITING_VIDEO.$tmp);
	}
	
}

/**
 * Creazione e inserimento di un nuovo segnaposto nel database.
 *
 * Questa funzione gestisce la creazione e l'inserimento di un nuovo segnaposto nel database.
 * Vengono prese le informazioni fornite tramite POST, come il tempo del segnaposto,
 * il nome e le note. Queste informazioni vengono quindi utilizzate per creare un oggetto Mark,
 * che viene successivamente inserito nel database.
 *
 * @param PDO $pdo L'oggetto PDO per la connessione al database.
 * @param Video $video L'oggetto Video associato al segnaposto.
 * @param Person $person L'oggetto Person che rappresenta l'autore del segnaposto.
 * @return bool True se l'inserimento ha avuto successo, altrimenti False.
 */
function newMark($pdo, $video, $person) {
    $timing = $_POST["timing_mark"];  // Ottiene il tempo del segnaposto dalla richiesta POST.
    $timing = timing_format_db($timing);  // Converte il formato del tempo per il database.

    $name = ($_POST["mark_name"] == "") ? null : $_POST["mark_name"];  // Ottiene il nome del segnaposto dalla richiesta POST.
    $note = ($_POST["mark_note"] == "") ? null : $_POST["mark_note"];  // Ottiene le note del segnaposto dalla richiesta POST.

    // Crea un oggetto Mark con le informazioni fornite.
    $mark = new Mark($timing, $name, $note, $video->getPath());

    return insertNewMark($pdo, $mark);  // Inserisce il segnaposto nel database e restituisce l'esito.
}


/**
 * Eliminazione multipla di marcatori dal database.
 *
 * Questa funzione gestisce l'eliminazione multipla di marcatori dal database.
 * Utilizza un array di ID di marcatori forniti tramite POST per eliminare i marcatori corrispondenti.
 *
 * @param PDO $pdo L'oggetto PDO per la connessione al database.
 * @return void
 */
function multipleDelete($pdo) {
    foreach ($_POST["id"] as $el) {
        deleteMarkFromId($pdo, $el);  // Elimina il segnaposto dal database.
    }
}