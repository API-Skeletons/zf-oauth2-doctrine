<?php

return array(
    'zf-mvc-auth' => array(
        'authentication' => array(
            'map' => array(
                'Api\\V1' => 'oauth2_doctrine',
            ),
        ),
    ),

    'zf-mvc-auth' => array(
        'authentication' => array(
            'adapters' => array(
                'oauth2_doctrine' => array(
                    'adapter' => 'ZF\\MvcAuth\\Authentication\\OAuth2Adapter',
                    'storage' => array(
                        'storage' => 'oauth2.doctrineadapter.default',
                    ),
                ),
            ),
        ),
    ),

    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOSqlite\Driver',
                'params' => array(
                    'memory' => 'true',
                ),
            ),
        ),
    ),
);
