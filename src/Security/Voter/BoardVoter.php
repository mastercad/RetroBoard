<?php

namespace App\Security\Voter;

use App\Entity\Board;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class BoardVoter extends Voter
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
            && $subject instanceof Board;
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

                $resultMembers = $subject->getBoardMembers()->filter(
                    function($boardMember) use ($user) {
                        return $boardMember->getUser() === $user
                            && in_array('ROLE_ADMIN', $boardMember->getRoles());
                    }
                );

                $resultTeamMembers = [];
                foreach ($subject->getBoardTeams() as $boardTeam) {
                    foreach ($boardTeam->getTeam()->getTeamMembers() as $teamMember) {
                        if ($teamMember->getMember() === $user
                            && $subject == $boardTeam->getBoard()
                            && in_array('ROLE_ADMIN', $teamMember->getMember()->getRoles())
                        ) {
                            $resultTeamMembers[] = $boardTeam->getBoard();
                        }
                    }
                }

                return 0 < count($resultMembers) || 0 < count($resultTeamMembers);
            case 'show':
                if ("Demo Board" === $subject->getName()) {
                    return true;
                }

                $resultMembers = $subject->getBoardMembers()->filter(
                    function ($boardMember) use ($user) {
                        return $boardMember->getUser() === $user;
                    }
                );
/*
                $resultTeamMembers = $subject->getBoardTeams()->filter(
                    function ($boardTeam) use ($user) {
                            return $boardTeam->getTeam()->getTeamMembers()->filter(function($teamMember) use ($user) {
                                echo "USER: ".$user->getId()." - TeamMember: ".$teamMember->getMember()->getId()."<br />";
                                return $teamMember->getMember() === $user;
                            }
                        );
                    }
                );
*/
                $resultTeamMembers = [];
                foreach ($subject->getBoardTeams() as $boardTeam) {
                    foreach ($boardTeam->getTeam()->getTeamMembers() as $teamMember) {
                        if ($teamMember->getMember() == $user) {
                            $resultTeamMembers[] = $teamMember->getMember();
                        }
                    }
                }

                return 0 < count($resultMembers) || 0 < count($resultTeamMembers);
            case 'archive':
                $results = $subject->getBoardMembers()->filter(
                    function($boardMember) use ($user) {
                        return $boardMember->getUser() === $user
                            && in_array('ROLE_ADMIN', $boardMember->getRoles());
                    }
                );
                return 0 < count($results);
        }

        return false;
    }
}
