<?php

namespace ZFTest\OAuth2\Doctrine\ORM;

use OAuth2\Storage\PublicKeyInterface;

class PublicKeyTest extends AbstractTest
{
    /** @dataProvider provideStorage */
    public function testSetAccessToken($storage)
    {
        $globalPublicKey = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCvfF+Cw8nzsc9Twam37SYpAW3+
lRGUle/hYnd9obfBvDHKBvgb1WfGCblwjwImGL9u0rEIW2sspkwBEsGGFFBmSaqq
fvEER7Yr++VIidOUHkas3cHO1TVoERO3s0THOobw0OzghPnMJL6ayelYOESwfnqR
WfuEMSaWaW0G38QPzwIDAQAB
-----END PUBLIC KEY-----
";

        $globalPrivateKey = "-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQCvfF+Cw8nzsc9Twam37SYpAW3+lRGUle/hYnd9obfBvDHKBvgb
1WfGCblwjwImGL9u0rEIW2sspkwBEsGGFFBmSaqqfvEER7Yr++VIidOUHkas3cHO
1TVoERO3s0THOobw0OzghPnMJL6ayelYOESwfnqRWfuEMSaWaW0G38QPzwIDAQAB
AoGAYHtBB+QdZJ6eHq6bYURBdsoSb6YFxGurN3+rsqb3IM0XkrvCLYtnQrqV+gym
Ycu5dHTiYHXitum3X9+wBseka692RYcYuQbBIeT64H91kiFKLBy1vy/g8cmUyI0X
TmabVBnFgS6JGL26C3zC71k3xmd0OQAEpAKg/vYaz2gTwAECQQDYiaEcS29aFsxm
vT3/IvNV17nGvH5sJAuOkKzf6P6TyE2NmAqSjqngm0wSwRdlARcWM+v6H2R/0qdF
6azDItuBAkEAz3eCWygU7pLOtw4VfrX1ppWBIw6qLNF2lKdKPnFqFk5c3GK9ek2G
tTn6NI3LT5NnKu2/YFTR4tr4hgBbdJfTTwJAWWQfxZ2Cn49P3I39PQmBqQuAnwGL
szsCJl2lcF4wUnPbSDvfCXepu5aAxjE+Zi0YCctvfHdfNsGQ2nTIJFqMgQJBAL5L
D/YsvYZWgeTFtlGS9M7nMpvFR7H0LqALEb5UqMns9p/usX0MvxJbK3Qo2uMSgP6P
M4pYQmuiDXJbwYcf+2ECQCB3s5z9niG6oxVicCfK/l6VJNPifhtr8N48jO0ejWeB
1OYsqgH36dp0vjhmtUZip0ikLOxdOueHeOZEjwlt2l8=
-----END RSA PRIVATE KEY-----
";

        /* assert values from storage */
        $this->assertEquals($globalPublicKey, $storage->getPublicKey('oauth_test_client'));
        $this->assertEquals($globalPrivateKey, $storage->getPrivateKey('oauth_test_client'));
        $this->assertEquals('rsa', $storage->getEncryptionAlgorithm('oauth_test_client'));

        $this->assertFalse($storage->getPublicKey('invalidclient'));
        $this->assertFalse($storage->getPublicKey('oauth_test_client2'));

        $this->assertFalse($storage->getPrivateKey('invalidclient'));
        $this->assertFalse($storage->getPrivateKey('oauth_test_client2'));

        $this->assertFalse($storage->getEncryptionAlgorithm('invalidclient'));
        $this->assertFalse($storage->getEncryptionAlgorithm('oauth_test_client2'));

        $this->assertTrue($storage->getPublicKey('event_stop_propagation'));
        $this->assertTrue($storage->getPrivateKey('event_stop_propagation'));
        $this->assertTrue($storage->getEncryptionAlgorithm('event_stop_propagation'));
    }
}
