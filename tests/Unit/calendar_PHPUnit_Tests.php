<?php

//Composer Autoloader per caricare PHPUnit
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../calendar/calendar-helper.php';


use PHPUnit\Framework\TestCase;

class calendar_PHPUnit_Tests extends TestCase
{
    private $id; // Proprietà per memorizzare l'ID dell'evento

    public function testSaveEvent()
    {
        // Configura il database di prova
        $dsn = 'mysql:host=127.0.0.1;dbname=testdb';
        $username = 'phpuser';
        $password = 'Ig86N3tUa9';


        // Connessione al database di prova
        $con = new PDO($dsn, $username, $password);

        // Dati di esempio per l'evento
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
        $sessionId = '4';

        // Call the function to be tested
        $this->id = save_event($groupId, $allDay, $startDate, $endDate, $daysOfWeek, $startTime, $endTime, $startRecur, $endRecur, $url, $society, $sport, $coach, $note, $eventType, $cameras, $sessionId);

        // Assertions
        $this->assertGreaterThan(0, $this->id); // Verifica che l'ID dell'evento sia maggiore di 0, indicando una creazione avvenuta con successo.
    }

    public function testIsAjaxRequest()
    {
        // Test an AJAX request
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
        $this->assertTrue(is_ajax_request());

        // Test a non-AJAX request
        $_SERVER['HTTP_X_REQUESTED_WITH'] = null;
        $this->assertFalse(is_ajax_request());
    }

    public function testAccorpaTime()
    {
        $date = '2023-07-06';
        $time = '09:00:00';
        $expectedDateTime = '2023-07-06 09:00:00';

        $dateTime = accorpaTime($date, $time);

        $this->assertEquals($expectedDateTime, $dateTime);
        
    }

    public function testModifyTraining()
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

        $result = edit_training($groupId, $startDate, $endDate, $startTime, $endTime, $url, $society, $note, $this->id);
        $this->assertEquals($result, $this->id);
    }

    public function test_save_cameras()
    {
        // Configurazione di prova
        $cameras = '["2", "3"]';

        // Chiamata alla funzione
        $result = save_cameras($cameras, $this->id);

        $this->assertEquals($result, $this->id);
    }

    public function test_getSportbyTeam()
    {
        // Configurazione di prova
        $squadra = array('id' => 1); // Dati della squadra di prova

        // Chiamata alla funzione
        $result = getSportbyTeam($squadra);

        // Verifica dei risultati
        $this->assertContains($result, ['Calcio5', 'Basket', 'Pallavolo']);
    }

    public function test_getSquadra()
    {
        // Configurazione di prova
        $society = 'Aquila Basket';

        // Chiamata alla funzione
        $result = getSquadra($society);

        $this->assertNotNull($result);
    }

    public function test_getEvents()
    {
        // Chiamata alla funzione
        $result = getEvents();

        // Verifica dei risultati
        $this->assertNotNull($result);
    }

    public function test_getEvent()
    {
        // Chiamata alla funzione
        $result = getEvent($this->id);

        // Verifica dei risultati
        $this->assertNotNull($result);
    }

    public function test_getInfoEvent()
    {
        // Chiamata alla funzione
        $result = getInfoEvent($this->id);
        // Verifica dei risultati
        $this->assertNotNull($result);
    }

    public function test_getMatches()
    {
        // Chiamata alla funzione
        $result = getMatches();
        $this->assertNotNull($result);
    }

    public function test_getCoachEvents()
    {
        $coach = 'coach@example.com';

        // Chiamata alla funzione
        $result = getCoachEvents($coach);
        $this->assertNotNull($result);
    }

    public function test_getNote()
    {

        // Chiamata alla funzione
        $result = getNote($this->id);
        $this->assertNotNull($result);
    }

    public function test_getEventColor()
    {
        $sport = 'Basket';
        // Chiamata alla funzione
        $result = getEventColor($sport);
        $this->assertNotNull($result);
    }

    public function test_getCameras()
    {

        // Chiamata alla funzione
        $result = getCameras($this->id);
        $this->assertNotNull($result);
    }

    public function test_getDatetimeEvent()
    {
        // Chiamata alla funzione
        $result = getDatetimeEvent($this->id);

        // Verifica dei risultati
        $this->assertNotNull($result);
    }

    public function test_getSociety()
    {
        // Chiamata alla funzione
        $result = getSocieties();
        // Verifica dei risultati
        $this->assertNotNull($result);
    }

    public function test_delete_training()
    {
        // Chiamata alla funzione
        $result = delete_training($this->id);
        // Verifica dei risultati
        $this->assertNotNull($result);
    }
}
