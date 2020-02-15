<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Column
 *
 * @ORM\Table(
 *  name="columns",
 *  indexes={
 *      @ORM\Index(name="columns_board_fk", columns={"board_fk"})
 *  }
 * )
 * @ORM\Entity
 */
class Column
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
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var int|null
     *
     * @ORM\Column(name="priority", type="integer", nullable=true)
     */
    private $priority = '0';

    /**
     * @var Board
     *
     * @ORM\ManyToOne(targetEntity="Board", inversedBy="columns")
     * @ORM\OrderBy({"priority" = "ASC"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="board_fk", referencedColumnName="id")
     * })
     */
    private $board;

    /**
     * @ORM\OneToMany(targetEntity="Ticket", mappedBy="column", cascade={"refresh", "remove", "persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"created" = "DESC"})
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
     * @return Collection|Ticket[]
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets[] = $ticket;
            $ticket->setBoard($this);
        }

        return $this;
    }
    
    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->contains($ticket)) {
            $this->tickets->removeElement($ticket);
            // set the owning side to null (unless already changed)
            if ($ticket->getBoard() === $this) {
                $ticket->setBoard(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
}
