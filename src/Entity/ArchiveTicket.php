<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ArchiveTicket.
 *
 * @ORM\Table(name="archive_tickets")
 * @ORM\Entity
 */
class ArchiveTicket
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
     * @ORM\Column(name="content", type="text", length=65535, nullable=false)
     */
    private $content;

    /**
     * @var ArchiveColumn
     *
     * @ORM\ManyToOne(targetEntity="ArchiveColumn", inversedBy="tickets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="column_fk", referencedColumnName="id")
     * })
     */
    private $column;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="creator", columnDefinition="integer unsigned", referencedColumnName="id", nullable=false)
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
     *   @ORM\JoinColumn(name="modifier", columnDefinition="integer unsigned", referencedColumnName="id")
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
     * @var archiveVoting
     *
     * One user has Many tickets
     * @ORM\OneToMany(
     *  targetEntity="ArchiveVoting",
     *  mappedBy="ticket",
     *  cascade={"refresh", "remove", "persist"},
     *  orphanRemoval=true
     * )
     */
    private $votings;

    public function __construct()
    {
        $this->votings = new ArrayCollection();
    }

    /**
     * Get the value of id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id.
     *
     * @return self
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the value of content.
     *
     * @return self
     */
    public function setContent(string $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the value of column.
     *
     * @return Column
     */
    public function getColumn(): ArchiveColumn
    {
        return $this->column;
    }

    /**
     * Set the value of column.
     *
     * @param Column $column
     */
    public function setColumn(?ArchiveColumn $column): self
    {
        $this->column = $column;

        return $this;
    }

    /**
     * Get the value of creator.
     */
    public function getCreator(): User
    {
        return $this->creator;
    }

    /**
     * Set the value of creator.
     *
     * @return self
     */
    public function setCreator(User $creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get the value of created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set the value of created.
     *
     * @return self
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get the value of modifier.
     *
     * @return User
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * Set the value of modifier.
     *
     * @return self
     */
    public function setModifier(User $modifier)
    {
        $this->modifier = $modifier;

        return $this;
    }

    /**
     * Get the value of modified.
     *
     * @return \DateTime|null
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set the value of modified.
     *
     * @param \DateTime|null $modified
     *
     * @return self
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get one user has Many tickets.
     */
    public function getVotings(): ArchiveVoting
    {
        return $this->votings;
    }

    /**
     * Set one user has Many tickets.
     */
    public function setVotings($votings): self
    {
        $this->votings = $votings;

        return $this;
    }

    public function addVoting(ArchiveVoting $voting): self
    {
        if (!$this->votings->contains($voting)) {
            $this->votings[] = $voting;
            $voting->setTicket($this);
        }

        return $this;
    }

    public function removeVoting(ArchiveVoting $voting): self
    {
        if ($this->votings->contains($voting)) {
            $this->votings->removeElement($voting);
            // set the owning side to null (unless already changed)
            if ($voting->getTicket() === $this) {
                $voting->setTicket(null);
            }
        }

        return $this;
    }
}
