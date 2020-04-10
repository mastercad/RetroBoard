<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;

/**
 * Board
 *
 * @ORM\Table(
 *  name="boards",
 *  indexes={
 *      @ORM\Index(name="boards_modifier_fk", columns={"modifier"}),
 *      @ORM\Index(name="boards_creator_fk", columns={"creator"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\BoardRepository"))
 */
class Board
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", length=11, columnDefinition="integer unsigned", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @Constraints\NotBlank
     * @ORM\Column(name="name", type="string", length=250, nullable=false)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @Constraints\NotBlank
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
     * @Constraints\NotBlank
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="creator", columnDefinition="integer unsigned", referencedColumnName="id", nullable=false)
     * })
     */
    private $creator;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modifier", columnDefinition="integer unsigned", referencedColumnName="id")
     * })
     */
    private $modifier;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="BoardInvitation", mappedBy="board", cascade={"refresh", "remove", "persist"}, orphanRemoval=true)
     */
    private $invitations;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="BoardMember", mappedBy="board", cascade={"refresh", "remove", "persist"}, orphanRemoval=true)
     */
    private $boardMembers;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="BoardTeam", mappedBy="board", cascade={"refresh", "remove", "persist"}, orphanRemoval=true)
     */
    private $boardTeams;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="BoardSubscriber", mappedBy="board", cascade={"refresh", "remove", "persist"}, orphanRemoval=true)
     */
    private $subscribers;

    /**
     * @var Collection
     *
     * @Constraints\NotBlank
     *
     * @ORM\OneToMany(targetEntity="Column", mappedBy="board", cascade={"refresh", "remove", "persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"priority" = "ASC"})
     */
    private $columns;

    public function __construct()
    {
        $this->columns = new ArrayCollection();
        $this->invitations = new ArrayCollection();
        $this->boardMembers = new ArrayCollection();
        $this->boardTeams = new ArrayCollection();
    }

    /**
     * Get the value of id
     *
     * @return  int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param  int  $id
     *
     * @return  self
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of name
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param  string  $name
     *
     * @return  self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set the value of created
     *
     * @param \DateTime $created
     *
     * @return  self
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get the value of modified
     *
     * @return \DateTime|null
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set the value of modified
     *
     * @param \DateTime|null  $modified
     *
     * @return  self
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get the value of creator
     *
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set the value of creator
     *
     * @param User $creator
     *
     * @return  self
     */
    public function setCreator(User $creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get the value of modifier
     *
     * @return User
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * Set the value of modifier
     *
     * @param User $modifier
     *
     * @return  self
     */
    public function setModifier(User $modifier)
    {
        $this->modifier = $modifier;

        return $this;
    }

    /**
     * @return Collection|Column[]
     */
    public function getColumns(): Collection
    {
        return $this->columns;
    }

    public function addColumn(Column $column): self
    {
        if (!$this->columns->contains($column)) {
            $this->columns[] = $column;
            $column->setBoard($this);
        }

        return $this;
    }

    public function removeColumn(Column $column): self
    {
        if ($this->columns->contains($column)) {
            $this->columns->removeElement($column);
            // set the owning side to null (unless already changed)
            if ($column->getBoard() === $this) {
                $column->setBoard(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BoardMember[]
     */
    public function getBoardMembers(): Collection
    {
        return $this->boardMembers;
    }

    public function addBoardMember(BoardMember $boardMember): self
    {
        if (!$this->boardMembers->contains($boardMember)) {
            $this->boardMembers[] = $boardMember;
            $boardMember->setBoard($this);
        }

        return $this;
    }

    public function removeBoardMember(BoardMember $boardMember): self
    {
        if ($this->boardMembers->contains($boardMember)) {
            $this->boardMembers->removeElement($boardMember);
            // set the owning side to null (unless already changed)
            if ($boardMember->getBoard() === $this) {
                $boardMember->setBoard(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BoardTeam[]
     */
    public function getBoardTeams(): Collection
    {
        return $this->boardTeams;
    }

    public function addBoardTeam(BoardTeam $boardTeam): self
    {
        if (!$this->boardTeams->contains($boardTeam)) {
            $this->boardTeams[] = $boardTeam;
            $boardTeam->setBoard($this);
        }

        return $this;
    }

    public function removeBoardTeam(BoardTeam $boardTeam): self
    {
        if ($this->boardTeams->contains($boardTeam)) {
            $this->boardTeams->removeElement($boardTeam);
            // set the owning side to null (unless already changed)
            if ($boardTeam->getBoard() === $this) {
                $boardTeam->setBoard(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BoardSubscriber[]
     */
    public function getBoardSubscribers(): Collection
    {
        return $this->subscribers;
    }

    public function addBoardSubscriber(BoardSubscriber $boardSubscriber): self
    {
        if (!$this->subscribers->contains($boardSubscriber)) {
            $this->subscribers[] = $boardSubscriber;
            $boardSubscriber->setBoard($this);
        }

        return $this;
    }

    public function removeBoardSubscriber(BoardSubscriber $boardSubscriber): self
    {
        if ($this->subscribers->contains($boardSubscriber)) {
            $this->subscribers->removeElement($boardSubscriber);
            // set the owning side to null (unless already changed)
            if ($boardSubscriber->getBoard() === $this) {
                $boardSubscriber->setBoard(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BoardInvitation[]
     */
    public function getBoardInvitations(): Collection
    {
        return $this->invitations;
    }

    public function addBoardInvitation(BoardInvitation $boardInvitation): self
    {
        if (!$this->invitations->contains($boardInvitation)) {
            $this->invitations[] = $boardInvitation;
            $boardInvitation->setBoard($this);
        }

        return $this;
    }

    public function removeBoardInvitation(BoardInvitation $boardInvitation): self
    {
        if ($this->invitations->contains($boardInvitation)) {
            $this->invitations->removeElement($boardInvitation);
            // set the owning side to null (unless already changed)
            if ($boardInvitation->getBoard() === $this) {
                $boardInvitation->setBoard(null);
            }
        }

        return $this;
    }

//    public function __toString()
//    {
//        return $this->getName();
//    }
}
