<?php

namespace ZF\OAuth2\Doctrine\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Scope
 */
class Scope
{
    /**
     * @var string
     */
    private $scope;

    /**
     * @var boolean
     */
    private $isDefault;

    /**
     * @var bigint
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $client;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $authorizationCode;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $refreshToken;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $accessToken;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->client = new ArrayCollection();
        $this->authorizationCode = new ArrayCollection();
        $this->refreshToken = new ArrayCollection();
        $this->accessToken = new ArrayCollection();
    }

    public function getArrayCopy()
    {
        return array(
            'id' => $this->getId(),
            'scope' => $this->getScope(),
            'isDefault' => $this->getIsDefault(),
        );
    }

    /**
     * Set scope
     *
     * @param string $scope
     * @return Scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return Scope
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Get id
     *
     * @return bigint
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Add client
     *
     * @param Client $client
     * @return Scope
     */
    public function addClient(Client $client)
    {
        $this->client[] = $client;

        return $this;
    }

    /**
     * Remove client
     *
     * @param Client $client
     */
    public function removeClient(Client $client)
    {
        $this->client->removeElement($client);
    }

    /**
     * Get client
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Add authorizationCode
     *
     * @param AuthorizationCode $authorizationCode
     * @return Scope
     */
    public function addAuthorizationCode(AuthorizationCode $authorizationCode)
    {
        $this->authorizationCode[] = $authorizationCode;

        return $this;
    }

    /**
     * Remove authorizationCode
     *
     * @param AuthorizationCode $authorizationCode
     */
    public function removeAuthorizationCode(AuthorizationCode $authorizationCode)
    {
        $this->authorizationCode->removeElement($authorizationCode);
    }

    /**
     * Get authorizationCode
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthorizationCode()
    {
        return $this->authorizationCode;
    }

    /**
     * Add refreshToken
     *
     * @param RefreshToken $refreshToken
     * @return Scope
     */
    public function addRefreshToken(RefreshToken $refreshToken)
    {
        $this->refreshToken[] = $refreshToken;

        return $this;
    }

    /**
     * Remove refreshToken
     *
     * @param RefreshToken $refreshToken
     */
    public function removeRefreshToken(RefreshToken $refreshToken)
    {
        $this->refreshToken->removeElement($refreshToken);
    }

    /**
     * Get refreshToken
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Add accessToken
     *
     * @param AccessToken $accessToken
     * @return Scope
     */
    public function addAccessToken(AccessToken $accessToken)
    {
        $this->accessToken[] = $accessToken;

        return $this;
    }

    /**
     * Remove accessToken
     *
     * @param AccessToken $accessToken
     */
    public function removeAccessToken(AccessToken $accessToken)
    {
        $this->accessToken->removeElement($accessToken);
    }

    /**
     * Get accessToken
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
}
