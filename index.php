<a href="index_sanbapolis.php">Index Sanbapolis</a><br>
<a href="index_vecchio.php">Index Xampp</a><br>

<p>Test</p><br>

<?php

$vet_hash = array (
    "mario" => password_hash("mario", PASSWORD_DEFAULT),
    "raffaele" => password_hash("raffaele", PASSWORD_DEFAULT),
    "antonio" => password_hash("antonio", PASSWORD_DEFAULT),
    "vincenzo" => password_hash("vincenzo", PASSWORD_DEFAULT),
    "manolo" => password_hash("manolo", PASSWORD_DEFAULT),
    "costanzo" => password_hash("costanzo", PASSWORD_DEFAULT),
    "arcangelo" => password_hash("arcangelo", PASSWORD_DEFAULT),
    "emmanuele" => password_hash("emmanuele", PASSWORD_DEFAULT),
    "lorenzo" => password_hash("lorenzo", PASSWORD_DEFAULT),
    "fulvio" => password_hash("fulvio", PASSWORD_DEFAULT),
    "oliviero" => password_hash("oliviero", PASSWORD_DEFAULT),
    
    "----------------" => "-------------------",

    "giovanni" => password_hash("giovanni", PASSWORD_DEFAULT),
    "laura" => password_hash("laura", PASSWORD_DEFAULT),
    "giuseppe" => password_hash("giuseppe", PASSWORD_DEFAULT),
    "francesca" => password_hash("francesca", PASSWORD_DEFAULT),
    "marco" => password_hash("marco", PASSWORD_DEFAULT),
    "simone" => password_hash("simone", PASSWORD_DEFAULT),
    "luigi" => password_hash("luigi", PASSWORD_DEFAULT),
    "andrea" => password_hash("andrea", PASSWORD_DEFAULT),
    "paolo" => password_hash("paolo", PASSWORD_DEFAULT),
    "roberto" => password_hash("roberto", PASSWORD_DEFAULT),
    "antonio" => password_hash("antonio", PASSWORD_DEFAULT),
    "davide" => password_hash("davide", PASSWORD_DEFAULT),
    "riccardo" => password_hash("riccardo", PASSWORD_DEFAULT),
    "enrico" => password_hash("enrico", PASSWORD_DEFAULT),
    "marco" => password_hash("marco", PASSWORD_DEFAULT),
    "gabriele" => password_hash("gabriele", PASSWORD_DEFAULT),
    "michele" => password_hash("michele", PASSWORD_DEFAULT),
    "andrea" => password_hash("andrea", PASSWORD_DEFAULT),
    "luca" => password_hash("luca", PASSWORD_DEFAULT),
    "paola" => password_hash("paola", PASSWORD_DEFAULT),
    "carlo" => password_hash("carlo", PASSWORD_DEFAULT),
    "elisa" => password_hash("elisa", PASSWORD_DEFAULT),
    "luigi" => password_hash("luigi", PASSWORD_DEFAULT),
    "giorgio" => password_hash("giorgio", PASSWORD_DEFAULT),
    "luisa" => password_hash("luisa", PASSWORD_DEFAULT)
);

foreach ($vet_hash as $key => $value) {
    echo $key . " " . $value . "<br>\n";
}


?>