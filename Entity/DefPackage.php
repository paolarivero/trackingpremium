<?php
// src/NvCarga/Bundle/Entity/DefPackage.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="defpackage")
 * @ORM\HasLifecycleCallbacks()
 */
class DefPackage
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="Carrier")
     * @ORM\JoinColumn(name="carrier_id", referencedColumnName="id")
     */
    protected $carrier;
    /**
     * @ORM\Column(type="date")
     */
    protected $arrivedate;
     /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $tracking;
    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $reference;
    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $description;
     /**
     * @Assert\NotBlank(message = "Debe asignar la cantidad")
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "Debe tener al menos {{ limit }} paquete",
     * )
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $quantity;
   /**
     * @Assert\NotBlank(message = "Debe asignar el peso")
     * @Assert\Range(
     *      min = 1,
     *      max = 2000,
     *      minMessage = "El peso mínimo debe ser igual o mayor a {{ limit }}lb",
     *      maxMessage = "El peso debe ser menor a {{ limit }}lb"
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $weight;
   /**
     * @Assert\NotBlank(message = "Debe asignar una longitud")
     * @Assert\Range(
     *      min = 0.01,
     *      max = 500,
     *      minMessage = "La longitud mínima debe ser igual o mayor a {{ limit }}",
     *      maxMessage = "La longitud debe ser menor a {{ limit }}"
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $length;
   /**
     * @Assert\NotBlank(message = "Debe asignar un valor del ancho")
     * @Assert\Range(
     *      min = 0.01,
     *      max = 500,
     *      minMessage = "El ancho mínimo debe ser igual o mayor a {{ limit }}",
     *      maxMessage = "El ancho debe ser menor a {{ limit }}"
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $width;
   /**
     * @Assert\NotBlank(message = "Debe asignar una altura")
     * @Assert\Range(
     *      min = 0.01,
     *      max = 500,
     *      minMessage = "La altura mínima debe ser igual o mayor a {{ limit }}",
     *      maxMessage = "La altura debe ser menor a {{ limit }}"
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $height;
    /**
     * @Assert\NotBlank(message = "Coloque un valor para el paquete")
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "El valor declarado debe ser igual o mayor a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
    */
    protected $value;
    
    /**
     * @ORM\OneToOne(targetEntity="Maincompany", inversedBy="defpackage")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;
    /**
     * @Assert\NotBlank(message = "Debe asignar la cantidad de bultos")
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "Debe tener al menos {{ limit }} bultos",
     * )
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $npack;
    
    /** 
    * @ORM\Column(type="string", columnDefinition="ENUM('Caja', 'Sobre', 'Bulto')" ) 
    */ 
    protected $packtype;
    
    public function __construct()
    {
        $this->quantity = 1;
        $this->npack = 1;
        $this->packtype = 'Caja';
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
     * Set arrivedate
     *
     * @param \DateTime $arrivedate
     *
     * @return DefPackage
     */
    public function setArrivedate($arrivedate)
    {
        $this->arrivedate = $arrivedate;

        return $this;
    }

    /**
     * Get arrivedate
     *
     * @return \DateTime
     */
    public function getArrivedate()
    {
        return $this->arrivedate;
    }

    /**
     * Set tracking
     *
     * @param string $tracking
     *
     * @return DefPackage
     */
    public function setTracking($tracking)
    {
        $this->tracking = $tracking;

        return $this;
    }

    /**
     * Get tracking
     *
     * @return string
     */
    public function getTracking()
    {
        return $this->tracking;
    }

    /**
     * Set reference
     *
     * @param string $reference
     *
     * @return DefPackage
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return DefPackage
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
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return DefPackage
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set weight
     *
     * @param string $weight
     *
     * @return DefPackage
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return string
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set length
     *
     * @param string $length
     *
     * @return DefPackage
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length
     *
     * @return string
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set width
     *
     * @param string $width
     *
     * @return DefPackage
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return string
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param string $height
     *
     * @return DefPackage
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return string
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return DefPackage
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set npack
     *
     * @param integer $npack
     *
     * @return DefPackage
     */
    public function setNpack($npack)
    {
        $this->npack = $npack;

        return $this;
    }

    /**
     * Get npack
     *
     * @return integer
     */
    public function getNpack()
    {
        return $this->npack;
    }

    /**
     * Set packtype
     *
     * @param string $packtype
     *
     * @return DefPackage
     */
    public function setPacktype($packtype)
    {
        $this->packtype = $packtype;

        return $this;
    }

    /**
     * Get packtype
     *
     * @return string
     */
    public function getPacktype()
    {
        return $this->packtype;
    }

    /**
     * Set carrier
     *
     * @param \NvCarga\Bundle\Entity\Carrier $carrier
     *
     * @return DefPackage
     */
    public function setCarrier(\NvCarga\Bundle\Entity\Carrier $carrier = null)
    {
        $this->carrier = $carrier;

        return $this;
    }

    /**
     * Get carrier
     *
     * @return \NvCarga\Bundle\Entity\Carrier
     */
    public function getCarrier()
    {
        return $this->carrier;
    }

    /**
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return DefPackage
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
