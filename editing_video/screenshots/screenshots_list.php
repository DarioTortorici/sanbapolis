<?php
session_start();

include('../../modals/header.php');
include_once("../../modals/navbar.php");
include '../video-helper.php';
include '../../classes/Screen.php';

setPreviusPage();

?>

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
                <input type="submit" class="btn btn-danger" value="Elimina">
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