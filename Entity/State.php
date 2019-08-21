<?php
// src/NvCarga/Bundle/Entity/State.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="state")
 * @ORM\HasLifecycleCallbacks()
 */
class State
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @Assert\NotBlank(message = "El Nombre de la estado no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "El nombre del estado debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre del estado no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    protected $name;
    /**
     * @ORM\OneToMany(targetEntity="City", mappedBy="state")
     */
    protected $cities;
     /**
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="states")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $country;

    public function __toString() {
        return (string) $this->getName();
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cities = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return State
     */
    public function addCity(\NvCarga\Bundle\Entity\City $city)
    {
        $this->cities[] = $city;

        return $this;
    }

    /**
     * Remove city
     *
     * @param \NvCarga\Bundle\Entity\City $city
     */
    public function removeCity(\NvCarga\Bundle\Entity\City $city)
    {
        $this->cities->removeElement($city);
    }

    /**
     * Get cities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCities()
    {
        return $this->cities;
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
    
    public function getNumcities()
    {
        $count = count($this->getCities());
        return $count;
    }
}
