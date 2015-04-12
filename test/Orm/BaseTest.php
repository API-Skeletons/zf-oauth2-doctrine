<?php

namespace ZFTest\OAuth2\Doctrine\Orm;

use Doctrine\ORM\Tools\SchemaTool;
use ZF\OAuth2\Doctrine\Entity;
use ZFTest\OAuth2\Doctrine\Entity\User;
use Zend\Crypt\Password\Bcrypt;
use Datetime;
use Exception;

abstract class BaseTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{
    public function provideStorage()
    {
        $this->setUp();

        $serviceManager = $this->getApplication()->getServiceManager();
        $config = $this->getApplication()->getConfig();
        $doctrineAdapter = $serviceManager->get($config['zf-oauth2-doctrine']['storage']);

        return array(array($doctrineAdapter));
    }

    protected function tearDown()
    {
    }

    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../asset/orm.config.php'
        );

        parent::setUp();

        $serviceManager = $this->getApplication()->getServiceManager();
        $objectManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        try {
            $objectManager->getRepository('ZF\OAuth2\Doctrine\Entity\Scope')->findAll();
        } catch (Exception $e) {
            $bcrypt = new Bcrypt();
            $bcrypt->setCost(14);

            // Create database
            $tool = new SchemaTool($objectManager);
            $res = $tool->createSchema($objectManager->getMetadataFactory()->getAllMetadata());

            // Fixtures
            $scope = new Entity\Scope();
            $scope->setScope('clientscope1');

            $scope2 = new Entity\Scope();
            $scope2->setScope('supportedscope1');

            $scope3 = new Entity\Scope();
            $scope3->setScope('supportedscope2');

            $scope4 = new Entity\Scope();
            $scope4->setScope('supportedscope3');

            $scope5 = new Entity\Scope();
            $scope5->setScope('defaultscope1');
            $scope5->setIsDefault(true);

            $scope6 = new Entity\Scope();
            $scope6->setScope('defaultscope2');
            $scope6->setIsDefault(true);

            $objectManager->persist($scope);
            $objectManager->persist($scope2);
            $objectManager->persist($scope3);
            $objectManager->persist($scope4);
            $objectManager->persist($scope5);
            $objectManager->persist($scope6);

            $user = new User();
            $user->setUsername('oauth_test_user');
            $user->setPassword($bcrypt->create('testpass'));
            $user->setProfile('profile');
            $user->setCountry('US');
            $user->setPhoneNumber('phone');
            $user->setEmail('doctrine@zfcampus');

            $user2 = new User();

            $objectManager->persist($user);
            $objectManager->persist($user2);

            $client = new Entity\Client();
            $client->setClientId('oauth_test_client');
            $client->setSecret($bcrypt->create('testpass'));
            $client->setGrantType(array(
                'implicit',
            ));
            $client->setUser($user);
            $client->addScope($scope);
            $scope->addClient($client);

            $client2 = new Entity\Client();
            $client2->setClientId('oauth_test_client2');
            $client2->setSecret($bcrypt->create('testpass'));
            $client2->setGrantType(array(
                'implicit',
            ));
            $client2->setUser($user2);

            $client3 = new Entity\Client();
            $client3->setClientId('oauth_test_client3');
            $client3->setUser($user2);

            $objectManager->persist($client);
            $objectManager->persist($client2);
            $objectManager->persist($client3);

            $accessToken = new Entity\AccessToken();
            $accessToken->setClient($client);
            $accessToken->setExpires(DateTime::createFromFormat('Y-m-d', '2020-01-01'));
            $accessToken->setAccessToken('testtoken');
            $accessToken->setUser($user);

            $objectManager->persist($accessToken);


            $authorizationCode = new Entity\AuthorizationCode();
            $authorizationCode->setAuthorizationCode('testtoken');
            $authorizationCode->setClient($client);
            $authorizationCode->setRedirectUri('http://redirect');
            $authorizationCode->setExpires(DateTime::createFromFormat('Y-m-d', '2020-01-01'));
            $authorizationCode->setUser($user);

            $objectManager->persist($authorizationCode);

            $refreshToken = new Entity\RefreshToken();
            $refreshToken->setClient($client);
            $refreshToken->setExpires(DateTime::createFromFormat('Y-m-d', '2020-01-01'));
            $refreshToken->setRefreshToken('testtoken');
            $refreshToken->setUser($user);

            $objectManager->persist($refreshToken);

            $jwt = new Entity\Jwt;
            $jwt->setClient($client);
            $jwt->setSubject('test_subject');
            $jwt->setPublicKey("-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCvfF+Cw8nzsc9Twam37SYpAW3+
lRGUle/hYnd9obfBvDHKBvgb1WfGCblwjwImGL9u0rEIW2sspkwBEsGGFFBmSaqq
fvEER7Yr++VIidOUHkas3cHO1TVoERO3s0THOobw0OzghPnMJL6ayelYOESwfnqR
WfuEMSaWaW0G38QPzwIDAQAB
-----END PUBLIC KEY-----
");

            $objectManager->persist($jwt);

            $publicKey = new Entity\PublicKey();
            $publicKey->setPublicKey("-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCvfF+Cw8nzsc9Twam37SYpAW3+
lRGUle/hYnd9obfBvDHKBvgb1WfGCblwjwImGL9u0rEIW2sspkwBEsGGFFBmSaqq
fvEER7Yr++VIidOUHkas3cHO1TVoERO3s0THOobw0OzghPnMJL6ayelYOESwfnqR
WfuEMSaWaW0G38QPzwIDAQAB
-----END PUBLIC KEY-----
");

            $publicKey->setPrivateKey("-----BEGIN RSA PRIVATE KEY-----
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
");
            $publicKey->setEncryptionAlgorithm('rsa');
            $publicKey->setClient($client);

            $objectManager->persist($publicKey);
            $objectManager->flush();
        }
    }
}
