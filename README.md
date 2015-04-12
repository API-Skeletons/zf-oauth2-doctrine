OAuth2 Server for Doctrine
==========================

This is an OAuth2 Doctrine Adapter for Apigility.  This provides the database structure and interaction for all aspects of OAuth2 including Authorization Code, Access Tokens, Refresh Tokens, JWT & JTI, and Scopes.

Requirements
------------

At this time only ORM is supported.  For ORM you will require `doctrine/doctrine-orm-module.


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

