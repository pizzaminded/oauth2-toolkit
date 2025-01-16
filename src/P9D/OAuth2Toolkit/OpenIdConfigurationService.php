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

class OpenIdConfigurationService
{
    private bool $configurationLoaded = false;

    private readonly OpenIdConfiguration $openIdConfiguration;

    public function __construct(
        private string              $configurationEndpoint,
        private HttpClientInterface $httpClient,
        private ?string             $clientId = null,
        private ?string             $clientSecret = null,
    )
    {
    }

    public function getAuthorizationUrl(
        string  $responseType,
        string  $redirectUri,
        ?string $clientId = null,
        ?string $scope = null,
        ?string $state = null,
    ): string
    {

        $configuration = $this
            ->httpClient
            ->request('GET', $this->configurationEndpoint)
            ->toArray();

        $url = parse_url($configuration['authorization_endpoint']);

        OAuth2ToolkitAssert::isArray($url);
        OAuth2ToolkitAssert::keyExists($url, 'scheme');
        OAuth2ToolkitAssert::keyExists($url, 'host');
        $urlScheme = $url['scheme'];
        $urlHost = $url['host'];


        parse_str($url['query'] ?? '', $queryArgs);

        $queryArgs['client_id'] = $clientId ?? $this->clientId;
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
        string  $grantType,
        ?string $code = null,
    ): AccessToken
    {
        $this->fetchConfiguration();

        $body = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => $grantType
        ];

        if ($code !== null) {
            $body['code'] = $code;
        }

        /**
         * @var array{
         *     access_token: string,
         *     token_type: string,
         *     expires_in: ?int,
         *     refresh_token: ?string,
         *     scope: ?string
         * } $tokenResponse
         */
        try {
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
            $message = $e
                ->getResponse()
                ->toArray(false)['error_description'];
            
            throw new OAuth2ToolkitException(
                sprintf(
                    'Bad Request occured during fetching an access token: "%s"',
                    $message
                )
            );
        }

        return AccessToken::fromArray($tokenResponse);
    }


    public function getJwks(): array
    {
        $this->fetchConfiguration();

        return $this
            ->httpClient
            ->request('GET', $this->openIdConfiguration->getJwksEndpoint())
            ->toArray();
        
    }
    
    /**
     * @throws OAuth2ToolkitException
     */
    public function fetchConfiguration(): void
    {
        if ($this->configurationLoaded) {
            return;
        }
        try {
            /**
             * @var array{
             *     authorization_endpoint: string,
             *     token_endpoint: string
             * } $configuration
             */
            $configuration = $this
                ->httpClient
                ->request('GET', $this->configurationEndpoint)
                ->toArray();

            $this->openIdConfiguration = new OpenIdConfiguration(
                authorizationEndpoint: $configuration['authorization_endpoint'],
                tokenEndpoint: $configuration['token_endpoint'],
                jwksEndpoint: $configuration['jwks_uri']
            );

            $this->configurationLoaded = true;
        } catch (ExceptionInterface $e) {
            throw new OAuth2ToolkitException(
                sprintf(
                    'Unable to fetch configuration from "%s": %s',
                    $this->configurationEndpoint,
                    $e->getMessage()
                ),
                previous: $e
            );
        }
    }
}
