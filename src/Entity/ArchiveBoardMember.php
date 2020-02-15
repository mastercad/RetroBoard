<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ArchiveBoardMember
 *
 * @ORM\Table(
 *  name="board_members_achive"
 * )
 * @ORM\Entity
 */
class ArchiveBoardMember
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false)
     * })
     */
    private $user;

    /**
     * @var json
     *
     * @ORM\Column(name="roles", type="json", length=250, nullable=false)
     */
    private $roles;

    /**
     * @var ArchiveBoard
     *
     * @ORM\ManyToOne(targetEntity="ArchiveBoard", inversedBy="members")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="board", referencedColumnName="id", nullable=false)
     * })
     */
    private $archiveBoard;

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

    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
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
     * Get the value of User
     *
     * @return User
     */ 
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @param User $user
     *
     * @return  self
     */ 
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of board
     *
     * @return ArchiveBoard
     */ 
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * Set the value of board
     *
     * @param ArchiveBoard $archiveBoard
     *
     * @return  self
     */ 
    public function setBoard(?ArchiveBoard $archiveBoard)
    {
        $this->board = $archiveBoard;

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
        $this->roles = $roles;
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
}