<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ArchiveBoard
 *
 * @ORM\Table(
 *  name="boards_archive"
 * )
 * @ORM\Entity
 */
class ArchiveBoard
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=250, nullable=false)
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
     * @ORM\OneToMany(targetEntity="ArchiveBoardInvitation", mappedBy="archive_board", cascade={"refresh", "remove", "persist"}, orphanRemoval=true)
     */
    private $invitations;

    /**
     * @ORM\OneToMany(targetEntity="ArchiveBoardMember", mappedBy="archive_board", cascade={"refresh", "remove", "persist"}, orphanRemoval=true)
     */
    private $members;

    /**
     * @ORM\OneToMany(targetEntity="ArchiveColumn", mappedBy="archive_board", cascade={"refresh", "remove", "persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"priority" = "ASC"})
     */
    private $columns;

    public function __construct()
    {
        $this->columns = new ArrayCollection();
        $this->invitations = new ArrayCollection();
        $this->members = new ArrayCollection();
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
     * @return  \DateTime
     */ 
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set the value of created
     *
     * @param  \DateTime  $created
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
     * @return  \DateTime|null
     */ 
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set the value of modified
     *
     * @param  \DateTime|null  $modified
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
     * @return Collection|ArchiveColumn[]
     */
    public function getColumns(): Collection
    {
        return $this->archiveColumns;
    }

    public function addColumn(ArchiveColumn $archiveColumn): self
    {
        if (!$this->archiveColumns->contains($archiveColumn)) {
            $this->archiveColumns[] = $archiveColumn;
            $archiveColumn->setArchiveBoard($this);
        }

        return $this;
    }
    
    public function removeColumn(ArchiveColumn $archiveColumn): self
    {
        if ($this->archiveColumns->contains($archiveColumn)) {
            $this->archiveColumns->removeElement($archiveColumn);
            // set the owning side to null (unless already changed)
            if ($archiveColumn->getArchiveBoard() === $this) {
                $archiveColumn->setArchiveBoard(null);
            }
        }

        return $this;
    }

    public function setBoardMembers(Collection $archiveBoardMembers) : self
    {
        $this->members = $archiveBoardMembers;

        return $this;
    }

    /**
     * @return Collection|ArchiveBoardMember[]
     */
    public function getBoardMembers(): Collection
    {
        return $this->members;
    }

    public function addBoardMember(ArchiveBoardMember $archiveBoardMember): self
    {
        if (!$this->members->contains($archiveBoardMember)) {
            $this->members[] = $archiveBoardMember;
            $archiveBoardMember->setBoard($this);
        }

        return $this;
    }
    
    public function removeBoardMember(ArchiveBoardMember $archiveBoardMember): self
    {
        if ($this->members->contains($archiveBoardMember)) {
            $this->members->removeElement($archiveBoardMember);
            // set the owning side to null (unless already changed)
            if ($archiveBoardMember->getBoard() === $this) {
                $archiveBoardMember->setBoard(null);
            }
        }

        return $this;
    }

    public function setBoardInvitations(Collection $archiveBoardInvitations) : self
    {
        $this->invitations = $archiveBoardInvitations;

        return $this;
    }

    /**
     * @return Collection|ArchiveBoardInvitation[]
     */
    public function getBoardInvitations(): Collection
    {
        return $this->invitations;
    }

    public function addBoardInvitation(ArchiveBoardInvitation $archiveBoardInvitation): self
    {
        if (!$this->invitations->contains($archiveBoardInvitation)) {
            $this->invitations[] = $archiveBoardInvitation;
            $archiveBoardInvitation->setBoard($this);
        }

        return $this;
    }
    
    public function removeBoardInvitation(ArchiveBoardMember $archiveBoardInvitation): self
    {
        if ($this->invitations->contains($archiveBoardInvitation)) {
            $this->invitations->removeElement($archiveBoardInvitation);
            // set the owning side to null (unless already changed)
            if ($archiveBoardInvitation->getBoard() === $this) {
                $archiveBoardInvitation->setBoard(null);
            }
        }

        return $this;
    }
    
    public function __toString()
    {
        return $this->getName();
    }
}
