<?php
include('../../modals/header.php');
include_once("../../modals/navbar.php");
include '../editing/video-editing-helper.php';
include '../../classes/Mark.php';
include '../../classes/Video.php';

//if(isPageRefreshed()){
setPreviusPage();
//}

include '../editing/error-checker.php';

?>

<div class="container mt-4">
        <form action="mark_manager.php?operation=multiple_mark_delete" method="post">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Selezione</th>
                        <th>Minutaggio</th>
                        <th>Nome</th>
                        <th>Descrizione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $pdo = get_connection();

                    try {
                        $marks = getMarksFromVideo($pdo, $video->getPath());
                        foreach ($marks as $el) {
                            echo <<<END
                            <tr class='clickable-row'>
                                <td><input type="checkbox" id="{$el->getId()}" name="id[]" value="{$el->getId()}"></td>
                                <td data-href='mark_details.php?id={$el->getId()}'>{$el->getTiming()}</td>
                                <td data-href='mark_details.php?id={$el->getId()}'>{$el->getName()}</td>
                                <td data-href='mark_details.php?id={$el->getId()}'>{$el->getNote()}</td>
                            </tr>\n
            END;
                        }
                    } catch (Exception $e) {
                        echo 'Eccezione: ',  $e->getMessage(), "\n";
                    }
                    ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-danger" id="deleteButton" disabled>Elimina</button>
            <!-- JavaScript to enable/disable the "Elimina" button -->
            <script>
                $(document).ready(function() {
                    $('input[type="checkbox"]').on('change', function() {
                        if ($('input[type="checkbox"]:checked').length > 0) {
                            $('#deleteButton').prop('disabled', false);
                        } else {
                            $('#deleteButton').prop('disabled', true);
                        }
                    });
                });
            </script>
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