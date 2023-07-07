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
            include("../../calendar/calendar-helper.php");

            // Configurazione di prova

            $groupId = 1;
            $allDay = 0; // false
            $startDate = '2023-07-01';
            $endDate = '2023-07-01';
            $daysOfWeek = '[2,3,4]';
            $startTime = '10:00:00';
            $endTime = '12:00:00';
            $startRecur = null;
            $endRecur = null;
            $url = 'http://example.com/event';
            $society = 'Aquila Basket';
            $sport = 'Basket';
            $coach = 'coach@example.com';
            $note = 'Evento unit test';
            $eventType = '0';
            $cameras = '["1", "2"]';
            $sessionId = '6';

            $passedTests = 0;
            $failedTests = 0;

            $id = test_save_event();

            function test_save_event()
            {
                global $groupId, $allDay, $startDate, $endDate, $daysOfWeek, $startTime, $endTime, $startRecur, $endRecur, $url, $society, $sport, $coach, $note, $eventType, $cameras, $sessionId;
                // Chiamata alla funzione
                $id = save_event($groupId, $allDay, $startDate, $endDate, $daysOfWeek, $startTime, $endTime, $startRecur, $endRecur, $url, $society, $sport, $coach, $note, $eventType, $cameras, $sessionId);

                // Verifica dei risultati
                return $id;
            }

            function test_edit_training()
            {
                // Configurazione di prova
                $groupId = 2;
                $startDate = '2023-07-01';
                $endDate = '2023-07-01';
                $startTime = '10:00:00';
                $endTime = '11:00:00';
                $url = 'http://example.com/modified_event';
                $society = 'Aquila Basket';
                $note = 'Evento unit test modificato';

                global $id;

                // Chiamata alla funzione
                $result = edit_training($groupId, $startDate, $endDate, $startTime, $endTime, $url, $society, $note, $id);

                // Verifica dei risultati
                if ($result === $id) {
                    echo " <p> Evento di allenamento modificato correttamente.\n\n </p>";
                    return true;
                } else {
                    echo " <p> Errore durante la modifica dell'evento di allenamento.\n\n </p>";
                    return false;
                }
            }

            function test_save_cameras()
            {
                // Configurazione di prova
                global $cameras, $id;

                // Chiamata alla funzione
                $result = save_cameras($cameras, $id);

                // Verifica dei risultati
                if ($result === $id) {
                    echo " <p> Telecamere salvate correttamente per l'evento con ID: $id.\n\n </p>";
                    return true;
                } else {
                    echo " <p> Errore durante il salvataggio delle telecamere per l'evento con ID: $id.\n\n </p>";
                    return false;
                }
            }

            function test_getSportbyTeam()
            {
                // Configurazione di prova
                $squadra = array('id' => 1); // Dati della squadra di prova

                // Chiamata alla funzione
                $result = getSportbyTeam($squadra);

                // Verifica dei risultati
                if ($result) {
                    echo " <p> Lo sport della squadra con ID " . $squadra['id'] . " è: $result";
                    return true;
                } else {
                    echo " <p> Impossibile determinare lo sport della squadra con ID " . $squadra['id'] . "\n\n </p>";
                    return false;
                }
            }

            function test_delete_training()
            {
                // Configurazione di prova
                global $id;

                // Chiamata alla funzione
                $result = delete_training($id);

                // Verifica dei risultati
                if ($result) {
                    echo " <p> L'evento con ID $id è stato eliminato correttamente.\n\n </p> </div>";
                    return true;
                } else {
                    echo " <p> Errore durante l'eliminazione dell'evento con ID $id. \n\n </p>";
                    return false;
                }
            }

            function test_getSquadra()
            {
                // Configurazione di prova
                global $society;

                // Chiamata alla funzione
                $result = getSquadra($society);

                // Verifica dei risultati
                if ($result) {
                    echo " <p> ID della squadra associata alla società $society: " . $result['id'] . "\n\n </p>";
                    return true;
                } else {
                    echo " <p> Nessuna squadra trovata per la società $society.";
                    return false;
                }
            }

            function test_getAuthorEvent()
            {
                // Configurazione di prova
                global $sessionId; // ID della sessione

                // Chiamata alla funzione
                $result = getAuthorEvent($sessionId);

                // Verifica dei risultati
                if ($result) {
                    echo " <p> Email dell'autore associato alla sessione $sessionId: " . $result . "\n\n </p>";
                    return true;
                } else {
                    echo " <p> Nessun autore trovato per la sessione $sessionId.";
                    return false;
                }
            }

            function test_getEvents()
            {
                // Chiamata alla funzione
                $result = getEvents();

                // Verifica dei risultati
                if ($result) {
                    echo " <p> Elenco degli eventi:\n" . $result . "\n\n </p>";
                    return true;
                } else {
                    echo " <p> Nessun evento trovato.";
                    return false;
                }
            }

            function test_getEvent($id)
            {
                // Chiamata alla funzione
                $result = getEvent($id);

                // Verifica dei risultati
                if ($result) {
                    echo " <p> Dettagli dell'evento:\n" . $result . "\n\n </p>";
                    return true;
                } else {
                    echo " <p> Evento non trovato.";
                    return false;
                }
            }

            function test_getInfoEvent($id)
            {
                // Chiamata alla funzione
                $result = getInfoEvent($id);

                // Verifica dei risultati
                if ($result) {
                    echo " <p> Dettagli dell'evento:\n" . $result . "\n\n </p>";
                    return true;
                } else {
                    echo " <p> Evento non trovato.";
                    return false;
                }
            }

            function test_getMatches()
            {
                // Chiamata alla funzione
                $result = getMatches();

                // Verifica dei risultati
                if ($result) {
                    echo " <p> Partite trovate:\n" . $result . "\n\n </p>";
                    return true;
                } else {
                    echo " <p> Nessuna partita trovata.";
                    return false;
                }
            }

            function test_getCoachEvents()
            {
                global $coach;

                // Chiamata alla funzione
                $result = getCoachEvents($coach);

                // Verifica dei risultati
                if ($result) {
                    echo " <p> Eventi dell'allenatore:\n" . $result . "\n\n </p>";
                    return true;
                } else {
                    echo " <p> Nessun evento trovato per l'allenatore.\n\n </p>";
                    return false;
                }
            }

            function test_getNote()
            {
                // Specifica l'ID dell'evento per il quale si vogliono ottenere le note
                global $id;

                // Chiamata alla funzione
                $result = getNote($id);

                // Verifica dei risultati
                if ($result) {
                    echo " <p> Note dell'evento:\n" . json_encode($result) . "\n\n </p>";
                    return true;
                } else {
                    echo " <p> Nessuna nota trovata per l'evento.\n\n </p>";
                    return false;
                }
            }

            function test_getEventColor()
            {
                // Specifica lo sport per il quale si vuole ottenere il colore dell'evento
                global $sport;

                // Chiamata alla funzione
                $result = getEventColor($sport);

                // Verifica dei risultati
                echo " <p> Colore dell'evento per lo sport '$sport': $result\n\n </p>";
                return true;
            }

            function test_getCameras()
            {
                // Specifica l'ID dell'evento per il quale si vogliono ottenere le telecamere
                global $id;

                // Chiamata alla funzione
                $result = getCameras($id);

                // Verifica dei risultati
                if ($result) {
                    echo " <p> Telecamere dell'evento con ID $id: $result\n\n </p>";
                    return true;
                } else {
                    echo " <p> Nessuna telecamera trovata per l'evento.\n\n </p>";
                    return false;
                }
            }

            function test_getDatetimeEvent()
            {
                // Specifica l'ID dell'evento per il quale si vogliono ottenere la data e l'ora
                global $id;

                // Chiamata alla funzione
                $result = getDatetimeEvent($id);

                // Verifica dei risultati
                echo " <p> Data e ora dell'evento con ID $id: $result\n\n </p>";
                return true;
            }

            function test_getSociety()
            {
                // Chiamata alla funzione
                $result = getSocieties();

                // Verifica dei risultati
                echo " <p> Elenco delle società sportive:\n" . $result . "\n\n </p>";
                return true;
            }

            // Contatori per i test passati e non passati
            $passedTests = 0;
            $failedTests = 0;
            $numberOfTests = 16;

            // Esegui il test e incrementa i contatori in base al risultato
            $id != null ? 1 : 0;
            $passedTests += test_getSociety() ? 1 : 0;
            $passedTests += test_getDatetimeEvent() ? 1 : 0;
            $passedTests += test_getCameras() ? 1 : 0;
            $passedTests += test_getCoachEvents() ? 1 : 0;
            $passedTests += test_getEventColor() ? 1 : 0;
            $passedTests += test_getNote() ? 1 : 0;
            $passedTests += test_getMatches() ? 1 : 0;
            $passedTests += test_getInfoEvent(1) ? 1 : 0;
            $passedTests += test_getEvent(1) ? 1 : 0;
            $passedTests += test_getEvents() ? 1 : 0;
            $passedTests += test_getAuthorEvent(1) ? 1 : 0;
            $passedTests += test_getSquadra() ? 1 : 0;
            $passedTests += test_getSportbyTeam() ? 1 : 0;
            $passedTests += test_save_cameras() ? 1 : 0;
            $passedTests += test_edit_training() ? 1 : 0;
            $passedTests += test_delete_training() ? 1 : 0;

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