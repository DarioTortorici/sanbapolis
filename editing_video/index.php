<?php
//serve per lo sviluppo del sito, andrà poi sostituito con il verp login.php

session_start();

include "php/db_connection.php";
include "php/functions.php";
include "php/classes/Person.php";

$pdo = get_connection();

//dopo che l'uetnte si è loggato estraggo dal db la persona e la salvo serializzata in session

if(!isset($_SESSION["person"])){
    $person = getPersonaFromEmail($pdo, "vincenzo.italiano@gmail.com");
    $_SESSION["person"] = serialize($person);
}
else{
    header("Location: videos_list.php");
}
?>

<link rel="stylesheet" href="css/style.css" method="post">
<form action="videos_list.php">
    <fieldset>
        <legend>Test Login form</legend>
        <label>Email</label>
        <input type="email" value="email@gmail.com" readonly><br>
        <label>Password</label>
        <input type="password" value="password" readonly><br>
        <input type="submit">
    </fieldset>
</form>