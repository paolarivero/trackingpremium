<?php
// src/NvCarga/Bundle/Entity/Region.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="region")
 * @ORM\HasLifecycleCallbacks()
 */
class Region
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @Assert\NotBlank(message = "El Nombre de la región no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 100,
     *      minMessage = "El nombre del región debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre del región no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $name;
     /**
     * @ORM\ManyToMany(targetEntity="City", inversedBy="regions")
     * @ORM\JoinTable(name="region_cities")
     */
    protected $region_cities;
     /**
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $country;
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;

    public function __toString() {
        return (string) $this->getName(); // . " (" . $this->getCountry() . ")";
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->region_cities = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return State
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
     * Add city
     *
     * @param \NvCarga\Bundle\Entity\City $city
     *
     * @return Region
     */
    public function addCity(\NvCarga\Bundle\Entity\City $city)
    {
        $this->region_cities[] = $city;

        return $this;
    }

    /**
     * Remove city
     *
     * @param \NvCarga\Bundle\Entity\City $city
     */
    public function removeCity(\NvCarga\Bundle\Entity\City $city)
    {
        $this->region_cities->removeElement($city);
    }

    /**
     * Get cities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRegionCities()
    {
        return $this->region_cities;
    }

    /**
     * Set country
     *
     * @param \NvCarga\Bundle\Entity\Country $country
     *
     * @return State
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
     * Add regionCity
     *
     * @param \NvCarga\Bundle\Entity\City $regionCity
     *
     * @return Region
     */
    public function addRegionCity(\NvCarga\Bundle\Entity\City $regionCity)
    {
        $this->region_cities[] = $regionCity;

        return $this;
    }

    /**
     * Remove regionCity
     *
     * @param \NvCarga\Bundle\Entity\City $regionCity
     */
    public function removeRegionCity(\NvCarga\Bundle\Entity\City $regionCity)
    {
        $this->region_cities->removeElement($regionCity);
    }

    /**
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Region
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
