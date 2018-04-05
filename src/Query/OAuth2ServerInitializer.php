<?php

namespace ZF\OAuth2\Doctrine\Query;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\OAuth2\Doctrine\Query\OAuth2ServerInterface;

class OAuth2ServerInitializer implements InitializerInterface
{
    /**
     * @param ContainerInterface $container
     * @param mixed $instance
     * @return \ZF\OAuth2\Doctrine\Query\OAuth2ServerInterface
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        return $this->internalInitialize($container, $instance);
    }

    /**
     * @param mixed $instance
     * @param ServiceLocatorInterface $serviceLocator
     * @return \ZF\OAuth2\Doctrine\Query\OAuth2ServerInterface
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator() ?: $serviceLocator;
        }

        return $this->internalInitialize($serviceLocator, $instance);
    }

    /**
     * @param ContainerInterface | ServiceLocatorInterface $container
     * @param mixed $instance
     * @return OAuth2ServerInterface
     * @throws InvalidArgumentException
     */
    protected function internalInitialize($container, $instance)
    {
        if (! $instance instanceof OAuth2ServerInterface) {
            return $instance;
        }

        if (! $container instanceof ContainerInterface && ! $container instanceof ServiceLocatorInterface) {
            throw new InvalidArgumentException('Invalid container');
        }

        $oAuth2ServerFactory = $container->get('ZF\OAuth2\Service\OAuth2Server');
        $oAuth2Server = $oAuth2ServerFactory();
        $instance->setOAuth2Server($oAuth2Server);

        return $instance;
    }
}
