[![Build Status](https://travis-ci.org/TomHAnderson/zf-oauth2-doctrine.svg?branch=0.1.0)](https://travis-ci.org/TomHAnderson/zf-oauth2-doctrine)
[![Latest Stable Version](https://poser.pugx.org/zfcampus/zf-oauth2-doctrine/v/stable.svg)](https://packagist.org/packages/zfcampus/zf-oauth2-doctrine) [![Total Downloads](https://poser.pugx.org/zfcampus/zf-oauth2-doctrine/downloads.svg)](https://packagist.org/packages/zfcampus/zf-oauth2-doctrine) [![Latest Unstable Version](https://poser.pugx.org/zfcampus/zf-oauth2-doctrine/v/unstable.svg)](https://packagist.org/packages/zfcampus/zf-oauth2-doctrine) [![License](https://poser.pugx.org/zfcampus/zf-oauth2-doctrine/license.svg)](https://packagist.org/packages/zfcampus/zf-oauth2-doctrine)

OAuth2 Server for Doctrine
==========================

This is an OAuth2 Doctrine Adapter for Apigility.  This provides the database structure and interaction for all aspects of OAuth2 including Authorization Code, Access Tokens, Refresh Tokens, JWT & JTI, and Scopes.

Requirements
------------

At this time only ORM is supported.  For ORM you will require `doctrine/doctrine-orm-module` through composer.


Suggested Improvements
----------------------

These are other libraries which support a Doctrine API:
```
zfcampus/zf-apigility-doctrine
zfcampus/zf-doctrine-querybuilder
```


Entity Relationship Diagram
---------------------------

In order to understand how OAuth2 works you will understand the ERD.  The ERD is stored in [Skipper](http://www.skipper18.com).  If you do not have Skipper and you are writing a Doctrine application now would be a good time to consider an upgrade to your practices.

The ERD is in the media directory.

Because you'll be integrating zf-oauth2-doctrine with your own ERD you may include the externally stored OAuth2-orm.module.xml skipper bundle in your ERD.


Configuration
-------------

Copy ```config/oauth2.doctrine-orm.global.php.dist``` to your autoload directory and rename to ```oauth2.doctrine-orm.global.php``` You will need to edit this file with at least your User entity, which is not provided.


The User Enity
--------------

This library supplies every entity you need to implement OAuth2 except the User entity.  The reason is so the User entity can be decoupled from the OAuth2 library instead to be linked dynamically at run time.  This allows, among other benefits, the ability to create an ERD without modifying the OAuth2-rm.module.xml module.

The User entity must implement `ZF/OAuth2/Doctrine/Entity/UserInterface.php`

The User entitiy for the unit test for this module is a good template to start from: (https://github.com/TomHAnderson/zf-oauth2-doctrine/blob/master/test/asset/module/Doctrine/src/Entity/User.php)[https://github.com/TomHAnderson/zf-oauth2-doctrine/blob/master/test/asset/module/Doctrine/src/Entity/User.php]


Using Default Entities
----------------------

Details for creating your database with the included entities are outside the scope of this project.  Generally this is done through doctrine-orm-module with ```php public/index.php orm:schema-tool:create```

By default this module uses the entities provided but you may toggle this and use your own entites (and map them in the mapping config section) by toggling this flag:

```
'zf-oauth2-doctrine' => array(
    'storage_settings' => array(
        'enable_default_entities' => true,
```


Securing Resources with zf-apigility-doctrine
------------------------------------------

This module is supported directly by zf-apigility-doctrine.  To add security to your resources create a DefaultOrm Query Provider and include:

```
use ZF\Apigility\Doctrine\Server\Query\Provider\DefaultOrm as ZFDefaultOrm;
use ZF\Rest\ResourceEvent;
use OAuth2\Server as OAuth2Server;
use ZF\ApiProblem\ApiProblem;

class DefaultOrm extends ZFDefaultOrm
{
    public function createQuery(ResourceEvent $event, $entityClass, $parameters)
    {
        $validate = $this->validateOAuth2();
        if ($validate instanceof ApiProblem) {
            return $validate;
        }

        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder->select('row')
            ->from($entityClass, 'row')
            ;

        return $queryBuilder;
    }
}

```

See (zfcampus/zf-apigility-doctrine)[https://github.com/zfcampus/zf-apigility-doctrine] for more details on Query Providers.

