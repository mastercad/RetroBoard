<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * BoardMember
 *
 * @ORM\Table(
 *  name="board_members",
 *  indexes={
 *      @ORM\Index(name="board_member_board_fk", columns={"board"}),
 *      @ORM\Index(name="board_member_user_fk", columns={"user"}),
 *      @ORM\Index(name="board_member_modifier_fk", columns={"modifier"}),
 *      @ORM\Index(name="board_member_creator_fk", columns={"creator"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\BoardMemberRepository"))
 */
class BoardMember
{
    private $tokenStorage;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", length=11, columnDefinition="integer unsigned", nullable=false)
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
     * @var array
     *
     * @ORM\Column(name="roles", type="json", length=250, nullable=false)
     */
    private $roles;

    /**
     * @var Board
     *
     * @ORM\ManyToOne(targetEntity="Board", inversedBy="boardMembers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="board", columnDefinition="integer unsigned", referencedColumnName="id", nullable=false)
     * })
     */
    private $board;

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
     *   @ORM\JoinColumn(name="modifier", columnDefinition="integer unsigned", referencedColumnName="id", nullable=true)
     * })
     */
    private $modifier;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    private $modified;

//    public function __construct(UserInterface $user)
//    public function __construct(TokenStorageInterface $tokenStorage)
    public function __construct()
    {
//        var_dump("UserName: ".$user->getId());
//        $this->tokenStorage = $tokenStorage;
//        $this->creator = $tokenStorage->getToken()->getUser();
        $this->created = new \DateTime("now");
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
     * @param User  $user
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

    public function addRole(string $role)
    {
        $this->roles[] = $role;
        return $this;
    }

    public function removeRole(string $role)
    {
        if (in_array($role, $this->roles)) {
            $index = array_search($role, $this->roles);
            unset($this->roles[$index]);
        }
        return $this;
    }

    /**
     * Get the value of creator
     *
     * @return User
     */
    public function getCreator()
    {
//        if (null === $this->creator) {
//            var_dump("User in getCreator: ".$this->tokenStorage->getToken()->getUser()->getName());
//            return $this->tokenStorage->getToken()->getUser();
//        }
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

    public function __toString()
    {
        return $this->getId();
    }
}
