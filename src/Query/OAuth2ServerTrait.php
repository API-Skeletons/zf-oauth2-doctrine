<?php

namespace ZF\OAuth2\Doctrine\Query;

use OAuth2\Server as OAuth2Server;
use OAuth2\Request as OAuth2Request;
use ZF\ApiProblem\ApiProblem;

trait OAuth2ServerTrait
{
    protected $oAuth2Server;

    public function setOAuth2Server(OAuth2Server $server)
    {
        $this->oAuth2Server = $server;

        return $this;
    }

    public function getOAuth2Server()
    {
        return $this->oAuth2Server;
    }

    public function validateOAuth2($scope = null)
    {
        if (! $this->getOAuth2Server()->verifyResourceRequest(
            OAuth2Request::createFromGlobals(),
            $response = null,
            $scope
        )) {
            $error = $this->getOAuth2Server()->getResponse();
            $parameters = $error->getParameters();
            $detail = isset($parameters['error_description']) ?
                $parameters['error_description']: $error->getStatusText();

            return new ApiProblem($error->getStatusCode(), $detail);
        }

        return true;
    }
}
