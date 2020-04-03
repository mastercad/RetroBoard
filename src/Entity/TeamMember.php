<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TeamMembers
 *
 * @ORM\Table(name="team_members", uniqueConstraints={@ORM\UniqueConstraint(name="team_members_UN", columns={"id", "member"})},
 *     indexes={
 *          @ORM\Index(name="team_members_creator_IDX", columns={"creator"}),
 *          @ORM\Index(name="team_members_id_IDX", columns={"id"}),
 *          @ORM\Index(name="team_members_member_IDX", columns={"member"}),
 *          @ORM\Index(name="team_members_modifier_IDX", columns={"modifier"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\TeamMemberRepository"))
 */
class TeamMember
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="member", referencedColumnName="id", nullable=false)
     * })
     */
    private $member;

    /**
     * @var Team
     *
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="members")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="team", referencedColumnName="id", nullable=false)
     * })
     */
    private $team;

    /**
     * @var json
     *
     * @ORM\Column(name="roles", type="json", length=250, nullable=false)
     */
    private $roles;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    private $modified;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="creator", referencedColumnName="id")
     * })
     */
    private $creator;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modifier", referencedColumnName="id")
     * })
     */
    private $modifier;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return TeamMember
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the value of team
     *
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set the value of team
     *
     * @param Team $team
     *
     * @return  self
     */
    public function setTeam(?Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return User
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param User $member
     *
     * @return TeamMember
     */
    public function setMember(?User $member)
    {
        $this->member = $member;
        return $this;
    }

    public function getRoles()
    {
        /** @var array $roles */
        $roles = $this->roles;
        // damit mindestens eine Rolle gesetzt wird
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles($roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     *
     * @return TeamMember
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param \DateTime|null $modified
     * @return TeamMember
     */
    public function setModified(?\DateTime $modified)
    {
        $this->modified = $modified;
        return $this;
    }

    /**
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param User $creator
     * @return TeamMember
     */
    public function setCreator(User $creator)
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @return User
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * @param User $modifier
     * @return TeamMember
     */
    public function setModifier(?User $modifier)
    {
        $this->modifier = $modifier;
        return $this;
    }

}
