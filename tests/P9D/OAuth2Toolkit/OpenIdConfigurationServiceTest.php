<?php

declare(strict_types=1);

namespace P9D\OAuth2Toolkit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class OpenIdConfigurationServiceTest extends TestCase
{
    public function testAccessTokenEndpointWillBeFetchedFromProviderIfNoConfigurationEndpointWasProvided(): void
    {
        $provider = new OpenIdConfigurationProvider(
            'random',
            'random',
            authorizationEndpoint: 'http://example.com/authorize',
            tokenEndpoint: 'http://example.com/token',
        );

        $service = new OpenIdConfigurationService(
            $provider,
            new MockHttpClient([
                function (string $method, string $url) {
                    self::assertSame('http://example.com/token', $url);

                    return new MockResponse(
                        json_encode([
                            'access_token' => 'access_token',
                            'token_type' => 'Bearer',
                        ])
                    );
                },
            ]),
        );

        $service->getAccessToken('client_credentials');
    }

    public function testGetAuthorizationUrlWouldUseEndpointFromPropertyWhenConfigNotDefined(): void
    {
        $provider = new OpenIdConfigurationProvider(
            'random',
            'random',
            authorizationEndpoint: 'http://example.com/authorize',
        );

        $service = new OpenIdConfigurationService(
            $provider,
            new MockHttpClient(),
        );

        self::assertSame(
            'http://example.com/authorize?client_id=random&response_type=code&redirect_uri=http%3A%2F%2Fexample.com',
            $service->getAuthorizationUrl('code', 'http://example.com')
        );
    }

    public function testGetAuthorizationUrlWouldUseEndpointFromPropertyWhenConfigIsDefined(): void
    {
        $provider = new OpenIdConfigurationProvider(
            'random',
            'random',
            configurationEndpoint: 'http://example.com/authorize2',
            authorizationEndpoint: 'http://example.com/authorize',
        );

        $service = new OpenIdConfigurationService(
            $provider,
            new MockHttpClient([
                new MockResponse(json_encode([
                    'authorization_endpoint' => 'http://example2.com/authorize',
                    'jwks_uri' => 'http://example2.com/authorize',
                    'token_endpoint' => 'http://example2.com/authorize',
                ])),
            ]),
        );

        self::assertSame(
            'http://example.com/authorize?client_id=random&response_type=code&redirect_uri=http%3A%2F%2Fexample.com',
            $service->getAuthorizationUrl('code', 'http://example.com')
        );
    }
}
