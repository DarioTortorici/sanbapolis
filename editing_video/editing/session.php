<?php
session_start();

//affinchè il tutto funzioni, serve una cartella chiamata ./editing_video/thumbnails

include '../../vendor/autoload.php';
include "../../authentication/db_connection.php";
include "../../classes/Person.php";
include "../../classes/Video.php";
include "video-editing-helper.php";

//qua ci sono i dati per fare il test
$id_session = 1;//intval($_GET["id"]);
$pdo = get_connection();

//estraggo tutti i video della sessione di registrazione specificata, che appartengo ad un determinato allenatore 
$videos = getVideosFromSession($pdo, "vincenzo.italiano@gmail.com", $id_session); 

//cerco quali telecamere sono stati usate per la sessione di reg specificata.
//questo serve se si vuole creare i pulsanti per mostrare solo i video registrati con la telecamere 1,2,3... non serve per le thumbnails
$cameras = getCamerasFromSession($pdo, $id_session);
?>

<?php
//FUNZIONI CHE NELLA VERSIONE IN LOCALE ERANO IN 'functions.php'

/**
 * estrae la minuiatura di un video, il primo frame del video
 * @param string $path_video path del video
 * @return string $thumb la path alla thumbnail
 */
function getVideoThumbnails($path_video){
    $thumb = "../thumbnails/thumb_".basename($path_video, ".mp4").".jpg";
    if (!file_exists($thumb)){
        $ffmpeg = FFMpeg\FFMpeg::create();
        $video = $ffmpeg->open("../".$path_video);
        $video
            ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(0))
            ->save($thumb);
    }
    return $thumb;
}

/**
 * Restitiusce la lista di telecamere usate in una sessione di registrazione
 * @param PDO La connessione al db
 * @param integer la sessione cercata 
 * @return array La lista delle telecamere
 */
function getCamerasFromSession($pdo, $session){
    $cameras = array();
    $query = "SELECT DISTINCT telecamera FROM video WHERE sessione = '$session'";
    $statement = $pdo->query($query);
    $publishers = $statement->fetchAll(PDO::FETCH_ASSOC);
    if ($publishers) {
        foreach ($publishers as $publisher) {
            try{                
                $id = $publisher['telecamera'];
                array_push($cameras, $id);
            } catch (Exception $e) {
                echo 'Eccezione: ',  $e->getMessage(), "\n";
            }
        }
    }
    return $cameras;
}
?>




<!--qui c'è la pagina web, e c'è tutto l'html per filtrare i video in base alla telecamera, 
uso js per nascondere o meno i video relativa ad una telecamera-->
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="../css/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="../js/functions.js"></script>
        <title>Sessione</title>
        <h1>Tutti i video della sessione</h1>
    </head>

    <body>
        <a class="button" href="../index.php">Home</a><br>

        <div>
            <?php
                foreach($cameras as $el){
                    echo "<button onclick=\"showVideoFromCamera('camera_$el')\">Telecamera $el</button>\n";
                }
            ?>
            <button onclick="showVideoFromCamera('session_list')">Tutti i video</button>
        </div>

        <?php
            foreach($cameras as $cam){//creo dei div che contengono i video suddivisi per telecamera
                echo <<< END

                <div id='camera_{$cam}' hidden>
                <table class='paleBlueRows'>
                    <tr>Telecamera $cam</tr>
                    <tr>
                        <th>Miniatura</th>
                        <th>Telecamera</th>
                        <th>Nome</th>
                        <th>Note</th>
                    </tr>\n
                END;
                foreach($videos as $el){
                    if ($el->getCamera() == $cam){
                        if(file_exists("../".$el->getPath())){
                            $thumb = getVideoThumbnails($el->getPath());
                        }
                        $link = VIDEO_MANAGER . "?operation=select_video&id={$el->getId()}";
                        echo <<< END
                            <tr class='clickable-row'>
                                <td data-href='$link'><img src="$thumb" alt="thumb {$el->getName()}" width="128" height="96"></td>
                                <td data-href='$link'>{$el->getCamera()}</td>
                                <td data-href='$link'>{$el->getName()}</td>
                                <td data-href='$link'>{$el->getNote()}</td>
                            </tr>
                        END;
                    }
                }
                echo "\n</table>\n</div>\n";
            }
        ?>

      <div id="session_list">
            <table class="paleBlueRows">
                <tr>Tutti i video</tr>
                <tr>
                    <th>Miniatura</th>
                    <th>Telecamera</th>
                    <th>Nome</th>
                    <th>Note</th>
                </tr>
                <?php
                    foreach($videos as $el){//estraggo tutti i video
                        if(file_exists("../".$el->getPath())){//controllo se la minuatura esiste
                            $thumb = getVideoThumbnails($el->getPath());//se non esiste la creo
                        }
                        $link = VIDEO_MANAGER . "?operation=select_video&id={$el->getId()}";
                        echo <<< END
                        \n<tr class='clickable-row'>
                            <td data-href='$link'><img src="$thumb" alt="thumb {$el->getName()}" width="128" height="96"></td>
                            <td data-href='$link'>{$el->getCamera()}</td>
                            <td data-href='$link'>{$el->getName()}</td>
                            <td data-href='$link'>{$el->getNote()}</td>
                        </tr>\n
                        END;
                    }
                ?>
            </table>
        </div>
    </body>
</html>

<script>
    jQuery(document).ready(function($) {
        $(".clickable-row td").click(function() {
            window.location = $(this).data("href");
        });
    });

    function showVideoFromCamera(camera){
        console.log(camera);
        let el = document.getElementById(camera);
        console.log(el);
        if (el.hidden){
            el.hidden = false;
        }
        else{
            el.hidden = true;
        }
    }
</script>