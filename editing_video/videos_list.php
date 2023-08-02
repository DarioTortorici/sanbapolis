<?php
session_start();

include 'php/db_connection.php';
include 'php/functions.php';
include 'php/classes/Mark.php';
include 'php/classes/Screen.php';
include 'php/classes/Video.php';
include 'php/classes/Person.php';

$pdo = get_connection();


if(isset($_SESSION["person"])){//se la person a è salvata, significa che è loggato
    $person = unserialize($_SESSION["person"]);
}
else {
    header("Location: index.php");
}

//$_SESSION["path_video"] = "video/video.mp4";
//$_SESSION["video"] = getVideoFromPath($pdo, $_SESSION["path_video"]);
//$video = $_SESSION["video"];
//$filename = basename($video->getPath(), ".mp4");


setPreviusPage();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="css/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="js/functions.js"></script>
        <title>Scelta Video</title>
        <h1>Scelta video</h1>
    </head>
    <body>
        <form action="php/video_manager.php?operation=multiple_video_delete" method="post" onsubmit="confirm('Sicuro di eliminare i video selezionati?')">
            <table class="paleBlueRows">
                <tr>
                    <th>Scelta</th>
                    <th>Nome</th>
                </tr>
                <?php
                try{               
                    //$videos = getVideosFromUser($pdo, "vincenzo.italiano@gmail.com");
                    $videos = getVideosFromUser($pdo, $person->getEmail());
                    foreach($videos as $el){
                        echo <<<END
                                    <tr class='clickable-row'>
                                        <td><input type="checkbox" id="{$el->getId()}" name="id[]" value="{$el->getId()}"></td>
                                        <td data-href='php/video_manager.php?operation=select_video&id={$el->getId()}'>{$el->getName()}</td>
                                    </tr>\n
                        END;
                    }
                } catch (Exception $e) {echo 'Eccezione: ',  $e->getMessage(), "\n";}
                ?>
            </table>
            <input type="submit" value="Elimina">
        </form>
    </body>
</html>

<script>
    jQuery(document).ready(function($) {
    $(".clickable-row td:not(:first-child)").click(function() {
        window.location = $(this).data("href");
    });
});
</script>