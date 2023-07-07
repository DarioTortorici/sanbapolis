<!doctype html>
<html lang="it">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>Calendar Unit Tests</title>
</head>

<body>
    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <div class="container text-center">
        <input type="checkbox" id="verboseModeCheckbox" checked value="Verbose-mode"> Verbose-mode
        <div class="fw-light" id="testResults">
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const checkbox = document.getElementById("verboseModeCheckbox");
                    const testResults = document.getElementById("testResults");

                    checkbox.addEventListener("change", function() {
                        if (this.checked) {
                            testResults.style.display = "block";
                        } else {
                            testResults.style.display = "none";
                        }
                    });
                });
            </script>

            <?php
            require __DIR__.'/../../profile/myteam-helper.php';

            function testGetTeams()
            {
                // Chiamata alla funzione da testare
                $result = getTeams();

                // Verifica dei risultati
                if ($result) {
                    echo " <p> Elenco delle squadre :\n" . $result . "\n\n </p>";
                    return true;
                } else {
                    echo " <p> Nessuna squadra trovata.";
                    return false;
                }
            }

            function testGetTeam()
            {
                // Configurazione di prova
                $id = 1;

                // Chiamata alla funzione da testare
                $result = getTeam($id);

                // Verifica dei risultati
                if ($result) {
                    echo " <p> La squadra con id =\n". $id . ":" . $result . "\n\n </p>";
                    return true;
                } else {
                    echo " <p> Nessuna squadra trovata.";
                    return false;
                }
            }

            function testGetTeamByCoach()
            {
                // Configurazione di prova
                $coach_email = 'coach@example.com';

                // Chiamata alla funzione da testare
                $result = getTeambyCoach($coach_email);

                // Verifica dei risultati
                if ($result) {
                    echo " <p> Elenco delle squadre di \n" . $coach_email . ":" . $result . "\n\n </p>";
                    return true;
                } else {
                    echo " <p> Nessuna squadra trovata associata a ". $coach_email ."\n\n </p>";
                    return false;
                }
            }

            function testGetPlayersByTeam()
            {
                // Configurazione di prova
                $teamid = 1;

                // Chiamata alla funzione da testare
                $result = getPlayersbyTeam($teamid);

                // Verifica dei risultati
                if ($result) {
                    echo " <p> Elenco dei giocatori della squadra :\n" . $result . "\n\n </p> </div>";
                    return true;
                } else {
                    echo " <p> Nessuna squadra trovata associata al ". $teamid ."\n\n </p> </div>";
                    return false;
                }
            }

            // Contatori per i test passati e non passati
            $passedTests = 0;
            $failedTests = 0;
            $numberOfTests = 4;

            // Esegui il test e incrementa i contatori in base al risultato
            $passedTests += testGetTeams() ? 1 : 0;
            $passedTests += testGetTeam() ? 1 : 0;
            $passedTests += testGetTeamByCoach() ? 1 : 0;
            $passedTests += testGetPlayersByTeam() ? 1 : 0;

            // Calcola il numero di test falliti
            $failedTests = $numberOfTests - $passedTests;

            // Stampa il risultato totale
            echo " <p> Test completati:\n";
            echo " Test passati: $passedTests\n";
            echo " Test falliti: $failedTests\n";

            ?>
        </div>


</body>

</html>