<?php

return array(
    'service_manager' => array(
        'invokables' => array(
            'ZF\OAuth2\Provider\UserId' => 
                'ZF\OAuth2\Provider\UserId\AuthenticationService',
        ),
        'abstract_factories' => array(
            'ZF\OAuth2\Doctrine\Factory\DoctrineMapperFactory',
        ),
        'factories' => array(
            'ZF\OAuth2\Adapter\DoctrineAdapter' => 
                'ZF\OAuth2\Doctrine\Factory\DoctrineAdapterFactory',
        ),
    ),
);
