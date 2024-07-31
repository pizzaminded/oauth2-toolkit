<?php

declare(strict_types=1);

namespace P9D\OAuth2Toolkit;

use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class OpenIdConfigurationService
{
    /**
     * @phpstan-param ProviderConfig $providerConfig
     */
    public function __construct(
        private array $providerConfig,
        private HttpClientInterface $httpClient
    ) {
    }

    public function getAuthorizationUrl(
        string $grantType,
        string $redirectUri,
        ?string $clientId = null,
        ?string $scope = null,
        ?string $state = null,
    ): string {
        /** @phpstan-var OpenIdConfiguration $configuration */
        $configuration = $this
            ->httpClient
            ->request('GET', $this->providerConfig['configuration_endpoint'])
            ->toArray();
        
        $url = parse_url($configuration['authorization_endpoint']);

        OAuth2ToolkitAssert::isArray($url);
        OAuth2ToolkitAssert::keyExists($url,'scheme');
        OAuth2ToolkitAssert::keyExists($url,'host');
        $urlScheme = $url['scheme'];
        $urlHost = $url['host'];


        parse_str($url['query'] ?? '', $queryArgs);

        $queryArgs['client_id'] = $clientId ?? $this->providerConfig['client_id'];
        $queryArgs['grant_type'] = $grantType;
        $queryArgs['redirect_uri'] = $redirectUri;

        if ($scope !== null) {
            $queryArgs['scope'] = $scope;
        }

        if ($state !== null) {
            $queryArgs['state'] = $state;
        }

        $url['query'] = http_build_query($queryArgs);

        return sprintf(
            '%s://%s%s%s?%s',
            $urlScheme,
            $urlHost,
            array_key_exists('port', $url) ? sprintf(':%s', $url['port']): '',
            array_key_exists('path', $url) ? $url['path']: '/',
            $url['query'],
        );
    }
}