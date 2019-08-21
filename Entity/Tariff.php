<?php
// src/NvCarga/Bundle/Entity/Tariff.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="tariff")
 * @ORM\HasLifecycleCallbacks()
 */
class Tariff
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @Assert\NotBlank(message = "El nombre de la tarifa no puede estar vacío")
     * @Assert\Length(
     *      min = 5,
     *      max = 50,
     *      minMessage = "La longitud mínima debe ser igual o mayor a {{ limit }}$",
     *      maxMessage = "La longitud debe ser menor a {{ limit }}$")
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    protected $name;
    /**
     * @ORM\ManyToOne(targetEntity="Region")
     * @ORM\JoinColumn(name="region_id", referencedColumnName="id")
     */
    protected $region;
    /**
     * @ORM\ManyToOne(targetEntity="Agency")
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="id")
     */
    protected $agency;
    /**
     * @ORM\ManyToOne(targetEntity="Shippingtype")
     * @ORM\JoinColumn(name="shippingtype_id", referencedColumnName="id")
     */
    protected $shippingtype;
    /**
     * @ORM\ManyToOne(targetEntity="Servicetype")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id")
     */
    protected $service;
    /**
     * @ORM\ManyToOne(targetEntity="Measure")
     * @ORM\JoinColumn(name="measure_id", referencedColumnName="id")
     */
    protected $measure;	
   /**
     * @ORM\Column(type="boolean")
     */
    protected $insurance;
   /**
     * @ORM\Column(type="boolean")
     */
    protected $tax;
   /**
     * @ORM\Column(type="boolean")
     */
    protected $dimentional;
    /**
     * @ORM\Column(type="boolean", options={"default"=true})
     */
    protected $active;
    /** 
    * @ORM\Column(type="string", columnDefinition="ENUM('Mayor', 'Peso', 'Peso Volumen')" ) 
    */ 
    protected $weightpay='Mayor';
   /**
     * @Assert\NotBlank(message = "El costo de la tarifa no puede estar vacío")
     * @Assert\Range(
     *      min = 0,
     *      max = 5000,
     *      minMessage = "El costo mínimo debe ser igual o mayor a {{ limit }}$",
     *      maxMessage = "El costo debe ser menor a {{ limit }}$")
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $cost; // Costo
   /** 
     * @Assert\NotBlank(message = "El valor de inicio de la tarifa no puede estar vacío")
     * @Assert\Range(
     *      min = 0.01,
     *      max = 5000,
     *      minMessage = "El valor de inicio mínimo debe ser igual o mayor a {{ limit }}",
     *      maxMessage = "El valor de inicio debe ser menor a {{ limit }}")
     * @ORM\Column(type="decimal", precision=10, scale=2)
    */
    protected $begin;
   /** 
   /** 
     * @Assert\NotBlank(message = "El valor de hasta de la tarifa no puede estar vacío")
     * @Assert\Range(
     *      min = 1,
     *      max = 10000,
     *      minMessage = "El valor de hasta mínimo debe ser igual o mayor a {{ limit }}",
     *      maxMessage = "El valor de hasta debe ser menor a {{ limit }}")
     * @ORM\Column(type="decimal", precision=10, scale=2) 
    */
    protected $until;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastupdate;
   /** 
     * @Assert\NotBlank(message = "El valor mínimo a cobrar por medida de la tarifa no puede estar vacío")
     * @Assert\Range(
     *      min = 0,
     *      max = 5000,
     *      minMessage = "El límite mínimo a cobrar por valor mínimo  de medida debe ser igual o mayor a {{ limit }}$",
     *      maxMessage = "El valor mínimo de medida debe ser menor a {{ limit }}$")
     * @ORM\Column(type="decimal", precision=10, scale=2) 
    */
    protected $minimun; // minimo a cobrar en Libras o Pie cubico (Valor Real)
   /**
     * @Assert\NotBlank(message = "El valor por medida a cobrar de la tarifa no puede estar vacío")
     * @Assert\Range(
     *      min = 0,
     *      max = 5000,
     *      minMessage = "El valor por medida mínimo debe ser igual o mayor a {{ limit }}$",
     *      maxMessage = "El valor por medida debe ser menor a {{ limit }}$")
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $value_measure; // Valor de la libra o pie cubico
   /**
     * @Assert\NotBlank(message = "El valor mínimo a cobrar en dólares de la tarifa no puede estar vacío")
     * @Assert\Range(
     *      min = 0,
     *      max = 5000,
     *      minMessage = "El valor mínimo a cobrar en dólares debe ser igual o mayor a {{ limit }}$",
     *      maxMessage = "El valor mínimo a cobrar en dólares debe ser menor a {{ limit }}$")
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $value_min; // Valor mínimo a cobrar en dolares
   /**
     @ORM\Column(type="string", columnDefinition="ENUM('Ninguno', 'Individual', 'Total')", nullable=false)
     */
    protected $minimun_limit; // Valor Libra (value_measure) * Libra minima(minumun) < valor total
   /**
     * @Assert\NotBlank(message = "La ganancia de la agencia por medida de la tarifa no puede estar vacía")
     * @Assert\Range(
     *      min = 0,
     *      max = 5000,
     *      minMessage = "La ganancia de la agencia por medida debe ser igual o mayor a {{ limit }}$",
     *      maxMessage = "La ganancia de la agencia por medida debe ser menor a {{ limit }}$")     
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $profit_ag; //Ganancia de la Agencia
   /**
     * @Assert\NotBlank(message = "El precio por volumen adicional de la tarifa no puede estar vacía")
     * @Assert\Range(
     *      min = 0,
     *      max = 5000,
     *      minMessage = "El precio por volumen adicional debe ser igual o mayor a {{ limit }}$",
     *      maxMessage = "El precio por volumen adicional debe ser menor a {{ limit }}$")
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $volume_price; //El valor que se cobra cuando el volumen es por separado
   /**
     * @Assert\NotBlank(message = "La ganancia de la agencia por volumen adicional de la tarifa no puede estar vacía")
     * @Assert\Range(
     *      min = 0,
     *      max = 5000,
     *      minMessage = "La ganancia de la agencia por volumen adicional debe ser igual o mayor a {{ limit }}$",
     *      maxMessage = "La ganancia de la agencia por volumen adicional debe ser menor a {{ limit }}$")     
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $profit_agv;// Ganancia de la Agencia cuando el volumen se cobra por separado
   /**
     * @Assert\NotBlank(message = "La cobro adicional de la tarifa no puede estar vacía")
     * @Assert\Range(
     *      min = 0,
     *      max = 5000,
     *      minMessage = "La cobro adicional debe ser igual o mayor a {{ limit }}$",
     *      maxMessage = "La cobro adicional debe ser menor a {{ limit }}$") 
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $additional; //Cargo adicional
   /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $label_additional; //Cargo adicional
   /**
     * @Assert\NotBlank(message = "Coloque un valor para el porcentaje de impuesto")
     * @Assert\Range(
     *      min = 0,
     *      max = 100,
     *      minMessage = "El porcentaje de impuesto debe ser igual o mayor a {{ limit }}",
     *      maxMessage = "El porcentaje de impuesto máximo debe ser menor o igual a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $tax_per;

   /**
     * @Assert\NotBlank(message = "Coloque un valor para el porcentaje a cobrar por seguro")
     * @Assert\Range(
     *      min = 0,
     *      max = 100,
     *      minMessage = "El porcentaje a cobrar por seguro debe ser igual o mayor a {{ limit }}",
     *      maxMessage = "El porcentaje máximo a cobrar por seguro debe ser menor o igual a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $insurance_per;
    
    public function __construct()
    {
        $this->tax_per = 0.0;
        $this->insurance_per = 0.0;
        $this->cost = 0.0;
        $this->begin = 0.01;
        $this->until = 5000;
        $this->minimun = 0;
        $this->value_min = 0.00;
        $this->profit_ag = 0.00;
        $this->volume_price = 0.00;
        $this->profit_agv = 0.00;
        $this->additional = 0.00;
        $this->label_additional = ' ';
    }
    public function __toString() {
        return (string) $this->getName();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Tariff
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set insurance
     *
     * @param boolean $insurance
     *
     * @return Tariff
     */
    public function setInsurance($insurance)
    {
        $this->insurance = $insurance;

        return $this;
    }

    /**
     * Get insurance
     *
     * @return boolean
     */
    public function getInsurance()
    {
        return $this->insurance;
    }

    /**
     * Set tax
     *
     * @param boolean $tax
     *
     * @return Tariff
     */
    public function setTax($tax)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get tax
     *
     * @return boolean
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Set dimentional
     *
     * @param boolean $dimentional
     *
     * @return Tariff
     */
    public function setDimentional($dimentional)
    {
        $this->dimentional = $dimentional;

        return $this;
    }

    /**
     * Get dimentional
     *
     * @return boolean
     */
    public function getDimentional()
    {
        return $this->dimentional;
    }

    /**
     * Set cost
     *
     * @param string $cost
     *
     * @return Tariff
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return string
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set begin
     *
     * @param integer $begin
     *
     * @return Tariff
     */
    public function setBegin($begin)
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * Get begin
     *
     * @return integer
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Set until
     *
     * @param integer $until
     *
     * @return Tariff
     */
    public function setUntil($until)
    {
        $this->until = $until;

        return $this;
    }

    /**
     * Get until
     *
     * @return integer
     */
    public function getUntil()
    {
        return $this->until;
    }

    /**
     * Set lastupdate
     *
     * @param \DateTime $lastupdate
     *
     * @return Tariff
     */
    public function setLastupdate($lastupdate)
    {
        $this->lastupdate = $lastupdate;

        return $this;
    }

    /**
     * Get lastupdate
     *
     * @return \DateTime
     */
    public function getLastupdate()
    {
        return $this->lastupdate;
    }

    /**
     * Set minimun
     *
     * @param integer $minimun
     *
     * @return Tariff
     */
    public function setMinimun($minimun)
    {
        $this->minimun = $minimun;

        return $this;
    }

    /**
     * Get minimun
     *
     * @return integer
     */
    public function getMinimun()
    {
        return $this->minimun;
    }

    /**
     * Set valueMeasure
     *
     * @param string $valueMeasure
     *
     * @return Tariff
     */
    public function setValueMeasure($valueMeasure)
    {
        $this->value_measure = $valueMeasure;

        return $this;
    }

    /**
     * Get valueMeasure
     *
     * @return string
     */
    public function getValueMeasure()
    {
        return $this->value_measure;
    }

    /**
     * Set valueMin
     *
     * @param string $valueMin
     *
     * @return Tariff
     */
    public function setValueMin($valueMin)
    {
        $this->value_min = $valueMin;

        return $this;
    }

    /**
     * Get valueMin
     *
     * @return string
     */
    public function getValueMin()
    {
        return $this->value_min;
    }

    /**
     * Set minimunLimit
     *
     * @param boolean $minimunLimit
     *
     * @return Tariff
     */
    public function setMinimunLimit($minimunLimit)
    {
        $this->minimun_limit = $minimunLimit;

        return $this;
    }

    /**
     * Get minimunLimit
     *
     * @return boolean
     */
    public function getMinimunLimit()
    {
        return $this->minimun_limit;
    }

    /**
     * Set profitAg
     *
     * @param string $profitAg
     *
     * @return Tariff
     */
    public function setProfitAg($profitAg)
    {
        $this->profit_ag = $profitAg;

        return $this;
    }

    /**
     * Get profitAg
     *
     * @return string
     */
    public function getProfitAg()
    {
        return $this->profit_ag;
    }

    /**
     * Set volumePrice
     *
     * @param string $volumePrice
     *
     * @return Tariff
     */
    public function setVolumePrice($volumePrice)
    {
        $this->volume_price = $volumePrice;

        return $this;
    }

    /**
     * Get volumePrice
     *
     * @return string
     */
    public function getVolumePrice()
    {
        return $this->volume_price;
    }

    /**
     * Set profitAgv
     *
     * @param string $profitAgv
     *
     * @return Tariff
     */
    public function setProfitAgv($profitAgv)
    {
        $this->profit_agv = $profitAgv;

        return $this;
    }

    /**
     * Get profitAgv
     *
     * @return string
     */
    public function getProfitAgv()
    {
        return $this->profit_agv;
    }

    /**
     * Set additional
     *
     * @param string $additional
     *
     * @return Tariff
     */
    public function setAdditional($additional)
    {
        $this->additional = $additional;

        return $this;
    }

    /**
     * Get additional
     *
     * @return string
     */
    public function getAdditional()
    {
        return $this->additional;
    }

    /**
     * Set labelAdditional
     *
     * @param string $labelAdditional
     *
     * @return Tariff
     */
    public function setLabelAdditional($labelAdditional)
    {
        $this->label_additional = $labelAdditional;

        return $this;
    }

    /**
     * Get labelAdditional
     *
     * @return string
     */
    public function getLabelAdditional()
    {
        return $this->label_additional;
    }

    /**
     * Set region
     *
     * @param \NvCarga\Bundle\Entity\Region $region
     *
     * @return Tariff
     */
    public function setRegion(\NvCarga\Bundle\Entity\Region $region = null)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return \NvCarga\Bundle\Entity\Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set agency
     *
     * @param \NvCarga\Bundle\Entity\Agency $agency
     *
     * @return Tariff
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

    /**
     * Set service
     *
     * @param \NvCarga\Bundle\Entity\Servicetype $service
     *
     * @return Tariff
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
     * Set taxPer
     *
     * @param string $taxper
     *
     * @return Guide
     */
    public function setTaxPer($taxPer)
    {
        $this->tax_per = $taxPer;

        return $this;
    }

    /**
     * Get taxPer
     *
     * @return string
     */
    public function getTaxPer()
    {
        return $this->tax_per;
    }
    /**
     * Set insurancePer
     *
     * @param string $insurancePer
     *
     * @return Guide
     */
    public function setInsurancePer($insurancePer)
    {
        $this->insurance_per = $insurancePer;

        return $this;
    }

    /**
     * Get insurancePer
     *
     * @return string
     */
    public function getInsurancePer()
    {
        return $this->insurance_per;
    }

    /**
     * Set measure
     *
     * @param \NvCarga\Bundle\Entity\Measure $measure
     *
     * @return Tariff
     */
    public function setMeasure(\NvCarga\Bundle\Entity\Measure $measure = null)
    {
        $this->measure = $measure;

        return $this;
    }

    /**
     * Get measure
     *
     * @return \NvCarga\Bundle\Entity\Measure
     */
    public function getMeasure()
    {
        return $this->measure;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Tariff
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set weightpay
     *
     * @param string $weightpay
     *
     * @return Tariff
     */
    public function setWeightpay($weightpay)
    {
        $this->weightpay = $weightpay;

        return $this;
    }

    /**
     * Get weightpay
     *
     * @return string
     */
    public function getWeightpay()
    {
        return $this->weightpay;
    }

    /**
     * Set shippingtype
     *
     * @param \NvCarga\Bundle\Entity\Shippingtype $shippingtype
     *
     * @return Tariff
     */
    public function setShippingtype(\NvCarga\Bundle\Entity\Shippingtype $shippingtype = null)
    {
        $this->shippingtype = $shippingtype;

        return $this;
    }

    /**
     * Get shippingtype
     *
     * @return \NvCarga\Bundle\Entity\Shippingtype
     */
    public function getShippingtype()
    {
        return $this->shippingtype;
    }
}
