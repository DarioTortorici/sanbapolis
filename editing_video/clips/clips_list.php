<?php

include('../../modals/header.php');
include_once("../../modals/navbar.php");
include '../editing/video-editing-helper.php';
include '../../classes/Video.php';

include '../editing/error-checker.php';

?>

<div class="container mt-4">
        <form action="clip_manager.php?operation=multiple_clip_delete" method="post">
            <table class="table table-striped paleBlueRows">
                <thead class="thead-light">
                    <tr>
                        <th>Selezione</th>
                        <th>Nome</th>
                        <th>Descrizione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $pdo = get_connection();

                    try {
                        $clips = getClipsFromVideo($pdo, $video->getPath());
                        foreach ($clips as $el) {
                            $link = "../editing/" . VIDEO_MANAGER . "?operation=select_video&id={$el->getId()}";
                            echo <<<END
                            <tr class='clickable-row'>
                                <td><input type="checkbox" id="{$el->getId()}" name="id[]" value="{$el->getId()}"></td>
                                <td data-href='$link'>{$el->getName()}</td>
                                <td data-href='$link'>{$el->getNote()}</td>
                            </tr>\n
            END;
                        }
                    } catch (Exception $e) {
                        echo 'Eccezione: ',  $e->getMessage(), "\n";
                    }
                    ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-danger">Elimina</button>
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