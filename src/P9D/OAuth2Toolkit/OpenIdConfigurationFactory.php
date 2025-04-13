<?php

declare(strict_types=1);

namespace P9D\OAuth2Toolkit;

use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class OpenIdConfigurationFactory
{
    /**
     * @param array{
     *     configuration_endpoint: string
     * }[] $providers
     */
    public function __construct(
        private array               $providers,
        private HttpClientInterface $httpClient
    ) {
    }

    public function createForProvider(string $providerName): OpenIdConfigurationService
    {
        return new OpenIdConfigurationService(
            configurationEndpoint: $this->providers[$providerName]['configuration_endpoint'],
            clientId: $this->providers[$providerName]['client_id'] ?? null,
            clientSecret: $this->providers[$providerName]['client_secret'] ?? null,
            httpClient: $this->httpClient
        );
    }

    public function create(
        string $configurationEndpoint
    ): OpenIdConfigurationService
    {
        return new OpenIdConfigurationService(
            $configurationEndpoint,
            httpClient: $this->httpClient
        );
    }
}
