<?php
// src/NvCarga/Bundle/Entity/Profile.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="profile")
 * @ORM\HasLifecycleCallbacks()
 */
class Profile 
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @Assert\NotBlank(message = "El Nombre del perfil no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "El nombre del perfil debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre del perfil no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    protected $name;
    /**
     * @ORM\Column(type="text")
    */
    protected $description;
     /**
     * @ORM\ManyToMany(targetEntity="Role")
     */
    protected $roles;
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;
    /**
     * @var ArrayCollection
     */
    protected $admins;
    /**
     * @var ArrayCollection
     */
    protected $views;
    
    public function __toString() {
        return (string) $this->getName() . ' (' . $this->getDescription() . ')';
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rolesadmin = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rolesview = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Profile
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
     * @return Profile
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
     * Add role
     *
     * @param \NvCarga\Bundle\Entity\Role $role
     *
     * @return Profile
     */
    public function addRole(\NvCarga\Bundle\Entity\Role $role)
    {
        $this->roles[] = $role;

        return $this;
    }

    /**
     * Remove role
     *
     * @param \NvCarga\Bundle\Entity\Role $role
     */
    public function removeRole(\NvCarga\Bundle\Entity\Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Add admin
     *
     * @param \NvCarga\Bundle\Entity\Role $role
     *
     * @return Profile
     */
    public function addAdmin(\NvCarga\Bundle\Entity\Role $role)
    {
        $this->admins[] = $role;

        return $this;
    }

    /**
     * Remove admin
     *
     * @param \NvCarga\Bundle\Entity\Role $role
     */
    public function removeAdmin(\NvCarga\Bundle\Entity\Role $role)
    {
        $this->admins->removeElement($role);
    }

    /**
     * Get admins
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdmins()
    {
        return $this->admins;
    }
    
    /**
     * Add view
     *
     * @param \NvCarga\Bundle\Entity\Role $role
     *
     * @return Profile
     */
    public function addView(\NvCarga\Bundle\Entity\Role $role)
    {
        $this->views[] = $role;

        return $this;
    }

    /**
     * Remove view
     *
     * @param \NvCarga\Bundle\Entity\Role $role
     */
    public function removeView(\NvCarga\Bundle\Entity\Role $role)
    {
        $this->views->removeElement($role);
    }

    /**
     * Get views
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getViews()
    {
        return $this->views;
    }
    
    /**
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Profile
     */
    public function setMaincompany(\NvCarga\Bundle\Entity\Maincompany $maincompany = null)
    {
        $this->maincompany = $maincompany;

        return $this;
    }

    /**
     * Get maincompany
     *
     * @return \NvCarga\Bundle\Entity\Maincompany
     */
    public function getMaincompany()
    {
        return $this->maincompany;
    }
}
