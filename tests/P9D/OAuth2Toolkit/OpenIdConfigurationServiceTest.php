<?php

namespace P9D\OAuth2Toolkit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;

class OpenIdConfigurationServiceTest extends TestCase
{
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
}