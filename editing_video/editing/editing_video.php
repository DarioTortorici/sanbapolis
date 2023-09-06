<?php
require_once '../../modals/header.php';
require_once '../../modals/navbar.php';
require_once 'video-editing-helper.php';
require_once '../../classes/Mark.php';
require_once '../../classes/Screen.php';
require_once '../../classes/Video.php';
require_once 'error-checker.php';

$pdo = get_connection();

// Ottiene il percorso del video dalla query string o imposta una stringa vuota se manca.
$video_location = $_GET['video'] ?? '';

if (!empty($video_location)) {
    // Se il percorso del video non è vuoto, lo recuperiamo dal database.
    $video = getVideoFromPath($pdo, $video_location);
    $_SESSION['video'] = serialize($video);  // Serializza e memorizza l'oggetto Video nella sessione.

    // Estrae il nome del file video senza l'estensione.
    $path = $video->getPath();
    $filename = basename($path, '.mp4');

    // Ottiene la data di registrazione dal parametro o dal file video.
    $recording_date = $_GET['recording_date'] ?? getRecordingDate($path);
} else {
    // Se il percorso del video è vuoto, recuperiamo l'oggetto Video dalla sessione.
    $video = unserialize($_SESSION['video']);
    
    // Estrae il nome del file video senza l'estensione.
    $path = $video->getPath();
    $filename = basename($path, '.mp4');
    
    // Ottiene la data di registrazione dal parametro o dal file video.
    $recording_date = $_GET['recording_date'] ?? getRecordingDate($path);
}


setPreviusPage();

if (isset($_GET['update']) && $_GET['update'] == 1) {
    // Se il parametro 'update' è presente nella query string e ha valore 1, mostra un messaggio di successo.
    echo '<div class="container alert alert-success d-flex align-items-center" role="alert">
              Modifiche avvenute con successo
              <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}
?>

<!-- Include CSS -->
<link rel="stylesheet" href="../../css/video/videoList.css">

<!-- Create the video player and playlist sections -->
<div class="video-gallery">
    <!-- Video Player -->
    <div class="video-player container embed-responsive embed-responsive-16by9">
        <video id="<?php echo $filename ?>" class="player embed-responsive-item" controls muted autoplay>
            <source src="<?php echo "../{$video->getPath()}" ?>" type="video/mp4">
        </video>
    </div>

    <!-- Playlist -->
    <div id="playlist" class="playlist">
        <ul id="video-list" class="list-group">
            <!-- Form to delete multiple videos -->
            <form action="<?php echo VIDEO_MANAGER; ?>?operation=multiple_video_delete" method="post" onsubmit="return confirm('Sicuro di eliminare i video selezionati?')">
                <?php
                try {
                    $videos = getPlaylist($video->getPath());

                    // Display playlist as a table
                    echo '<table class="table table-striped table-hover">';
                    echo '<thead>
                        <tr>
                            <th></th>
                            <th id="playlist-date">' . $recording_date . '</th>
                            <th>Download</th>
                        </tr>
                      </thead>';

                    // Iterate through videos and create rows
                    foreach ($videos as $el) {
                        $link = "../" . $el->getPath();
                        echo <<<END
                        <tr class='clickable-row'>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="{$el->getId()}" name="id[]" value="{$el->getId()}">
                                </div>
                            </td>        
                            <td data-href='$link'>{$el->getName()}</td>
                            <td>
                                <a href="$link" download class="btn btn-success btn-sm">Scarica</a>
                            </td>
                        </tr>
                END;
                    }

                    echo '</table>';
                } catch (Exception $e) {
                    echo 'Eccezione: ', $e->getMessage(), "\n";
                }

                ?>
                <div class="mt-3">
                    <input type="submit" class="btn btn-danger" value="Elimina">
                </div>
            </form>
        </ul>
    </div>

</div>

<!-- Pulsantiera -->
<div class="container mt-4">
    <form action="../screenshots/screen_manager.php?operation=get_screen" method="post" class="mb-4">
        <div class="input-group">
            <input type="text" class="form-control" name="timing_video" id="timing_video" hidden readonly>

            <button class="btn btn-primary mr-2" type="button" id="mark" onclick="segnaposto()">Aggiungi Segnaposto</button>

            <button type="submit" class="btn btn-success mr-2">Screen</button>
        </div>
    </form>

    <a href="<?php if ($video != null) {
                    echo "../clips/clip.php?id=" . $video->getId();
                } ?>" class="btn btn-warning mr-2">Vai a Estrai Clip</a>
    <button id="openVideoModal" class="btn btn-info">Dettagli Video</button>
    <button id="openClipModal" class="btn btn-secondary">Gestione clip</button>
    <button id="openMarksModal" class="btn btn-secondary">Gestione segnaposti</button>
    <button id="openScreensModal" class="btn btn-secondary">Gestione screenshots</button>

</div>

