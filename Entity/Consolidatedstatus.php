<?php
// src/NvCarga/Bundle/Entity/Consolidatedstatus.php

namespace NvCarga\Bundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="consolidatedtatus")
 * @ORM\HasLifecycleCallbacks()
 */
class Consolidatedstatus
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
     /**
     * @Assert\NotBlank(message = "El Nombre del status no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 40,
     *      minMessage = "El nombre de la status debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre de la status no puede tener mas de {{ limit }} caracteres"
     * )
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
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $creationdate;
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $inherited;

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
     * @return Consolidatedstatus
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
     * @return Consolidatedstatus
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
     * @return Consolidatedstatus
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
     * @return Consolidatedstatus
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
     * @return Consolidatedstatus
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
     * @return Consolidatedstatus
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
     * Set inherited
     *
     * @param boolean $inherited
     *
     * @return Consolidatedstatus
     */
    public function setInherited($inherited)
    {
        $this->inherited = $inherited;

        return $this;
    }

    /**
     * Get inherited
     *
     * @return boolean
     */
    public function getInherited()
    {
        return $this->inherited;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Consolidatedstatus
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
}
