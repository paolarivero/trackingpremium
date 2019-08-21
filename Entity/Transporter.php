<?php
// src/NvCarga/Bundle/Entity/Transporter.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="transporter")
 * @ORM\HasLifecycleCallbacks()
 */
class Transporter
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

     /**
     * @ORM\Column(type="string", length=50,  unique=true, nullable=false))
     */
    protected $name;
     /**
     * @ORM\Column(type="string", length=20)
     */
    protected $phone;
     /**
     * @ORM\Column(type="string", length=20)
     */
    protected $fax;
     /**
     * @ORM\Column(type="string", length=30, nullable=false)
     */
    protected $contact;
     /**
     * @ORM\Column(type="string", length=20)
     */
    protected $zip;
    /**
     * @ORM\Column(type="string", length=60)
     */
    protected $address;
   /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    protected $city;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
   /**
     * @ORM\Column(type="integer")
     */
    protected $customs_area;
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;
    public function __toString() {
        return (string) ($this->getName() . ' ' . $this->getLastname());
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
     * @return Transporter
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
     * Set phone
     *
     * @param string $phone
     *
     * @return Transporter
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set fax
     *
     * @param string $fax
     *
     * @return Transporter
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set contact
     *
     * @param string $contact
     *
     * @return Transporter
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return Transporter
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
     * Set address
     *
     * @param string $address
     *
     * @return Transporter
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
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Transporter
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
     * Set customsArea
     *
     * @param integer $customsArea
     *
     * @return Transporter
     */
    public function setCustomsArea($customsArea)
    {
        $this->customs_area = $customsArea;

        return $this;
    }

    /**
     * Get customsArea
     *
     * @return integer
     */
    public function getCustomsArea()
    {
        return $this->customs_area;
    }

    /**
     * Set city
     *
     * @param \NvCarga\Bundle\Entity\City $city
     *
     * @return Transporter
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
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Transporter
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
