<?php
session_start();

include 'db_connection.php';
include 'functions.php';
include 'classes/Screen.php';

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="../css/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="../js/functions.js"></script>
        <title>Dettagli Screen</title>
        <h1>Dettagli Screen</h1>
    </head>
    <body>
        <a href="../index.php" class="button">Home</a><br>

<?php
$pdo = get_connection();

if(isset($_GET["id"])){
    $screen = getScreenfromId($pdo, $_GET["id"]);
    if($screen != null){
        echo <<< END
            <div class="screen_details">
                <img id="{$screen->getId()}" src="../{$screen->getPath()}" alt="img">
                <form action="screen_manager.php?operation=update_screen&id={$screen->getId()}" method="post">
                <fieldset>
                    <legend>Dettagli Screenshot</legend>
                    
                    <label for="screen_name">Nome:</label>
                    <input type="text" name="screen_name" id="screen_name" value="{$screen->getName()}"><br>

                    <label for="screen_note">Descrizione:</label>
                    <textarea id="screen_note" name="screen_note" rows="2" cols="30">{$screen->getNote()}</textarea>

                    <input type="submit" value="Salva">
                    <input type="submit" value="Elmina" formaction="screen_manager.php?operation=delete_screen&id={$screen->getId()}">
                </fieldset>
            </form>
            </div>
        END;
        if(isset($_GET["updated"])){
            echo "<div id=\"snackbar\" class=\"show\">Screenshot modificato correttamente</div>";
        }  
    }
    else{
        if(!isset($_GET["screen_deleted"])){
            echo "<p id=\"screen_mess\">Screenshot non trovato</p>";
        }     
        else {
            if($_GET["screen_deleted"]=="true"){
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
    if (findGetParameter("updated") != null){  
        window.onload = showSnackbar();
    }
</script>