<?php
// src/NvCarga/Bundle/Entity/Guide.php

namespace NvCarga\Bundle\Entity;

use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="guide")
 * @ORM\HasLifecycleCallbacks()
 */
class Guide
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
   /**
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="countryfrom_id", referencedColumnName="id")
     */
    private $countryfrom;
    /**
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="countryto_id", referencedColumnName="id")
     */
    private $countryto;		
   /**
     * @ORM\ManyToOne(targetEntity="Agency")
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="id")
     */
    protected $agency;
   /**
     * @ORM\Column(type="string", length=50, unique=false, nullable=false)
     */
    protected $number;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $contain;
     /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $emailnoti;
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $mobilnoti;
    /**
     * @Assert\File(
     *     maxSize = "512000",
     *     maxSizeMessage = "El tamaño del archivo debe ser menor a  {{ limit }} bytes",
     *     mimeTypes = {"image/jpeg", "image/png"},
     *     mimeTypesMessage = "Por favor seleccione un tipo de archivo válido (png/jpg)"
     * )
     */
    private $file;
    /** 
     * @ORM\Column(type="blob",  nullable=true) 
    */
    protected $imageData;
    /** 
     * @ORM\Column(type="string", nullable=true) 
    */
    protected $imageType;
   /** 
     * @ORM\Column(type="integer", nullable=true) 
    */
    protected $imageSize;
   /**
     * @Assert\NotBlank(message = "Debe asignar un peso")
     * @Assert\Range(
     *      min = 1,
     *      max = 2000,
     *      minMessage = "El peso mínimo debe ser igual o mayor a {{ limit }}lb",
     *      maxMessage = "El peso debe ser menor a {{ limit }}lb"
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $realweight;
   /** 
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "Debe tener al menos {{ limit }} pieza",
     * )
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $pieces;
   /**
     * @Assert\NotBlank(message = "Coloque un valor para el paquete")
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "El valor declarado debe ser igual o mayor a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $declared;
   /**
     * @Assert\NotBlank(message = "Coloque un valor para el porcentaje de impuesto")
     * @Assert\Range(
     *      min = 0,
     *      max = 100,
     *      minMessage = "El porcentaje de impuesto debe ser igual o mayor a {{ limit }}",
     *      maxMessage = "El porcentaje de impuesto máximo debe ser menor o igual a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $tax_per;
    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $tax_paid;
   /** 
     * @ORM\Column(type="string", nullable=true) 
    */
    protected $ordernumber; // SE DEBE DESCRIBIR EL USO
   /**
     * @Assert\NotBlank(message = "Coloque un valor para el valor a asegurar")
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "El valor a asegurar debe ser igual o mayor a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $insurance_amount;
   /**
     * @Assert\NotBlank(message = "Coloque un valor para el porcentaje a cobrar por seguro")
     * @Assert\Range(
     *      min = 0,
     *      max = 100,
     *      minMessage = "El porcentaje a cobrar por seguro debe ser igual o mayor a {{ limit }}",
     *      maxMessage = "El porcentaje máximo a cobrar por seguro debe ser menor o igual a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $insurance_per;
   /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $insurance_paid;
   /**
     * @Assert\NotBlank(message = "Coloque un descuento")
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "El descuento debe ser igual o mayor a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $discount;
   /**
     * @Assert\NotBlank(message = "Coloque un valor para otros cargos")
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "El valor de otros cargos debe ser igual o mayor a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $otherfees;
   /**
     * @Assert\NotBlank(message = "Presione calcular para determinar flete")
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $freight;
     /**
     * @Assert\NotBlank(message = "Presione calcular para determinar flete por volumen")
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $volfreight;
   /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $paidweight;
   /**
     * @Assert\NotBlank(message = "Calcule o asigne el valor de la medida")
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "El valor de la medida debe ser igual o mayor a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $measurevalue;
    /**
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "El total a pagar debe ser por lo menos {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $totalpaid;
   /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="sguides")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id")
     */
    protected $sender;
    /**
     * @ORM\ManyToOne(targetEntity="Baddress")
     * @ORM\JoinColumn(name="addressee_id", referencedColumnName="id")
     */
    protected $addressee; 

    /**
     * @ORM\ManyToOne(targetEntity="Tariff")
     * @ORM\JoinColumn(name="tariff_id", referencedColumnName="id")
     */
    protected $tariff; 
    /**
     * @ORM\ManyToOne(targetEntity="COD")
     * @ORM\JoinColumn(name="cod_id", referencedColumnName="id")
     */
    protected $cod;   
    /**
     * @ORM\ManyToOne(targetEntity="Paidtype")
     * @ORM\JoinColumn(name="paidtype_id", referencedColumnName="id")
     */
    protected $paidtype;

    /**
     * @ORM\ManyToOne(targetEntity="Shippingtype")
     * @ORM\JoinColumn(name="shippingtype_id", referencedColumnName="id")
     */
    protected $shippingtype;
    /**
     * @ORM\ManyToOne(targetEntity="Consolidated", inversedBy="guides")
     * @ORM\JoinColumn(name="consol_id", referencedColumnName="id")
     */
    protected $consol;
    /**
     * @ORM\ManyToOne(targetEntity="Bag", inversedBy="guides")
     * @ORM\JoinColumn(name="bag_id", referencedColumnName="id")
     */
    protected $bag;
    /**
     * @ORM\ManyToOne(targetEntity="Bill", inversedBy="guides")
     * @ORM\JoinColumn(name="bill_id", referencedColumnName="id")
     */
    protected $bill;
    /**
     * @ORM\OneToOne(targetEntity="WHrec", mappedBy="guide")
     * @ORM\JoinColumn(name="whrec_id", referencedColumnName="id")
     */
    protected $whrec;
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Receipt", mappedBy="guide")
     */
    protected $receipts;
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Moveguides", mappedBy="guide")
     */
    protected $moves;
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="processedby_id", referencedColumnName="id")
    */
    protected $processedby;
    /**
     * @ORM\OneToOne(targetEntity="Receipt")
     * @ORM\JoinColumn(name="masterec_id", referencedColumnName="id")
     */
    protected $masterec; 
   /**
     * @Assert\NotBlank(message = "Asigne el pago incial")
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "El valor del pago incial debe ser igual o mayor a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $downpayment;
    /**
     @ORM\Column(type="string", columnDefinition="ENUM('Ninguno', 'Individual', 'Total')", nullable=false)
     */
    protected $roundmeasure='Ninguno';
    /**
     * @var ArrayCollection
     */
    protected $packages;
    /**
     * @var ArrayCollection
     */
    protected $services;
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;
    /**
     * @Assert\File(
     *     maxSize = "512000",
     *     maxSizeMessage = "El tamaño del archivo debe ser menor a  {{ limit }} bytes",
     *     mimeTypes = {"image/jpeg", "image/png"},
     *     mimeTypesMessage = "Por favor, seleccione un tipo de archivo válido (png/jpg)")
     */
     private $filesig;
     /**
     * @ORM\Column(name="signature", type="string", length=255, nullable=true)
     */
    protected $signature;
    /**
     @ORM\Column(type="boolean", options={"default":false})
     */
    protected $movealone;
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Trackguide", mappedBy="guide")
     */
    protected $tracks;
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Statusguide", mappedBy="guide")
     */
    protected $liststatus;
    /**
     @ORM\Column(type="boolean", options={"default":true})
     */
    protected $statusconsol;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->liststatus = new \Doctrine\Common\Collections\ArrayCollection();
        $this->receipts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->moves = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tracks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->packages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->services = new \Doctrine\Common\Collections\ArrayCollection();
        $this->downpayment = 0.0;
        $this->tax_paid = 0.0;
        $this->insurance_paid = 0.0;
        $this->movealone = false;
        $this->statusconsol = true;
        
    }
 
    public function addPackage(\NvCarga\Bundle\Entity\Minipack $package)
    {
        $this->packages[] = $package;

        return $this;
    }

    public function removePackage(\NvCarga\Bundle\Entity\Minipack $package)
    {
	
        $this->packages->removeElement($package);
    }

    public function getPackages()
    {
        return $this->packages;
    }

   public function addService(\NvCarga\Bundle\Entity\ClassServ $service)
    {
        $this->services[] = $service;

        return $this;
    }

    public function removeService(\NvCarga\Bundle\Entity\ClassServ $service)
    {
	
        $this->services->removeElement($service);
    }

    public function getServices()
    {
        return $this->services;
    }
    public function __toString() {
        return (string) ($this->getNumber());
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
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Guide
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
     * Set contain
     *
     * @param string $contain
     *
     * @return Guide
     */
    public function setContain($contain)
    {
        $this->contain = $contain;

        return $this;
    }

    /**
     * Get contain
     *
     * @return string
     */
    public function getContain()
    {
        return $this->contain;
    }

    /**
     * Set emailnoti
     *
     * @param boolean $emailnoti
     *
     * @return Guide
     */
    public function setEmailnoti($emailnoti)
    {
        $this->emailnoti = $emailnoti;

        return $this;
    }

    /**
     * Get emailnoti
     *
     * @return boolean
     */
    public function getEmailnoti()
    {
        return $this->emailnoti;
    }

    /**
     * Set mobilnoti
     *
     * @param boolean $mobilnoti
     *
     * @return Guide
     */
    public function setMobilnoti($mobilnoti)
    {
        $this->mobilnoti = $mobilnoti;

        return $this;
    }

    /**
     * Get mobilnoti
     *
     * @return boolean
     */
    public function getMobilnoti()
    {
        return $this->mobilnoti;
    }

    /**
     * Set imageData
     *
     * @param string $imageData
     *
     * @return Guide
     */
    public function setImageData($imageData)
    {
        $this->imageData = $imageData;

        return $this;
    }

    /**
     * Get imageData
     *
     * @return string
     */
    public function getImageData()
    {
        return $this->imageData;
    }

    /**
     * Set imageType
     *
     * @param string $imageType
     *
     * @return Guide
     */
    public function setImageType($imageType)
    {
        $this->imageType = $imageType;

        return $this;
    }

    /**
     * Get imageType
     *
     * @return string
     */
    public function getImageType()
    {
        return $this->imageType;
    }

    /**
     * Set imageSize
     *
     * @param integer $imageSize
     *
     * @return Guide
     */
    public function setImageSize($imageSize)
    {
        $this->imageSize = $imageSize;

        return $this;
    }

    /**
     * Get imageSize
     *
     * @return integer
     */
    public function getImageSize()
    {
        return $this->imageSize;
    }

    /**
     * Set realweight
     *
     * @param string $realweight
     *
     * @return Guide
     */
    public function setRealweight($realweight)
    {
        $this->realweight = $realweight;

        return $this;
    }

    /**
     * Get realweight
     *
     * @return string
     */
    public function getRealweight()
    {
        return $this->realweight;
    }
    /**
     * Set pieces
     *
     * @param integer $pieces
     *
     * @return Guide
     */
    public function setPieces($pieces)
    {
        $this->pieces = $pieces;

        return $this;
    }

    /**
     * Get pieces
     *
     * @return integer
     */
    public function getPieces()
    {
        return $this->pieces;
    }

    /**
     * Set declared
     *
     * @param string $declared
     *
     * @return Guide
     */
    public function setDeclared($declared)
    {
        $this->declared = $declared;

        return $this;
    }

    /**
     * Get declared
     *
     * @return string
     */
    public function getDeclared()
    {
        return $this->declared;
    }

    /**
     * Set ordernumber
     *
     * @param string $ordernumber
     *
     * @return Guide
     */
    public function setOrdernumber($ordernumber)
    {
        $this->ordernumber = $ordernumber;

        return $this;
    }

    /**
     * Get ordernumber
     *
     * @return string
     */
    public function getOrdernumber()
    {
        return $this->ordernumber;
    }

    /**
     * Set insuranceAmount
     *
     * @param string $insuranceAmount
     *
     * @return Guide
     */
    public function setInsuranceAmount($insuranceAmount)
    {
        $this->insurance_amount = $insuranceAmount;

        return $this;
    }

    /**
     * Get insuranceAmount
     *
     * @return string
     */
    public function getInsuranceAmount()
    {
        return $this->insurance_amount;
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
     * Set insurancePaid
     *
     * @param string $insurancePaid
     *
     * @return Guide
     */
    public function setInsurancePaid($insurancePaid)
    {
        $this->insurance_paid = $insurancePaid;

        return $this;
    }

    /**
     * Get insurancePaid
     *
     * @return string
     */
    public function getInsurancePaid()
    {
        return $this->insurance_paid;
    }

    /**
     * Set discount
     *
     * @param string $discount
     *
     * @return Guide
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get discount
     *
     * @return string
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set otherfees
     *
     * @param string $otherfees
     *
     * @return Guide
     */
    public function setOtherfees($otherfees)
    {
        $this->otherfees = $otherfees;

        return $this;
    }

    /**
     * Get otherfees
     *
     * @return string
     */
    public function getOtherfees()
    {
        return $this->otherfees;
    }

    /**
     * Set freight
     *
     * @param string $freight
     *
     * @return Guide
     */
    public function setFreight($freight)
    {
        $this->freight = $freight;

        return $this;
    }

    /**
     * Get freight
     *
     * @return string
     */
    public function getFreight()
    {
        return $this->freight;
    }

    /**
     * Set volfreight
     *
     * @param string $volfreight
     *
     * @return Guide
     */
    public function setVolfreight($volfreight)
    {
        $this->volfreight = $volfreight;

        return $this;
    }

    /**
     * Get volfreight
     *
     * @return string
     */
    public function getVolfreight()
    {
        return $this->volfreight;
    }

    /**
     * Set paidweight
     *
     * @param string $paidweight
     *
     * @return Guide
     */
    public function setPaidweight($paidweight)
    {
        $this->paidweight = $paidweight;

        return $this;
    }

    /**
     * Get paidweight
     *
     * @return string
     */
    public function getPaidweight()
    {
        return $this->paidweight;
    }

    /**
     * Set measurevalue
     *
     * @param string $measurevalue
     *
     * @return Guide
     */
    public function setMeasurevalue($measurevalue)
    {
        $this->measurevalue = $measurevalue;

        return $this;
    }

    /**
     * Get measurevalue
     *
     * @return string
     */
    public function getMeasurevalue()
    {
        return $this->measurevalue;
    }

    /**
     * Set totalpaid
     *
     * @param integer $totalpaid
     *
     * @return Guide
     */
    public function setTotalpaid($totalpaid)
    {
        $this->totalpaid = $totalpaid;

        return $this;
    }

    /**
     * Get totalpaid
     *
     * @return integer
     */
    public function getTotalpaid()
    {
        return $this->totalpaid;
    }

    /**
     * Set agency
     *
     * @param \NvCarga\Bundle\Entity\Agency $agency
     *
     * @return Guide
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
     * Set sender
     *
     * @param \NvCarga\Bundle\Entity\Customer $sender
     *
     * @return Guide
     */
    public function setSender(\NvCarga\Bundle\Entity\Customer $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return \NvCarga\Bundle\Entity\Customer
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set addressee
     *
     * @param \NvCarga\Bundle\Entity\Baddress $addressee
     *
     * @return Guide
     */
    public function setAddressee(\NvCarga\Bundle\Entity\Baddress $addressee = null)
    {
        $this->addressee = $addressee;

        return $this;
    }

    /**
     * Get addressee
     *
     * @return \NvCarga\Bundle\Entity\Baddress
     */
    public function getAddressee()
    {
        return $this->addressee;
    }

    /**
     * Set tariff
     *
     * @param \NvCarga\Bundle\Entity\Tariff $tariff
     *
     * @return Guide
     */
    public function setTariff(\NvCarga\Bundle\Entity\Tariff $tariff = null)
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * Get tariff
     *
     * @return \NvCarga\Bundle\Entity\Tariff
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * Set shippingtype
     *
     * @param \NvCarga\Bundle\Entity\Shippingtype $shippingtype
     *
     * @return Guide
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

    /**
     * Set consol
     *
     * @param \NvCarga\Bundle\Entity\Consolidated $consol
     *
     * @return Guide
     */
    public function setConsol(\NvCarga\Bundle\Entity\Consolidated $consol = null)
    {
        $this->consol = $consol;

        return $this;
    }

    /**
     * Get consol
     *
     * @return \NvCarga\Bundle\Entity\Consolidated
     */
    public function getConsol()
    {
        return $this->consol;
    }

    /**
     * Set bill
     *
     * @param \NvCarga\Bundle\Entity\Bill $bill
     *
     * @return Guide
     */
    public function setBill(\NvCarga\Bundle\Entity\Bill $bill = null)
    {
        $this->bill = $bill;

        return $this;
    }

    /**
     * Get bill
     *
     * @return \NvCarga\Bundle\Entity\Bill
     */
    public function getBill()
    {
        return $this->bill;
    }
    /**
     * Set file
     *
     * @param string $file
     *
     * @return Guide
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get imageFile
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    public function upload()
    {
    	if (null === $this->file) {
       	 return;
    	}
	$infile = new File($this->file);
	if ($infile) {	
		$this->setImageType($infile->getMimeType());
		$this->setImageSize($infile->getSize());
    	
	
    		$strm = fopen($this->file->getRealPath(),'rb');
    		$this->setImageData(stream_get_contents($strm));
	} else {
		return;
	}
	
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
     * Set taxPaid
     *
     * @param string $taxPaid
     *
     * @return Guide
     */
    public function setTaxPaid($taxPaid)
    {
        $this->tax_paid = $taxPaid;

        return $this;
    }

    /**
     * Get taxPaid
     *
     * @return string
     */
    public function getTaxPaid()
    {
        return $this->tax_paid;
    }

    /**
     * Add receipt
     *
     * @param \NvCarga\Bundle\Entity\Receipt $receipt
     *
     * @return Guide
     */
    public function addReceipt(\NvCarga\Bundle\Entity\Receipt $receipt)
    {
        $this->receipts[] = $receipt;

        return $this;
    }

    /**
     * Remove receipt
     *
     * @param \NvCarga\Bundle\Entity\Receipt $receipt
     */
    public function removeReceipt(\NvCarga\Bundle\Entity\Receipt $receipt)
    {
        $this->receipts->removeElement($receipt);
	
    }

    /**
     * Get receipts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReceipts()
    {
        return $this->receipts;
    }

    /**
     * Set processedby
     *
     * @param \NvCarga\Bundle\Entity\User $processedby
     *
     * @return Guide
     */
    public function setProcessedby(\NvCarga\Bundle\Entity\User $processedby = null)
    {
        $this->processedby = $processedby;

        return $this;
    }

    /**
     * Get processedby
     *
     * @return \NvCarga\Bundle\Entity\User
     */
    public function getProcessedby()
    {
        return $this->processedby;
    }

    /**
     * Set number
     *
     * @param string $number
     *
     * @return Guide
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set cod
     *
     * @param \NvCarga\Bundle\Entity\COD $cod
     *
     * @return Guide
     */
    public function setCod(\NvCarga\Bundle\Entity\COD $cod = null)
    {
        $this->cod = $cod;

        return $this;
    }

    /**
     * Get cod
     *
     * @return \NvCarga\Bundle\Entity\COD
     */
    public function getCod()
    {
        return $this->cod;
    }

    /**
     * Set paidtype
     *
     * @param \NvCarga\Bundle\Entity\Paidtype $paidtype
     *
     * @return Guide
     */
    public function setPaidtype(\NvCarga\Bundle\Entity\Paidtype $paidtype = null)
    {
        $this->paidtype = $paidtype;

        return $this;
    }

    /**
     * Get paidtype
     *
     * @return \NvCarga\Bundle\Entity\Paidtype
     */
    public function getPaidtype()
    {
        return $this->paidtype;
    }

    /**
     * Add move
     *
     * @param \NvCarga\Bundle\Entity\Moveguides $move
     *
     * @return Guide
     */
    public function addMove(\NvCarga\Bundle\Entity\Moveguides $move)
    {
        $this->moves[] = $move;

        return $this;
    }

    /**
     * Remove move
     *
     * @param \NvCarga\Bundle\Entity\Moveguides $move
     */
    public function removeMove(\NvCarga\Bundle\Entity\Moveguides $move)
    {
        $this->moves->removeElement($move);
    }

    /**
     * Get moves
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMoves()
    {
        return $this->moves;
    }

    /**
     * Set bag
     *
     * @param \NvCarga\Bundle\Entity\Bag $bag
     *
     * @return Guide
     */
    public function setBag(\NvCarga\Bundle\Entity\Bag $bag = null)
    {
        $this->bag = $bag;

        return $this;
    }

    /**
     * Get bag
     *
     * @return \NvCarga\Bundle\Entity\Bag
     */
    public function getBag()
    {
        return $this->bag;
    }

    /**
     * Set countryfrom
     *
     * @param \NvCarga\Bundle\Entity\Country $countryfrom
     *
     * @return Guide
     */
    public function setCountryfrom(\NvCarga\Bundle\Entity\Country $countryfrom = null)
    {
        $this->countryfrom = $countryfrom;

        return $this;
    }

    /**
     * Get countryfrom
     *
     * @return \NvCarga\Bundle\Entity\Country
     */
    public function getCountryfrom()
    {
        return $this->countryfrom;
    }

    /**
     * Set countryto
     *
     * @param \NvCarga\Bundle\Entity\Country $countryto
     *
     * @return Guide
     */
    public function setCountryto(\NvCarga\Bundle\Entity\Country $countryto = null)
    {
        $this->countryto = $countryto;

        return $this;
    }

    /**
     * Get countryto
     *
     * @return \NvCarga\Bundle\Entity\Country
     */
    public function getCountryto()
    {
        return $this->countryto;
    }

    public function newpackages()
    {
        $this->packages = new \Doctrine\Common\Collections\ArrayCollection();
    }
     public function newservices()
    {
        $this->sevices = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set masterec
     *
     * @param \NvCarga\Bundle\Entity\Receipt $masterec
     *
     * @return Guide
     */
    public function setMasterec(\NvCarga\Bundle\Entity\Receipt $masterec = null)
    {
        $this->masterec = $masterec;

        return $this;
    }

    /**
     * Get masterec
     *
     * @return \NvCarga\Bundle\Entity\Receipt
     */
    public function getMasterec()
    {
        return $this->masterec;
    }

    /**
     * Set downpayment
     *
     * @param string $downpayment
     *
     * @return Guide
     */
    public function setDownpayment($downpayment)
    {
        $this->downpayment = $downpayment;

        return $this;
    }

    /**
     * Get downpayment
     *
     * @return string
     */
    public function getDownpayment()
    {
        return $this->downpayment;
    }

    /**
     * Set roundmeasure
     *
     * @param string $roundmeasure
     *
     * @return Guide
     */
    public function setRoundmeasure($roundmeasure)
    {
        $this->roundmeasure = $roundmeasure;

        return $this;
    }

    /**
     * Get roundmeasure
     *
     * @return string
     */
    public function getRoundmeasure()
    {
        return $this->roundmeasure;
    }

    /**
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Guide
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

    /**
     * Set signature
     *
     * @param string $signature
     *
     * @return Guide
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }
     public function getFilesig()
    {
        return $this->filesig;
    }

    public function setFilesig($filesig)
    {
        $this->file = $filesig;

        return $this;
    }
    public function uploadSignature($path) {
        $file = $this->getFilesig();

        $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

        // moves the file to the directory where Files are stored
        $file->move(
            $path,
            $fileName
        );

        // updates the 'File' property to store the PDF file name
        // instead of its contents
        if ($this->getSignature()) {
             unlink($path . '/' . $this->getSignature());
        }
        $this->setSignature($fileName);
    }
    
    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    /**
     * Set whrec
     *
     * @param \NvCarga\Bundle\Entity\HWrec $whrec
     *
     * @return Guide
     */
    public function setWhrec(\NvCarga\Bundle\Entity\WHrec $whrec = null)
    {
        $this->whrec = $whrec;

        return $this;
    }

    /**
     * Get whrec
     *
     * @return \NvCarga\Bundle\Entity\HWrec
     */
    public function getWhrec()
    {
        return $this->whrec;
    }

    /**
     * Set movealone
     *
     * @param boolean $movealone
     *
     * @return Guide
     */
    public function setMovealone($movealone)
    {
        $this->movealone = $movealone;

        return $this;
    }

    /**
     * Get movealone
     *
     * @return boolean
     */
    public function getMovealone()
    {
        return $this->movealone;
    }

    /**
     * Add track
     *
     * @param \NvCarga\Bundle\Entity\Trackguide $track
     *
     * @return Guide
     */
    public function addTrack(\NvCarga\Bundle\Entity\Trackguide $track)
    {
        $this->tracks[] = $track;

        return $this;
    }

    /**
     * Remove track
     *
     * @param \NvCarga\Bundle\Entity\Trackguide $track
     */
    public function removeTrack(\NvCarga\Bundle\Entity\Trackguide $track)
    {
        $this->tracks->removeElement($track);
    }

    /**
     * Get tracks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTracks()
    {
        return $this->tracks;
    }

    /**
     * Set statusconsol
     *
     * @param boolean $statusconsol
     *
     * @return Guide
     */
    public function setStatusconsol($statusconsol)
    {
        $this->statusconsol = $statusconsol;

        return $this;
    }

    /**
     * Get statusconsol
     *
     * @return boolean
     */
    public function getStatusconsol()
    {
        return $this->statusconsol;
    }

    /**
     * Add liststatus
     *
     * @param \NvCarga\Bundle\Entity\Statusguide $liststatus
     *
     * @return Guide
     */
    public function addListstatus(\NvCarga\Bundle\Entity\Statusguide $liststatus)
    {
        $this->liststatus[] = $liststatus;

        return $this;
    }

    /**
     * Remove liststatus
     *
     * @param \NvCarga\Bundle\Entity\Statusguide $liststatus
     */
    public function removeListstatus(\NvCarga\Bundle\Entity\Statusguide $liststatus)
    {
        $this->liststatus->removeElement($liststatus);
    }

    /**
     * Get liststatus
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getListstatus()
    {
        return $this->liststatus;
    }
    public function getLaststatus()
    {
        $array1 = $this->liststatus;
        $array2 = $this->moves;
        $sort = array();
        $newarray = array();
        $count = 0;
        foreach ($array1 as $status) {
            $sort[$count] = $status->getDate();
            $newarray[$count]['datetime'] = $status->getDate();
            $newarray[$count]['status'] = $status->getStep()->getName();
            $count++;
        }
        if ($this->getStatusconsol() && $this->getConsol()) {
             $array3 = $this->getConsol()->getListstatus();
             foreach ($array3 as $status) {
                $sort[$count] = $status->getDate();
                $newarray[$count]['datetime'] = $status->getDate();
                $newarray[$count]['status'] = $status->getStep()->getName();
                $count++;
            }
             
        }
        foreach ($array2 as $move) {
            $sort[$count] = $move->getMovdate();
            $newarray[$count]['datetime'] = $move->getMovdate();
            $newarray[$count]['status'] = $move->getStatus()->getName();
            $count++;
        }
        array_multisort($sort, SORT_DESC, $newarray);
        if (count($newarray) > 0) {
            $last = $newarray[0];
            return $last['status'];
        } else {
            return (string)('');
        }
    }
}
