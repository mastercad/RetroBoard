<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BoardMember
 *
 * @ORM\Table(
 *  name="board_teams",
 *  indexes={
 *      @ORM\Index(name="board_teams_team_fk", columns={"team"}),
 *      @ORM\Index(name="board_teams_board_fk", columns={"board"}),
 *      @ORM\Index(name="board_teams_modifier_fk", columns={"modifier"}),
 *      @ORM\Index(name="board_teams_creator_fk", columns={"creator"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\BoardMemberRepository"))
 */
class BoardTeam
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Team
     *
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="teams")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="team", referencedColumnName="id", nullable=false)
     * })
     */
    private $team;

    /**
     * @var Board
     *
     * @ORM\ManyToOne(targetEntity="Board", inversedBy="teams")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="board", referencedColumnName="id", nullable=false)
     * })
     */
    private $board;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="creator", referencedColumnName="id", nullable=false)
     * })
     */
    private $creator;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modifier", referencedColumnName="id", nullable=true)
     * })
     */
    private $modifier;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    private $modified;

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
     * Get the value of Team
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
     * @param Team  $team
     *
     * @return  self
     */ 
    public function setTeam(Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get the value of board
     *
     * @return  Board
     */ 
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * Set the value of board
     *
     * @param Board $board
     *
     * @return  self
     */ 
    public function setBoard(?Board $board)
    {
        $this->board = $board;

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
     * @param User  $creator
     *
     * @return self
     */ 
    public function setCreator(User $creator)
    {
        $this->creator = $creator;

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
     * @param \DateTime  $created
     *
     * @return  self
     */ 
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;

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
     * @param User  $modifier
     *
     * @return  self
     */ 
    public function setModifier(User $modifier)
    {
        $this->modifier = $modifier;

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
     * @return Collection|BoardMember[]
     */
    public function getBoardMember(): Collection
    {
        return $this->members;
    }

    public function addBoardMember(BoardMember $boardMember): self
    {
        if (!$this->teams->contains($boardMember)) {
            $this->teams[] = $boardMember;
            $boardMember->setBoardTeam($this);
        }

        return $this;
    }

    public function removeBoardTeam(BoardTeam $boardTeam): self
    {
        if ($this->members->contains($boardTeam)) {
            $this->members->removeElement($boardTeam);
            // set the owning side to null (unless already changed)
            if ($boardTeam->getBoard() === $this) {
                $boardTeam->setBoard(null);
            }
        }

        return $this;
    }
}