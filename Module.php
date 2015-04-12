<?php

namespace ZF\OAuth2\Doctrine;

use Zend\ModuleManager\ModuleManager;
use ZF\OAuth2\EventListener\UserClientSubscriber;
use Doctrine\ORM\Mapping\Driver\XmlDriver;

class Module
{
    public function onBootstrap($e)
    {
        $app     = $e->getParam('application');
        $sm      = $app->getServiceManager();
        $config = $sm->get('Config');

        // Enable default entities
        if (isset($config['zf-oauth2-doctrine']['storage_settings']['default_entities_enable'])
            && $config['zf-oauth2-doctrine']['storage_settings']['default_entities_enable']) {
            $chain = $sm->get($config['zf-oauth2-doctrine']['storage_settings']['driver']);
            $chain->addDriver(new XmlDriver(__DIR__ . '/config/orm'), 'ZF\OAuth2\Doctrine\Entity');
        }

        // Enable default documents
        if (isset($config['zf-oauth2-doctrine']['storage_settings']['default_documents_enable'])
            && $config['zf-oauth2-doctrine']['storage_settings']['default_documents_enable']) {
            $driver = $config['zf-oauth2-doctrine']['storage_settings']['default_documents_driver'];
            $chain = $sm->get($config['zf-oauth2-doctrine']['storage_settings']['driver']);
            $chain->addDriver(new $driver(__DIR__ . '/config/odm'), 'ZF\OAuth2\Doctrine\Document');
        }

        if (isset($config['zf-oauth2-doctrine']['storage_settings']['dynamic_mapping'])
            && $config['zf-oauth2-doctrine']['storage_settings']['dynamic_mapping']) {

            $userClientSubscriber = new UserClientSubscriber($config['zf-oauth2-doctrine']['storage_settings']['dynamic_mapping']);

            $eventManager = $sm->get($config['zf-oauth2-doctrine']['storage_settings']['event_manager']);

            // $em is an instance of the Event Manager
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
