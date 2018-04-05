<?php

namespace ZF\OAuth2\Doctrine\Query;

use OAuth2\Server as OAuth2Server;

interface OAuth2ServerInterface
{
    public function setOauth2Server(OAuth2Server $server);
    public function getOauth2Server();
    public function validateOauth2($scope = null);
}
