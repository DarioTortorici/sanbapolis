<!doctype html>
<html lang="it">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>Authentication Unit Tests</title>
</head>

<body>
    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <div class="container text-center fw-light">
        <?php

        // Funzione di integration test per l'autenticazione
        function test_authentication($email, $password)
        {
            // Simula una richiesta HTTP con i dati di input
            $_POST['email'] = isset($email) ? $email : '';
            $_POST['password'] = isset($password) ? $password : '';

            // Avvia l'output del buffer per catturare la risposta
            ob_start();

            // Esegui la funzione di autenticazione
            include('../authentication/login-process.php');

            // Ottieni la risposta dalla funzione di autenticazione
            $response = ob_get_clean();

            // Decodifica la risposta JSON in un array
            $responseArray = json_decode($response, true);

            // Verifica se l'autenticazione è avvenuta con successo
            if ($responseArray['success'] === true) {
                echo "<p>Authentication successful\n</p>";
            } else {
                echo "<p>Authentication failed\n\n</p>";
            }
        }

        echo "Integration tests per l'autenticazione\n\n";

        // Test caso di autenticazione con credenziali corrette
        echo "\n";
        echo "<h5> Test 1 - Credenziali corrette\n </h5>";
        test_authentication('dario.tortorici@studenti.unitn.it', 'A1234567!');
        echo "\n";

        // Test caso di autenticazione con email errata
        echo "\n";
        echo "<h5> Test 2 - Email incorretta\n </h5>";
        test_authentication('example@example.com', 'password123');
        echo "\n";

        // Test caso di autenticazione con password errata
        echo "\n";
        echo "<h5>Test 3 - Incorrect password\n</h5>";
        test_authentication('dario.tortorici@studenti.unitn.it', 'password123');
        echo "\n";
        ?>
    </div>


</body>

</html>