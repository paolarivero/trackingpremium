<?php
// src/NvCarga/Bundle/Entity/Minipack.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class Minipack
{
   protected $number;
   /**
     * @Assert\NotBlank(message = "Debe asignar el peso")
     * @Assert\Range(
     *      min = 1,
     *      max = 2000,
     *      minMessage = "El peso mínimo debe ser igual o mayor a {{ limit }}lb",
     *      maxMessage = "El peso debe ser menor a {{ limit }}lb"
     * )
     */
    protected $weight;
   /**
     * @Assert\NotBlank(message = "Debe asignar una longitud")
     * @Assert\Range(
     *      min = 0.01,
     *      max = 500,
     *      minMessage = "La longitud mínima debe ser igual o mayor a {{ limit }}f",
     *      maxMessage = "La longitud debe ser menor a {{ limit }}f"
     * )
     */
    protected $length;
   /**
     * @Assert\NotBlank(message = "Debe asignar un valor del ancho")
     * @Assert\Range(
     *      min = 0.01,
     *      max = 500,
     *      minMessage = "El ancho mínimo debe ser igual o mayor a {{ limit }}f",
     *      maxMessage = "El ancho debe ser menor a {{ limit }}f"
     * )
     */
    protected $width;
   /**
     * @Assert\NotBlank(message = "Debe asignar una altura")
     * @Assert\Range(
     *      min = 0.01,
     *      max = 500,
     *      minMessage = "La altura mínima debe ser igual o mayor a {{ limit }}f",
     *      maxMessage = "La altura debe ser menor a {{ limit }}f"
     * )
     */
    protected $height;
    /**
     * @Assert\NotBlank(message = "Coloque un valor para el paquete")
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "El valor declarado debe ser igual o mayor a {{ limit }}",
     * )
    */
    protected $value;
    
    protected $tracking;
    /**
     * @Assert\NotBlank(message = "Debe asignar la cantidad de bultos")
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "Debe tener al menos {{ limit }} bultos",
     * )
     */
    protected $npack;
    
    protected $packtype;
    
    public function __construct()
    {
        $this->value = 0;
    }
    /**
     * Set tracking
     *
     * @param string $tracking
     *
     * @return Minipack
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
     * Set weight
     *
     * @param string $weight
     *
     * @return Receipt
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
     * @return Receipt
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
     * @return Receipt
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
     * @return Receipt
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
     * @return Receipt
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
     * Set number
     *
     * @param string $value
     *
     * @return Minipack
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }
    /**
     * Set npack
     *
     * @param integer $npack
     *
     * @return Receipt
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
     * @return Receipt
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
}
