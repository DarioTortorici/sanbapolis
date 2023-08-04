<?php
session_start();

include('../../modals/header.php');
include_once("../../modals/navbar.php");
include '../video-helper.php';
include '../../classes/Mark.php';

setPreviusPage();
?>


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