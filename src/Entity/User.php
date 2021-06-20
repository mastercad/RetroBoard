<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User.
 *
 * @ORM\Table(name="users", options={"auto_increment": 1}, indexes={
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
     * @ORM\Column(name="id", type="integer", length=11, columnDefinition="integer unsigned", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
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
     * @ORM\Column(name="avatar_path", type="string", length=250, nullable=true)
     */
    private $avatarPath;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=7, nullable=true)
     */
    private $color;

    /**
     * @var string
     *
     * @ORM\Column(name="google_id", type="string", length=255, nullable=true)
     */
    private $googleId;

    /**
     * @var string
     *
     * @ORM\Column(name="github_id", type="string", length=255, nullable=true)
     */
    private $githubId;

    /**
     * @var string
     *
     * @ORM\Column(name="microsoft_id", type="string", length=255, nullable=true)
     */
    private $microsoftId;

    /**
     * @var string
     *
     * @ORM\Column(name="okta_id", type="string", length=255, nullable=true)
     */
    private $oktaId;

    /**
     * @var json
     *
     * @ORM\Column(name="roles", type="json", length=250, nullable=false)
     */
    private $roles;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="creations")
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="modifications")
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
     * @var user
     *
     * One user has Many tickets
     * @ORM\OneToMany(
     *  targetEntity="User",
     *  mappedBy="modifier",
     *  cascade={"refresh", "remove", "persist"},
     *  orphanRemoval=true
     * )
     */
    private $modifications;

    /**
     * @var user
     *
     * One user has Many tickets
     * @ORM\OneToMany(
     *  targetEntity="User",
     *  mappedBy="creator",
     *  cascade={"refresh", "remove", "persist"},
     *  orphanRemoval=true
     * )
     */
    private $creations;

    public function __construct()
    {
        $this->boards = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->creations = new ArrayCollection();
        $this->modifications = new ArrayCollection();
        $this->roles = ['ROLE_GUEST'];
    }

    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password.
     *
     * @return self
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
        $roles[] = 'ROLE_GUEST';

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
//        $this->password = null;
//        $this->salt = null;
    }

    /**
     * Get the value of name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name.
     *
     * @return self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
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

    public function getGoogleId()
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId)
    {
        $this->googleId = $googleId;
    }

    public function getGithubId()
    {
        return $this->githubId;
    }

    public function setGithubId(?string $githubId)
    {
        $this->githubId = $githubId;
    }

    public function getMicrosoftId()
    {
        return $this->microsoftId;
    }

    public function setMicrosoftId(?string $microsoftId)
    {
        $this->microsoftId = $microsoftId;
    }

    public function getOktaId()
    {
        return $this->oktaId;
    }

    public function setOktaId(?string $oktaId)
    {
        $this->oktaId = $oktaId;
    }

    /**
     * Get the value of email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email.
     *
     * @param string $email
     *
     * @return self
     */
    public function setEmail($email)
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
     * Get the value of creator.
     *
     * @return User
     */
    public function getCreator()
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
     * @param User $modifier
     *
     * @return self
     */
    public function setModifier(?User $modifier)
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
     * @return self
     */
    public function setModified(?\DateTime $modified)
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
        return serialize([
            $this->id,
            $this->password,
            $this->email,
            $this->name,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->password,
            $this->email,
            $this->name) = unserialize($serialized, ['allowed_classes' => false]);
    }

    /**
     * Get one user has Many tickets.
     */
    public function getModifications()
    {
        return $this->modifications;
    }

    /**
     * Set one user has Many tickets.
     *
     * @return self
     */
    public function setModifications($modifications)
    {
        $this->modifications = $modifications;

        return $this;
    }

    public function addModifier(User $modifier): self
    {
        if (!$this->modifications->contains($modifier)) {
            $this->modifications[] = $modifier;
            $modifier->setModifier($this);
        }

        return $this;
    }

    public function removeModifier(User $modifier): self
    {
        if ($this->modifications->contains($modifier)) {
            $this->modifications->removeElement($modifier);
            // set the owning side to null (unless already changed)
            if ($modifier->getModifier() === $this) {
                $modifier->setModifier(null);
            }
        }

        return $this;
    }

    /**
     * Get one user has Many tickets.
     */
    public function getCreations()
    {
        return $this->creations;
    }

    /**
     * Set one user has Many tickets.
     *
     * @return self
     */
    public function setCreations($creations)
    {
        $this->creations = $creations;

        return $this;
    }

    public function addCreator(User $creator): self
    {
        if (!$this->creations->contains($creator)) {
            $this->creations[] = $creator;
            $creator->setCreator($this);
        }

        return $this;
    }

    public function removeCreator(User $creator): self
    {
        if ($this->creations->contains($creator)) {
            $this->creations->removeElement($creator);
            // set the owning side to null (unless already changed)
            if ($creator->getCreator() === $this) {
                $creator->setCreator(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return (string) $this->getId();
    }
}
