<?php
session_start();

include 'functions.php';
include 'db_connection.php';
include 'classes/Mark.php';

$pdo = get_connection();

if(isset($_GET["timing"])){
    echo $_GET["timing"];
}

if(isset($_GET["operation"])){
	switch ($_GET["operation"]){
		case "new_mark":
			echo "new mark<br>";
			if(isset($_POST["timing_mark"])){
				$timing = $_POST["timing_mark"];
				$timing = timing_format_db($timing);//fromato corretto per db
				$name = ($_POST["mark_name"] == "") ? null : $_POST["mark_name"];
				$note = ($_POST["mark_note"] == "") ? null : $_POST["mark_note"];
				$video = $_SESSION["path_video"];
				$mark = new Mark($timing, $name, $note, $video);
				echo insertNewMark($pdo, $mark);
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
				header("Location: " . getPreviusPage());
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
		header("Location: editing_video.php$tmp");
	}
	
}

function multipleDelete($pdo){
	foreach($_POST["id"] as $el){
		deleteMarkFromId($pdo, $el);
	}
}