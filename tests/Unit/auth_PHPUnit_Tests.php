<?php

//Composer Autoloader per caricare PHPUnit
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../authentication/auth-helper.php';


use PHPUnit\Framework\TestCase;

class auth_PHPUnit_Tests extends TestCase
{
    public function testValidateInputText()
    {
        $input = '  Example Text  ';
        $expectedOutput = 'Example Text';

        $result = validate_input_text($input);

        $this->assertEquals($expectedOutput, $result);
    }

    public function testValidateInputTextEmpty()
    {
        $input = '';
        $expectedOutput = '';

        $result = validate_input_text($input);

        $this->assertEquals($expectedOutput, $result);
    }

    public function testValidateInputEmail()
    {
        $input = '  example@example.com  ';
        $expectedOutput = 'example@example.com';

        $result = validate_input_email($input);

        $this->assertEquals($expectedOutput, $result);
    }

    public function testValidateInputEmailEmpty()
    {
        $input = '';
        $expectedOutput = '';

        $result = validate_input_email($input);

        $this->assertEquals($expectedOutput, $result);
    }

    public function testValidatePassword()
    {
        // Test caso in cui la password soddisfa tutti i requisiti
        $password = 'Password1!';
        $this->assertTrue(validate_password($password));

        // Test caso in cui la password non ha una lunghezza sufficiente
        $password = 'pass';
        $this->assertFalse(validate_password($password));

        // Test caso in cui la password non contiene una lettera maiuscola
        $password = 'password1!';
        $this->assertFalse(validate_password($password));

        // Test caso in cui la password non contiene un carattere speciale
        $password = 'Password1';
        $this->assertFalse(validate_password($password));
    }

    public function testValidateSocietyCode()
    {
        // Prepara un'istanza mock di PDO per il test
        $pdoMock = $this->createMock(PDO::class);

        // Prepara un'istanza mock di PDOStatement per il test
        $stmtMock = $this->createMock(PDOStatement::class);

        // Imposta il comportamento del PDOStatement mock
        $stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn(['count' => 1]);

        // Imposta il comportamento del PDO mock
        $pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        // Esegui il test passando le istanze mock di PDO e PDOStatement
        $result = validate_society_code($pdoMock, 'EAGLEB');

        // Verifica se il risultato del test è corretto
        $this->assertTrue($result);
    }

    public function testValidateTeamCode()
    {
        // Prepara un'istanza mock di PDO per il test
        $pdoMock = $this->createMock(PDO::class);

        // Prepara un'istanza mock di PDOStatement per il test
        $stmtMock = $this->createMock(PDOStatement::class);

        // Imposta il comportamento del PDOStatement mock
        $stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn(['count' => 1]);

        // Imposta il comportamento del PDO mock
        $pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        // Esegui il test passando le istanze mock di PDO e PDOStatement
        $result = validate_team_code($pdoMock, 'BSKTTN');

        // Verifica se il risultato del test è corretto
        $this->assertTrue($result);
    }

    public function testAddCoach()
    {
        // Configura il database di prova
        $dsn = 'mysql:host=127.0.0.1;dbname=testdb';
        $username = 'phpuser';
        $password = 'Ig86N3tUa9';


        // Connessione al database di prova
        $con = new PDO($dsn, $username, $password);

        $userID = random_int(1000, 2000);;

        $email = 'coachemail@example.com';

        // Inserisci un record di prova nella tabella 'persone'
        $query = "INSERT INTO persone (session_id, email) VALUES (:userID, :email)";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':userID', $userID);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Esegui il test
        $result = addCoach($con, $email, 'EAGLEB');

        // Verifica se l'inserimento è avvenuto con successo
        $this->assertTrue($result);

