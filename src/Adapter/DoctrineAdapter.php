<?php

namespace ZF\OAuth2\Doctrine\Adapter;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use OAuth2\OpenID\Storage\UserClaimsInterface as OpenIDUserClaimsInterface;
use OAuth2\OpenID\Storage\AuthorizationCodeInterface as OpenIDAuthorizationCodeInterface;
use OAuth2\Storage\AuthorizationCodeInterface;
use OAuth2\Storage\AccessTokenInterface;
use OAuth2\Storage\ClientCredentialsInterface;
use OAuth2\Storage\UserCredentialsInterface;
use OAuth2\Storage\RefreshTokenInterface;
use OAuth2\Storage\JwtBearerInterface;
use OAuth2\Storage\ScopeInterface;
use OAuth2\Storage\PublicKeyInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Crypt\Password\Bcrypt;
use Zend\Mvc\MvcEvent;
use ZF\OAuth2\Doctrine\EventListener\DynamicMappingSubscriber;
use DoctrineModule\Persistence\ProvidesObjectManager as ProvidesObjectManagerTrait;
use ZF\OAuth2\Doctrine\Mapper\MapperManager;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Zend\Config\Config;
use Exception;
use DateTime;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

/**
 * Doctrine storage for OAuth2
 *
 * @author Tom Anderson <tom.h.anderson@gmail.com>
 */
