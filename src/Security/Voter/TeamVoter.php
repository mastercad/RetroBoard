<?php

namespace App\Security\Voter;

use App\Entity\Team;
use App\Entity\TeamMember;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TeamVoter extends Voter
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
        return in_array($attribute, ['edit', 'create', 'show', 'delete', 'archive'])
            && $subject instanceof Team;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        switch ($attribute) {
            case 'create':
                return $user instanceof UserInterface && in_array('ROLE_USER', $user->getRoles());
            case 'delete':
            case 'edit':
                if (!$user instanceof UserInterface) {
                    return false;
                }

                $results = $subject->getTeamMembers()->filter(
                    function (TeamMember $teamMember) use ($user) {
                        return $teamMember->getMember() === $user
                            && in_array('ROLE_ADMIN', $teamMember->getRoles());
                    }
                );

                return 0 < count($results);
            case 'show':
                if ('Demo Team' === $subject->getName()) {
                    return true;
                }

                $results = $subject->getTeamMembers()->filter(
                    function (TeamMember $teamMember) use ($user) {
                        return $teamMember->getMember() === $user;
                    }
                );

                return 0 < count($results);
            case 'archive':
                $results = $subject->getTeamMembers()->filter(
                    function (TeamMember $teamMember) use ($user) {
                        return $teamMember->getMember() === $user
                            && in_array('ROLE_ADMIN', $teamMember->getRoles());
                    }
                );

                return 0 < count($results);
        }

        return false;
    }
}
