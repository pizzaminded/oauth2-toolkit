<?php

declare(strict_types=1);

namespace P9D\OAuth2Toolkit;

use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class OpenIdConfigurationFactory
{
    /**
     * @param array<string, OpenIdConfigurationProvider> $providers
     */
    public function __construct(
        private array               $providers,
        private HttpClientInterface $httpClient
    ) {
    }

    public function createForProvider(string $providerName): OpenIdConfigurationService
    {
        return new OpenIdConfigurationService(
            provider: $this->providers[$providerName],
            httpClient: $this->httpClient
        );
    }
}
