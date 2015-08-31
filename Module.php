<?php

namespace ZF\OAuth2\Doctrine;

use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Config\Config;
use Zend\Mvc\MvcEvent;

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
            __NAMESPACE__ => __DIR__ . '/src/',
        ]]];
    }

    /**
     * Retrieve module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e) {
        $serviceManager = $e->getParam('application')->getServiceManager()
            ->get('oauth2.doctrineadapter.default')->bootstrap($e);
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                'oauth2.doctrineadapter.default' => function($serviceManager) {

                    $globalConfig = $serviceManager->get('Config');
                    $config = new Config($globalConfig['zf-oauth2-doctrine']['default']);
                    $factory = $serviceManager->get('ZF\OAuth2\Doctrine\Adapter\DoctrineAdapterFactory');
                    $factory->setConfig($config);
                    $adapter = $factory->createService($serviceManager);

                    return $adapter;
                }
            ],
        ];
    }
}
