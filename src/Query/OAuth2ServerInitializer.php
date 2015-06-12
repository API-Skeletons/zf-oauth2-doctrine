<?php

namespace ZF\OAuth2\Doctrine\Query;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\OAuth2\Doctrine\Query\OAuth2ServerInterface;

class OAuth2ServerInitializer implements InitializerInterface
{
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof OAuth2ServerInterface) {
            $oAuth2ServerFactory = $serviceLocator->getServiceLocator()->get('ZF\OAuth2\Service\OAuth2Server');
            $oAuth2Server = $oAuth2ServerFactory();
            $instance->setOAuth2Server($oAuth2Server);
        }
    }
}
