<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\MicrosoftClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use KnpU\OAuth2ClientBundle\Security\Exception\FinishRegistrationException;
use Psr\Log\LoggerInterface;
use Stevenmaguire\OAuth2\Client\Provider\MicrosoftResourceOwner;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class MicrosoftAuthenticator extends SocialAuthenticator
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
        $this->logger->info('Microsoft Auth Supports: '.$request->attributes->get('_route'));
        // continue ONLY if the current ROUTE matches the check ROUTE
        return 'connect_microsoft_check' === $request->attributes->get('_route');
    }

    /**
     * @return \League\OAuth2\Client\Token\AccessToken|mixed
     */
    public function getCredentials(Request $request)
    {
        $request->getSession()->set(
            Security::LAST_USERNAME,
            'MICROSOFT_SESSION_USER'
        );
        // this method is only called if supports() returns true
        return $this->fetchAccessToken($this->getMicrosoftClient());
    }

    /**
     * @param mixed $credentials
     *
     * @return User|object|\Symfony\Component\Security\Core\User\UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var MicrosoftResourceOwner $microsoftUser */
        $microsoftUser = $this->getMicrosoftClient()->fetchUserFromToken($credentials);
        $this->logger->info('Microsoft User Email: '.$microsoftUser->getEmail());
        $email = $microsoftUser->getEmail();
        $user = null;
        $this->logger->info('Email: '.$microsoftUser->getEmail());
        $this->logger->info('Id: '.$microsoftUser->getId());
        $this->logger->info('Name: '.$microsoftUser->getName());
        $microsoftResponse = $microsoftUser->toArray();

        $this->logger->info('Response: '.print_r($microsoftResponse, true));

        $name = $microsoftUser->getName();

        if (empty($name)) {
            $name = $microsoftUser->getFirstname().' '.$microsoftUser->getLastname();
        }

        // 1) have they logged in with Google before? Easy!
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['microsoftId' => $microsoftUser->getId()]);

        if ($existingUser) {
            $user = $existingUser;
        } else {
            if (!empty($email)) {
                // 2) do we have a matching user by email?
                $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
            } else {
                $email = uniqid('__MICROSOFT_RANDOM_EMAIL_');
            }

            if (!$user) {
                /** @var User $user */
                $systemUser = $this->em->getRepository(User::class)->find(1);
                $user = new User();
                $user->setEmail($email);
                $user->setCreator($systemUser);
                $user->setCreated(new \DateTime());
                $user->setName($name);
                $user->setPassword('');
            }
        }

        // 3) Maybe you just want to "register" them by creating a User object
        $user->setMicrosoftId($microsoftUser->getId());

        if (empty($user->getAvatarPath())) {
            $user->setAvatarPath($microsoftResponse['avatar_url']);
        }
        $this->em->persist($user);
        $this->em->flush();

        return $user; // $userProvider->loadUserByUsername($user->getName());
    }

    /**
     * @return MicrosoftClient
     */
    private function getMicrosoftClient()
    {
        return $this->clientRegistry->getClient('microsoft');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception instanceof FinishRegistrationException) {
            $this->saveUserInfoToSession($request, $exception);

            return new RedirectResponse('/login-failure');
        }

        $this->saveAuthenticationErrorToSession($request, $exception);

        return new RedirectResponse('/login-secure');
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
