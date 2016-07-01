<?php

namespace ZFTest\OAuth2\Doctrine\Listener;

use Zend\EventManager\Event;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Log\Logger;
use ZF\OAuth2\Doctrine\Adapter\DoctrineAdapter;

class TestEvents extends AbstractListenerAggregate
{
    protected $handlers = array();
    protected $doctrineAdapter;

    public function __construct(DoctrineAdapter $doctrineAdapter)
    {
        $this->doctrineAdapter = $doctrineAdapter;
    }

    public function attach(EventManagerInterface $events)
    {
        $this->handlers[] = $events->attach('checkUserCredentials', array($this, 'checkUserCredentials'));
        $this->handlers[] = $events->attach('checkClientCredentials', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('isPublicClient', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('getClientDetails', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('setClientDetails', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('checkRestrictedGrantType', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('getClientScope', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('getAccessToken', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('setAccessToken', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('getAuthorizationCode', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('setAuthorizationCode', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('expireAuthorizationCode', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('checkUserCredentials', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('getUserDetails', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('getUserClaims', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('getRefreshToken', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('setRefreshToken', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('unsetRefreshToken', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('scopeExists', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('getDefaultScope', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('getClientKey', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('getJti', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('setJti', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('getPublicKey', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('getPrivateKey', array($this, 'emptyEvent'));
        $this->handlers[] = $events->attach('getEncryptionAlgorithm', array($this, 'emptyEvent'));
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
