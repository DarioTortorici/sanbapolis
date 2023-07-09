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
    $this->tester->amOnPage('/calendar.php');

    // Clic su un giorno nel calendario utilizzando la classe specifica di FullCalendar
    $this->tester->click('.fc-day[data-date="2023-07-10"]');

    // Compila il modulo per l'aggiunta di un nuovo evento
    $this->tester->selectOption('society', 'Aquila Basket');
    $this->tester->fillField('start-date', '2023-07-10');
    $this->tester->fillField('end-date', '2023-07-10');
    $this->tester->fillField('start-time', '10:00');
    $this->tester->fillField('end-time', '12:00');
    $this->tester->fillField('description', 'Descrizione dell\'evento');

    // Invio del modulo
    $this->tester->click('#save-event');

    // Verifica che il modal sia stato chiuso
    $this->tester->dontSeeElement('#add-event-modal');

    // Verifica che l'evento sia stato creato correttamente nel calendario utilizzando la classe specifica di FullCalendar
    $this->tester->seeElement('.fc-event-title:contains("Aquila Basket")');

    // Puoi continuare con ulteriori asserzioni o azioni nel tuo test
}

public function testCalendarEventDeletion()
{
    $this->tester->amOnPage('/calendar.php');

    // Clic su un evento nel calendario per aprire il modal di visualizzazione
    $this->tester->click('.fc-event');

    // Verifica che il modal di visualizzazione sia stato aperto correttamente
    $this->tester->seeElement('#show-event-modal');

    // Ottieni l'ID dell'evento dal modal di visualizzazione
    $eventId = $this->tester->grabAttributeFrom('#event-id', 'data-event-id');

    // Elimina l'evento utilizzando l'ID ottenuto
    $this->tester->amOnPage('/delete-event.php?eventId=' . $eventId);

    // Verifica che l'evento sia stato eliminato correttamente
    $this->tester->dontSeeElement('.fc-event-title:contains("' . $eventId . '")');
}


}