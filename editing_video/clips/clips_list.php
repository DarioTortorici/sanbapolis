<?php
session_start();

include('../../modals/header.php');
include_once("../../modals/navbar.php");
include '../video-helper.php';
include '../../classes/Video.php';
?>


<div>
    <form action="clip_manager.php?operation=multiple_clip_delete" method="post">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Selezione</th>
                    <th>Nome</th>
                    <th>Descrizione</th>
                </tr>
            </thead>
            <?php
            $pdo = get_connection();

            try {
                $clips = getClipsFromVideo($pdo, $_SESSION["path_video"]);
                foreach ($clips as $el) {
                    echo <<<END
                    <tr class='clickable-row'>
                        <td><input type="checkbox" id="{$el->getId()}" name="id[]" value="{$el->getId()}"></td>
                        <td data-href='clip.php?id={$el->getId()}'>{$el->getName()}</td>
                        <td data-href='clip.php?id={$el->getId()}'>{$el->getNote()}</td>
                    </tr>\n
        END;
                }
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
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