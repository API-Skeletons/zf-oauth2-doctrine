<?php

namespace ZFTest\OAuth2\Doctrine\ORM;

use OAuth2\Storage\ClientCredentialsInterface;

class ClientCredentialsTest extends AbstractTest
{
    /** @dataProvider provideStorage */
    public function testCheckClientCredentials(ClientCredentialsInterface $storage)
    {
        if ($storage instanceof NullStorage) {
            $this->markTestSkipped('Skipped Storage: ' . $storage->getMessage());

            return;
        }

        // nonexistant client_id
        $pass = $storage->checkClientCredentials('fakeclient', 'testpass');
        $this->assertFalse($pass);

        // invalid password
        $pass = $storage->checkClientCredentials('oauth_test_client', 'invalidcredentials');
        $this->assertFalse($pass);

        // valid credentials
        $pass = $storage->checkClientCredentials('oauth_test_client', 'testpass');
        $this->assertTrue($pass);

        $this->assertTrue($storage->checkClientCredentials('event_stop_propagation', ''));
    }
}
