<?php

/**
 * The user entity is always stored in another namespace than ZF\OAuth2
 */
$userEntity = 'ZFTest\OAuth2\Doctrine\Entity\User';

return array(
    'zf-oauth2-doctrine' => array(
        'default' => array(
            'object_manager' => 'doctrine.entitymanager.orm_default',
            'event_manager' => 'doctrine.eventmanager.orm_default',
            'driver' => 'doctrine.driver.orm_default',
            'auth_identity_fields' => array('username'),
            'enable_default_entities' => true,
            'bcrypt_cost' => 14, # match zfcuser
            // Dynamically map the user_entity to the client_entity
            'dynamic_mapping' => array(
                'user_entity' => array(
                    'entity' => $userEntity,
                    'field' => 'user',
                ),
                'client_entity' => array(
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                    'field' => 'client',
                    'additional_mapping_data' => array(
                        'joinColumns' => array(
                            array(
                                'onDelete' => 'CASCADE'
                            ),
                        ),
                    ),
                ),
                'access_token_entity' => array(
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\AccessToken',
                    'field' => 'accessToken',
                    'additional_mapping_data' => array(
                        'joinColumns' => array(
                            array(
                                'onDelete' => 'CASCADE'
                            ),
                        ),
                    ),
                ),
                'authorization_code_entity' => array(
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\AuthorizationCode',
                    'field' => 'authorizationCode',
                    'additional_mapping_data' => array(
                        'joinColumns' => array(
                            array(
                                'onDelete' => 'CASCADE'
                            ),
                        ),
                    ),
                ),
                'refresh_token_entity' => array(
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\RefreshToken',
                    'field' => 'refreshToken',
                    'additional_mapping_data' => array(
                        'joinColumns' => array(
                            array(
                                'onDelete' => 'CASCADE'
                            ),
                        ),
                    ),
                ),
            ),
            'mapping' => array(
                'User' => array(
                    'entity' => $userEntity,
                    'mapping' => array(
                        'user_id' => array(
                            'type' => 'field',
                            'name' => 'id',
                            'datatype' => 'integer',
                        ),
                        'username' => array(
                            'type' => 'field',
                            'name' => 'username',
                            'datatype' => 'string',
                        ),
                        'password' => array(
                            'type' => 'field',
                            'name' => 'password',
                            'datatype' => 'string',
                        ),
                    ),
                ),

                'Client' => array(
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                    'mapping' => array(
                        'client_id' => array(
                            'type' => 'field',
                            'name' => 'clientId',
                            'datatype' => 'integer',
                        ),
                        'client_secret' => array(
                            'type' => 'field',
                            'name' => 'secret',
                            'datatype' => 'string',
                        ),
                        'redirect_uri' => array(
                            'type' => 'field',
                            'name' => 'redirectUri',
                            'datatype' => 'text',
                        ),
                        'grant_types' => array(
                            'type' => 'field',
                            'name' => 'grantType',
                            'datatype' => 'array',
                        ),
                        'scope' => array(
                            'type' => 'collection',
                            'name' => 'scope',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Scope',
                            'mapper' => 'Scope',
                        ),
                        'user_id' => array(
                            'type' => 'relation',
                            'name' => 'user',
                            'entity_field_name' => 'id',
                            'entity' => $userEntity,
                            'datatype' => 'integer',
                            'allow_null' => true,
                        ),
                    ),
                ),

                'AccessToken' => array(
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\AccessToken',
                    'mapping' => array(
                        'access_token' => array(
                            'type' => 'field',
                            'name' => 'accessToken',
                            'datatype' => 'text',
                        ),
                        'expires' => array(
                            'type' => 'field',
                            'name' => 'expires',
                            'datatype' => 'datetime',
                        ),
                        'scope' => array(
                            'type' => 'collection',
                            'name' => 'scope',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Scope',
                            'mapper' => 'Scope',
                        ),
                        'client_id' => array(
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'clientId',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                            'datatype' => 'integer',
                        ),
                        'user_id' => array(
                            'type' => 'relation',
                            'name' => 'user',
                            'entity_field_name' => 'id',
                            'entity' => $userEntity,
                            'datatype' => 'integer',
                            'allow_null' => true,
                        ),
                    ),
                ),

                'RefreshToken' => array(
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\RefreshToken',
                    'mapping' => array(
                        'refresh_token' => array(
                            'type' => 'field',
                            'name' => 'refreshToken',
                            'datatype' => 'string',
                        ),
                        'expires' => array(
                            'type' => 'field',
                            'name' => 'expires',
                            'datatype' => 'datetime',
                        ),
                        'scope' => array(
                            'type' => 'collection',
                            'name' => 'scope',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Scope',
                            'mapper' => 'Scope',
                        ),
                        'client_id' => array(
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'clientId',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                            'datatype' => 'integer',
                        ),
                        'user_id' => array(
                            'type' => 'relation',
                            'name' => 'user',
                            'entity_field_name' => 'id',
                            'entity' => $userEntity,
                            'datatype' => 'integer',
                            'allow_null' => true,
                        ),
                    ),
                ),

                'AuthorizationCode' => array(
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\AuthorizationCode',
                    'mapping' => array(
                        'authorization_code' => array(
                            'type' => 'field',
                            'name' => 'authorizationCode',
                            'datatype' => 'string',
                        ),
                        'redirect_uri' => array(
                            'type' => 'field',
                            'name' => 'redirectUri',
                            'datatype' => 'text',
                        ),
                        'expires' => array(
                            'type' => 'field',
                            'name' => 'expires',
                            'datatype' => 'datetime',
                        ),
                        'scope' => array(
                            'type' => 'collection',
                            'name' => 'scope',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Scope',
                            'mapper' => 'Scope',
                        ),
                        'id_token' => array(
                            'type' => 'field',
                            'name' => 'idToken',
                            'datatype' => 'text',
                        ),
                        'client_id' => array(
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'clientId',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                            'datatype' => 'integer',
                        ),
                        'user_id' => array(
                            'type' => 'relation',
                            'name' => 'user',
                            'entity_field_name' => 'id',
                            'entity' => $userEntity,
                            'datatype' => 'integer',
                            'allow_null' => true,
                        ),
                    ),
                ),

                'Jwt' => array(
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\Jwt',
                    'mapping' => array(
                        'subject' => array(
                            'type' => 'field',
                            'name' => 'subject',
                            'datatype' => 'string',
                        ),
                        'public_key' => array(
                            'type' => 'field',
                            'name' => 'publicKey',
                            'datatype' => 'text',
                        ),
                        'client_id' => array(
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'clientId',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                            'datatype' => 'integer',
                        ),
                    ),
                ),

                'Jti' => array(
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\Jti',
                    'mapping' => array(
                        'subject' => array(
                            'type' => 'field',
                            'name' => 'subject',
                            'datatype' => 'string',
                        ),
                        'audience' => array(
                            'type' => 'field',
                            'name' => 'audience',
                            'datatype' => 'string',
                        ),
                        'expires' => array(
                            'type' => 'field',
                            'name' => 'expires',
                            'datatype' => 'datetime',
                        ),
                        'jti' => array(
                            'type' => 'field',
                            'name' => 'jti',
                            'datatype' => 'text',
                        ),
                        'client_id' => array(
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'clientId',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                            'datatype' => 'integer',
                        ),
                    ),
                ),

                'Scope' => array(
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\Scope',
                    'mapping' => array(
                        'scope' => array(
                            'type' => 'field',
                            'name' => 'scope',
                            'datatype' => 'text',
                        ),
                        'is_default' => array(
                            'type' => 'field',
                            'name' => 'isDefault',
                            'datatype' => 'boolean',
                        ),
                        'client_id' => array(
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'clientId',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                            'datatype' => 'integer',
                        ),
                    ),
                ),

                'PublicKey' => array(
                    'entity' => 'ZF\OAuth2\Doctrine\Entity\PublicKey',
                    'mapping' => array(
                        'public_key' => array(
                            'type' => 'field',
                            'name' => 'publicKey',
                            'datatype' => 'text',
                        ),
                        'private_key' => array(
                            'type' => 'field',
                            'name' => 'privateKey',
                            'datatype' => 'text',
                        ),
                        'encryption_algorithm' => array(
                            'type' => 'field',
                            'name' => 'encryptionAlgorithm',
                            'datatype' => 'string',
                        ),
                        'client_id' => array(
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'clientId',
                            'entity' => 'ZF\OAuth2\Doctrine\Entity\Client',
                            'datatype' => 'integer',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
