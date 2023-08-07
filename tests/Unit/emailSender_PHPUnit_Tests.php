<?php

//Composer Autoloader per caricare PHPUnit
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../modals/email-handler.php';
require __DIR__ . '/../../authentication/auth-helper.php';

use PHPUnit\Framework\TestCase;

class emailSender_PHPUnit_Tests extends TestCase
{
    // Mock database connection object
    private $mockConnection;

    protected function setUp(): void
    {
        // Set up a mock database connection for testing
        $this->mockConnection = $this->getMockBuilder('PDO')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testValidUserType()
    {
        // Arrange
        $userType = 'allenatori';
        $invitedEmail = 'testdaeliminara@example.com';

        // Act
        $result = insertInvitedEmail($userType, $invitedEmail);

        // Assert
        $this->assertTrue($result);
    }

    public function testInvalidUserType()
    {
        // Arrange
        $userType = 'invalid';
        $invitedEmail = 'test2@example.com';

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        insertInvitedEmail($userType, $invitedEmail);
    }

}
