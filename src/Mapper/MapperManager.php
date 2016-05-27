<?php

namespace ZF\OAuth2\Doctrine\Mapper;

use Zend\ServiceManager\AbstractPluginManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager as ProvidesObjectManagerTrait;
use Zend\Config\Config;
use Zend\ServiceManager\Exception;

class MapperManager implements
    ObjectManagerAwareInterface
{
    use ProvidesObjectManagerTrait;

    public function __construct($services)
    {
        $this->services = $services;
    }

    protected $services;

    protected $config;

    protected $shareByDefault = false;

    /**
     * Default set of plugins
     *
     * @var array
     */
    protected $invokableClasses = array(
        'user' => 'ZF\OAuth2\Doctrine\Mapper\User',
        'client' => 'ZF\OAuth2\Doctrine\Mapper\Client',
        'accesstoken' => 'ZF\OAuth2\Doctrine\Mapper\AccessToken',
        'refreshtoken' => 'ZF\OAuth2\Doctrine\Mapper\RefreshToken',
        'authorizationcode' => 'ZF\OAuth2\Doctrine\Mapper\AuthorizationCode',
        'jwt' => 'ZF\OAuth2\Doctrine\Mapper\Jwt',
        'jti' => 'ZF\OAuth2\Doctrine\Mapper\Jti',
        'scope' => 'ZF\OAuth2\Doctrine\Mapper\Scope',
        'publickey' => 'ZF\OAuth2\Doctrine\Mapper\PublicKey',
    );

    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function get($name, $options = array(), $usePeeringServiceManagers = true)
    {
        $instance = new $this->invokableClasses[strtolower($name)];
        $instance->setConfig($this->getConfig()->$name);
        $instance->setObjectManager($this->getObjectManager());
        $instance->setMapperManager($this);

        return $instance;
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