class DoctrineAdapter implements
    AuthorizationCodeInterface,
    AccessTokenInterface,
    ClientCredentialsInterface,
    UserCredentialsInterface,
    RefreshTokenInterface,
    JwtBearerInterface,
    ScopeInterface,
    PublicKeyInterface,
    OpenIDUserClaimsInterface,
    OpenIDAuthorizationCodeInterface,
    ObjectManagerAwareInterface,
    EventManagerAwareInterface
{
    use ProvidesObjectManagerTrait;
    use EventManagerAwareTrait;

    /**
     * @var MapperManager
     */
    protected $mapperManager;

    /**
     * @var Bcrypt
     */
    protected $bcrypt;

    /**
     * @var array
     */
    protected $config = array();

    public function getMapperManager()
    {
        return $this->mapperManager;
    }

    /**
     * @param MapperManager
     * @return $this
     */
    public function setMapperManager(MapperManager $manager)
    {
        $this->mapperManager = $manager;

        return $this;
    }

    /**
     * @return Bcrypt
     */
    public function getBcrypt()
    {
        if (null === $this->bcrypt) {
            $this->bcrypt = new Bcrypt();
            $this->bcrypt->setCost($this->getConfig()->bcrypt_cost);
        }

        return $this->bcrypt;
    }

    /**
     * Check password using bcrypt
     *
     * @param string $user
     * @param string $password
     * @return bool
     */
    protected function checkPassword($user, $password)
    {
        return $this->verifyHash($password, $user['password']);
    }

    /**
     * Check hash using bcrypt
     *
     * @param $hash
     * @param $check
     * @return bool
     */
    protected function verifyHash($check, $hash)
    {
        return $this->getBcrypt()->verify($check, $hash);
    }

    /**
     * Set the config for the entities implementing the interfaces
     *
     * @param Config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Because the DoctrineAdapter is not created when added to the service
     * manager it must be bootstrapped specifically in the onBootstrap event
     */
    public function bootstrap(MvcEvent $e)
    {
        $serviceManager = $e->getParam('application')->getServiceManager();

        // Enable default entities
        if (isset($this->getConfig()->enable_default_entities) && $this->getConfig()->enable_default_entities) {
            $chain = $serviceManager->get($this->getConfig()->driver);
            $chain->addDriver(new XmlDriver(__DIR__ . '/../../config/orm'), 'ZF\OAuth2\Doctrine\Entity');
        }

        if (isset($this->getConfig()->dynamic_mapping) && $this->getConfig()->dynamic_mapping) {
            $userClientSubscriber = new DynamicMappingSubscriber(
                $this->getConfig()->dynamic_mapping,
                $this->getConfig()->mapping
            );
            $eventManager = $serviceManager->get($this->getConfig()->event_manager);
            $eventManager->addEventSubscriber($userClientSubscriber);
        }
    }


    /* OAuth2\Storage\ClientCredentialsInterface */
    /**
     * Make sure that the client credentials is valid.
     *
     * @param $client_id
     * Client identifier to be check with.
     * @param $client_secret
     * (optional) If a secret is required, check that they've given the right one.
     *
     * @return
     * TRUE if the client credentials are valid, and MUST return FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-3.1
     *
     * @ingroup oauth2_section_3
     */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'client_id' => $client_id,
                'client_secret' => $client_secret,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineClientIdField =
            $this->getConfig()->mapping->Client->mapping->client_id->name;
        $doctrineClientSecretField =
            $this->getConfig()->mapping->Client->mapping->client_secret->name;

        $client = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->Client->entity)
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            return false;
        }

        $mapper = $this->getMapperManager()->get('Client');
        $mapper->exchangeDoctrineArray($client->getArrayCopy());
        $data = $mapper->getOAuth2ArrayCopy();

        return $this->verifyHash($client_secret, $data['client_secret']);
    }

    /* OAuth2\Storage\ClientCredentialsInterface */
    /**
     * Determine if the client is a "public" client, and therefore
     * does not require passing credentials for certain grant types
     *
     * @param $client_id
     * Client identifier to be check with.
     *
     * @return
     * TRUE if the client is public, and FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-2.3
     * @see https://github.com/bshaffer/oauth2-server-php/issues/257
     *
     * @ingroup oauth2_section_2
     */
    public function isPublicClient($client_id)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'client_id' => $client_id,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineClientIdField =
            $this->getConfig()->mapping->Client->mapping->client_id->name;

        $client = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->Client->entity)
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            return false;
        }

        return ($client->getSecret()) ? false: true;
    }


    /* OAuth2\Storage\ClientInterface */
    /**
     * Get client details corresponding client_id.
     *
     * OAuth says we should store request URIs for each registered client.
     * Implement this function to grab the stored URI for a given client id.
     *
     * @param $client_id
     * Client identifier to be check with.
     *
     * @return array
     *               Client details. The only mandatory key in the array is "redirect_uri".
     *               This function MUST return FALSE if the given client does not exist or is
     *               invalid. "redirect_uri" can be space-delimited to allow for multiple valid uris.
     *               <code>
     *               return array(
     *               "redirect_uri" => REDIRECT_URI,      // REQUIRED redirect_uri registered for the client
     *               "client_id"    => CLIENT_ID,         // OPTIONAL the client id
     *               "grant_types"  => GRANT_TYPES,       // OPTIONAL an array of restricted grant types
     *               "user_id"      => USER_ID,           // OPTIONAL the user identifier associated with this client
     *               "scope"        => SCOPE,             // OPTIONAL the scopes allowed for this client
     *               );
     *               </code>
     *
     * @ingroup oauth2_section_4
     */
    public function getClientDetails($client_id)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'client_id' => $client_id,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineClientIdField =
            $this->getConfig()->mapping->Client->mapping->client_id->name;

        $client = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->Client->entity)
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            return false;
        }

        $mapper = $this->getMapperManager()->get('Client');
        $mapper->exchangeDoctrineArray($client->getArrayCopy());
        $data = $mapper->getOAuth2ArrayCopy();

        return $data;
    }

    /* !!!!! OAuth2\Storage\ClientInterface */
    /**
     * This function isn't in the interface but called often
     */
    public function setClientDetails(
        $client_id,
        $client_secret = null,
        $redirect_uri = null,
        $grant_types = null,
        $scope = null,
        $user_id = null
    ) {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri' => $redirect_uri,
                'grant_types' => $grant_types,
                'scope' => $scope,
                'user_id' => $user_id,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineClientIdField =
            $this->getConfig()->mapping->Client->mapping->client_id->name;

        $client = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->Client->entity)
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            $clientClass = $this->getConfig()->mapping->Client->entity;
            $client = new $clientClass;
            $client->setClientId($client_id);
            $this->getObjectManager()->persist($client);
        }

        $scopes = new ArrayCollection;
        foreach ((array) $scope as $scopeString) {
            $scopes->add($this->getObjectManager()
                ->getRepository($this->getConfig()->mapping->Scope->entity)
                ->findOneBy(array(
                    $this->getConfig()->mapping->Scope->mapping->scope->name
                        => $scopeString,
                )));
        }

        $client->exchangeArray(array(
            $this->getConfig()->mapping->Client->mapping->client_secret->name
                => $client_secret,
            $this->getConfig()->mapping->Client->mapping->redirect_uri->name
                => $redirect_uri,
            $this->getConfig()->mapping->Client->mapping->grant_types->name
                => $grant_types,
            $this->getConfig()->mapping->Client->mapping->scope->name
                => $scopes,
        ));

        $this->getObjectManager()->flush();

        return true;
    }

    /* OAuth2\Storage\ClientInterface */
    /**
     * Check restricted grant types of corresponding client identifier.
     *
     * If you want to restrict clients to certain grant types, override this
     * function.
     *
     * @param $client_id
     * Client identifier to be check with.
     * @param $grant_type
     * Grant type to be check with
     *
     * @return
     * TRUE if the grant type is supported by this client identifier, and
     * FALSE if it isn't.
     *
     * @ingroup oauth2_section_4
     */
    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'client_id' => $client_id,
                'grant_type' => $grant_type,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineClientIdField =
            $this->getConfig()->mapping->Client->mapping->client_id->name;

        $client = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->Client->entity)
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            return false;
        }

        if ($client->getGrantType()) {
            return in_array($grant_type, $client->getGrantType());
        }

        // @codeCoverageIgnoreStart
        // if grant_types are not defined, then none are restricted
        return true;
        // @codeCoverageIgnoreEnd
    }

    /* OAuth2\Storage\ClientInterface */
    /**
     * Get the scope associated with this client
     *
     * @return
     * STRING the space-delineated scope list for the specified client_id
     */
    public function getClientScope($client_id)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'client_id' => $client_id,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineClientIdField =
            $this->getConfig()->mapping->Client->mapping->client_id->name;

        $client = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->Client->entity)
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            return false;
        }

        $mapper = $this->getMapperManager()->get('Client');
        $mapper->exchangeDoctrineArray($client->getArrayCopy());
        $data = $mapper->getOAuth2ArrayCopy();

        return $data['scope'];
    }

    /* OAuth2\Storage\AccessTokenInterface */
    /**
     * Look up the supplied oauth_token from storage.
     *
     * We need to retrieve access token data as we create and verify tokens.
     *
     * @param $oauth_token
     * oauth_token to be check with.
     *
     * @return
     * An associative array as below, and return NULL if the supplied oauth_token
     * is invalid:
     * - expires: Stored expiration in unix timestamp.
     * - client_id: (optional) Stored client identifier.
     * - user_id: (optional) Stored user identifier.
     * - scope: (optional) Stored scope values in space-separated string.
     * - id_token: (optional) Stored id_token (if "use_openid_connect" is true).
     *
     * @ingroup oauth2_section_7
     */
    public function getAccessToken($access_token)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'access_token' => $access_token,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineAccessTokenField =
            $this->getConfig()->mapping->AccessToken->mapping->access_token->name;

        $accessToken = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->AccessToken->entity)
            ->findOneBy(
                array(
                    $doctrineAccessTokenField => $access_token,
                )
            );

        if (!$accessToken) {
            return false;
        }

        $mapper = $this->getMapperManager()->get('AccessToken');
        $mapper->exchangeDoctrineArray($accessToken->getArrayCopy());
        $data = $mapper->getOAuth2ArrayCopy();

        return $data;
    }

    /* OAuth2\Storage\AccessTokenInterface */
    /**
     * Store the supplied access token values to storage.
     *
     * We need to store access token data as we create and verify tokens.
     *
     * @param $oauth_token    oauth_token to be stored.
     * @param $client_id      client identifier to be stored.
     * @param $user_id        user identifier to be stored.
     * @param int    $expires expiration to be stored as a Unix timestamp.
     * @param string $scope   OPTIONAL Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_4
     */
    public function setAccessToken(
        $access_token,
        $client_id,
        $user_id,
        $expires,
        $scope = null
    ) {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'access_token' => $access_token,
                'client_id' => $client_id,
                'user_id' => $user_id,
                'expires' => $expires,
                'scope' => $scope,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineAccessTokenField =
            $this->getConfig()->mapping->AccessToken->mapping->access_token->name;

        $accessToken = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->AccessToken->entity)
            ->findOneBy(
                array(
                    $doctrineAccessTokenField => $access_token,
                )
            );

        if (!$accessToken) {
            $entityClass = $this->getConfig()->mapping->AccessToken->entity;

            $accessToken = new $entityClass;
            $this->getObjectManager()->persist($accessToken);
        }

        $mapper = $this->getMapperManager()->get('AccessToken');
        $mapper->exchangeOAuth2Array(array(
            'access_token' => $access_token,
            'client_id' => $client_id,
            'user_id' => $user_id,
            'expires' => $expires,
            'scope' => $scope,
        ));

        $accessToken->exchangeArray($mapper->getDoctrineArrayCopy());

        $this->getObjectManager()->flush();

        return true;
    }

    /* OAuth2\Storage\AuthorizationCodeInterface */
    /**
     * Fetch authorization code data (probably the most common grant type).
     *
     * Retrieve the stored data for the given authorization code.
     *
     * Required for OAuth2::GRANT_TYPE_AUTH_CODE.
     *
     * @param $code
     * Authorization code to be check with.
     *
     * @return
     * An associative array as below, and NULL if the code is invalid
     * @code
     * return array(
     *     "client_id"    => CLIENT_ID,      // REQUIRED Stored client identifier
     *     "user_id"      => USER_ID,        // REQUIRED Stored user identifier
     *     "expires"      => EXPIRES,        // REQUIRED Stored expiration in unix timestamp
     *     "redirect_uri" => REDIRECT_URI,   // REQUIRED Stored redirect URI
     *     "scope"        => SCOPE,          // OPTIONAL Stored scope values in space-separated string
     * );
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.1
     *
     * @ingroup oauth2_section_4
     */
    public function getAuthorizationCode($code)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'code' => $code,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineAuthorizationCode =
            $this->getConfig()->mapping->AuthorizationCode->mapping->authorization_code->name;
        $doctrineExpiresField =
            $this->getConfig()->mapping->AuthorizationCode->mapping->expires->name;

        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder->select('authorizationCode')
            ->from($this->getConfig()->mapping->AuthorizationCode->entity, 'authorizationCode')
            ->andwhere("authorizationCode.$doctrineAuthorizationCode = :code")
            ->andwhere("authorizationCode.$doctrineExpiresField > :now")
            ->setParameter('code', $code)
            ->setParameter('now', new DateTime())
            ;

        try {
            $authorizationCode = $queryBuilder->getQuery()->getSingleResult();
        } catch (Exception $e) {
            $authorizationCode = false;
        }

        if (!$authorizationCode) {
            return false;
        }

        $mapper = $this->getMapperManager()->get('AuthorizationCode');
        $mapper->exchangeDoctrineArray($authorizationCode->getArrayCopy());

        $authorizationCodeClientAssertion = new \ZF\OAuth2\Doctrine\ClientAssertionType\AuthorizationCode();
        $authorizationCodeClientAssertion->exchangeArray($mapper->getOAuth2ArrayCopy());

        return $authorizationCodeClientAssertion;
    }

    /* OAuth2\Storage\AuthorizationCodeInterface */
    /**
     * Take the provided authorization code values and store them somewhere.
     *
     * This function should be the storage counterpart to getAuthCode().
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * Required for OAuth2::GRANT_TYPE_AUTH_CODE.
     *
     * @param string $code         Authorization code to be stored.
     * @param mixed  $client_id    Client identifier to be stored.
     * @param mixed  $user_id      User identifier to be stored.
     * @param string $redirect_uri Redirect URI(s) to be stored in a space-separated string.
     * @param int    $expires      Expiration to be stored as a Unix timestamp.
     * @param string $scope        OPTIONAL Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_4
     */
    public function setAuthorizationCode(
        $code,
        $client_id,
        $user_id,
        $redirect_uri,
        $expires,
        $scope = null,
        $id_token = null
    ) {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'code' => $code,
                'client_id' => $client_id,
                'user_id' => $user_id,
                'redirect_uri' => $redirect_uri,
                'expires' => $expires,
                'scope ' => $scope,
                'id_token ' => $id_token,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineAuthorizationCodeField =
            $this->getConfig()->mapping->AuthorizationCode->mapping->authorization_code->name;

        $authorizationCode = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->AuthorizationCode->entity)
            ->findOneBy(
                array(
                    $doctrineAuthorizationCodeField => $code,
                )
            );

        if (!$authorizationCode) {
            $entityClass = $this->getConfig()->mapping->AuthorizationCode->entity;

            $authorizationCode= new $entityClass;
            $this->getObjectManager()->persist($authorizationCode);
        }

        $mapper = $this->getMapperManager()->get('AuthorizationCode');
        $mapper->exchangeOAuth2Array(array(
            'authorization_code' => $code,
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'expires' => $expires,
            'scope' => $scope,
            'id_token' => $id_token,
            'user_id' => $user_id,
        ));

        $authorizationCode->exchangeArray($mapper->getDoctrineArrayCopy());

        $this->getObjectManager()->flush();

        return true;
    }

    /* OAuth2\Storage\AuthorizationCodeInterface */
    /**
     * once an Authorization Code is used, it must be exipired
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.1.2
     *
     *    The client MUST NOT use the authorization code
     *    more than once.  If an authorization code is used more than
     *    once, the authorization server MUST deny the request and SHOULD
     *    revoke (when possible) all tokens previously issued based on
     *    that authorization code
     *
     */
    public function expireAuthorizationCode($code)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'code' => $code,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineAuthorizationCodeField =
            $this->getConfig()->mapping->AuthorizationCode->mapping->authorization_code->name;

        $authorizationCode = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->AuthorizationCode->entity)
            ->findOneBy(
                array(
                    $doctrineAuthorizationCodeField => $code,
                )
            );

        if ($authorizationCode) {
            $doctrineExpiresField =
                $this->getConfig()->mapping->AuthorizationCode->mapping->expires->name;
            $authorizationCode->exchangeArray(array(
                $doctrineExpiresField => new DateTime(), # maybe subtract 1 second?
            ));

            $this->getObjectManager()->flush();
        }

        return true;
    }

    /* OAuth2\Storage\UserCredentialsInterface */
    /**
     * Grant access tokens for basic user credentials.
     *
     * Check the supplied username and password for validity.
     *
     * You can also use the $client_id param to do any checks required based
     * on a client, if you need that.
     *
     * Required for OAuth2::GRANT_TYPE_USER_CREDENTIALS.
     *
     * @param $username
     * Username to be check with.
     * @param $password
     * Password to be check with.
     *
     * @return
     * TRUE if the username and password are valid, and FALSE if it isn't.
     * Moreover, if the username and password are valid, and you want to
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.3
     *
     * @ingroup oauth2_section_4
     */
    public function checkUserCredentials($username, $password)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'username' => $username,
                'password' => $password,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $qb = $this->getObjectManager()->createQueryBuilder();

        $qb->select(array('u'))
            ->from($this->getConfig()->mapping->User->entity, 'u')
            ->setParameter('username', $username);

        foreach ($this->getConfig()->auth_identity_fields as $field) {
            $doctrineField = $this->getConfig()->mapping->User->mapping->$field->name;
            $qb->orWhere(sprintf("u.%s = :username", $doctrineField));
        }

        $user = $qb->getQuery()->getOneOrNullResult();

        if ($user) {
            $mapper = $this->getMapperManager()->get('User');
            $mapper->exchangeDoctrineArray($user->getArrayCopy());

            return $this->checkPassword($mapper->getOAuth2ArrayCopy(), $password);
        }

        return false;
    }

    /* OAuth2\Storage\UserCredentialsInterface */
    /**
     * @return
     * ARRAY the associated "user_id" and optional "scope" values
     * This function MUST return FALSE if the requested user does not exist or is
     * invalid. "scope" is a space-separated list of restricted scopes.
     * @code
     * return array(
     *     "user_id"  => USER_ID,    // REQUIRED user_id to be stored with the authorization code or access token
     *     "scope"    => SCOPE       // OPTIONAL space-separated list of restricted scopes
     * );
     * @endcode
     */
    public function getUserDetails($username)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'username' => $username,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $qb = $this->getObjectManager()->createQueryBuilder();

        $qb->select(array('u'))
            ->from($this->getConfig()->mapping->User->entity, 'u')
            ->setParameter('username', $username);

        foreach ($this->getConfig()->auth_identity_fields as $field) {
            $doctrineField = $this->getConfig()->mapping->User->mapping->$field->name;
            $qb->orWhere(sprintf("u.%s = :username", $doctrineField));
        }

        $user = $qb->getQuery()->getOneOrNullResult();

        if ($user) {
            $mapper = $this->getMapperManager()->get('User');
            $mapper->exchangeDoctrineArray($user->getArrayCopy());

            return $mapper->getOAuth2ArrayCopy();
        }

        return false;
    }

    /* OAuth2\OpenID\Storage\UserClaimsInterface */
    /**
     * Return claims about the provided user id.
     *
     * Groups of claims are returned based on the requested scopes. No group
     * is required, and no claim is required.
     *
     * @param $user_id
     * The id of the user for which claims should be returned.
     * ## Although the spec says id the rest of the class uses username so I changed this to use username
     * @param $scope
     * The requested scope.
     * Scopes with matching claims: profile, email, address, phone.
     *
     * @return
     * An array in the claim => value format.
     *
     * @see http://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
     */
    public function getUserClaims($username, $scope)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'username' => $username,
                'scope' => $scope,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineUsernameField = $this->getConfig()->mapping->User->mapping->username->name;

        $user = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->User->entity)
            ->findOneBy(
                array(
                    $doctrineUsernameField => $username,
                )
            );

        if (!$user) {
            return false;
        }

        // Return any fields from the user table as an associative array
        // which match the constants defined in this class.
        switch ($scope) {
            case 'profile':
                return array_intersect_key(
                    $user->getArrayCopy(),
                    array_flip(explode(' ', self::PROFILE_CLAIM_VALUES))
                );
                break;
            case 'email':
                return array_intersect_key(
                    $user->getArrayCopy(),
                    array_flip(explode(' ', self::EMAIL_CLAIM_VALUES))
                );
                break;
            case 'address':
                return array_intersect_key(
                    $user->getArrayCopy(),
                    array_flip(explode(' ', self::ADDRESS_CLAIM_VALUES))
                );
                break;
            case 'phone':
                return array_intersect_key(
                    $user->getArrayCopy(),
                    array_flip(explode(' ', self::PHONE_CLAIM_VALUES))
                );
                break;
            default:
                break;
        }

        return false;
    }

    /* OAuth2\Storage\RefreshTokenInterface */
    /**
     * Grant refresh access tokens.
     *
     * Retrieve the stored data for the given refresh token.
     *
     * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
     *
     * @param $refresh_token
     * Refresh token to be check with.
     *
     * @return
     * An associative array as below, and NULL if the refresh_token is
     * invalid:
     * - refresh_token: Refresh token identifier.
     * - client_id: Client identifier.
     * - user_id: User identifier.
     * - expires: Expiration unix timestamp, or 0 if the token doesn't expire.
     * - scope: (optional) Scope values in space-separated string.
     *
     * @see http://tools.ietf.org/html/rfc6749#section-6
     *
     * @ingroup oauth2_section_6
     */
    # If expired return null
    public function getRefreshToken($refresh_token)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'refresh_token' => $refresh_token,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineRefreshTokenField =
            $this->getConfig()->mapping->RefreshToken->mapping->refresh_token->name;
        $doctrineExpiresField =
            $this->getConfig()->mapping->RefreshToken->mapping->expires->name;

        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder->select('refreshToken')
            ->from($this->getConfig()->mapping->RefreshToken->entity, 'refreshToken')
            ->andwhere("refreshToken.$doctrineRefreshTokenField = :token")
            ->andwhere("refreshToken.$doctrineExpiresField > :now")
            ->setParameter('token', $refresh_token)
            ->setParameter('now', new DateTime())
            ;

        try {
            $refreshToken = $queryBuilder->getQuery()->getSingleResult();

            $mapper = $this->getMapperManager()->get('RefreshToken');
            $mapper->exchangeDoctrineArray($refreshToken->getArrayCopy());

            return $mapper->getOAuth2ArrayCopy();
        } catch (Exception $e) {
            // no result ok
        }

        return false;
    }

    /* OAuth2\Storage\RefreshTokenInterface */
    /**
     * Take the provided refresh token values and store them somewhere.
     *
     * This function should be the storage counterpart to getRefreshToken().
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
     *
     * @param $refresh_token
     * Refresh token to be stored.
     * @param $client_id
     * Client identifier to be stored.
     * @param $user_id
     * User identifier to be stored.
     * @param $expires
     * Expiration timestamp to be stored. 0 if the token doesn't expire.
     * @param $scope
     * (optional) Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_6
     */
    public function setRefreshToken(
        $refresh_token,
        $client_id,
        $user_id,
        $expires,
        $scope = null
    ) {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'refresh_token' => $refresh_token,
                'client_id' => $client_id,
                'user_id' => $user_id,
                'expires' => $expires,
                'scope ' => $scope,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineRefreshTokenField =
            $this->getConfig()->mapping->RefreshToken->mapping->refresh_token->name;

        $refreshToken= $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->RefreshToken->entity)
            ->findOneBy(
                array(
                    $doctrineRefreshTokenField => $refresh_token,
                )
            );

        if (!$refreshToken) {
            $entityClass = $this->getConfig()->mapping->RefreshToken->entity;

            $refreshToken= new $entityClass;
            $this->getObjectManager()->persist($refreshToken);
        }

        $mapper = $this->getMapperManager()->get('RefreshToken');
        $mapper->exchangeOAuth2Array(array(
            'refresh_token' => $refresh_token,
            'client_id' => $client_id,
            'user_id' => $user_id,
            'expires' => $expires,
            'scope' => $scope,
            'user_id' => $user_id,
        ));

        $scopes = new ArrayCollection;
        foreach ((array) $scope as $scopeString) {
            $scopes->add($this->getObjectManager()
                ->getRepository($this->getConfig()->mapping->Scope->entity)
                ->findOneBy(array(
                    $this->getConfig()->mapping->Scope->mapping->scope->name => $scopeString,
                )));
        }

        $refreshToken->exchangeArray($mapper->getDoctrineArrayCopy());

        $this->getObjectManager()->flush();

        return true;
    }

    /* OAuth2\Storage\RefreshTokenInterface */
    /**
     * Expire a used refresh token.
     *
     * This is not explicitly required in the spec, but is almost implied.
     * After granting a new refresh token, the old one is no longer useful and
     * so should be forcibly expired in the data store so it can't be used again.
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * @param $refresh_token
     * Refresh token to be expirse.
     *
     * @ingroup oauth2_section_6
     */
    public function unsetRefreshToken($refresh_token)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'refresh_token' => $refresh_token,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineRefreshTokenCodeField =
            $this->getConfig()->mapping->RefreshToken->mapping->refresh_token->name;

        $refreshToken = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->RefreshToken->entity)
            ->findOneBy(
                array(
                    $doctrineRefreshTokenCodeField => $refresh_token,
                )
            );

        if ($refreshToken) {
            $doctrineExpiresField =
                $this->getConfig()->mapping->RefreshToken->mapping->expires->name;
            $refreshToken ->exchangeArray(array(
                $doctrineExpiresField => new DateTime(), # maybe subtract 1 second?
            ));

            $this->getObjectManager()->flush();
        }

        return true;
    }

    /* OAuth2\Storage\ScopeInterface */
    /**
     * Check if the provided scope exists.
     *
     * @param $scope
     * A space-separated string of scopes.
     *
     * @return
     * TRUE if it exists, FALSE otherwise.
     */
    public function scopeExists($scope)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'scope ' => $scope,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $scopeArray = explode(' ', $scope);

        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder
            ->select('scope')
            ->from($this->getConfig()->mapping->Scope->entity, 'scope')
            ->andwhere(
                $queryBuilder->expr()->in('scope.scope', $scopeArray)
            )
            ;

        $result = $queryBuilder->getQuery()->getResult();

        return sizeof($result) == sizeof($scopeArray);
    }

    /* OAuth2\Storage\ScopeInterface */
    /**
     * The default scope to use in the event the client
     * does not request one. By returning "false", a
     * request_error is returned by the server to force a
     * scope request by the client. By returning "null",
     * opt out of requiring scopes
     *
     * @param $client_id
     * An optional client id that can be used to return customized default scopes.
     *
     * @return
     * string representation of default scope, null if
     * scopes are not defined, or false to force scope
     * request by the client
     *
     * ex:
     *     'default'
     * ex:
     *     null
     */
    public function getDefaultScope($client_id = null)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'client_id' => $client_id,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineScopeIsDefaultField =
            $this->getConfig()->mapping->Scope->mapping->is_default->name;

        $scope = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->Scope->entity)
            ->findBy(
                array(
                    $doctrineScopeIsDefaultField => true,
                )
            );

        $return = array();
        foreach ($scope as $s) {
            $mapper = $this->getMapperManager()->get('Scope');
            $mapper->exchangeDoctrineArray($s->getArrayCopy());
            $data = $mapper->getOAuth2ArrayCopy();

            $return[] = $data['scope'];
        }

        return implode(' ', $return);
    }

    /* OAuth2\Storage\JWTBearerInterface */
    /**
     * Get the public key associated with a client_id
     *
     * @param $client_id
     * Client identifier to be checked with.
     *
     * @return
     * STRING Return the public key for the client_id if it exists, and MUST return FALSE if it doesn't.
     */
    public function getClientKey($client_id, $subject)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'client_id' => $client_id,
                'subject' => $subject,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineClientIdField = $this->getConfig()->mapping->Client->mapping->client_id->name;

        $client = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->Client->entity)
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            return false;
        }

        $doctrineClientIdField = $this->getConfig()->mapping->Jwt->mapping->client_id->name;
        $doctrineSubjectField = $this->getConfig()->mapping->Jwt->mapping->subject->name;

        try {
            $jwt = $this->getObjectManager()
                ->getRepository($this->getConfig()->mapping->Jwt->entity)
                ->findOneBy(
                    array(
                        $doctrineClientIdField => $client,
                        $doctrineSubjectField => $subject,
                    )
                );
        } catch (Exception $e) {
            // No result from doctrine ok
        }

        if (!$jwt) {
            return false;
        }

        $mapper = $this->getMapperManager()->get('Jwt');
        $mapper->exchangeDoctrineArray($jwt->getArrayCopy());
        $data = $mapper->getOAuth2ArrayCopy();

        return ($data['public_key']) ?: false;
    }

    /* OAuth2\Storage\JwtBearerInterface */
    /**
     * Get a jti (JSON token identifier) by matching against the client_id, subject, audience and expiration.
     *
     * @param $client_id
     * Client identifier to match.
     *
     * @param $subject
     * The subject to match.
     *
     * @param $audience
     * The audience to match.
     *
     * @param $expiration
     * The expiration of the jti.
     *
     * @param $jti
     * The jti to match.
     *
     * @return
     * An associative array as below, and return NULL if the jti does not exist.
     * - issuer: Stored client identifier.
     * - subject: Stored subject.
     * - audience: Stored audience.
     * - expires: Stored expiration in unix timestamp.
     * - jti: The stored jti.
     */
    public function getJti(
        $client_id,
        $subject,
        $audience,
        $expires,
        $jti
    ) {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'client_id' => $client_id,
                'subject' => $subject,
                'audience' => $audience,
                'expires' => $expires,
                'jti' => $jti,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineClientIdField = $this->getConfig()->mapping->Jti->mapping->client_id->name;
        $doctrineSubjectField = $this->getConfig()->mapping->Jti->mapping->subject->name;
        $doctrineAudienceField = $this->getConfig()->mapping->Jti->mapping->audience->name;
        $doctrineExpirationField = $this->getConfig()->mapping->Jti->mapping->expires->name;
        $doctrineJtiField = $this->getConfig()->mapping->Jti->mapping->jti->name;

        $mapper = $this->getMapperManager()->get('Jti');
        $mapper->exchangeOAuth2Array(array(
            'client_id' => $client_id,
            'subject' => $subject,
            'audience' => $audience,
            'expires' => $expires,
            'jti' => $jti,
        ));

        // Fetch doctrine array and filter for parameter values
        $query = $mapper->getDoctrineArrayCopy();

        $jti= $this->getObjectManager()->getRepository($this->getConfig()->mapping->Jti->entity)
            ->findOneBy(array(
                $doctrineClientIdField => $query['client'],
                $doctrineSubjectField => $query['subject'],
                $doctrineAudienceField => $query['audience'],
                $doctrineExpirationField => $query['expires'],
                $doctrineJtiField => $query['jti'],
            ));

        if (!$jti) {
            return false;
        }

        $mapper = $this->getMapperManager()->get('Jti');
        $mapper->exchangeDoctrineArray($jti->getArrayCopy());

        return $mapper->getOAuth2ArrayCopy();
    }

    /* OAuth2\Storage\JwtBearerInterface */
    /**
     * Store a used jti so that we can check against it to prevent replay attacks.
     * @param $client_id
     * Client identifier to insert.
     *
     * @param $subject
     * The subject to insert.
     *
     * @param $audience
     * The audience to insert.
     *
     * @param $expires
     * The expiration of the jti.
     *
     * @param $jti
     * The jti to insert.
     */
    public function setJti($client_id, $subject, $audience, $expires, $jti)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'client_id' => $client_id,
                'subject' => $subject,
                'audience' => $audience,
                'expires' => $expires,
                'jti' => $jti,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $jtiEntityClass = $this->getConfig()->mapping->Jti->entity;
        $jtiEntity = new $jtiEntityClass;

        $mapper = $this->getMapperManager()->get('Jti');
        $mapper->exchangeOAuth2Array(array(
            'client_id'  => $client_id,
            'subject'    => $subject,
            'audience'   => $audience,
            'expires'    => $expires,
            'jti'        => $jti,
        ));

        $jtiEntity->exchangeArray($mapper->getDoctrineArrayCopy());

        $this->getObjectManager()->persist($jtiEntity);
        $this->getObjectManager()->flush();

        return true;
    }

    /* OAuth2\Storage\PublicKeyInterface */
    public function getPublicKey($client_id = null)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'client_id' => $client_id,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineClientIdField = $this->getConfig()->mapping->Client->mapping->client_id->name;

        $client = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->Client->entity)
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client || !$client->getPublicKey()) {
            return false;
        }

        $mapper = $this->getMapperManager()->get('PublicKey');
        $mapper->exchangeDoctrineArray($client->getPublicKey()->getArrayCopy());

        $publicKeyOAuth2 = $mapper->getOAuth2ArrayCopy();

        return $publicKeyOAuth2['public_key'];
    }

    /* OAuth2\Storage\PublicKeyInterface */
    public function getPrivateKey($client_id = null)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'client_id' => $client_id,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineClientIdField = $this->getConfig()->mapping->Client->mapping->client_id->name;

        $client = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->Client->entity)
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client || !$client->getPublicKey()) {
            return false;
        }

        $mapper = $this->getMapperManager()->get('PublicKey');
        $mapper->exchangeDoctrineArray($client->getPublicKey()->getArrayCopy());

        $publicKeyOAuth2 = $mapper->getOAuth2ArrayCopy();

        return $publicKeyOAuth2['private_key'];
    }

    /* OAuth2\Storage\PublicKeyInterface */
    public function getEncryptionAlgorithm($client_id = null)
    {
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [
                'client_id' => $client_id,
            ]
        );
        if ($results->stopped()) {
            return $results->last();
        }

        $doctrineClientIdField = $this->getConfig()->mapping->Client->mapping->client_id->name;

        $client = $this->getObjectManager()
            ->getRepository($this->getConfig()->mapping->Client->entity)
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client || !$client->getPublicKey()) {
            return false;
        }

        $mapper = $this->getMapperManager()->get('PublicKey');
        $mapper->exchangeDoctrineArray($client->getPublicKey()->getArrayCopy());

        $publicKeyOAuth2 = $mapper->getOAuth2ArrayCopy();

        return $publicKeyOAuth2['encryption_algorithm'];
    }
}
