<?php

namespace App\Security\Voter;

use App\Entity\Board;
use App\Entity\BoardTeam;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class BoardTeamVoter extends Voter
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
        return in_array($attribute, ['edit', 'create', 'show', 'delete'])
            && $subject instanceof BoardTeam;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        switch ($attribute) {
            case 'create':
                return $user instanceof UserInterface;
            case 'edit':
                if (!$user instanceof UserInterface) {
                    return false;
                }

                $results = $subject->getBoard()->getBoardMembers()->filter(
                    function ($boardMember) use ($user) {
                        return $boardMember->getUser() === $user
                            && in_array('ROLE_ADMIN', $boardMember->getRoles());
                    }
                );

                return 0 < count($results);
            case 'show':
                if ("Demo Board" === $subject->getName()) {
                    return true;
                }

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
