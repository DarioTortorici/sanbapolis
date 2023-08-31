<?php

include '../../modals/header.php';
include_once "../../modals/navbar.php";
include '../editing/video-editing-helper.php';
include '../../classes/Screen.php';
include '../../classes/Video.php';

include '../editing/error-checker.php';

    $pdo = get_connection();

    if (isset($_GET["id"])) {
        $screen = getScreenfromId($pdo, $_GET["id"]);
        if ($screen != null) {
            echo <<< END
<div class="container mt-5">
    <div class="screen_details card">
        <img id="{$screen->getId()}" src="../{$screen->getPath()}" class="card-img-top" alt="img">
        <form action="screen_manager.php?operation=update_screen&id={$screen->getId()}" method="post" class="card-body">
            <fieldset>
                <legend class="mb-3">Dettagli Screenshot</legend>
    
                <div class="mb-3">
                    <label for="screen_name" class="form-label">Nome:</label>
                    <input type="text" name="screen_name" id="screen_name" value="{$screen->getName()}" class="form-control">
                </div>
    
                <div class="mb-3">
                    <label for="screen_note" class="form-label">Descrizione:</label>
                    <textarea id="screen_note" name="screen_note" rows="2" class="form-control">{$screen->getNote()}</textarea>
                </div>
    
                <button type="submit" class="btn btn-primary">Salva</button>
                <button type="submit" class="btn btn-danger" formaction="screen_manager.php?operation=delete_screen&id={$screen->getId()}">Elimina</button>
            </fieldset>
        </form>
    </div>
</div>
END;
            if (isset($_GET["updated"])) {
                echo "<div id=\"snackbar\" class=\"show\">Screenshot modificato correttamente</div>";
            }
        } else {
            if (!isset($_GET["screen_deleted"])) {
                echo "<p id=\"screen_mess\">Screenshot non trovato</p>";
            } else {
                if ($_GET["screen_deleted"] == "true") {
                    //echo "screen eliminato correttamente<br>";
                    //echo getPreviusPage();
                    header("Location: " . getPreviusPage());
                }
            }
        }
    }
    ?>
</body>

</html>

<script>
    if (findGetParameter("updated") != null) {
        window.onload = showSnackbar();
    }
</script>