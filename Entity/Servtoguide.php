<?php
// src/NvCarga/Bundle/Entity/Servtoguide.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="servtoguide")
 * @ORM\HasLifecycleCallbacks()
 */
class Servtoguide
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $servicedate;
   /**
     * @Assert\NotBlank(message = "Debe asignar el cantidad")
     * @Assert\Range(
     *      min = 0.1,
     *      minMessage = "La cantidad mínimo debe ser igual o mayor a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $amount; 
   /**
     * @Assert\NotBlank(message = "Debe asignar el total")
     * @Assert\Range(
     *      min = 0.001,
     *      minMessage = "El total mínimo debe ser igual o mayor a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $total;  
   /** 
     * @ORM\ManyToOne(targetEntity="Adservice") 
     * @ORM\JoinColumn(name="adservice_id", referencedColumnName="id", nullable=false) 
     */
    protected $adservice;

    /** 
     * @ORM\ManyToOne(targetEntity="Guide") 
     * @ORM\JoinColumn(name="guide_id", referencedColumnName="id", nullable=false) 
     */
    protected $guide;

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
     * Set servicedate
     *
     * @param \DateTime $servicedate
     *
     * @return Servtoguide
     */
    public function setServicedate($servicedate)
    {
        $this->servicedate = $servicedate;

        return $this;
    }

    /**
     * Get servicedate
     *
     * @return \DateTime
     */
    public function getServicedate()
    {
        return $this->servicedate;
    }

    /**
     * Set amount
     *
     * @param string $amount
     *
     * @return Servtoguide
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set adservice
     *
     * @param \NvCarga\Bundle\Entity\Adservice $adservice
     *
     * @return Servtoguide
     */
    public function setAdservice(\NvCarga\Bundle\Entity\Adservice $adservice)
    {
        $this->adservice = $adservice;

        return $this;
    }

    /**
     * Get adservice
     *
     * @return \NvCarga\Bundle\Entity\Adservice
     */
    public function getAdservice()
    {
        return $this->adservice;
    }

    /**
     * Set guide
     *
     * @param \NvCarga\Bundle\Entity\Guide $guide
     *
     * @return Servtoguide
     */
    public function setGuide(\NvCarga\Bundle\Entity\Guide $guide)
    {
        $this->guide = $guide;

        return $this;
    }

    /**
     * Get guide
     *
     * @return \NvCarga\Bundle\Entity\Guide
     */
    public function getGuide()
    {
        return $this->guide;
    }

    /**
     * Set total
     *
     * @param string $total
     *
     * @return Servtoguide
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return string
     */
    public function getTotal()
    {
        return $this->total;
    }
}
