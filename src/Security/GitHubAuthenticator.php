<?php

namespace App\Security;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use Symfony\Component\HttpFoundation\RedirectResponse;
use KnpU\OAuth2ClientBundle\Client\Provider\GithubClient;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use KnpU\OAuth2ClientBundle\Security\Exception\FinishRegistrationException;

class GitHubAuthenticator extends SocialAuthenticator
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

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        $this->logger->info("GitHub Auth Supports: ".$request->attributes->get('_route'));
        // continue ONLY if the current ROUTE matches the check ROUTE
        return 'connect_github_check' === $request->attributes->get('_route');
    }

    /**
     * @param Request $request
     * @return \League\OAuth2\Client\Token\AccessToken|mixed
     */
    public function getCredentials(Request $request)
    {
        $request->getSession()->set(
            Security::LAST_USERNAME,
            "GITHUB_SESSION_USER"
        );

        return $this->fetchAccessToken($this->getGitHubClient());
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User|null|object|\Symfony\Component\Security\Core\User\UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var GithubResourceOwner $googleUser */
        $gitHubUser = $this->getGitHubClient()->fetchUserFromToken($credentials);
        $this->logger->info("GitHub User Email: ".$gitHubUser->getEmail());
        $email = $gitHubUser->getEmail();
        $user = null;
        $this->logger->info("Email: ".$gitHubUser->getEmail());
        $this->logger->info("Id: ".$gitHubUser->getId());
        $this->logger->info("Name: ".$gitHubUser->getName());
        $this->logger->info("NickName: ".$gitHubUser->getNickName());
        $this->logger->info("Url: ".$gitHubUser->getUrl());
        $gitHubResponse = $gitHubUser->toArray();

        $this->logger->info("Response: ".print_r($gitHubResponse, true));

        $name = $gitHubUser->getName();

        if (empty($name)) {
            $name = $gitHubUser->getNickName();
        }

        // 1) have they logged in with Google before? Easy!
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['githubId' => $gitHubUser->getId()]);

        if ($existingUser) {
            $user = $existingUser;
        } else {
            if (!empty($email)) {
                // 2) do we have a matching user by email?
                $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
            } else {
                $email = uniqid("__GITHUB_RANDOM_EMAIL_");
            }

            if (!$user) {
                /** @var User $user */
                $systemUser = $this->em->getRepository(User::class)->find(1);
                $user = new User;
                $user->setEmail($email);
                $user->setCreator($systemUser);
                $user->setCreated(new \DateTime());
                $user->setName($name);
                $user->setPassword("");
            }
        }

        // 3) Maybe you just want to "register" them by creating a User object
        $user->setGitHubId($gitHubUser->getId());

        if (empty($user->getAvatarPath())) {
            $user->setAvatarPath($gitHubResponse['avatar_url']);
        }
        $this->em->persist($user);
        $this->em->flush();

        return $user; // $userProvider->loadUserByUsername($user->getName());
    }

    /**
     * @return GithubClient
     */
    private function getGitHubClient()
    {
        return $this->clientRegistry->getClient('github');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception instanceof FinishRegistrationException) {
            $this->saveUserInfoToSession($request, $exception);
            return new RedirectResponse("/login-failure");
        }

        $this->saveAuthenticationErrorToSession($request, $exception);
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