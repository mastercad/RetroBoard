<?php

namespace App\Security\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;
use WellingGuzman\OAuth2\Client\Provider\Exception\OktaIdentityProviderException;
use WellingGuzman\OAuth2\Client\Provider\OktaResourceOwner;

class OktaProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    private $baseUrl;
    private $logger;
    protected $scopes;
    private $apiVersion = 'v1';

    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        $this->metaDataUrl = $options['metadata_url'];
        $this->baseUrl = $options['base_url'];

        if (isset($options['scopes'])) {
            $this->scopes = $options['scopes'];
        }
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->getBaseApiUrl().'/authorize';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->getBaseApiUrl().'/token';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getBaseApiUrl().'/userinfo';
    }

    /**
     * Requests an access token using a specified grant and option set.
     *
     * @param mixed $grant
     *
     * @throws IdentityProviderException
     *
     * @return AccessTokenInterface
     */
    public function getAccessToken($grant, array $options = [])
    {
        $grant = $this->verifyGrant($grant);
        $params = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
        ];

        $params = $grant->prepareRequestParameters($params, $options);
        $request = $this->getAccessTokenRequest($params);
        $response = $this->getParsedResponse($request);

        if (false === is_array($response)) {
            throw new UnexpectedValueException('Invalid response received from Authorization Server. Expected JSON.');
        }
        $prepared = $this->prepareAccessTokenResponse($response);
        $token = $this->createAccessToken($prepared, $grant);

        return $token;
    }

    /**
     * Redirects the client for authorization.
     *
     * @return mixed
     */
    public function authorize(
        array $options = [],
        callable $redirectHandler = null
    ) {
        $url = $this->getAuthorizationUrl($options);
        if ($redirectHandler) {
            return $redirectHandler($url, $this);
        }

        // @codeCoverageIgnoreStart
        header('Location: '.$url);
        exit;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Requests resource owner details.
     *
     * @return mixed
     */
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        $url = $this->getResourceOwnerDetailsUrl($token);
        $request = $this->getAuthenticatedRequest(self::METHOD_GET, $url, $token);

        $response = $this->getParsedResponse($request);

        if (false === is_array($response)) {
            throw new UnexpectedValueException('Invalid response received from Authorization Server. Expected JSON.');
        }

        return $response;
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return ['email profile openid'];
    }

    /**
     * Checks a provider response for errors.
     *
     * @throws IdentityProviderException
     *
     * @param array|string $data Parsed response data
     *
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw OktaIdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @return ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $user = new OktaResourceOwner($response);

        return $user->setDomain($this->getBaseApiUrl());
    }

    /**
     * Gets the api base url.
     *
     * @return string
     */
    protected function getBaseApiUrl()
    {
        return $this->baseUrl.'/'.$this->apiVersion;
    }
}
