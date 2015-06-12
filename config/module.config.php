<?php

return array(
    'service_manager' => array(
        'abstract_factories' => array(
            'ZF\OAuth2\Doctrine\Factory\DoctrineMapperFactory',
        ),
        'factories' => array(
            'ZF\OAuth2\Doctrine\Adapter\DoctrineAdapter' =>
                'ZF\OAuth2\Doctrine\Factory\DoctrineAdapterFactory',
        ),
    ),
    'zf-apigility-doctrine-query-create-filter' => array(
        'initializers' => array(
            'ZF\OAuth2\Doctrine\Query\OAuth2ServerInitializer',
        ),
    ),
    'zf-apigility-doctrine-query-provider' => array(
        'initializers' => array(
            'ZF\OAuth2\Doctrine\Query\OAuth2ServerInitializer',
        ),
    ),
);
