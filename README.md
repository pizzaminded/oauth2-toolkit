# `p9d/oauth2-toolkit`

Tools to deal with OAuth2/OpenID protocols.


If your identity providers are providing an well-known OpenID Configuration endpoint, you can use them to discover all
required things to integrate with them:


```php
$providers = [
    'your-provider-name-here' => [
        'configuration_endpoint' => 'https://<url>/realms/ACME/.well-known/openid-configuration',
        'client_id' => 'XXXXXXXXXXXXX'
    ],
    'google' => [
        'configuration_endpoint' => 'https://accounts.google.com/.well-known/openid-configuration'
    ]   
];

$factory = new \P9D\OAuth2Toolkit\OpenIdConfigurationFactory(
    $providers,
    \Symfony\Component\HttpClient\HttpClient::create()
);


# Access provider config via:
$provider = $factory->createForProvider('your-provider-name-here');
```

## Provider configuration:
- `configuration_endpoint` - URL to [OpenID Discovery Endpoint](https://openid.net/specs/openid-connect-discovery-1_0.html)
- `?client_id` Client ID used for this provider

## Methods available in `OpenIdConfigurationService`

### `getAuthorizationUrl(): string`

Returns a link user should be redirected to login.

#### parameters:
- `grantType`,
- `redirectUri`
- `?clientId` - when not passed, value passed in `client_id` from configuration will be used.
- `?scope`
- `?state`

### `getJwks(): array`

Returns an array of JSON Web Key Set. 
