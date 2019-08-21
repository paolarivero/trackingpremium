<?php
// src/NvCarga/Bundle/Entity/Guidestatus.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="guidestatus")
 * @ORM\HasLifecycleCallbacks()
 */
class Guidestatus
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", length=40, unique=true, nullable=false)
     */
    protected $name;
    /**
     * @ORM\Column(type="integer", unique=true, nullable=false)
     */
    protected $position;
     /**
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $country;
    /**
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    protected $latitude;
    /**
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    protected $longitude;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $address;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\Column(type="boolean", nullable=true)
    */
    protected $isinherited;

    /**
     * @ORM\Column(type="boolean", nullable=true)
    */
    protected $emailnoti = false;

    public function __toString() {
        return (string) $this->getName();
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
     * @return Guidestatus
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
     * Set latitude
     *
     * @param string $latitude
     *
     * @return Guidestatus
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return Guidestatus
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Guidestatus
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
     * @return Guidestatus
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
     * Set country
     *
     * @param \NvCarga\Bundle\Entity\Country $country
     *
     * @return Guidestatus
     */
    public function setCountry(\NvCarga\Bundle\Entity\Country $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return \NvCarga\Bundle\Entity\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Guidestatus
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set isinherited
     *
     * @param boolean $isinherited
     *
     * @return Guidestatus
     */
    public function setIsinherited($isinherited)
    {
        $this->isinherited = $isinherited;

        return $this;
    }

    /**
     * Get isinherited
     *
     * @return boolean
     */
    public function getIsinherited()
    {
        return $this->isinherited;
    }
    /**
     * Set emailnoti
     *
     * @param boolean $emailnoti
     *
     * @return Guide
     */
    public function setEmailnoti($emailnoti)
    {
        $this->emailnoti = $emailnoti;

        return $this;
    }

    /**
     * Get emailnoti
     *
     * @return boolean
     */
    public function getEmailnoti()
    {
        return $this->emailnoti;
    }
}
