<?php

namespace ZF\OAuth2\Doctrine\Query;

use OAuth2\Server as OAuth2Server;

interface OAuth2ServerInterface
{
    public function setOAuth2Server(OAuth2Server $server);
    public function getOAuth2Server();
    public function validateOAuth2($scope = null);
}