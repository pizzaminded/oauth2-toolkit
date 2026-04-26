<?php

declare(strict_types=1);

namespace P9D\OAuth2Toolkit;

final readonly class OpenIdConfigurationProvider
{
    public function __construct(
        private(set) string $clientId,
        private(set) string $clientSecret,
        private(set) ?string $configurationEndpoint = null,
        private(set) ?string $authorizationEndpoint = null,
        private(set) ?string $tokenEndpoint = null,
        private(set) ?string $jwksEndpoint = null
    ) {
    }
}
