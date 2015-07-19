<?php

namespace ZF\OAuth2\Doctrine\Mapper;

use Zend\ServiceManager\AbstractPluginManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager as ProvidesObjectManagerTrait;
use Zend\Config\Config;
use Zend\ServiceManager\Exception;

class MapperManager extends AbstractPluginManager implements
    ObjectManagerAwareInterface
{
    use ProvidesObjectManagerTrait;

    protected $config;

    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function get($resourceName) {
        $resource = parent::get($resourceName);
        $resource->setConfig($this->getConfig()->$resourceName);
        $resource->setObjectManager($this->getObjectManager());

        return $resource;
    }

    public function getAll()
    {
        $resources = array();
        foreach ($this->getConfig() as $resourceName => $config) {
            $resources[] = $this->get($resourceName);
        }

        return $resources;
    }

    /**
     * @param mixed $command
     *
     * @return void
     * @throws Exception\RuntimeException
     */
    public function validatePlugin($command)
    {
        if ($command instanceof AbstractMapper) {
            // we're okay
            return;
        }

        // @codeCoverageIgnoreStart
        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement ZF\OAuth2\Doctrine\Mapper\AbstractMapper',
            (is_object($command) ? get_class($command) : gettype($command))
        ));
        // @codeCoverageIgnoreEnd
    }
}