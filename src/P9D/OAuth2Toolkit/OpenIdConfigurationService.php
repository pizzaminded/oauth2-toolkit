<?php

declare(strict_types=1);

namespace P9D\OAuth2Toolkit;

use P9D\OAuth2Toolkit\Exception\MissingOpenIdParameterException;
use P9D\OAuth2Toolkit\Exception\OAuth2ToolkitException;
use P9D\OAuth2Toolkit\Model\AccessToken;
use P9D\OAuth2Toolkit\Model\OpenIdConfiguration;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @phpstan-type Jwk array{
 *            e: string,
 *            alg: string,
 *            kty: string,
 *            use: string,
 *            kid: string,
 *            n: string
 *        }
 * @phpstan-type JwksEndpointResponse array{
 *       keys: Jwk[]
 *   }
 */
class OpenIdConfigurationService
{
    private bool $configurationLoaded = false;

    private OpenIdConfiguration $openIdConfiguration;

    public function __construct(
        private OpenIdConfigurationProvider $provider,
        private HttpClientInterface $httpClient,
    ) {
    }

    /**
     * @throws OAuth2ToolkitException
     * @throws MissingOpenIdParameterException
     */
    public function getAuthorizationUrl(
        string $responseType,
        string $redirectUri,
        ?string $clientId = null,
        ?string $scope = null,
        ?string $state = null,
    ): string {
        $this->fetchConfiguration();

        $endpoint = $this->provider->authorizationEndpoint ?? $this->openIdConfiguration->getAuthorizationEndpoint();
        $url = parse_url($endpoint);

        OAuth2ToolkitAssert::isArray($url);
        OAuth2ToolkitAssert::keyExists($url, 'scheme');
        OAuth2ToolkitAssert::keyExists($url, 'host');
        $urlScheme = $url['scheme'];
        $urlHost = $url['host'];

        parse_str($url['query'] ?? '', $queryArgs);

        $queryArgs['client_id'] = $clientId ?? $this->provider->clientId;
        $queryArgs['response_type'] = $responseType;
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
            array_key_exists('port', $url) ? sprintf(':%s', $url['port']) : '',
            array_key_exists('path', $url) ? $url['path'] : '/',
            $url['query'],
        );
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws OAuth2ToolkitException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws MissingOpenIdParameterException
     */
    public function getAccessToken(
        string $grantType,
        ?string $code = null,
    ): AccessToken {
        $this->fetchConfiguration();

        $body = [
            'client_id' => $this->provider->clientId,
            'client_secret' => $this->provider->clientSecret,
            'grant_type' => $grantType,
        ];

        if ($code !== null) {
            $body['code'] = $code;
        }

        try {
            /**
             * @var array{
             *     access_token: non-empty-string,
             *     token_type: non-empty-string,
             *     expires_in: ?int,
             *     refresh_token: ?string,
             *     scope: ?string
             * } $tokenResponse
             */
            $tokenResponse = $this
                ->httpClient
                ->request(
                    'POST',
                    $this->openIdConfiguration->getTokenEndpoint(),
                    [
                        'body' => $body,
                    ]
                )
                ->toArray();
        } catch (ClientException $e) {
            /** @var string $message */
            $message = $e
                ->getResponse()
                ->toArray(false)['error_description'];

            throw new OAuth2ToolkitException(sprintf('Bad Request occurred during fetching an access token: "%s"', $message));
        }

        return AccessToken::fromArray($tokenResponse);
    }

    /**
     * @phpstan-return JwksEndpointResponse
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws MissingOpenIdParameterException
     * @throws OAuth2ToolkitException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getJwks(): array
    {
        $this->fetchConfiguration();

        /** @var JwksEndpointResponse $response */
        $response = $this
            ->httpClient
            ->request('GET', $this->provider->jwksEndpoint ?? $this->openIdConfiguration->getJwksEndpoint())
            ->toArray();

        return $response;
    }

    /**
     * @throws OAuth2ToolkitException
     */
    private function fetchConfiguration(): void
    {
        if ($this->configurationLoaded || $this->provider->configurationEndpoint === null) {
            return;
        }
        try {
            /**
             * @var array{
             *     authorization_endpoint: string,
             *     token_endpoint: string,
             *     jwks_uri: non-empty-string
             * } $configuration
             */
            $configuration = $this
                ->httpClient
                ->request('GET', $this->provider->configurationEndpoint)
                ->toArray();

            $this->openIdConfiguration = new OpenIdConfiguration(
                authorizationEndpoint: $configuration['authorization_endpoint'],
                tokenEndpoint: $configuration['token_endpoint'],
                jwksEndpoint: $configuration['jwks_uri']
            );

            $this->configurationLoaded = true;
        } catch (ExceptionInterface $e) {
            throw new OAuth2ToolkitException(sprintf('Unable to fetch configuration from "%s": %s', $this->provider->configurationEndpoint, $e->getMessage()), previous: $e);
        }
    }
}
