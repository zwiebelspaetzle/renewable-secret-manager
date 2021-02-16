<?php

use PHPUnit\Framework\TestCase;
use \RenewableSecretManager\SecretManager\SecretManager;

class SecretManagerUnitTest extends TestCase
{
    public function setUp(): void
    {
        $this->secretManager = new SecretManager();
    }

    public function tearDown(): void
    {
        unset($this->secretManager);
    }

    public function testSimpleSecret()
    {
        $this->secretManager->newStaticSecret('username', 'bob@example.com');
        $this->assertEquals('bob@example.com', $this->secretManager->getSecret('username'));
    }

    public function testRenewableSecret()
    {
        $this->secretManager->newRenewableSecret(
            'time-test',
            [$this, 'fetcher'],
            [$this, 'checker']
        );

        $begin = time();
        $this->assertEquals($begin, $this->secretManager->getSecret('time-test'));

        sleep(1); // wait enough to change time, but not expire secret
        $this->assertNotEquals($begin, time()); // time should be different now...
        $this->assertNotEquals(time(), $this->secretManager->getSecret('time-test')); // ... but the secret the same

        sleep(3);  // wait for secret to expire. make sure we get a new one
        $this->assertEquals(time(), $this->secretManager->getSecret('time-test'));
    }

    public function fetcher()
    {
        return time();
    }

    public function checker($secret) {
        $valid = (time() - $secret < 4);
        return $valid;
    }
}