        // Verifica se l'allenatore è stato aggiunto alla tabella "allenatori"
        $query = "SELECT COUNT(*) as count FROM allenatori WHERE email = :email";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count);

        // Verifica se è stata creata una relazione con la squadra nella tabella "allenatori_squadre"
        $query = "SELECT COUNT(*) as count FROM allenatori_squadre WHERE email_allenatore = :email";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count);

        // Pulisci il database di prova
        $query = ("DELETE FROM allenatori WHERE email = :email");
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $query = ("DELETE FROM allenatori_squadre WHERE email_allenatore = :email");
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $query = ("DELETE FROM persone WHERE email = :email");
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();



        // Chiudi la connessione al database
        $con = null;
    }

    public function testAddPlayer()
    {
        // Configura il database di prova
        $dsn = 'mysql:host=127.0.0.1;dbname=testdb';
        $username = 'phpuser';
        $password = 'Ig86N3tUa9';

        // Connessione al database di prova
        $con = new PDO($dsn, $username, $password);

        $userID = random_int(1000, 2000);;

        $email = 'playeremail@example.com';

        // Inserisci un record di prova nella tabella 'persone'
        $query = "INSERT INTO persone (session_id, email) VALUES (:userID, :email)";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':userID', $userID);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Esegui il test
        $result = addPlayer($con, $email, 'BSKTTN');

        // Verifica se l'inserimento è avvenuto con successo
        $this->assertTrue($result);

        // Verifica se il giocatore è stato aggiunto alla tabella "giocatori"
        $query = "SELECT COUNT(*) as count FROM giocatori WHERE email = :email";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count);

        // Verifica se è stata creata una relazione con la squadra nella tabella "giocatori_squadre"
        $query = "SELECT COUNT(*) as count FROM giocatori_squadre WHERE email_giocatore = :email";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count);

        // Pulisci il database di prova
        $query = ("DELETE FROM giocatori WHERE email = :email");
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $query = ("DELETE FROM giocatori_squadre WHERE email_giocatore = :email");
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $query = ("DELETE FROM persone WHERE email = :email");
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Chiudi la connessione al database
        $con = null;
    }

    public function testAddFan()
    {
        // Configura il database di prova
        $dsn = 'mysql:host=127.0.0.1;dbname=testdb';
        $username = 'phpuser';
        $password = 'Ig86N3tUa9';

        // Connessione al database di prova
        $con = new PDO($dsn, $username, $password);

        $userID = random_int(1000, 2000);;

        $email = 'fanemail@example.com';

        // Inserisci un record di prova nella tabella 'persone'
        $query = "INSERT INTO persone (session_id, email) VALUES (:userID, :email)";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':userID', $userID);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Esegui il test
        $result = addFan($con, $email);

        // Verifica se il tifoso è stato aggiunto con successo
        $this->assertTrue($result);

        // Verifica se il tifoso è presente nella tabella 'tifosi'
        $query = "SELECT COUNT(*) as count FROM tifosi WHERE email = :email";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count);

        // Pulisci il database di prova
        $query = ("DELETE FROM tifosi WHERE email = :email");
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $query = ("DELETE FROM persone WHERE email = :email");
        $stmt = $con->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Chiudi la connessione al database
        $con = null;
    }

    public function testGetUserInfo()
    {
        // Configura il database di prova
        $dsn = 'mysql:host=127.0.0.1;dbname=testdb';
        $username = 'phpuser';
        $password = 'Ig86N3tUa9';

        // Connessione al database di prova
        $con = new PDO($dsn, $username, $password);
        // Genera un ID di sessione casuale per il test
        $userID = random_int(1000, 2000);

        try {
            // Inserisci un record di prova nella tabella 'persone'
            $query = "INSERT INTO persone (session_id) VALUES (:userID)";
            $stmt = $con->prepare($query);
            $stmt->bindParam(':userID', $userID);
            $stmt->execute();

            // Esegui il test
            $result = get_user_info($con, $userID);

            // Verifica se il risultato non è false
            $this->assertNotFalse($result);

            // Verifica se il campo "userType" è impostato correttamente
            $this->assertArrayHasKey('userType', $result);
            $this->assertContains($result['userType'], ['allenatore', 'giocatore', 'manutentore', 'tifoso']);
        } catch (PDOException $e) {
            // Gestione degli errori di connessione o query
            $this->fail("PDOException: " . $e->getMessage());
        } finally {

            // Pulisci il database di prova
            $query = "DELETE FROM persone WHERE session_id = :userID";
            $stmt = $con->prepare($query);
            $stmt->bindParam(':userID', $userID);
            $stmt->execute();

            // Chiudi la connessione al database
            $con = null;
        }
    }
}
