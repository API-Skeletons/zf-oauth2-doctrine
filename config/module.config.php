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

    'controllers' => array(
        'invokables' => array(
            'ZF\OAuth2\Doctrine\Controller\Jwt' => 'ZF\OAuth2\Doctrine\Controller\JwtController',
            'ZF\OAuth2\Doctrine\Controller\PublicKey' => 'ZF\OAuth2\Doctrine\Controller\PublicKeyController',
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'create-jwt' => array(
                    'options' => array(
                        'route'    => 'oauth2:jwt:create',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Controller\Jwt',
                            'action'     => 'create'
                        ),
                    ),
                ),
                'create-public-key' => array(
                    'options' => array(
                        'route'    => 'oauth2:public-key:create',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Controller\PublicKey',
                            'action'     => 'create'
                        ),
                    ),
                ),
            ),
        ),
    ),
);
