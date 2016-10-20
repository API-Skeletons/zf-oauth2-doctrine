<?php

return [
    'service_manager' => [
        'invokables' => [
            'ZFTest\OAuth2\Doctrine\Listener\TestEvents'
            => 'ZFTest\OAuth2\Doctrine\Listener\TestEvents',
        ],
    ],
    'doctrine' => [
        'driver' => [
            'orm_driver' => [
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\XmlDriver',
                'paths' => [
                    0 => __DIR__ . '/orm',
                ],
            ],
            'orm_default' => [
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\DriverChain',
                'drivers' => [
                    'ZFTest\\OAuth2\\Doctrine\\Entity' => 'orm_driver',
                ],
            ],
        ],
    ],
];
