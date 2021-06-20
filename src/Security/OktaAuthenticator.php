<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use KnpU\OAuth2ClientBundle\Security\Exception\FinishRegistrationException;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

// &error=invalid_scope&error_description=The+authorization+server+resource+does+not+have+any+configured+default+scopes%2C+'scope'+must+be+provided.
// -> https://support.okta.com/help/s/article/How-do-I-create-a-scope-for-my-Authorization-Server

// &error=invalid_scope&error_description=One+or+more+scopes+are+not+configured+for+the+authorization+server+resource.

class OktaAuthenticator extends SocialAuthenticator
{
    use TargetPathTrait;

    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Undocumented variable.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GoogleAuthenticator constructor.
     */
    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return bool
     */
    public function supports(Request $request)
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return 'connect_okta_check' === $request->attributes->get('_route');
    }

    /**
     * @return \League\OAuth2\Client\Token\AccessToken|mixed
     */
    public function getCredentials(Request $request)
    {
        $request->getSession()->set(
            Security::LAST_USERNAME,
            'OKTA_SESSION_USER'
        );

        return $this->fetchAccessToken($this->getOktaClient());
    }

    /**
     * @param mixed $credentials
     *
     * @return User|object|\Symfony\Component\Security\Core\User\UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var GenericResourceOwner $oktaUser */
        $oktaUser = $this->getOktaClient()->fetchUserFromToken($credentials);
        $oktaResponse = $oktaUser->toArray();

        $user = null;
        $email = $oktaResponse['email'];

        $existingUser = $this->em->getRepository(User::class)->findOneBy(['oktaId' => $oktaUser->getId()]);

        if ($existingUser) {
            $user = $existingUser;
        } else {
            if (!empty($email)) {
                // 2) do we have a matching user by email?
                $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
            } else {
                $email = uniqid('__OKTA_RANDOM_EMAIL_').'@byte-artist.de';
            }
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $name = null;
                if (isset($oktaResponse['preferred_username'])) {
                    $name = $oktaResponse['preferred_username'];
                }
                if (empty($name)
                    && isset($oktaResponse['name'])
                ) {
                    $name = $oktaResponse['name'];
                }

                if (empty($name)
                    && (isset($oktaResponse['given_name'])
                        || isset($oktaResponse['family_name']))
                ) {
                    $name = trim($oktaResponse['given_name'].' '.$oktaResponse['family_name']);
                }

                /** @var User $user */
                $systemUser = $this->em->getRepository(User::class)->find(1);
                $user = new User();
                $user->setEmail($email);
                $user->setName($name);
                $user->setPassword('');
                $user->setCreator($systemUser);
                $user->setCreated(new \DateTime());
            }
        }

        // 3) Maybe you just want to "register" them by creating a User object
        $user->setOktaId($oktaUser->getId());

        $this->em->persist($user);
        $this->em->flush();

        return $user; // $userProvider->loadUserByUsername($user->getName());
    }

    /**
     * @return OAuth2ClientInterface
     */
    private function getOktaClient()
    {
        return $this->clientRegistry->getClient('okta');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception instanceof FinishRegistrationException) {
            $this->saveUserInfoToSession($request, $exception);

//            $registrationUrl = $this->router->generate('connect_google_registration');
//            return new RedirectResponse("/login-failure");
            return new RedirectResponse('/login');
        }

        $this->saveAuthenticationErrorToSession($request, $exception);

//        $loginUrl = $this->router->generate('security_login');
//        return new RedirectResponse("/login-secure");
        return new RedirectResponse('/login');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
//        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
//            return new RedirectResponse($targetPath);
//        }

        return new RedirectResponse('/');
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     *
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            '/login', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
