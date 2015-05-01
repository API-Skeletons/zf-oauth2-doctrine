OAuth2 Doctrine Adapter for Apigility
=====================================

[![Build Status](https://travis-ci.org/TomHAnderson/zf-oauth2-doctrine.svg?branch=0.1.0)](https://travis-ci.org/TomHAnderson/zf-oauth2-doctrine)
[![Coverage Status](https://coveralls.io/repos/TomHAnderson/zf-oauth2-doctrine/badge.svg)](https://coveralls.io/r/TomHAnderson/zf-oauth2-doctrine)
[![Total Downloads](https://poser.pugx.org/zfcampus/zf-oauth2-doctrine/downloads)](https://packagist.org/packages/zfcampus/zf-oauth2-doctrine) 


About
-----

This provides the ORM entity definitions for all aspects of OAuth2 including Authorization Code, Access Tokens, Refresh Tokens, JWT & JTI, and Scopes.

![Entity Relationship Diagram](https://github.com/TomHAnderson/zf-oauth2-doctrine/blob/master/media/oauth2-doctrine-erd.png)

Installation
------------

Installation of this module uses composer. For composer documentation, please refer to [getcomposer.org](http://getcomposer.org/).

```sh
$ php composer.phar require zfcampus/zf-oauth2-doctrine "~0.2"
```

Add this module to your application's configuration:

```php
'modules' => array(
   ...
   'ZF\OAuth2\Doctrine',
),
```


Configuration
-------------

Copy ```config/oauth2.doctrine-orm.global.php.dist``` to your autoload directory and rename to ```oauth2.doctrine-orm.global.php``` You will need to edit this file with at least your User entity, which is not provided.


The User Entity
--------------

This repository supplies every entity you need to implement OAuth2 except the User entity.  The reason is so the User entity can be decoupled from the OAuth2 Doctrine repository instead to be linked dynamically at run time.  This allows, among other benefits, the ability to create an ERD without modifying the `OAuth2-orm.module.xml` module.

The User entity must implement `ZF\OAuth2\Doctrine\Entity\UserInterface`

The User entitiy for the unit test for this module is a good template to start from: [https://github.com/TomHAnderson/zf-oauth2-doctrine/blob/master/test/asset/module/Doctrine/src/Entity/User.php](https://github.com/TomHAnderson/zf-oauth2-doctrine/blob/master/test/asset/module/Doctrine/src/Entity/User.php)


Using Default Entities
----------------------

Details for creating your database with the included entities are outside the scope of this project.  Generally this is done through doctrine-orm-module with ```php public/index.php orm:schema-tool:create```

By default this module uses the entities provided but you may toggle this and use your own entites (and map them in the mapping config section) by toggling this flag:

```
'zf-oauth2-doctrine' => array(
    'storage_settings' => array(
        'enable_default_entities' => true,
```

