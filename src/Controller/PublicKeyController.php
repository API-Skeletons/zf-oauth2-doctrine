<?php

namespace ZF\OAuth2\Doctrine\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use RuntimeException;
use ZF\OAuth2\Doctrine\Entity;

class PublicKeyController extends AbstractActionController
{
    public function createAction()
    {
        $console = $this->getServiceLocator()->get('console');
        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest){
            throw new RuntimeException('You can only use this action from a console.');
        }

        // Get the client id
        $clientId = '';
        while (!$clientId) {
            $clientId = Prompt\Line::prompt("Client ID: ", false, 255);
            $client = $objectManager->getRepository('ZF\OAuth2\Doctrine\Entity\Client')->findOneBy(array(
                'clientId' => $clientId,
            ));
            if (!$client) {
                $console->write($clientId . " not found\n", Color::RED);
                $clientId = '';
            }
            if ($client and sizeof($client->getPublicKey())) {
                $console->write($clientId . " already has a public key\n", Color::RED);
                $clientId = '';
            }
        }

        // Get public key path
        $publicKeyPath= '';
        while (!file_exists($publicKeyPath)) {
            $publicKeyPath = Prompt\Line::prompt("Public key path: ", false, 255);
        }
        $publicKey = file_get_contents($publicKeyPath);

        // Get private key path
        $privateKeyPath= '';
        while (!file_exists($privateKeyPath)) {
            $privateKeyPath = Prompt\Line::prompt("Private key path: ", false, 255);
        }
        $privateKey = file_get_contents($privateKeyPath);

        $options = array(
            0 => 'HS256',
            1 => 'HS384',
            2 => 'HS512',
            3 => 'RS256',
            4 => 'RS384',
            5 => 'RS512',
        );
        $encryptionAlgorithm = Prompt\Select::prompt("Encryption Algorithm: ", $options, false, false);

        $publicKeyEntity = new Entity\PublicKey;
        $publicKeyEntity->setClient($client);
        $publicKeyEntity->setPublicKey($publicKey);
        $publicKeyEntity->setPrivateKey($privateKey);
        $publicKeyEntity->setEncryptionAlgorithm($options[$encryptionAlgorithm]);

        $objectManager->persist($publicKeyEntity);
        $objectManager->flush();

        $console->write("Public key has been created\n", Color::GREEN);
    }
}
