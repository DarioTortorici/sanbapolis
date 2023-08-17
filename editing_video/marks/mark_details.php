<?php

include '../../modals/header.php';
include_once "../../modals/navbar.php";
include '../editing/video-editing-helper.php';

include '../../classes/Mark.php';

$pdo = get_connection();
?>

<?php

if (isset($_GET["id"])) {
    $mark = getMarkFromId($pdo, $_GET["id"]);
    echo '<div id="mark_details_edit">
    <form action="mark_manager.php?operation=update_mark&id=' . $mark->getId() . '" method="post">
        <fieldset>
            <legend>Dettagli Segnaposto</legend>
            
            <div class="form-group">
                <label for="timing_mark">Timing:</label>
                <input type="text" class="form-control" name="timing_mark" id="timing_mark" value="' . $mark->getTiming() . '" readonly>
            </div>

            <div class="form-group">
                <label for="mark_name">Nome:</label>
                <input type="text" class="form-control" name="mark_name" id="mark_name" value="' . $mark->getName() . '">
            </div>

            <div class="form-group">
                <label for="mark_note">Descrizione:</label>
                <textarea class="form-control" id="mark_note" name="mark_note" rows="2" cols="30">' . $mark->getNote() . '</textarea>
            </div>

            <button type="submit" class="btn btn-primary" formaction="../editing/editing_video.php?timing_screen=' . $mark->getTiming() . '">Salva</button>
            <button type="submit" class="btn btn-danger" formaction="mark_manager.php?operation=delete_mark&id=' . $mark->getId() . '">Elimina</button>
        </fieldset>
    </form>
</div>';
} else {
    echo "<p>ERRORE: Segnaposto non trovato</p>";
}


?>

</body>

</html>