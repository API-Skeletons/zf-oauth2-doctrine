<?php

namespace ZF\OAuth2\Doctrine;

use Zend\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    /**
     * Return general purpose zf-oauth2-doctrine configuration
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $provider->getDependencyConfig(),
            'zf-apigility-doctrine-query-create-filter' => $provider->getQueryCreateFilterConfig(),
            'zf-apigility-doctrine-query-provider' => $provider->getQueryProviderConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'factories' => [
                Adapter\DoctrineAdapterFactory::class => InvokableFactory::class,
                Mapper\MapperManager::class => InvokableFactory::class,
                Adapter\DoctrineAdapter::class => InvokableFactory::class,
            ],
            'shared' => [
                Adapter\DoctrineAdapterFactory::class => false,
                Mapper\MapperManager::class => false,
            ],
        ];
    }

    /**
     * Return QueryCreateFilter configuration.
     *
     * @return array
     */
    public function getQueryCreateFilterConfig()
    {
        return [
            'initializers' => [
                Query\OAuth2ServerInitializer::class,
            ],
        ];
    }

    /**
     * Return QueryProvider configuration.
     *
     * @return array
     */
    public function getQueryProviderConfig()
    {
        return [
            'initializers' => [
                Query\OAuth2ServerInitializer::class,
            ],
        ];
    }
}