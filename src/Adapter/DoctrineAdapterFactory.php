<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Doctrine\Adapter;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\Config\Config;
use ZF\OAuth2\Doctrine\Delegator\DelegatorInterface;

class DoctrineAdapterFactory implements FactoryInterface
{
    private $config;

    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @param ServiceLocatorInterface $services
     * @throws ZF\OAuth2\Controller\Exception\RuntimeException
     * @return ZF\OAuth2\Doctrine\Adapter\DoctrineAdapter
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $adapter = $services->get('ZF\OAuth2\Doctrine\Adapter\DoctrineAdapter');

        $adapter->setConfig($this->config);
        $adapter->setObjectManager($this->loadObjectManager($services, $this->config->object_manager));
        $adapter->setMapperManager($this->loadMapperManager($services, $this->config));

        return $adapter;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param Zend\Config\Config $this->config
     *
     * @return ObjectManager
     * @throws \Zend\ServiceManager\Exception\ServiceNotCreatedException
     */
    protected function loadObjectManager(ServiceLocatorInterface $services, $objectManagerAlias)
    {
        if ($services->has($objectManagerAlias)) {
            $objectManager = $services->get($objectManagerAlias);
        } else {
            // @codeCoverageIgnoreStart
            throw new ServiceNotCreatedException('The object_manager ' . $objectManagerAlias . ' could not be found.');
        }
        // @codeCoverageIgnoreEnd

        return $objectManager;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param string
     *
     * @return ObjectManager
     * @throws \Zend\ServiceManager\Exception\ServiceNotCreatedException
     */
    protected function loadMapperManager(ServiceLocatorInterface $services, Config $config)
    {
        if ($services->has('ZF\OAuth2\Doctrine\Mapper\MapperManager')) {
            $mapperManager = new \ZF\OAuth2\Doctrine\Mapper\MapperManager($services);
        } else {
            // @codeCoverageIgnoreStart
            throw new ServiceNotCreatedException('The MapperManager '
                . 'ZF\OAuth2\Doctrine\Mapper\MapperManager'
                . ' could not be found.');
        }
        // @codeCoverageIgnoreEnd

        $mapperManager->setConfig($config->mapping);
        $mapperManager->setObjectManager($this->loadObjectManager($services, $config->object_manager));

        return $mapperManager;
    }
}
