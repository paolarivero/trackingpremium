<?php
// src/NvCarga/Bundle/Entity/Role.php

namespace NvCarga\Bundle\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="role")
 * @ORM\HasLifecycleCallbacks()
 */
class Role implements RoleInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="user_roles")
     */
    protected $users;
    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $name;
    /**
     * @ORM\Column(type="text")
     */
    protected $description;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;

    public function getRole() {
        return $this->getName();
    }

    public function getRoleUsers()
    {
        return $this->users;
    }   

    public function __toString() {
        
        return (string) $this->getDescription();
        //return (string) $this->getName();// . ' Puede: ' . $this->getDescription();
    }
    public function getDisplayName() {
        $pos = strripos($this->getName(),'_');
        return (string) strtolower(substr($this->getName(), $pos+1));
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Role
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Role
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Role
     */
    public function setCreationdate($creationdate)
    {
        $this->creationdate = $creationdate;

        return $this;
    }

    /**
     * Get creationdate
     *
     * @return \DateTime
     */
    public function getCreationdate()
    {
        return $this->creationdate;
    }

    /**
     * Add user
     *
     * @param \NvCarga\Bundle\Entity\User $user
     *
     * @return Role
     */
    public function addUser(\NvCarga\Bundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \NvCarga\Bundle\Entity\User $user
     */
    public function removeUser(\NvCarga\Bundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }
}
