<?php

namespace App\Security\Voter;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class BoardInvitationVoter extends Voter
{
    private $security;
    private $logger;

    public function __construct(LoggerInterface $logger, Security $security)
    {
        $this->security = $security;
        $this->logger = $logger;
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['edit', 'accept', 'archive', 'create', 'show', 'delete'])
            && $subject instanceof \App\Entity\BoardInvitation;
    }

    /**
     * Undocumented function
     *
     * @param  [type]          $attribute
     * @param  BoardInvitation $subject
     * @param  TokenInterface  $token
     * @return void
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
        case 'edit':
            $results = $subject->getBoard()->getBoardMembers()->filter(
                function ($boardMember) use ($user) {
                    return $boardMember->getUser() === $user 
                        && in_array('ROLE_ADMIN', $boardMember->getRoles());
                }
            );
            return 0 < count($results);
        case 'accept':
            if (strtolower($subject->getEmail()) == strtolower($user->getEmail())) {
                return true;
            }
            break;
        case 'delete':
            if (strtolower($subject->getEmail()) == strtolower($user->getEmail())) {
                return true;
            }
            $results = $subject->getBoard()->getBoardMembers()->filter(
                function ($boardMember) use ($user) {
                    return $boardMember->getUser() === $user
                        && in_array('ROLE_ADMIN', $boardMember->getRoles());
                }
            );
            return 0 < count($results);
        case 'create':
            $results = $subject->getBoard()->getBoardMembers()->filter(
                function ($boardMember) use ($user) {
                    return $boardMember->getUser() === $user;
                }
            );
            return 0 < count($results);
        case 'show':
            $results = $subject->getBoard()->getBoardMembers()->filter(
                function ($boardMember) use ($user) {
                    return $boardMember->getUser() === $user;
                }
            );
            return 0 < count($results);
        }

        return false;
    }
}
