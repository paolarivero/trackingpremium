<?php
// src/NvCarga/Bundle/Entity/DefReceiver.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="defreceiver")
 * @ORM\HasLifecycleCallbacks()
 */
class DefReceiver
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
     /**
     * @Assert\NotBlank(message = "El Nombre del cliente en la dirección no puede estar vacío")
     * @Assert\Length(
     *      min = 2,
     *      max = 40,
     *      minMessage = "El nombre del cliente debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre del cliente no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=40, nullable=false)
     */
    protected $name;
     /**
     * @Assert\Length(
     *      min = 0,
     *      max = 40,
     *      minMessage = "El apellido del cliente debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El apellido del cliente no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    protected $lastname;
    /**
     * @Assert\NotBlank(message = "La dirección del cliente no puede estar vacía")
     * @Assert\Length(
     *      min = 4,
     *      max = 120,
     *      minMessage = "El dirección del cliente debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El dirección del cliente no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="text", length=120, nullable=false))
     */
    protected $address;
     /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $phone;
     /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $mobile;
     /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $barrio;
     /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $zip;
     /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $docid;
   /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="customers")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    protected $city;
    /**
     * @ORM\OneToOne(targetEntity="Maincompany", inversedBy="defreceiver")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;

    protected $completedir;
    
    public function getCompletedir() {
        return (string) ($this->name . ' ' . $this->lastname . '. ' . $this->address . '. ' . $this->getCity() . ', '. $this->getCity()->getState() . ' (' . $this->getCity()->getState()->getCountry() .')' );
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
     * @return DefReceiver
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
     * @return DefReceiver
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
     * Set lastname
     *
     * @param string $lastname
     *
     * @return DefReceiver
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return DefReceiver
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
     * Set mobile
     *
     * @param string $mobile
     *
     * @return DefReceiver
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set barrio
     *
     * @param string $barrio
     *
     * @return DefReceiver
     */
    public function setBarrio($barrio)
    {
        $this->barrio = $barrio;

        return $this;
    }

    /**
     * Get barrio
     *
     * @return string
     */
    public function getBarrio()
    {
        return $this->barrio;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return DefReceiver
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
     * Set city
     *
     * @param \NvCarga\Bundle\Entity\City $city
     *
     * @return DefReceiver
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
     * Set docid
     *
     * @param string $docid
     *
     * @return DefReceiver
     */
    public function setDocid($docid)
    {
        $this->docid = $docid;

        return $this;
    }

    /**
     * Get docid
     *
     * @return string
     */
    public function getDocid()
    {
        return $this->docid;
    }
}
