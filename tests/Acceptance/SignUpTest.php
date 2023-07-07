<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class SignUpTest extends \Codeception\Test\Unit
{
    protected AcceptanceTester $tester;

    protected function _before()
    {
    }

    public function testRedirectToRegistrationPage()
    {
        // Inizio dalla home
        $this->tester->amOnPage('');

        // Fai clic sul bottone di registrazione
        $this->tester->click('Iscriviti');

        // Verifica che il reindirizzamento sia avvenuto correttamente
        $this->tester->seeCurrentUrlEquals('/authentication/register.php');

        // Testa il rendering corretto della pagina di registrazione
        $this->tester->seeResponseCodeIs(200);

        // Controlla che venga visualizzato il form di registrazione
        $this->tester->seeElement('#Registerform');

        // Aggiungi ulteriori asserzioni per verificare il rendering corretto della pagina
    }

    public function testRegistrationWithInvalidData()
    {
        $this->tester->amOnPage('/authentication/register.php');
        // Compila il modulo di registrazione con dati invalidi

        $this->tester->fillField('firstName', 'Dario');
        $this->tester->fillField('lastName', 'Tortorici');
        $this->tester->fillField('email', 'dario.tortorici@example.com');
        $this->tester->fillField('password', 'Password');
        $this->tester->fillField('confirm_pwd', 'Password');
        $this->tester->selectOption('userType', 'giocatore');
        $this->tester->fillField('teamCode', 'BSKTTN');

        // Verifica che la checkbox sia inizialmente non spuntata
        $this->tester->dontSeeCheckboxIsChecked('#agreement');

        // Spunta la checkbox dei termini e condizioni
        $this->tester->checkOption('#agreement');

        // Verifica che la checkbox sia ora spuntata
        $this->tester->seeCheckboxIsChecked('#agreement');

        // Esegui il submit del modulo
        $this->tester->click('Continue');

        $this->tester->see('La password deve contenere almeno 8 caratteri, di cui uno maiuscolo ed uno speciale.');
    }

    public function testRegistrationWithValidData()
    {
        $this->tester->amOnPage('/authentication/register.php');
        // Compila il modulo di registrazione con dati invalidi
        $this->tester->fillField('firstName', 'Dario');
        $this->tester->fillField('lastName', 'Tortorici');
        $this->tester->fillField('email', 'dario.tortorici@example.com');
        $this->tester->fillField('password', 'Password123!');
        $this->tester->fillField('confirm_pwd', 'Password123!');
        $this->tester->selectOption('userType', 'giocatore');
        $this->tester->fillField('teamCode', 'BSKTTN');

        // Verifica che la checkbox sia inizialmente non spuntata
        $this->tester->dontSeeCheckboxIsChecked('#agreement');

        // Spunta la checkbox dei termini e condizioni
        $this->tester->checkOption('#agreement');

        // Verifica che la checkbox sia ora spuntata
        $this->tester->seeCheckboxIsChecked('#agreement');

        // Esegui il submit del modulo
        $this->tester->click('Continue');
    }
}
