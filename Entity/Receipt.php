<?php
// src/NvCarga/Bundle/Entity/Receipt.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="receipt")
 * @ORM\HasLifecycleCallbacks()
 */
class Receipt
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

     /**
     * @ORM\Column(type="string", length=30, unique=false, nullable=false)
     */
    protected $number;
    /**
     * @ORM\ManyToOne(targetEntity="Agency")
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="id")
     */
    protected $agency;
   /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="receiptdby_id", referencedColumnName="id")
     */
    protected $receiptd_by;
    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="shipped")
     * @ORM\JoinColumn(name="shipper_id", referencedColumnName="id")
     */
    protected $shipper;
    /**
     * @ORM\ManyToOne(targetEntity="Baddress")
     * @ORM\JoinColumn(name="receiver_id", referencedColumnName="id")
     */
    protected $receiver;
    /**
     * @ORM\ManyToOne(targetEntity="Carrier", inversedBy="receipts")
     * @ORM\JoinColumn(name="carrier_id", referencedColumnName="id")
     */
    protected $carrier;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
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
     * @ORM\Column(type="text", length=200, nullable=true)
     */
    protected $note;
   /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $description;
   /**
     * @ORM\ManyToOne(targetEntity="Receiptstatus", inversedBy="receipts")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;
    /**
     * @ORM\ManyToOne(targetEntity="Receipttype", inversedBy="receipts")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $type;
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
     * @ORM\ManyToOne(targetEntity="Guide", inversedBy="receipts")
     * @ORM\JoinColumn(name="guide_id", referencedColumnName="id")
     */
    protected $guide;
    /**
     * @ORM\ManyToOne(targetEntity="WHrec", inversedBy="receipts")
     * @ORM\JoinColumn(name="whrec_id", referencedColumnName="id")
     */
    protected $whrec;
    /**
     * @ORM\ManyToOne(targetEntity="Receipt", inversedBy="receipts")
     * @ORM\JoinColumn(name="master_id", referencedColumnName="id")
     */
    protected $master;
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Receipt", mappedBy="master")
     */
    protected $receipts;
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany")
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

    /**
     * @Assert\File(
     *     maxSize = "512000",
     *     maxSizeMessage = "El tamaño del archivo debe ser menor a  {{ limit }} bytes",
     *     mimeTypes = {"image/jpeg", "image/png"},
     *     mimeTypesMessage = "Por favor, seleccione un tipo de archivo válido (png/jpg)")
     */
     private $file;
     
     /**
     * @ORM\Column(name="signature", type="string", length=255, nullable=true)
     */
    protected $signature;
    
    protected $packages;
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Statusreceipt", mappedBy="receipt")
     */
    protected $liststatus;
    /**
     @ORM\Column(type="boolean", options={"default":true})
     */
    protected $statusguide;
    /**
     @ORM\Column(type="boolean", options={"default":false})
     */
    protected $statuswhrec;


    public function __construct()
    {
        $this->quantity = 1;
        $this->packtype = 'Caja';
        $this->packages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->receipts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->liststatus = new \Doctrine\Common\Collections\ArrayCollection();
        $this->statusguide = true;
        $this->statuswhrec = false; 
    }

    public function getPackages()
    {
        return $this->packages;
    }

    public function setPackages(ArrayCollection $packages)
    {
        $this->packages = $packages;
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
     * Set number
     *
     * @param string $number
     *
     * @return Receipt
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
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Receipt
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
     * Set tracking
     *
     * @param string $tracking
     *
     * @return Receipt
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
     * @return Receipt
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
     * @return Receipt
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
     * @return Receipt
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
     * Set agency
     *
     * @param \NvCarga\Bundle\Entity\Agency $agency
     *
     * @return Receipt
     */
    public function setAgency(\NvCarga\Bundle\Entity\Agency $agency = null)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency
     *
     * @return \NvCarga\Bundle\Entity\User
     */
    public function getAgency()
    {
        return $this->agency;
    }
    /**
     * Set receiptdBy
     *
     * @param \NvCarga\Bundle\Entity\User $receiptdBy
     *
     * @return Receipt
     */
    public function setReceiptdBy(\NvCarga\Bundle\Entity\User $receiptdBy = null)
    {
        $this->receiptd_by = $receiptdBy;

        return $this;
    }

    /**
     * Get receiptdBy
     *
     * @return \NvCarga\Bundle\Entity\User
     */
    public function getReceiptdBy()
    {
        return $this->receiptd_by;
    }

    /**
     * Set shipper
     *
     * @param \NvCarga\Bundle\Entity\Customer $shipper
     *
     * @return Receipt
     */
    public function setShipper(\NvCarga\Bundle\Entity\Customer $shipper = null)
    {
        $this->shipper = $shipper;

        return $this;
    }

    /**
     * Get shipper
     *
     * @return \NvCarga\Bundle\Entity\Customer
     */
    public function getShipper()
    {
        return $this->shipper;
    }

    /**
     * Set receiver
     *
     * @param \NvCarga\Bundle\Entity\Baddress $receiver
     *
     * @return Receipt
     */
    public function setReceiver(\NvCarga\Bundle\Entity\Baddress $receiver = null)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get receiver
     *
     * @return \NvCarga\Bundle\Entity\Baddress
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set carrier
     *
     * @param \NvCarga\Bundle\Entity\Carrier $carrier
     *
     * @return Receipt
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
     * Set status
     *
     * @param \NvCarga\Bundle\Entity\Receiptstatus $status
     *
     * @return Receipt
     */
    public function setStatus(\NvCarga\Bundle\Entity\Receiptstatus $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \NvCarga\Bundle\Entity\Receiptstatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set type
     *
     * @param \NvCarga\Bundle\Entity\Receipttype $type
     *
     * @return Receipt
     */
    public function setType(\NvCarga\Bundle\Entity\Receipttype $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \NvCarga\Bundle\Entity\Receipttype
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return Receipt
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }
    
    /**
     * Add package
     *
     * @param \NvCarga\Bundle\Entity\Package $package
     *
     * @return Receipt
     */
    public function addPackage(\NvCarga\Bundle\Entity\Package $package)
    {
        $this->packages[] = $package;

        return $this;
    }
    /**
     * Remove package
     *
     * @param \NvCarga\Bundle\Entity\Package $package
     */
    public function removePackage(\NvCarga\Bundle\Entity\Package $package)
    {
        $this->packages->removeElement($package);
    }

    /**
     * Set guide
     *
     * @param \NvCarga\Bundle\Entity\Guide $guide
     *
     * @return Receipt
     */
    public function setGuide(\NvCarga\Bundle\Entity\Guide $guide = null)
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
     * Set arrivedate
     *
     * @param \DateTime $arrivedate
     *
     * @return Receipt
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
     * Set master
     *
     * @param \NvCarga\Bundle\Entity\Receipt $master
     *
     * @return Receipt
     */
    public function setMaster(\NvCarga\Bundle\Entity\Receipt $master = null)
    {
        $this->master = $master;

        return $this;
    }

    /**
     * Get master
     *
     * @return \NvCarga\Bundle\Entity\Receipt
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     * Add receipt
     *
     * @param \NvCarga\Bundle\Entity\Receipt $receipt
     *
     * @return Receipt
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
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Receipt
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

    

    /**
     * Set signature
     *
     * @param string $signature
     *
     * @return Receipt
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
     public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }
    public function uploadSignature($path) {
        $file = $this->getFile();

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
     * @param \NvCarga\Bundle\Entity\WHrec $whrec
     *
     * @return Receipt
     */
    public function setWhrec(\NvCarga\Bundle\Entity\WHrec $whrec = null)
    {
        $this->whrec = $whrec;

        return $this;
    }

    /**
     * Get whrec
     *
     * @return \NvCarga\Bundle\Entity\WHrec
     */
    public function getWhrec()
    {
        return $this->whrec;
    }

    /**
     * Set statusguide
     *
     * @param boolean $statusguide
     *
     * @return Receipt
     */
    public function setStatusguide($statusguide)
    {
        $this->statusguide = $statusguide;

        return $this;
    }

    /**
     * Get statusguide
     *
     * @return boolean
     */
    public function getStatusguide()
    {
        return $this->statusguide;
    }

    /**
     * Set statuswhrec
     *
     * @param boolean $statuswhrec
     *
     * @return Receipt
     */
    public function setStatuswhrec($statuswhrec)
    {
        $this->statuswhrec = $statuswhrec;

        return $this;
    }

    /**
     * Get statuswhrec
     *
     * @return boolean
     */
    public function getStatuswhrec()
    {
        return $this->statuswhrec;
    }

    /**
     * Add liststatus
     *
     * @param \NvCarga\Bundle\Entity\Statusreceipt $liststatus
     *
     * @return Receipt
     */
    public function addListstatus(\NvCarga\Bundle\Entity\Statusreceipt $liststatus)
    {
        $this->liststatus[] = $liststatus;

        return $this;
    }

    /**
     * Remove liststatus
     *
     * @param \NvCarga\Bundle\Entity\Statusreceipt $liststatus
     */
    public function removeListstatus(\NvCarga\Bundle\Entity\Statusreceipt $liststatus)
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
        $array = $this->liststatus;
        $sort = array();
        $newarray = array();
        $count = 0;
        foreach ($array as $status) {
            $sort[$count] = $status->getDate();
            $newarray[$count]['datetime'] = $status->getDate();
            $newarray[$count]['status'] = $status->getStep()->getName();
            $count++;
        }
        array_multisort($sort, SORT_DESC, $newarray);
        $last = $newarray[0];
        return $last['status'];
    }
    
}
