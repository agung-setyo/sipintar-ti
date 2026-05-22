<?php
use PHPUnit\Framework\TestCase;

final class AuthTest extends TestCase
{
    public function testConfigFilesExist()
    {
        $this->assertFileExists(__DIR__ . '/../config/session.php');
        $this->assertFileExists(__DIR__ . '/../config/app.php');
    }
}
