<?php
// src/NvCarga/Bundle/Entity/Tax.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="tax")
 * @ORM\HasLifecycleCallbacks()
 */
class Tax
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
     /**
     * @ORM\ManyToOne(targetEntity="Servicetype")
     * @ORM\JoinColumn(name="servicetype_id", referencedColumnName="id")
     */
    protected $service;
     /**
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $country;
    /**
     * @ORM\ManyToOne(targetEntity="Agency")
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="id")
     */
    protected $agency;
   /** 
     * @ORM\Column(type="integer") 
    */
    protected $master_tax; // Porcentaje de impuesto para la agencia master
   /** 
     * @ORM\Column(type="integer") 
    */
    protected $agency_tax; // Porcentaje de impuesto de la agencia

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
     * Set masterTax
     *
     * @param integer $masterTax
     *
     * @return Tax
     */
    public function setMasterTax($masterTax)
    {
        $this->master_tax = $masterTax;

        return $this;
    }

    /**
     * Get masterTax
     *
     * @return integer
     */
    public function getMasterTax()
    {
        return $this->master_tax;
    }

    /**
     * Set agencyTax
     *
     * @param integer $agencyTax
     *
     * @return Tax
     */
    public function setAgencyTax($agencyTax)
    {
        $this->agency_tax = $agencyTax;

        return $this;
    }

    /**
     * Get agencyTax
     *
     * @return integer
     */
    public function getAgencyTax()
    {
        return $this->agency_tax;
    }

    /**
     * Set service
     *
     * @param \NvCarga\Bundle\Entity\Servicetype $service
     *
     * @return Tax
     */
    public function setService(\NvCarga\Bundle\Entity\Servicetype $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return \NvCarga\Bundle\Entity\Servicetype
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set country
     *
     * @param \NvCarga\Bundle\Entity\Country $country
     *
     * @return Tax
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
     * Set agency
     *
     * @param \NvCarga\Bundle\Entity\Agency $agency
     *
     * @return Tax
     */
    public function setAgency(\NvCarga\Bundle\Entity\Agency $agency = null)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency
     *
     * @return \NvCarga\Bundle\Entity\Agency
     */
    public function getAgency()
    {
        return $this->agency;
    }
}
