<?php
session_start();

include 'db_connection.php';
include 'functions.php';
include 'classes/Screen.php';

setPreviusPage();

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="../css/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="../js/functions.js"></script>
        <title>Tutti gli screenshots</title>
        <h1>Tutti gli screenshots</h1>
    </head>
    <body>
        <a href="../index.php" class="button">Home</a><br>

        <div>
            <form action="screen_manager.php?operation=multiple_screen_delete" method="post">
                <table class="paleBlueRows">
                    <tr>
                        <th>Selezione</th>
                        <th>Immagine</th>
                        <th>Nome</th>
                        <th>Descrizione</th>
                    </tr>
<?php
$pdo = get_connection();

try{               
    $screenahots = getScreenshotsFromVideo($pdo, $_SESSION["path_video"]);
    foreach($screenahots as $el){
        echo <<<END
                    <tr class='clickable-row'>
                        <td><input type="checkbox" id="{$el->getId()}" name="id[]" value="{$el->getId()}"></td>
                        <td data-href='screen_details.php?id={$el->getId()}'><img id="{$el->getId()}" src="../{$el->getPath()}" alt="img" width="128" height="96"></td>
                        <td data-href='screen_details.php?id={$el->getId()}'>{$el->getName()}</td>
                        <td data-href='screen_details.php?id={$el->getId()}'>{$el->getNote()}</td>
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