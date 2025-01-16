<?php

namespace P9D\OAuth2Toolkit\Model;

class AccessToken
{
    public function __construct(
        private string  $accessToken,
        private string  $tokenType,
        private ?int    $expiresIn = null,
        private ?string $refreshToken = null,
        private ?string $scope = null,
    )
    {
    }

    public static function fromArray(
        array $data
    ): self
    {
        return new self(
            $data['access_token'],
            $data['token_type'],
            $data['expires_in'] ?? null,
            $data['refresh_token'] ?? null,
            $data['scope'] ?? null,
        );
    }


    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function getExpiresIn(): ?int
    {
        return $this->expiresIn;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

}