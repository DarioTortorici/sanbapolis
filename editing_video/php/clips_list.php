<?php
session_start();

include 'db_connection.php';
include 'functions.php';
include 'classes/Video.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="../css/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="../js/functions.js"></script>
        <title>Tutti le clip</title>
        <h1>Tutti le clip </h1>
    </head>
    <body>
        <a href="../index.php" class="button">Home</a><br>

        <div>
            <form action="clip_manager.php?operation=multiple_clip_delete" method="post">
                <table class="paleBlueRows">
                    <tr>
                        <th>Selezione</th>
                        <th>Nome</th>
                        <th>Descrizione</th>
                    </tr>

<?php
$pdo = get_connection();

try{               
    $clips = getClipsFromVideo($pdo, $_SESSION["path_video"]);
    foreach($clips as $el){
        echo <<<END
                    <tr class='clickable-row'>
                        <td><input type="checkbox" id="{$el->getId()}" name="id[]" value="{$el->getId()}"></td>
                        <td data-href='clip.php?id={$el->getId()}'>{$el->getName()}</td>
                        <td data-href='clip.php?id={$el->getId()}'>{$el->getNote()}</td>
                    </tr>\n
        END;
    }
} catch (Exception $e) {echo 'Eccezione: ',  $e->getMessage(), "\n";}
?>
                </table>
                <input type="submit" value="Elimina">
            </form>
        </div>
    </body>
</html>

<script>
    jQuery(document).ready(function($) {
    $(".clickable-row td:not(:first-child)").click(function() {
        window.location = $(this).data("href");
    });
});
</script>