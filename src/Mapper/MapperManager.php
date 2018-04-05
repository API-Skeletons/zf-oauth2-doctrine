<?php

namespace ZF\OAuth2\Doctrine\Mapper;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager as ProvidesObjectManagerTrait;
use Zend\Config\Config;
use Zend\ServiceManager\Exception;

class MapperManager implements
    ObjectManagerAwareInterface
{
    use ProvidesObjectManagerTrait;

    /**
     * @var Config
     */
    protected $config;
    /**
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Default set of plugins
     *
     * @var array
     */
    protected $invokableClasses = [
        'user' => 'ZF\OAuth2\Doctrine\Mapper\User',
        'client' => 'ZF\OAuth2\Doctrine\Mapper\Client',
        'accesstoken' => 'ZF\OAuth2\Doctrine\Mapper\AccessToken',
        'refreshtoken' => 'ZF\OAuth2\Doctrine\Mapper\RefreshToken',
        'authorizationcode' => 'ZF\OAuth2\Doctrine\Mapper\AuthorizationCode',
        'jwt' => 'ZF\OAuth2\Doctrine\Mapper\Jwt',
        'jti' => 'ZF\OAuth2\Doctrine\Mapper\Jti',
        'scope' => 'ZF\OAuth2\Doctrine\Mapper\Scope',
        'publickey' => 'ZF\OAuth2\Doctrine\Mapper\PublicKey',
    ];

    /**
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $name
     * @param array $options
     * @param bool $usePeeringServiceManagers
     * @return AbstractMapper
     */
    public function get($name, $options = [], $usePeeringServiceManagers = true)
    {
        /** @var AbstractMapper $instance */
        $instance = new $this->invokableClasses[strtolower($name)];
        $instance->setConfig($this->getConfig()->$name);
        $instance->setObjectManager($this->getObjectManager());
        $instance->setMapperManager($this);

        return $instance;
    }

    public function getAll()
    {
        $resources = [];
        foreach ($this->getConfig() as $resourceName => $config) {
            $resources[] = $this->get($resourceName);
        }

        return $resources;
    }

    /**
     * @param mixed $command
     *
     * @return void
     * @throws Exception\InvalidServiceException
     */
    public function validatePlugin($command)
    {
        if ($command instanceof AbstractMapper) {
            // we're okay
            return;
        }

        // @codeCoverageIgnoreStart
        throw new Exception\InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement ZF\OAuth2\Doctrine\Mapper\AbstractMapper',
            (is_object($command) ? get_class($command) : gettype($command))
        ));
        // @codeCoverageIgnoreEnd
    }
}
