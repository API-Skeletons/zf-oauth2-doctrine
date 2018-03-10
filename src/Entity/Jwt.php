<?php

namespace ZF\OAuth2\Doctrine\Entity;

/**
 * Jwt
 */
class Jwt
{
    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var bigint
     */
    private $id;

    /**
     * @var Client
     */
    private $client;

    public function getArrayCopy()
    {
        return array(
            'id' => $this->getId(),
            'client' => $this->getClient(),
            'subject' => $this->getSubject(),
            'publicKey' => $this->getPublicKey(),
        );
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return Jwt
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set publicKey
     *
     * @param string $publicKey
     * @return Jwt
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * Get publicKey
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
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

    /**
     * Set client
     *
     * @param Client $client
     * @return Jwt
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
