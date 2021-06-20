<?php

namespace App\Security;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use KnpU\OAuth2ClientBundle\Security\Exception\FinishRegistrationException;

class GoogleAuthenticator extends SocialAuthenticator
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
     * Undocumented variable
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GoogleAuthenticator constructor.
     * @param ClientRegistry $clientRegistry
     * @param EntityManagerInterface $em
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
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return 'connect_google_check' === $request->attributes->get('_route');
    }

    /**
     * @param Request $request
     * @return \League\OAuth2\Client\Token\AccessToken|mixed
     */
    public function getCredentials(Request $request)
    {
        $request->getSession()->set(
            Security::LAST_USERNAME,
            "GOOGLE_SESSION_USER"
        );
        // this method is only called if supports() returns true
        return $this->fetchAccessToken($this->getGoogleClient());
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User|null|object|\Symfony\Component\Security\Core\User\UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleClient()->fetchUserFromToken($credentials);
        $user = null;
        $email = $googleUser->getEmail();

        // 1) have they logged in with Google before? Easy!
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['googleId' => $googleUser->getId()]);

        if ($existingUser) {
            $user = $existingUser;
        } else {
            if (!empty($email)) {
                // 2) do we have a matching user by email?
                $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
            } else {
                $email = uniqid("__GOOGLE_RANDOM_EMAIL_");
            }
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                /** @var User $user */
                $systemUser = $this->em->getRepository(User::class)->find(1);
                $user = new User;
                $user->setEmail($email);
                $user->setCreator($systemUser);
                $user->setCreated(new \DateTime());
                $user->setName($googleUser->getName());
                $user->setPassword("");
            }
        }

        // 3) Maybe you just want to "register" them by creating a User object
        $user->setGoogleId($googleUser->getId());
        if (empty($user->getAvatarPath())) {
            $user->setAvatarPath($googleUser->getAvatar());
        }
        $this->em->persist($user);
        $this->em->flush();

        return $user; // $userProvider->loadUserByUsername($user->getName());
    }

    /**
     * @return GoogleClient
     */
    private function getGoogleClient()
    {
        return $this->clientRegistry->getClient('google');
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return null|Response
     */
    /*
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
//        return null;
//        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
//            return new RedirectResponse($targetPath);
//        }

        return new RedirectResponse("/");
    }
*/
    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return null|Response
     */
    /*
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }
    */

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception instanceof FinishRegistrationException) {
            $this->saveUserInfoToSession($request, $exception);

//            $registrationUrl = $this->router->generate('connect_google_registration');
            return new RedirectResponse("/login-failure");
        }

        $this->saveAuthenticationErrorToSession($request, $exception);

//        $loginUrl = $this->router->generate('security_login');
        return new RedirectResponse("/login-secure");
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
//        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
//            return new RedirectResponse($targetPath);
//        }

        return new RedirectResponse("/");
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     *
     * @param Request $request
     * @param AuthenticationException|null $authException
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
