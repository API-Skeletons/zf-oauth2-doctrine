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

class JwtController extends AbstractActionController
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
                $console->write('Client ID ' . $clientId . ' not found', Color::RED);
                $clientId = '';
            }
        }

        // Get the subject
        $subject = Prompt\Line::prompt("The subject, usually a user_id.  Not required: ", true, 255);

        // Get public key path
        $publicKeyPath= '';
        while (!file_exists($publicKeyPath)) {
            $publicKeyPath = Prompt\Line::prompt("Public key path: ", false, 255);
        }
        $publicKey = file_get_contents($publicKeyPath);

        $jwt = new Entity\Jwt;
        $jwt->setClient($client);
        $jwt->setSubject($subject);
        $jwt->setPublicKey($publicKey);

        $objectManager->persist($jwt);
        $objectManager->flush();

        $console->write("JWT has been created\n", Color::GREEN);
    }
}
