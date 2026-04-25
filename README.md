# `p9d/oauth2-toolkit`

Tools to deal with OAuth2/OpenID protocols.

Use this package if your app needs to handle OAuth2/OIDC credentials to third party services, and you need something
to manage them in a organized way.

```php
$providers = [
    'your-provider-name-here' => new \P9D\OAuth2Toolkit\OpenIdConfigurationProvider(
        clientId: 'XXXXXXX',
        clientSecret: 'YYYYYYYY',
        configurationEndpoint: 'https://<url>/realms/ACME/.well-known/openid-configuration'
    )
    
    /**
     * If service does not expose openid-configuration (or you want to override it), you can pass URLs directly:
     */
    'your-another-provider-name' => new \P9D\OAuth2Toolkit\OpenIdConfigurationProvider(
        clientId: 'XXXXXXX',
        clientSecret: 'YYYYYYYY',
        tokenEndpoint: 'https://example.com/oauth2/token',
        authorizationEndpoint: 'https://example.com/oauth2/autorize',
        jwksEndpoint: 'https://example.com/oauth2/jwks',
    )
];

$factory = new \P9D\OAuth2Toolkit\OpenIdConfigurationFactory(
    $providers,
    \Symfony\Component\HttpClient\HttpClient::create()
);


# Access provider config via:
$provider = $factory->createForProvider('your-provider-name-here');
```

## Provider configuration:
- `clientId` - *required* 
- `clientSecret` - *required*
- `configurationEndpoint` - URL to [OpenID Discovery Endpoint](https://openid.net/specs/openid-connect-discovery-1_0.html)
- `tokenEndpoint` - [OAuth2 Token Endpoint](https://datatracker.ietf.org/doc/html/rfc6749#section-3.2) 
- `authorizationEndpoint` - [OAuth2 Authorization Endpoint](https://datatracker.ietf.org/doc/html/rfc6749#section-3.1) 
- `jwksEndpoint` - `jwks_uri` endpoint in OpenID Discovery spec.

> [!NOTE]
> You can use `tokenEndpoint`, `authorizationEndpoint`, `jwksEndpoint` either with both configuration Endpoint defined or not.
> When defined, these properties will **override** values received from configuration. 
> All of these properties are optional, you can skip any of those you do not use.

## Methods available in `OpenIdConfigurationService`

### `getAuthorizationUrl(): string`

Returns a link user should be redirected to login.

#### parameters:
- `grantType`,
- `redirectUri`
- `clientId` - *optional* - when not passed, value passed in `client_id` from configuration will be used.
- `scope` - *optional*
- `state` - *optional*

### `getJwks(): array`

Returns an array of JSON Web Key Set. 

### `getAccessToken(): AccessToken` 

Exchanges code to access tokens.

#### parameters:
- `grantType`
- `code` - *optional* when using `client_credentials`
