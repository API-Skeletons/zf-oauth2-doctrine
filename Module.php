<?php

namespace ZF\OAuth2\Doctrine;

use Zend\ModuleManager\ModuleManager;
use ZF\OAuth2\Doctrine\EventListener\DynamicMappingSubscriber;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;

class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ConsoleUsageProviderInterface
{
    public function getConsoleUsage(Console $console)
    {
        return array(
            'oauth2:jwt:create' => 'Create a JWT for a given client',
            'oauth2:public-key:create' => 'Create the PublicKey data for a given client',
        );
    }

    public function onBootstrap($e)
    {
        $app     = $e->getParam('application');
        $sm      = $app->getServiceManager();
        $config = $sm->get('Config');


        // Enable default entities
        if (isset($config['zf-oauth2-doctrine']['storage_settings']['enable_default_entities'])
            && $config['zf-oauth2-doctrine']['storage_settings']['enable_default_entities']) {
            $chain = $sm->get($config['zf-oauth2-doctrine']['storage_settings']['driver']);
            $chain->addDriver(new XmlDriver(__DIR__ . '/config/orm'), 'ZF\OAuth2\Doctrine\Entity');
        }

        // Enable default documents
        // Enable default documents
        if (isset($config['zf-oauth2-doctrine']['storage_settings']['enable_default_documents'])
            && $config['zf-oauth2-doctrine']['storage_settings']['enable_default_documents']) {
            $driver = $config['zf-oauth2-doctrine']['storage_settings']['default_documents_driver'];
            $chain = $sm->get($config['zf-oauth2-doctrine']['storage_settings']['driver']);
            $chain->addDriver(new $driver(__DIR__ . '/config/odm'), 'ZF\OAuth2\Doctrine\Document');
        }

        if (isset($config['zf-oauth2-doctrine']['storage_settings']['dynamic_mapping'])
            && $config['zf-oauth2-doctrine']['storage_settings']['dynamic_mapping']) {

            $userClientSubscriber = new DynamicMappingSubscriber(
                $config['zf-oauth2-doctrine']['storage_settings']['dynamic_mapping'],
                $config['zf-oauth2-doctrine']['storage_settings']['mapping']
            );
            $eventManager = $sm->get($config['zf-oauth2-doctrine']['storage_settings']['event_manager']);
            $eventManager->addEventSubscriber($userClientSubscriber);
        }
    }

    /**
     * Retrieve autoloader configuration
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array('Zend\Loader\StandardAutoloader' => array('namespaces' => array(
            __NAMESPACE__ => __DIR__ . '/src/',
        )));
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
}
