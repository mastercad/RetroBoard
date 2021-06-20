<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Teams.
 *
 * @ORM\Table(
 *  name="teams",
 *  indexes={
 *      @ORM\Index(name="teams_creator_IDX", columns={"creator"}),
 *      @ORM\Index(name="teams_id_IDX", columns={"id"}),
 *      @ORM\Index(name="teams_modifier_IDX", columns={"modifier"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\TeamRepository"))
 */
class Team
{
    /**
     * @var int
     *
     * @ORM\Column(
     *  name="id",
     *  type="integer",
     *  length=11,
     *  columnDefinition="integer unsigned",
     *  nullable=false,
     *  options={"unsigned"=true}
     * )
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

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
     *   @ORM\JoinColumn(name="creator", columnDefinition="integer unsigned", referencedColumnName="id", nullable=true)
     * })
     */
    private $creator;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modifier", columnDefinition="integer unsigned", referencedColumnName="id", nullable=true)
     * })
     */
    private $modifier;

    /**
     * @ORM\OneToMany(
     *  targetEntity="TeamInvitation",
     *  mappedBy="team",
     *  cascade={"refresh", "remove", "persist"},
     *  orphanRemoval=true
     * )
     */
    private $invitations;

    /**
     * @ORM\OneToMany(
     *  targetEntity="TeamMember",
     *  mappedBy="team",
     *  cascade={"refresh", "remove", "persist"},
     *  orphanRemoval=true
     * )
     */
    private $members;

    /**
     * @ORM\OneToMany(
     *  targetEntity="BoardTeam",
     *  mappedBy="team",
     *  cascade={"refresh", "remove", "persist"},
     *  orphanRemoval=true
     * )
     */
    private $boardTeams;

    public function __construct()
    {
        $this->invitations = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->boardTeams = new ArrayCollection();
    }

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
     * @return Team
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Team
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return Team
     */
    public function setCreated($created)
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
     *
     * @return Team
     */
    public function setModified($modified)
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
     *
     * @return Team
     */
    public function setCreator($creator)
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
     *
     * @return Team
     */
    public function setModifier($modifier)
    {
        $this->modifier = $modifier;

        return $this;
    }

    /**
     * @return Collection|TeamMember[]
     */
    public function getTeamMembers(): ?Collection
    {
        return $this->members;
    }

    public function addTeamMember(TeamMember $teamMember): self
    {
        if (!$this->members->contains($teamMember)) {
            $this->members[] = $teamMember;
            $teamMember->setTeam($this);
        }

        return $this;
    }

    public function removeTeamMember(TeamMember $teamMember): self
    {
        if ($this->members->contains($teamMember)) {
            $this->members->removeElement($teamMember);
            // set the owning side to null (unless already changed)
            if ($teamMember->getTeam() === $this) {
                $teamMember->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TeamInvitation[]
     */
    public function getTeamInvitations(): ?Collection
    {
        return $this->invitations;
    }

    public function addTeamInvitation(TeamInvitation $teamInvitation): self
    {
        if (!$this->invitations->contains($teamInvitation)) {
            $this->invitations[] = $teamInvitation;
            $teamInvitation->setTeam($this);
        }

        return $this;
    }

    public function removeBoardInvitation(TeamInvitation $teamInvitation): self
    {
        if ($this->invitations->contains($teamInvitation)) {
            $this->invitations->removeElement($teamInvitation);
            // set the owning side to null (unless already changed)
            if ($teamInvitation->getTeam() === $this) {
                $teamInvitation->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TeamMember[]
     */
    public function getBoardTeams(): ?Collection
    {
        return $this->boardTeams;
    }

    public function addBoardTeam(BoardTeam $boardTeam): self
    {
        if (!$this->boardTeams->contains($boardTeam)) {
            $this->boardTeams[] = $boardTeam;
            $boardTeam->setTeam($this);
        }

        return $this;
    }

    public function removeBoardTeam(BoardTeam $boardTeam): self
    {
        if ($this->boardTeams->contains($boardTeam)) {
            $this->boardTeams->removeElement($boardTeam);
            // set the owning side to null (unless already changed)
            if ($boardTeam->getTeam() === $this) {
                $boardTeam->setTeam(null);
            }
        }

        return $this;
    }
}
