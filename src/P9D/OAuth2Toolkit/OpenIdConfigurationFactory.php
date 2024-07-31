<?php

declare(strict_types=1);

namespace P9D\OAuth2Toolkit;

use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class OpenIdConfigurationFactory
{
    /**
     * @phpstan-param ProviderConfigArray $providers
     */
    public function __construct(
        private array               $providers,
        private HttpClientInterface $httpClient
    ) {
    }

    public function createForProvider(string $providerName): OpenIdConfigurationService
    {
        return new OpenIdConfigurationService(
            $this->providers[$providerName],
            $this->httpClient
        );
    }
}
