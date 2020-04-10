<?php

namespace App\Security\Provider;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use UnexpectedValueException;
use WellingGuzman\OAuth2\Client\Provider\Exception\OktaIdentityProviderException;
use WellingGuzman\OAuth2\Client\Provider\OktaResourceOwner;


/** [accessToken:protected] => eyJraWQiOiJaTkRTRFdXTURkMW1La0YtYkFzSVZtS0IwWXRJS09JZTUzM1NwU3BUalE4IiwiYWxnIjoiUlMyNTYifQ.eyJ2ZXIiOjEsImp0aSI6IkFULkYxMFB0UkM1aXNQeHRqaEprWDduWk10THlCVXlBWHp5ZVRDVTFPTnV1U1UiLCJpc3MiOiJodHRwczovL2Rldi0xNzk4NzEub2t0YS5jb20vb2F1dGgyL2RlZmF1bHQiLCJhdWQiOiJhcGk6Ly9kZWZhdWx0IiwiaWF0IjoxNTg2NDIzODk5LCJleHAiOjE1ODY0Mjc0OTksImNpZCI6IjBvYTVmeDZsNXZKOW8xMDgxNHg2IiwidWlkIjoiMDB1NWZ4Y3hnckRWblNocFY0eDYiLCJzY3AiOlsiZW1haWxfdGVzdCJdLCJzdWIiOiJhbmRyZWFzLmtlbXBlQGJ5dGUtYXJ0aXN0LmRlIn0.QybLZRHWmi6UH0-MQgaMmhuqBlUuhmIMyBc3wJnMfDeDAu-qgJQ1Sj8CiJDZvaaN3aIDu8_xZ7cyX49fy4hIijIHq446vvVBTJx7cZmNLMm7DLGUc_xLoOWM7qXXvS2AWhySYEe5nRGe-0SDDso39rZQ5_BYgzN-YW6MPzfZzol7__EpERjd7AYBc6VJMrUS4RGvUBEck7h27CLN-GlV718QbnM1WHQQywaEkQ9kyJIInZ-MFy5e2y6lrsvyJCJzUqGKSQSyz6Ui-fsB3zaIY30NOSVUHZeTWHvCfFLkqqSfxRXOI8HP3y_CUJAHnhU2wuu212BmqaFBj_1KIQeTLw     
 *  [expires:protected] => 1586427499
 *  [refreshToken:protected] =>      [
 *      resourceOwnerId:protected] =>      [
 *          values:protected] => Array         (
 *              [token_type] => Bearer
 *              [scope] => email_test
 */

/**
 * Response: Array (     [sub] => 00u5fxcxgrDVnShpV4x6     [name] => Andreas Kempe     [locale] => en-US     [email] => andreas.kempe@byte-artist.de     [preferred_username] => andreas.kempe@byte-artist.de     [given_name] => Andreas     [family_name] => Kempe     [zoneinfo] => America/Los_Angeles     [updated_at] => 1586411248     [email_verified] => 1 )  [] []
 */
class OktaProvider extends AbstractProvider {

    use BearerAuthorizationTrait;

    private $baseUrl;
    private $logger;
    protected $scopes;
    private $apiVersion="v1";

    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        $this->metaDataUrl = $options['metadata_url'];
        $this->baseUrl = $options['base_url'];

        if (isset($options['scopes'])) {
            $this->scopes = $options['scopes'];
        }
//        $this->grantFactory->setGrant('jwt_bearer', new JwtBearer());
    }

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     *
     * @return string
     */
    public function getBaseAuthorizationUrl() {
        return $this->getBaseApiUrl().'/authorize';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params) {
        return $this->getBaseApiUrl().'/token';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token) {
        return $this->getBaseApiUrl().'/userinfo';
    }

    /**
     * Requests an access token using a specified grant and option set.
     *
     * @param  mixed $grant
     * @param  array $options
     * @throws IdentityProviderException
     * @return AccessTokenInterface
     */
    public function getAccessToken($grant, array $options = [])
    {
        $grant = $this->verifyGrant($grant);
        $params = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
        ];

        $params   = $grant->prepareRequestParameters($params, $options);
        $request  = $this->getAccessTokenRequest($params);
        $response = $this->getParsedResponse($request);

        if (false === is_array($response)) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.'
            );
        }
        $prepared = $this->prepareAccessTokenResponse($response);
        $token    = $this->createAccessToken($prepared, $grant);

        return $token;
    }

    /**
     * Redirects the client for authorization.
     *
     * @param  array $options
     * @param  callable|null $redirectHandler
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
        header('Location: ' . $url);
        exit;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Requests resource owner details.
     *
     * @param  AccessToken $token
     * @return mixed
     */
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        $url = $this->getResourceOwnerDetailsUrl($token);
        $request = $this->getAuthenticatedRequest(self::METHOD_GET, $url, $token);

        $response = $this->getParsedResponse($request);

        if (false === is_array($response)) {
            throw new UnexpectedValueException ('Invalid response received from Authorization Server. Expected JSON.');
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
    protected function getDefaultScopes() {
        return ['email profile openid'];
    }

    /**
     * Checks a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array|string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data) {
        if (isset($data['error'])) {
            throw OktaIdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     *
     * @return ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $user = new OktaResourceOwner($response);

        return $user->setDomain($this->getBaseApiUrl());
    }

    /**
     * Gets the api base url
     *
     * @return string
     */
    protected function getBaseApiUrl()
    {
        return $this->baseUrl.'/'.$this->apiVersion;
    }
}