<?php

return [
    'service_manager' => [
        'invokables' => [
            'ZF\OAuth2\Doctrine\Adapter\DoctrineAdapterFactory' =>
                'ZF\OAuth2\Doctrine\Adapter\DoctrineAdapterFactory',
            'ZF\OAuth2\Doctrine\Mapper\MapperManager' =>
                'ZF\OAuth2\Doctrine\Mapper\MapperManager',
        ],
    ],

    'zf-apigility-doctrine-query-create-filter' => [
        'initializers' => [
            'ZF\OAuth2\Doctrine\Query\OAuth2ServerInitializer',
        ],
    ],

    'zf-apigility-doctrine-query-provider' => [
        'initializers' => [
            'ZF\OAuth2\Doctrine\Query\OAuth2ServerInitializer',
        ],
    ],

    'oauth2-doctrineadapter-mappermanager' => [
        'invokables' => [
            'User' => 'ZF\OAuth2\Doctrine\Mapper\User',
            'Client' => 'ZF\OAuth2\Doctrine\Mapper\Client',
            'AccessToken' => 'ZF\OAuth2\Doctrine\Mapper\AccessToken',
            'RefreshToken' => 'ZF\OAuth2\Doctrine\Mapper\RefreshToken',
            'AuthorizationCode' => 'ZF\OAuth2\Doctrine\Mapper\AuthorizationCode',
            'Jwt' => 'ZF\OAuth2\Doctrine\Mapper\Jwt',
            'Jti' => 'ZF\OAuth2\Doctrine\Mapper\Jti',
            'Scope' => 'ZF\OAuth2\Doctrine\Mapper\Scope',
            'PublicKey' => 'ZF\OAuth2\Doctrine\Mapper\PublicKey',
        ],
        'shared' => [
            'User' => false,
            'Client' => false,
            'AccessToken' => false,
            'RefreshToken' => false,
            'AuthorizationCode' => false,
            'Jwt' => false,
            'Jti' => false,
            'Scope' => false,
            'PublicKey' => false,
        ],
    ],
];
