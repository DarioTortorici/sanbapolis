<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class CalendarTest extends \Codeception\Test\Unit
{
    protected AcceptanceTester $tester;

    protected function _before()
    {
    }

    public function testCalendarEventCreation()
    {
         // Imposta cookie chiamato 'email' per evitare il reindirizzamento al login
        $this->tester->setCookie('email', 'manutentore@example.com');
        // Partiamo dalla pagina calendario
        $this->tester->amOnPage('/calendar/calendar.php');
        // Accertiamo che non siamo stati reindirizzati e che il cookie sia andato a buon fine
        $this->tester->seeCurrentUrlEquals('/calendar/calendar.php');
        // Verifichiamo che il calendario sia stato creato
        $this->tester->seeElement('#calendar');
        
       
        $this->tester->selectOption('society', 'Aquila Basket');
        $this->tester->fillField('startTime', '10:00');
        $this->tester->fillField('endTime', '12:00');

        // Invio del modulo
        $this->tester->click('#save-event');
        
    }

    public function testCalendarEventDeletion()
    {
         // Imposta cookie chiamato 'email' per evitare il reindirizzamento al login
         $this->tester->setCookie('email', 'manutentore@example.com');

         // Partiamo dalla pagina calendario
         $this->tester->amOnPage('/calendar/calendar.php');
         // Accertiamo che non siamo stati reindirizzati e che il cookie sia andato a buon fine
         $this->tester->seeCurrentUrlEquals('/calendar/calendar.php');   
        
        $this->tester->seeElement('#show-event-modal');

        // Ottieni l'ID dell'evento dal modal di visualizzazione
        $eventId = $this->tester->grabAttributeFrom('#event-id', 'data-event-id');

        // Elimina l'evento utilizzando l'ID ottenuto
        $this->tester->amOnPage('/delete-event.php?eventId=' . $eventId);
    
    }
}
