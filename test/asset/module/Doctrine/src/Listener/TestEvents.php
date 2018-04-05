<?php

namespace ZFTest\OAuth2\Doctrine\Listener;

use Zend\EventManager\Event;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Log\Logger;
use ZF\OAuth2\Doctrine\Adapter\DoctrineAdapter;

class TestEvents extends AbstractListenerAggregate
{
    protected $handlers = [];
    protected $doctrineAdapter;

    public function __construct(DoctrineAdapter $doctrineAdapter)
    {
        $this->doctrineAdapter = $doctrineAdapter;
    }

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->handlers[] = $events->attach('checkUserCredentials', [$this, 'checkUserCredentials']);
        $this->handlers[] = $events->attach('checkClientCredentials', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('isPublicClient', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('getClientDetails', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('setClientDetails', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('checkRestrictedGrantType', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('getClientScope', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('getAccessToken', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('setAccessToken', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('getAuthorizationCode', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('setAuthorizationCode', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('expireAuthorizationCode', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('checkUserCredentials', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('getUserDetails', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('getUserClaims', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('getRefreshToken', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('setRefreshToken', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('unsetRefreshToken', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('scopeExists', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('getDefaultScope', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('getClientKey', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('getJti', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('setJti', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('getPublicKey', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('getPrivateKey', [$this, 'emptyEvent']);
        $this->handlers[] = $events->attach('getEncryptionAlgorithm', [$this, 'emptyEvent']);
    }

    public function emptyEvent(Event $e)
    {
        $params = $e->getParams();
        $checkStopPropagation = reset($params);
        if ($checkStopPropagation == 'event_stop_propagation') {
            $e->stopPropagation();

            return true;
        }
    }

    public function checkUserCredentials(Event $e)
    {
        if ($e->getParams()['username'] == 'test_event_true') {
            $e->stopPropagation();

            return true;
        }

        if ($e->getParams()['username'] == 'test_event_false') {
            $e->stopPropagation();

            return false;
        }
    }
}
