<?php
session_start();

include 'db_connection.php';
include 'functions.php';
include 'classes/Mark.php';

setPreviusPage();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="../css/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="../js/functions.js"></script>
        <title>Tutti i segnaposti</title>
        <h1>Tutti i segnaposti</h1>
    </head>
    <body>
        <a href="../index.php" class="button">Home</a><br>

        <div>
            <form action="mark_manager.php?operation=multiple_mark_delete" method="post">
                <table class="paleBlueRows">
                    <tr>
                        <th>Selezione</th>
                        <th>Minutaggio</th>
                        <th>Nome</th>
                        <th>Descrizione</th>
                    </tr>
<?php
$pdo = get_connection();

try{               
    $marks = getMarksFromVideo($pdo, $_SESSION["path_video"]);
    foreach($marks as $el){
        echo <<<END
                    <tr class='clickable-row'>
                        <td><input type="checkbox" id="{$el->getId()}" name="id[]" value="{$el->getId()}"></td>
                        <td data-href='mark_details.php?id={$el->getId()}'>{$el->getTiming()}</td>
                        <td data-href='mark_details.php?id={$el->getId()}'>{$el->getName()}</td>
                        <td data-href='mark_details.php?id={$el->getId()}'>{$el->getNote()}</td>
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