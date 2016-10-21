<?php

namespace ZF\OAuth2\Doctrine;

use Interop\Container\ContainerInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Config\Config;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\OAuth2\Doctrine\Adapter\DoctrineAdapterFactory;

class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface
{
    /**
     * Retrieve autoloader configuration
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return ['Zend\Loader\StandardAutoloader' => ['namespaces' => [
            __NAMESPACE__ => __DIR__,
        ]]];
    }

    /**
     * Retrieve module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {
        /** @var ServiceLocatorInterface $serviceManager */
        $serviceManager = $e->getParam('application')->getServiceManager();
        $serviceManager->get('oauth2.doctrineadapter.default')->bootstrap($e);
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                'oauth2.doctrineadapter.default' => function ($serviceManager) {
                    /** @var ServiceLocatorInterface|ContainerInterface $serviceManager */
                    $globalConfig = $serviceManager->get('Config');
                    $config = new Config($globalConfig['zf-oauth2-doctrine']['default']);
                    /** @var DoctrineAdapterFactory $factory */
                    $factory = $serviceManager->get(DoctrineAdapterFactory::class);
                    $factory->setConfig($config);
                    return $factory->createService($serviceManager);
                }
            ],
        ];
    }
}
