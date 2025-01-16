<?php
declare(strict_types=1);

namespace P9D\OAuth2Toolkit\Model;

use P9D\OAuth2Toolkit\Exception\MissingOpenIdParameterException;

class OpenIdConfiguration
{
    public function __construct(
        private ?string $authorizationEndpoint = null,
        private ?string $tokenEndpoint = null,
        private ?string $jwksEndpoint = null,
    )
    {
    }

    public function getTokenEndpoint(): string
    {
        if($this->tokenEndpoint === null) {
            throw new MissingOpenIdParameterException(
                sprintf(
                    'Parameter "%s" is missing in OpenID Configuration!',
                    'token_endpoint'
                )
            );
        }

        return $this->tokenEndpoint;
    }

    public function getAuthorizationEndpoint(): string
    {
        if($this->authorizationEndpoint === null) {
            throw new MissingOpenIdParameterException(
                sprintf(
                    'Parameter "%s" is missing in OpenID Configuration!',
                    'authorization_endpoint'
                )
            );
        }

        return $this->authorizationEndpoint;
    }
    
    public function getJwksEndpoint(): string
    {
        if($this->jwksEndpoint === null) {
            throw new MissingOpenIdParameterException(
                sprintf(
                    'Parameter "%s" is missing in OpenID Configuration!',
                    'jwks_uri'
                )
            );
        }

        return $this->jwksEndpoint;
    }
}