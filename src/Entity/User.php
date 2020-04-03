<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Serializable;

/**
 * User
 *
 * @ORM\Table(name="users", indexes={
 *     @ORM\Index(name="retro_modifier_fk", columns={"modifier"}),
 *     @ORM\Index(name="retro_creator_fk", columns={"creator"})}
 * )
 * @ORM\Entity
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface, EquatableInterface, Serializable
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
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=250, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=250, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=250, nullable=true)
     */
    private $salt;

    /**
     * @var string
     *
     * @ORM\Column(name="activity_token", type="string", length=250, nullable=true)
     */
    private $activityToken;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar_path", type="string", length=250, nullable=false)
     */
    private $avatarPath;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=7, nullable=false)
     */
    private $color;

    /**
     * @var json
     *
     * @ORM\Column(name="roles", type="json", length=250, nullable=false)
     */
    private $roles;

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
     *   @ORM\JoinColumn(name="modifier", referencedColumnName="id")
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
        $this->boards = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->roles = ['ROLE_USER'];
    }

    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @param  string  $password
     *
     * @return  self
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

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

    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }

    public function getUsername()
    {
        return $this->name;
    }

    public function setUsername($username)
    {
        $this->name = $username;
        return $this;
    }

    public function eraseCredentials()
    {
        $this->password = null;
        $this->salt = null;
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
     * @param string  $name
     *
     * @return self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
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
     * Get the value of email
     *
     * @return  string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @param  string  $email
     *
     * @return  self
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    public function getActivityToken()
    {
        return $this->activityToken;
    }

    public function setActivityToken(?string $activityToken)
    {
        $this->activityToken = $activityToken;
        return $this;
    }

    public function getAvatarPath()
    {
        return $this->avatarPath;
    }

    public function setAvatarPath($avatarPath)
    {
        $this->avatarPath = $avatarPath;
        return $this;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        $this->color = $color;
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

    public function isEqualTo(UserInterface $user)
    {
//        if ($this->password !== $user->getPassword()) {
//            return false;
//        }

//        if ($this->salt !== $user->getSalt()) {
//            return false;
//        }

        if ($this->name !== $user->getUsername()) {
            return false;
        }
        return true;
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->password,
            $this->email,
            $this->name,
        ));
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->password,
            $this->email,
            $this->name,
        ) = unserialize($serialized, array('allowed_classes' => false));
    }

    public function __toString()
    {
        return (string)$this->getId();
    }
}
