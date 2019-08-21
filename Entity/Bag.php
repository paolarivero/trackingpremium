<?php
// src/NvCarga/Bundle/Entity/Bag.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="bag")
 * @ORM\HasLifecycleCallbacks()
 */
class Bag
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
     /**
     * @ORM\Column(type="string", length=30, nullable=false, unique=false)
     */
    protected $number;
     /**
     * @ORM\ManyToOne(targetEntity="Agency")
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="id")
     */
    protected $agency;
     /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\OneToMany(targetEntity="Guide", mappedBy="bag")
     */
    protected $guides;
    /** 
    * @ORM\Column(type="string", columnDefinition="ENUM('LISTA', 'ENTREGADA', 'CONSOLIDADA')") 
    */ 
    protected $status;	
     /**
     * @ORM\ManyToOne(targetEntity="Maincompany")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;  
    public function __toString() {
        return (string) ($this->getNumber());
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->guides = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set number
     *
     * @param string $number
     *
     * @return Bag
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Bag
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
     * Set status
     *
     * @param string $status
     *
     * @return Bag
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set agency
     *
     * @param \NvCarga\Bundle\Entity\Agency $agency
     *
     * @return Bag
     */
    public function setAgency(\NvCarga\Bundle\Entity\Agency $agency = null)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency
     *
     * @return \NvCarga\Bundle\Entity\Agency
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * Add guide
     *
     * @param \NvCarga\Bundle\Entity\Guide $guide
     *
     * @return Bag
     */
    public function addGuide(\NvCarga\Bundle\Entity\Guide $guide)
    {
        $this->guides[] = $guide;

        return $this;
    }

    /**
     * Remove guide
     *
     * @param \NvCarga\Bundle\Entity\Guide $guide
     */
    public function removeGuide(\NvCarga\Bundle\Entity\Guide $guide)
    {
        $this->guides->removeElement($guide);
    }

    /**
     * Get guides
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGuides()
    {
        return $this->guides;
    }

    /**
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Bag
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
