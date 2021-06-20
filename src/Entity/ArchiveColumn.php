<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ArchiveColumn
 *
 * @ORM\Table(
 *  name="columns_archive"
 * )
 * @ORM\Entity
 */
class ArchiveColumn
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
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var int|null
     *
     * @ORM\Column(name="priority", length=2, type="integer", nullable=true)
     */
    private $priority = '0';

    /**
     * @var ArchiveBoard
     *
     * @ORM\ManyToOne(targetEntity="ArchiveBoard", inversedBy="columns")
     * @ORM\OrderBy({"priority" = "ASC"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="board", columnDefinition="integer unsigned", referencedColumnName="id", nullable=false)
     * })
     */
    private $board;

    /**
     * @ORM\OneToMany(
     *  targetEntity="ArchiveTicket",
     *  mappedBy="column",
     *  cascade={"refresh", "remove", "persist"},
     *  orphanRemoval=true
     * )
     * @ORM\OrderBy({"created" = "ASC"})
     */
    private $tickets;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
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
    public function setId(?int $id)
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
     * Get the value of priority
     *
     * @return  int|null
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set the value of priority
     *
     * @param  int|null  $priority
     *
     * @return  self
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

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
     * @param ArchiveBoard $board
     *
     * @return  self
     */
    public function setBoard(?ArchiveBoard $board)
    {
        $this->board = $board;

        return $this;
    }

    /**
     * @return Collection|ArchiveTicket[]
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(ArchiveTicket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets[] = $ticket;
            $ticket->setBoard($this);
        }

        return $this;
    }
    
    public function removeTicket(ArchiveTicket $ticket): self
    {
        if ($this->tickets->contains($ticket)) {
            $this->tickets->removeElement($ticket);
            // set the owning side to null (unless already changed)
            if ($ticket->getColumn() === $this) {
                $ticket->setColumn(null);
            }
        }

        return $this;
    }
}