<!-- Marks List -->
<div class="container mt-4" id="marks">
    <table id="list_marks" class="table table-striped">
        <thead class="thead-light">
            <tr>
                <th scope="col">Minutaggio</th>
                <th scope="col">Nome</th>
                <th scope="col">Dettagli</th>
                <th scope="col">Vai al Timing</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $marks = getMarksFromVideo($pdo, $video->getPath());
            try {
                if ($marks != null) {
                    foreach ($marks as $el) {
                        $name = ($el->getName() == null) ? "-" : $el->getName();
                        $timing = timing_format_from_db_to_int($el->getTiming());
                        echo <<< END
                                <tr>
                                    <td>{$el->getTiming()}</td>
                                    <td>$name</td>
                                    <td><a href="../marks/mark_details.php?id={$el->getId()}" class="btn btn-secondary btn-sm">Dettagli</a></td>
                                    <td><a href="javascript:goToTiming(document.getElementById('{$filename}'), '$timing')" class="btn btn-primary btn-sm">Vai al Timing</a></td>
                                </tr>
                            END;
                    }
                }
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Screenshot List -->
<div id="screen_area" class="grid-container">
    <?php
    $screenshots = getScreenshotsFromVideo($pdo, $video->getPath());
    try {
        foreach ($screenshots as $el) {
            $img_name = substr($el->getPath(), strpos($el->getPath(), "/") + 1);
            echo <<< END
                <div class="grid-item">
                    <a href="../screenshots/screen_details.php?id={$el->getId()}">
                        <img id="{$el->getId()}" src="../{$el->getPath()}" alt="$img_name" width="426" height="240">
                    </a>
                    <br>
                    <a href="../screenshots/screen_details.php?id={$el->getId()}&timing_video=">
            END;
            echo ($el->getName() == null) ? $img_name : $el->getName();
            echo "</a>\n\t";

            // Aggiungi il link di download
            echo <<< DOWNLOAD
                <div>
                    <a href="../{$el->getPath()}" download class="btn btn-success btn-sm mt-2">Scarica</a>
                </div>
            DOWNLOAD;

            echo "</div>\n";
        }
    } catch (Exception $e) {
        echo 'Eccezione: ',  $e->getMessage(), "\n";
    }
    ?>
</div>


<div hidden id="snackbar">Esiste già un segnaposto con quel minutaggio</div>

<!--
   Sezione Modal.
-->

<!-- Modal per aggiungere un segnaposto -->
<div class="modal fade" id="addMarkModal" tabindex="-1" aria-labelledby="addMarkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../marks/mark_manager.php?operation=new_mark" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="markModalLabel">Segnaposto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="timing_mark">Timing:</label>
                        <input type="text" class="form-control" name="timing_mark" id="timing_mark" readonly>
                    </div>
                    <div class="form-group">
                        <label for="mark_name">Nome:</label>
                        <input type="text" class="form-control" name="mark_name" id="mark_name">
                    </div>
                    <div class="form-group">
                        <label for="mark_note">Descrizione:</label>
                        <textarea class="form-control" id="mark_note" name="mark_note" rows="2" cols="30"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Dettagli Video -->
<div class="modal modal-dialog modal-lg" tabindex="-1" role="dialog" id="videoModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalToggleLabel"> <?php echo $filename; ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container mt-4">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="video-gallery">
                                <div class="video-player embed-responsive embed-responsive-16by9">
                                    <video id="<?php echo $filename ?>" controls muted autoplay>
                                        <source src="<?php if ($video != null) {
                                                            echo "../{$video->getPath()}";
                                                        } ?>" type="video/mp4">
                                    </video>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <form action="<?php echo VIDEO_MANAGER ?>?operation=update_video" method="post" onsubmit="return confirm('Confermi?')">
                                <fieldset>
                                    <legend>Modifica Video</legend>
                                    <div class="form-group">
                                        <label for="video_name">Nome:</label>
                                        <input type="text" class="form-control" name="video_name" id="video_name" value="<?php if ($video != null) {
                                                                                                                                echo $video->getName();
                                                                                                                            } ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="video_note">Descrizione:</label>
                                        <textarea class="form-control" name="video_note" id="video_note" rows="3"><?php if ($video != null) {
                                                                                                                        echo $video->getNote();
                                                                                                                    } ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Salva</button>
                                    <button type="submit" class="btn btn-danger" formaction="<?php echo VIDEO_MANAGER ?>?operation=delete_video">Elimina Video</button>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gestione Clip -->
<div class="modal modal-dialog modal-lg" tabindex="-1" role="dialog" id="clipModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalToggleLabel"> Lista Clip</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container mt-4">
                    <form action="../clips/clip_manager.php?operation=multiple_clip_delete" method="post">
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
            </div>
        </div>
    </div>
</div>

<!-- Modal Gestione Segnaposti -->
<div class="modal modal-dialog modal-lg" tabindex="-1" role="dialog" id="marksModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalToggleLabel"> Lista segnaposti </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container mt-4">
                    <form action="../marks/mark_manager.php?operation=multiple_mark_delete" method="post">
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
            </div>
        </div>
    </div>
</div>

<!-- Modal Gestione Screenshot -->
<div class="modal modal-dialog modal-lg" tabindex="-1" role="dialog" id="screensModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalToggleLabel"> Lista Screenshots </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container mt-4">
                    <form action="../screenshots/screen_manager.php?operation=multiple_screen_delete" method="post">
                        <table class="table table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Selezione</th>
                                    <th>Immagine</th>
                                    <th>Nome</th>
                                    <th>Descrizione</th>
                                </tr>
                            </thead>
                            <tbody>


                                <?php
                                $pdo = get_connection();

                                try {
                                    $screenahots = getScreenshotsFromVideo($pdo, $video->getPath());
                                    foreach ($screenahots as $el) {
                                        echo <<<END
                    <tr class='clickable-row'>
                        <td><input type="checkbox" id="{$el->getId()}" name="id[]" value="{$el->getId()}"></td>
                        <td data-href='screen_details.php?id={$el->getId()}'><img id="{$el->getId()}" src="../{$el->getPath()}" alt="img" width="128" height="96"></td>
                        <td data-href='screen_details.php?id={$el->getId()}'>{$el->getName()}</td>
                        <td data-href='screen_details.php?id={$el->getId()}'>{$el->getNote()}</td>
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
            </div>
        </div>
    </div>
</div>

<?php

include('../../modals/footer.php');

?>
<!-- Include JavaScript files -->
<script src="../../js/video/video-scripts.js"></script>
<script src="../../js/video/editing_video-scripts.js"></script>