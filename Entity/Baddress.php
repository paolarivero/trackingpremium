<?php
// src/NvCarga/Bundle/Entity/Baddress.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="baddress")
 * @ORM\HasLifecycleCallbacks()
 */
class Baddress
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
     *      max = 255,
     *      minMessage = "El dirección del cliente debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El dirección del cliente no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="text", length=255, nullable=false))
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
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="baddress")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    protected $customer;
   /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="customers")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    protected $city;

    protected $completedir;
    
    public function __toString() {
        return (string) ($this->name . ' ' . $this->lastname ); 
    }
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
     * @return Baddress
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
     * @return Baddress
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
     * @return Baddress
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
     * @return Baddress
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
     * @return Baddress
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
     * @return Baddress
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
     * @return Baddress
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
     * @return Baddress
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
     * Set customer
     *
     * @param \NvCarga\Bundle\Entity\Customer $customer
     *
     * @return Baddress
     */
    public function setCustomer(\NvCarga\Bundle\Entity\Customer $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \NvCarga\Bundle\Entity\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set docid
     *
     * @param string $docid
     *
     * @return Baddress
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
