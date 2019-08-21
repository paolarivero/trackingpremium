<?php
// src/NvCarga/Bundle/Entity/Alert.php

namespace NvCarga\Bundle\Entity;

use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="alert")
 * @ORM\HasLifecycleCallbacks()
 */
class Alert
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @Assert\NotBlank(message = "Debe asignar un valor de tracking")
     * @ORM\Column(type="string", length=80, unique=false, nullable=false)
    */
    protected $tracking;
    /**
     * @ORM\ManyToOne(targetEntity="Shippingtype")
     * @ORM\JoinColumn(name="shippingtype_id", referencedColumnName="id")
     */
    protected $shippingtype;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @Assert\NotBlank(message = "Debe asignar un fecha estimada de llegada del paquete")
     * @ORM\Column(type="date", nullable=false)
     */
    protected $arrivedate;   
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
     * @ORM\Column(type="boolean")
     */
    protected $insurance;
    /**
     * @ORM\ManyToOne(targetEntity="Carrier")
     * @ORM\JoinColumn(name="carrier_id", referencedColumnName="id")
     */
    protected $carrier;	
   /** 
     * @Assert\NotBlank(message = "Seleccione el número de piezas del paquete")
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "La alerta debe tener al menos {{ limit }} pieza",
     * )
     * @ORM\Column(type="integer", nullable=false, options={"default"= 1}) 
    */
    protected $pieces;
    /**
     * @Assert\NotBlank(message = "Coloque el peso del paquete")
     * @Assert\Range(
     *      min = 0,
     *      max = 5000,
     *      minMessage = "El peso mínimo debe ser igual o mayor a {{ limit }}lb",
     *      maxMessage = "El peso debe ser menor a {{ limit }}lb"
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $weight;
   /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $description;
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
     * @ORM\ManyToOne(targetEntity="Pobox")
     * @ORM\JoinColumn(name="pobox_id", referencedColumnName="id")
     */
    protected $pobox;
    /**
     * @ORM\OneToOne(targetEntity="Receipt")
     * @ORM\JoinColumn(name="receipt_id", referencedColumnName="id")
     */
    protected $receipt;
    /**
     * @ORM\ManyToOne(targetEntity="Baddress")
     * @ORM\JoinColumn(name="baddress_id", referencedColumnName="id")
     */
    protected $baddress;
    /**
     * @ORM\Column(type="boolean", options={"default"= 1} )
     */
    protected $isshowed;
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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $instructions;
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;

    public function __toString() {
        return (string) ($this->getTracking());
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
     * Set tracking
     *
     * @param string $tracking
     *
     * @return Alert
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
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Alert
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
     * Set arrivedate
     *
     * @param \DateTime $arrivedate
     *
     * @return Alert
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
     * Set imageData
     *
     * @param string $imageData
     *
     * @return Alert
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
     * @return Alert
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
     * @return Alert
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
     * Set insurance
     *
     * @param boolean $insurance
     *
     * @return Alert
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
     * Set pieces
     *
     * @param integer $pieces
     *
     * @return Alert
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
     * Set weight
     *
     * @param string $weight
     *
     * @return Alert
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
     * Set description
     *
     * @param string $description
     *
     * @return Alert
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
     * Set value
     *
     * @param string $value
     *
     * @return Alert
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
     * Set shippingtype
     *
     * @param \NvCarga\Bundle\Entity\Shippingtype $shippingtype
     *
     * @return Alert
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
     * Set carrier
     *
     * @param \NvCarga\Bundle\Entity\Carrier $carrier
     *
     * @return Alert
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
     * Set pobox
     *
     * @param \NvCarga\Bundle\Entity\Pobox $pobox
     *
     * @return Alert
     */
    public function setPobox(\NvCarga\Bundle\Entity\Pobox $pobox = null)
    {
        $this->pobox = $pobox;

        return $this;
    }

    /**
     * Get pobox
     *
     * @return \NvCarga\Bundle\Entity\Pobox
     */
    public function getPobox()
    {
        return $this->pobox;
    }

    /**
     * Set baddress
     *
     * @param \NvCarga\Bundle\Entity\Baddress $baddress
     *
     * @return Alert
     */
    public function setBaddress(\NvCarga\Bundle\Entity\Baddress $baddress = null)
    {
        $this->baddress = $baddress;

        return $this;
    }

    /**
     * Get baddress
     *
     * @return \NvCarga\Bundle\Entity\Baddress
     */
    public function getBaddress()
    {
        return $this->baddress;
    }


    /**
     * Set receipt
     *
     * @param \NvCarga\Bundle\Entity\Receipt $receipt
     *
     * @return Alert
     */
    public function setReceipt(\NvCarga\Bundle\Entity\Receipt $receipt = null)
    {
        $this->receipt = $receipt;

        return $this;
    }

    /**
     * Get receipt
     *
     * @return \NvCarga\Bundle\Entity\Receipt
     */
    public function getReceipt()
    {
        return $this->receipt;
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
     * Set instructions
     *
     * @param string $instructions
     *
     * @return Alert
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;

        return $this;
    }

    /**
     * Get instructions
     *
     * @return string
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * Set isshowed
     *
     * @param boolean $isshowed
     *
     * @return Alert
     */
    public function setIsshowed($isshowed)
    {
        $this->isshowed = $isshowed;

        return $this;
    }

    /**
     * Get isshowed
     *
     * @return boolean
     */
    public function getIsshowed()
    {
        return $this->isshowed;
    }

    /**
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Alert
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
