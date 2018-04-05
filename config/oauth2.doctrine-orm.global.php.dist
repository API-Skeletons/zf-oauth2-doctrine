<?php

/**
 * The user entity is always stored in another namespace than ZF\OAuth2
 */
$userEntity = 'ZFTest\OAuth2\Doctrine\Entity\User';

return [
    'zf-oauth2-doctrine' => [
        'default' => [
            'object_manager' => 'doctrine.entitymanager.orm_default',
            'event_manager' => 'doctrine.eventmanager.orm_default',
            'driver' => 'doctrine.driver.orm_default',
            'enable_default_entities' => true,
            'bcrypt_cost' => 10, # match php default
            'auth_identity_fields' => ['username'],
            // Dynamically map the user_entity to the client_entity
            'dynamic_mapping' => [
                'user_entity' => [
                    'entity' => $userEntity,
                    'field' => 'user',
                ],
                'client_entity' => [
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                    'field' => 'client',
                    'additional_mapping_data' => [
                        'joinColumns' => [
                            [
                                'onDelete' => 'CASCADE'
                            ],
                        ],
                    ],
                ],
                'access_token_entity' => [
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\AccessToken',
                    'field' => 'accessToken',
                    'additional_mapping_data' => [
                        'joinColumns' => [
                            [
                                'onDelete' => 'CASCADE'
                            ],
                        ],
                    ],
                ],
                'authorization_code_entity' => [
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\AuthorizationCode',
                    'field' => 'authorizationCode',
                    'additional_mapping_data' => [
                        'joinColumns' => [
                            [
                                'onDelete' => 'CASCADE'
                            ],
                        ],
                    ],
                ],
                'refresh_token_entity' => [
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\RefreshToken',
                    'field' => 'refreshToken',
                    'additional_mapping_data' => [
                        'joinColumns' => [
                            [
                                'onDelete' => 'CASCADE'
                            ],
                        ],
                    ],
                ],
                'scope_entity' => [
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\Scope',
                    'field' => 'scope',
                ],
            ],
            'mapping' => [
                'User' => [
                    'entity' => $userEntity,
                    'mapping' => [
                        'user_id' => [
                            'type' => 'field',
                            'name' => 'id',
                            'datatype' => 'bigint',
                        ],
                        'username' => [
                            'type' => 'field',
                            'name' => 'username',
                            'datatype' => 'string',
                        ],
                        'password' => [
                            'type' => 'field',
                            'name' => 'password',
                            'datatype' => 'string',
                        ],
                    ],
                ],

                'Client' => [
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                    'mapping' => [
                        'client_id' => [
                            'type' => 'field',
                            'name' => 'clientId',
                            'datatype' => 'string',
                        ],
                        'client_secret' => [
                            'type' => 'field',
                            'name' => 'secret',
                            'datatype' => 'string',
                        ],
                        'redirect_uri' => [
                            'type' => 'field',
                            'name' => 'redirectUri',
                            'datatype' => 'text',
                        ],
                        'grant_types' => [
                            'type' => 'field',
                            'name' => 'grantType',
                            'datatype' => 'array',
                        ],
                        'scope' => [
                            'type' => 'collection',
                            'name' => 'scope',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Scope',
                            'mapper' => 'Scope',
                        ],
                        'user_id' => [
                            'type' => 'relation',
                            'name' => 'user',
                            'entity_field_name' => 'id',
                            'entity' => $userEntity,
                            'datatype' => 'bigint',
                            'allow_null' => true,
                        ],
                    ],
                ],

                'AccessToken' => [
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\AccessToken',
                    'mapping' => [
                        'access_token' => [
                            'type' => 'field',
                            'name' => 'accessToken',
                            'datatype' => 'text',
                        ],
                        'expires' => [
                            'type' => 'field',
                            'name' => 'expires',
                            'datatype' => 'datetime',
                        ],
                        'scope' => [
                            'type' => 'collection',
                            'name' => 'scope',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Scope',
                            'mapper' => 'Scope',
                        ],
                        'client_id' => [
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'clientId',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                            'datatype' => 'bigint',
                        ],
                        'user_id' => [
                            'type' => 'relation',
                            'name' => 'user',
                            'entity_field_name' => 'id',
                            'entity' => $userEntity,
                            'datatype' => 'bigint',
                            'allow_null' => true,
                        ],
                    ],
                ],

                'RefreshToken' => [
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\RefreshToken',
                    'mapping' => [
                        'refresh_token' => [
                            'type' => 'field',
                            'name' => 'refreshToken',
                            'datatype' => 'string',
                        ],
                        'expires' => [
                            'type' => 'field',
                            'name' => 'expires',
                            'datatype' => 'datetime',
                        ],
                        'scope' => [
                            'type' => 'collection',
                            'name' => 'scope',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Scope',
                            'mapper' => 'Scope',
                        ],
                        'client_id' => [
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'clientId',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                            'datatype' => 'bigint',
                        ],
                        'user_id' => [
                            'type' => 'relation',
                            'name' => 'user',
                            'entity_field_name' => 'id',
                            'entity' => $userEntity,
                            'datatype' => 'bigint',
                            'allow_null' => true,
                        ],
                    ],
                ],

                'AuthorizationCode' => [
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\AuthorizationCode',
                    'mapping' => [
                        'authorization_code' => [
                            'type' => 'field',
                            'name' => 'authorizationCode',
                            'datatype' => 'string',
                        ],
                        'redirect_uri' => [
                            'type' => 'field',
                            'name' => 'redirectUri',
                            'datatype' => 'text',
                        ],
                        'expires' => [
                            'type' => 'field',
                            'name' => 'expires',
                            'datatype' => 'datetime',
                        ],
                        'scope' => [
                            'type' => 'collection',
                            'name' => 'scope',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Scope',
                            'mapper' => 'Scope',
                        ],
                        'id_token' => [
                            'type' => 'field',
                            'name' => 'idToken',
                            'datatype' => 'text',
                        ],
                        'client_id' => [
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'clientId',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                            'datatype' => 'bigint',
                        ],
                        'user_id' => [
                            'type' => 'relation',
                            'name' => 'user',
                            'entity_field_name' => 'id',
                            'entity' => $userEntity,
                            'datatype' => 'bigint',
                            'allow_null' => true,
                        ],
                    ],
                ],

                'Jwt' => [
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\Jwt',
                    'mapping' => [
                        'subject' => [
                            'type' => 'field',
                            'name' => 'subject',
                            'datatype' => 'string',
                        ],
                        'public_key' => [
                            'type' => 'field',
                            'name' => 'publicKey',
                            'datatype' => 'text',
                        ],
                        'client_id' => [
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'clientId',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                            'datatype' => 'bigint',
                        ],
                    ],
                ],

                'Jti' => [
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\Jti',
                    'mapping' => [
                        'subject' => [
                            'type' => 'field',
                            'name' => 'subject',
                            'datatype' => 'string',
                        ],
                        'audience' => [
                            'type' => 'field',
                            'name' => 'audience',
                            'datatype' => 'string',
                        ],
                        'expires' => [
                            'type' => 'field',
                            'name' => 'expires',
                            'datatype' => 'datetime',
                        ],
                        'jti' => [
                            'type' => 'field',
                            'name' => 'jti',
                            'datatype' => 'text',
                        ],
                        'client_id' => [
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'clientId',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                            'datatype' => 'bigint',
                        ],
                    ],
                ],

                'Scope' => [
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\Scope',
                    'mapping' => [
                        'scope' => [
                            'type' => 'field',
                            'name' => 'scope',
                            'datatype' => 'text',
                        ],
                        'is_default' => [
                            'type' => 'field',
                            'name' => 'isDefault',
                            'datatype' => 'boolean',
                        ],
                        'client_id' => [
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'clientId',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                            'datatype' => 'bigint',
                        ],
                    ],
                ],

                'PublicKey' => [
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\PublicKey',
                    'mapping' => [
                        'public_key' => [
                            'type' => 'field',
                            'name' => 'publicKey',
                            'datatype' => 'text',
                        ],
                        'private_key' => [
                            'type' => 'field',
                            'name' => 'privateKey',
                            'datatype' => 'text',
                        ],
                        'encryption_algorithm' => [
                            'type' => 'field',
                            'name' => 'encryptionAlgorithm',
                            'datatype' => 'string',
                        ],
                        'client_id' => [
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'clientId',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                            'datatype' => 'bigint',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
