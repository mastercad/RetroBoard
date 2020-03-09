<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TeamMembers
 *
 * @ORM\Table(name="team_members", uniqueConstraints={@ORM\UniqueConstraint(name="team_members_UN", columns={"id", "member"})}, indexes={@ORM\Index(name="team_members_creator_IDX", columns={"creator"}), @ORM\Index(name="team_members_id_IDX", columns={"id"}), @ORM\Index(name="team_members_member_IDX", columns={"member"}), @ORM\Index(name="team_members_modifier_IDX", columns={"modifier"})})
 * @ORM\Entity
 */
class TeamMember
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="member", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $member;

    /**
     * @var int
     *
     * @ORM\Column(name="created", type="integer", nullable=false, options={"unsigned"=true})
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


}
