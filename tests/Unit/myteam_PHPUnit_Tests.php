<?php

//Composer Autoloader per caricare PHPUnit
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../profile/myteam-helper.php';

use PHPUnit\Framework\TestCase;

class myteam_PHPUnit_Tests extends TestCase
{
    public function testGetTeams()
    {
        // Chiamata alla funzione da testare
        $result = getTeams();

        // Verifica dei risultati;
        $this->assertNotEmpty($result);
    }

    public function testGetTeam()
    {
        // Configurazione di prova
        $id = 1;

        // Chiamata alla funzione da testare
        $result = getTeam($id);

        // Verifica dei risultati
        $this->assertNotEmpty($result);
    }

    public function testGetTeamByCoach()
    {
        // Configurazione di prova
        $coach_email = 'coach@example.com';

        // Chiamata alla funzione da testare
        $result = getTeambyCoach($coach_email);

        // Verifica dei risultati
        $this->assertNotEmpty($result);
    }

    public function testGetPlayersByTeam()
    {
        // Configurazione di prova
        $teamid = 1;

        // Chiamata alla funzione da testare
        $result = getPlayersbyTeam($teamid);

        // Verifica dei risultati
        $this->assertNotEmpty($result);
    }
}
