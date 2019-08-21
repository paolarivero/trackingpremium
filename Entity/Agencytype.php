<?php
// src/NvCarga/Bundle/Entity/Agencytype.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="agencytype")
 * @ORM\HasLifecycleCallbacks()
 */
class Agencytype
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
   /**
     * @Assert\NotBlank(message = "El nombre del tipo de agencia no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 40,
     *      minMessage = "El nombre de tipo de agencia debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre de tipo de agencia no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=40, unique=true, nullable=false)
     */
    protected $name;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\OneToMany(targetEntity="Agency", mappedBy="type")
     */
    protected $agencies;

    public function __toString() {
        return (string) $this->getName();
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->agencies = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Agencytype
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
     * @return Agencytype
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
     * @return Agencytype
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
     * Add agency
     *
     * @param \NvCarga\Bundle\Entity\Agency $agency
     *
     * @return Agencytype
     */
    public function addAgency(\NvCarga\Bundle\Entity\Agency $agency)
    {
        $this->agencies[] = $agency;

        return $this;
    }

    /**
     * Remove agency
     *
     * @param \NvCarga\Bundle\Entity\Agency $agency
     */
    public function removeAgency(\NvCarga\Bundle\Entity\Agency $agency)
    {
        $this->agencies->removeElement($agency);
    }

    /**
     * Get agencies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAgencies()
    {
        return $this->agencies;
    }
}
