OAuth2 Doctrine Adapter for Apigility
=====================================

[![Build Status](https://travis-ci.org/API-Skeletons/zf-oauth2-doctrine.svg)](https://travis-ci.org/API-Skeletons/zf-oauth2-doctrine)
[![Total Downloads](https://poser.pugx.org/api-skeletons/zf-oauth2-doctrine/downloads)](https://packagist.org/packages/api-skeletons/zf-oauth2-doctrine)


About
-----

This provides a Doctrine adapter for [zfcampus/zf-mvc-auth](https://github.com/zfcampus/zf-oauth2)
and entity definitions for all aspects of OAuth2 including
Authorization Code, Access Tokens, Refresh Tokens, JWT & JTI, and Scopes.

![Entity Relationship Diagram](https://raw.githubusercontent.com/API-Skeletons/zf-oauth2-doctrine/master/media/oauth2-doctrine-erd.png)
Entity Relationship Diagram created with [Skipper](https://skipper18.com)

Installation
------------

Installation of this module uses composer. For composer documentation, please refer to [getcomposer.org](http://getcomposer.org/).

```sh
$ php composer.phar require api-skeletons/zf-oauth2-doctrine "^2.0"
```

Add this module to your application's configuration:

```php
'modules' => [
   ...
   'ZF\OAuth2\Doctrine',
],
```


The User Entity
--------------

This repository supplies every entity you need to implement OAuth2 except the User entity.
The reason is so the User entity can be decoupled from the OAuth2 Doctrine repository
instead to be linked dynamically at run time.  This allows, among other benefits, the
ability to create an ERD without modifying the `OAuth2-orm.module.xml` module.

The User entity must implement `ZF\OAuth2\Doctrine\Entity\UserInterface`

The User entity for the unit test for this module is a good template to start from:
[https://github.com/api-skeletons/zf-oauth2-doctrine/blob/master/test/asset/module/Doctrine/src/Entity/User.php](https://github.com/api-skeletons/zf-oauth2-doctrine/blob/master/test/asset/module/Doctrine/src/Entity/User.php)



Module Configuration
-------------

Copy ```config/oauth2.doctrine-orm.global.php.dist``` to your autoload directory and
rename to ```oauth2.doctrine-orm.global.php``` This config has multiple sections for multiple
adapters.  Out of the box this module provides a `default` adapter.  You will need to edit this file with
at least your User entity, which is not provided.


Configuration With zfcampus/zf-mvc-auth
------------------------------

By default this module includes a `oauth2.doctrineadapter.default` adapter.
The adapter is used to create storage from services.  Add this configuration to your `config/autoload/zf-mvc-auth-oauth2-override.global.php`

```php
    'zf-mvc-auth' => array(
        'authentication' => array(
            'adapters' => array(
                'oauth2_doctrine' => array(
                    'adapter' => 'ZF\\MvcAuth\\Authentication\\OAuth2Adapter',
                    'storage' => array(
                        'storage' => 'oauth2.doctrineadapter.default',
                        'route' => '/oauth',
                    ),
                ),
            ),
        ),
    ),
```


Configuration with zfcampus/zf-oauth2
-------------------------------------

Add the default storage adapter to the zf-oauth default storage.  `zfcampus/zf-oauth2` provides an `oauth2.local.php` file.  This repository's recommendation is to create a new `config/autoload/oauth2.global.php` file and set the following configuration as well as any OAuth2 server sesstings e.g. `allow_implicit`.

```php
'zf-oauth2' => array(
    'storage' => 'oauth2.doctrineadapter.default',
```

It is possible to use this library with a second set of entities for a second OAuth2 server in the same application using two or more APIs.  See http://blog.tomhanderson.com/2015/08/using-zf-oauth2-doctrine-for-multiple.html


Using Default Entities
----------------------

Details for creating your database with the included entities are outside the scope of this project.
Generally this is done through [doctrine/doctrine-orm-module](https://github.com/doctrine/DoctrineORMModule)
with ```php public/index.php orm:schema-tool:create```

By default this module uses the entities provided but you may use the adapter with your own entites
(and map them in the mapping config section) by toggling this flag:

```php
'zf-oauth2-doctrine' => [
    'default' => [
        'enable_default_entities' => true,
```


Customizing Many to One Mapping
-------------------------------

If you need to customize the call to mapManyToOne, which creates the dynamic joins to the User
entity from the default entites, you may add any parameters to the
`['dynamic_mapping']['default_entity']['additional_mapping_data']` element.  An example for a
User entity with a primary key of user_id which does not conform to the metadata naming strategy
is added to each entity:

```php
'refresh_token_entity' => [
    'entity' => 'ZF\OAuth2\Doctrine\Entity\RefreshToken',
    'field' => 'refreshToken',
    'additional_mapping_data' => [
        'joinColumns' => [
            [
                'name' => 'user_id',
                'referencedColumnName' => 'user_id',
            ],
        ],
    ],
],

```

Identity field on User entity
-----------------------------

By default this Doctrine adapter retrieves the user by the `username` field on the configured
User entity. If you need to use a different or multiple fields you may do so via the
'auth_identity_fields' key. For example, ZfcUser allows users to authenticate by username and/or email fields.

An example to match ZfcUser `auth_identity_fields` configuration:
```php
'zf-oauth2-doctrine' => [
    'default' => [
        'auth_identity_fields' => ['username', 'email'],
```


Validate zf-apigility-doctrine resources
----------------------------------------

To validate the OAuth2 session with Query Create Filters and Query Providers implement
`ZF\OAuth2\Doctrine\OAuth2ServerInterface` and use `ZF\OAuth2\Doctrine\OAuth2ServerTrait`.
Then call `$result = $this->validateOAuth2($scope);` in the filter function.


Events
------

Zend Framework 2 events are fully supported.  Return values are used if propagation is stopped allowing you to write your own handlers for any OAuth2 Adapter method.


Extensions
----------

Other module(s) which extend the functionality this repository provides.

* [api-skeletons/zf-oauth2-doctrine-console](https://github.com/API-Skeletons/zf-oauth2-doctrine-console) -
Console management of a default zf-oauth2-doctrine installation.

* [basz/zf-oauth2-doctrine-mutabletablenames](https://github.com/basz/zf-oauth2-doctrine-mutatetablenames) -
If you do not want to use the default table names provided with the default entities this module lets you customize them.

