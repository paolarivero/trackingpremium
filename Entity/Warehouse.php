<?php
// src/NvCarga/Bundle/Entity/Warehouse.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="warehouse")
 * @ORM\HasLifecycleCallbacks()
 */
class Warehouse
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

     /**
     * @ORM\Column(type="string", length=80, nullable=false)
     */
    protected $name;

     /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected $address;
     /**
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    protected $zip;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="warehouses")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    protected $city;
     /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastupdate;
    /**
     * @ORM\OneToOne(targetEntity="Agency", mappedBy="warehouse")
     */
    protected $agency;
    /**
     * @ORM\OneToMany(targetEntity="Pobox", mappedBy="warehouse")
     */
    protected $poboxs;
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany", inversedBy="agencies")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;
    
    public function __toString() {
        return (string) $this->getName() . ' ' . $this->getAgency();
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
     * @return Warehouse
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
     * Set address
     *
     * @param string $address
     *
     * @return Warehouse
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return Warehouse
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Warehouse
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
     * Set description
     *
     * @param string $description
     *
     * @return Warehouse
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
     * Set lastupdate
     *
     * @param \DateTime $lastupdate
     *
     * @return Warehouse
     */
    public function setLastupdate($lastupdate)
    {
        $this->lastupdate = $lastupdate;

        return $this;
    }

    /**
     * Get lastupdate
     *
     * @return \DateTime
     */
    public function getLastupdate()
    {
        return $this->lastupdate;
    }

    /**
     * Set city
     *
     * @param \NvCarga\Bundle\Entity\City $city
     *
     * @return Warehouse
     */
    public function setCity(\NvCarga\Bundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \NvCarga\Bundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set agency
     *
     * @param \NvCarga\Bundle\Entity\Agency $agency
     *
     * @return Warehouse
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
     * Constructor
     */
    public function __construct()
    {
        $this->poboxs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add pobox
     *
     * @param \NvCarga\Bundle\Entity\Pobox $pobox
     *
     * @return Warehouse
     */
    public function addPobox(\NvCarga\Bundle\Entity\Pobox $pobox)
    {
        $this->poboxs[] = $pobox;

        return $this;
    }

    /**
     * Remove pobox
     *
     * @param \NvCarga\Bundle\Entity\Pobox $pobox
     */
    public function removePobox(\NvCarga\Bundle\Entity\Pobox $pobox)
    {
        $this->poboxs->removeElement($pobox);
    }

    /**
     * Get poboxs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPoboxs()
    {
        return $this->poboxs;
    }

    /**
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Warehouse
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
