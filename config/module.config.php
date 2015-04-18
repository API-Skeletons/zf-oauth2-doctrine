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
);
