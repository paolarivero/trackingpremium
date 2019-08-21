<?php
// src/NvCarga/Bundle/Entity/Maincompany.php

namespace NvCarga\Bundle\Entity;

use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="maincompany")
 * @ORM\HasLifecycleCallbacks()
 */
class Maincompany
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @Assert\NotBlank(message = "El nombre de la empresa NO puede estar vacío")
     * @Assert\Length(
     *      min = 5,
     *	    max = 50,
     *      minMessage = "El nombre de la empresa debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre de la empresa no debe tener mas de {{ limit }} caracteres",
     * )
     * @ORM\Column(type="string", length=50, unique=true, nullable=false)
     */
    protected $name;
   /** 
     * @Assert\NotBlank(message = "Debe asignar un valor al factor dimensional")
     * @Assert\Range(
     *      min = 10,
     *	    max = 1000,
     *      minMessage = "El factor dimensional debe ser al menos {{ limit }}",
     *      maxMessage = "El factor dimensional debe ser a lo sumo {{ limit }}",
     * )
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $dimfactor;
    /**
     * @Assert\NotBlank(message = "El acrónimo de la empresa NO puede estar vacío")
     * @Assert\Length(
     *      min = 2,
     *	    max = 20,
     *      minMessage = "El acrónimo de la empresa debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El acrónimo de la empresa no debe tener mas de {{ limit }} caracteres",
     * )
     * @ORM\Column(type="string", length=20, unique=true, nullable=false)
     */
    protected $acronym;
   /**
     * @Assert\NotBlank(message = "El prefijo del número de la guía no puede estar vacío")
     * @Assert\Length(
     *      min = 2,
     *	    max = 10,
     *      minMessage = "El prefijo debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El prefijo no debe tener mas de {{ limit }} caracteres",
     * )
     * @Assert\Regex(
     *     pattern="/^[a-z,A-Z,0-9,-]+$/i",
     *     match=true,
     *     message="El prefijo solo debe contener letras (a-z,A-Z), Números y  '-'"
     * )
     * @ORM\Column(type="string", length=10, nullable=false, options={"default":"000"})
     */
    protected $prefixguide;
   /**
     * @Assert\NotBlank(message = "El prefijo no puede estar vacío")
     * @Assert\Length(
     *      min = 2,
     *	    max = 10,
     *      minMessage = "El prefijo debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El prefijo no debe tener mas de {{ limit }} caracteres",
     * )
     * @Assert\Regex(
     *     pattern="/^[a-z,A-Z,0-9,-]+$/i",
     *     match=true,
     *     message="El prefijo solo debe contener letras (a-z,A-Z), Números y  '-'"
     * )
     * @ORM\Column(type="string", length=10,  nullable=false, options={"default":"000"})
     */
    protected $prefixpobox;
   /**
     * @Assert\NotBlank(message = "El prefijo no puede estar vacío")
     * @Assert\Length(
     *      min = 2,
     *	    max = 10,
     *      minMessage = "El prefijo debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El prefijo no debe tener mas de {{ limit }} caracteres",
     * )
     * @Assert\Regex(
     *     pattern="/^[a-z,A-Z,0-9,-]+$/i",
     *     match=true,
     *     message="El prefijo solo debe contener letras (a-z,A-Z), Números y  '-'"
     * )
     * @ORM\Column(type="string", length=10, nullable=false, options={"default":"000"})
     */
    protected $prefixconsol;
    /**
     * @Assert\NotBlank(message = "El URL de la empresa NO puede estar vacío")
     * @Assert\Length(
     *      min = 8,
     *	    max = 50,
     *      minMessage = "El URL de la empresa debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El URL de la empresa no debe tener mas de {{ limit }} caracteres",
     * )
     * @ORM\Column(type="string", length=80, unique=true, nullable=false)
     */
    protected $url;
    /**
     * @Assert\NotBlank(message = "El homepage del sistema NO puede estar vacío. Debe ser de la forma nombreempresa.trackingpremium.com")
     * @Assert\Length(
     *      min = 8,
     *	    max = 50,
     *      minMessage = "El homepage del sistema debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El homepage del sistema no debe tener mas de {{ limit }} caracteres",
     * )
     * @ORM\Column(type="string", length=80, unique=true, nullable=false)
     */
    protected $homepage;
    /**
     * @ORM\Column(type="string", length=80, unique=true, nullable=true)
     */
    protected $homepage_aux;
    
     /**
     * @Assert\NotBlank(message = "La empresa debe tener un EMAIL")
     * @Assert\Email(
     *     message = "El email '{{ value }}' no es válido.")
     * @ORM\Column(type="string", length=60, unique=true, nullable=false)
     */
    protected $email;
    /**
     * @ORM\OneToMany(targetEntity="Agency", mappedBy="maincompany")
     */
    protected $agencies;
     /**
     * @Assert\NotBlank(message = "El mensaje a casilleros no puede estar vacío")
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "El mensaje a casilleros debe tener al menos {{ limit }} caracteres",
     * )
     * @ORM\Column(type="text", nullable=false)
     */	
    protected $poboxmsg;
    /**
     @ORM\Column(type="string", columnDefinition="ENUM('Ninguno', 'Individual', 'Total')", nullable=false)
     */
    protected $roundweight='Ninguno';
    /**
     @ORM\Column(type="string", columnDefinition="ENUM('Ninguno', 'Individual', 'Total')", nullable=false)
     */
    protected $roundvol='Ninguno';

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    protected $roundtotal;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    protected $customername;
   /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    protected $companyname;
   /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    protected $numbername;
   /** 
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "La numeración inicial de casilleros debe ser al menos {{ limit }}",
     * )
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $ininum;

   /** 
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "La numeración debe ser al menos {{ limit }}",
     * )
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $iniguide;
   /**
     * @Assert\NotBlank(message = "Debe asignar el valor para convertir a unidades de volumen")
     * @Assert\Range(
     *      min = 0.1,
     *      minMessage = "El valor debe ser igual o mayor a {{ limit }}lb",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $convertvol;
    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="maincompany")
     */
    protected $users;
    /** 
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $countguides;
    /** 
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $countreceipts;
    /** 
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $countwhrecs;
    /** 
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $countbills;
    /** 
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $countbags;
    /** 
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $countpoboxes;
    /** 
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $countcustomers;
     /** 
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $countconsolidates;
    /** 
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $countusers;
    /** 
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $countagencies;
    /** 
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $countaccounts;
    /** 
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $countalerts;
    /** 
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $countadservices;
    /** 
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $countcompanies;
    /** 
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $countadmins;
    /**
     * @ORM\ManyToMany(targetEntity="Country", inversedBy="maincompanies")
     * @ORM\JoinTable(name="maincompanies_countries")
     */
    protected $countries;
    /**
     * @ORM\OneToOne(targetEntity="Format", mappedBy = "maincompany")
     * @ORM\JoinColumn(name="format_id", referencedColumnName="id")
     */
    protected $format;
    /**
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(name="companyuser_id", referencedColumnName="id")
     */
    protected $companyuser;
    /**
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     */
    protected $token;
    /**
     * @ORM\Column(name="poboxheader", type="string", length=255, nullable=false, options={"default":"Registrar un casillero es rápido y gratuito - sin compromisos ni contratos."})
     */
    protected $poboxheader;
    
    /**
     * @Assert\File(
     *     maxSize = "512000",
     *     maxSizeMessage = "El tamaño del archivo debe ser menor a  {{ limit }} bytes",
     *     mimeTypes = {"image/jpeg", "image/png"},
     *     mimeTypesMessage = "Por favor, seleccione un tipo de archivo válido (png/jpg)")
    */
    private $file;
    
    /**
     * @ORM\Column(name="logo", type="string", length=255, nullable=true)
     */
    protected $logo;
    /**
     * @ORM\OneToOne(targetEntity="Subscriber", mappedBy = "maincompany")
     * @ORM\JoinColumn(name="subscriber_id", referencedColumnName="id")
     */
    protected $subscriber;
    /**
     * @ORM\ManyToOne(targetEntity="Plan", inversedBy="companies")
     * @ORM\JoinColumn(name="plan_id", referencedColumnName="id")
     */
    protected $plan;
    /**
     * @ORM\Column(name="logomain", type="string", length=255, nullable=true)
     */
    protected $logomain;
    /**
     * @ORM\Column(name="background", type="string", length=255, nullable=true)
     */
    protected $background;
     /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    protected $inactive;
    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    protected $showalltariffs;
    /**
     * @ORM\Column(type="boolean", options={"default"=true})
     */
    protected $firststatus;
    /**
     * @Assert\Url(
     *    message = "El valor del URL  '{{ value }}' no es válido",
     *    protocols = {"http", "https"}
     * )
     * @Assert\Length(
     *      min = 8,
     *	    max = 150,
     *      minMessage = "El URL de los pagos debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El URL de los pagosno debe tener mas de {{ limit }} caracteres",
     * )
     * @ORM\Column(type="string", length=150, unique=false, nullable=true)
     */
    protected $billurl;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->agencies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->countries = new \Doctrine\Common\Collections\ArrayCollection();
        
        $this->countguides=0;
        $this->countreceipts=0;
        $this->countwhrecs=0;
        $this->countbills=0;
        $this->countbags=0;
        $this->countpoboxes=0;
        $this->countcustomers=0;
        $this->countconsolidates=0;
        $this->countusers=0;
        $this->countagencies=0;
        $this->countaccounts=0;
        $this->countalerts=0;
        $this->countadservices=0;
        $this->countcompanies=0;
        $this->countadmins=0;
        $this->firststatus=true;
        $this->showalltariffs=false;
    }
    public function __toString() {
        return (string) $this->getName();
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
    /**
     * Set logo
     *
     * @param string $logo
     *
     * @return Maincompany
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }
    public function upload($id, $path, $fileName, $methodname) {
        
        if (null === $fileName) {
            return;
        }
        $get = 'get' . $methodname;
        $set = 'set' . $methodname;
        // updates the 'File' property to store the PDF file name
        // instead of its contents
        if ($this->$get() && ($this->$get() != 'usuario.png')) {
            if (file_exists($path . '/' . $this->$get())) {
                unlink($path . '/' . $this->$get());
            }
        }
        if (file_exists($path. '/tmp/' . $fileName)) {
            rename($path. '/tmp/' . $fileName, $path . '/' . $fileName);
            $this->$set($fileName);
        }
        foreach(glob($path .'/tmp/' . $id . '-*') as $f) {
            unlink($f);
        }
    }
    /*
    public function upload($path) {
        
        if (null === $this->file) {
            return;
        }
        $file = $this->getFile();
    
        $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

        // moves the file to the directory where Files are stored
        $file->move(
            $path,
            $fileName
        );

        // updates the 'File' property to store the PDF file name
        // instead of its contents
        if ($this->getLogo() && ($this->getLogo() != 'default.png')) {
            if (file_exists($path . '/' . $this->getLogo())) {
                unlink($path . '/' . $this->getLogo());
            }
        }
        $this->setLogo($fileName);
    }
    */
    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        //return md5(uniqid());
        return $this->getAcronym();
    }

    /**
     * Set maxwhrecs
     *
     * @param integer $maxwhrecs
     *
     * @return Maincompany
     */
    public function setMaxwhrecs($maxwhrecs)
    {
        $this->maxwhrecs = $maxwhrecs;

        return $this;
    }

    /**
     * Get maxwhrecs
     *
     * @return integer
     */
    public function getMaxwhrecs()
    {
        return $this->maxwhrecs;
    }

    /**
     * Set countwhrecs
     *
     * @param integer $countwhrecs
     *
     * @return Maincompany
     */
    public function setCountwhrecs($countwhrecs)
    {
        $this->countwhrecs = $countwhrecs;

        return $this;
    }

    /**
     * Get countwhrecs
     *
     * @return integer
     */
    public function getCountwhrecs()
    {
        return $this->countwhrecs;
    }

    /**
     * Set subscriber
     *
     * @param \NvCarga\Bundle\Entity\Subscriber $subscriber
     *
     * @return Maincompany
     */
    public function setSubscriber(\NvCarga\Bundle\Entity\Subscriber $subscriber = null)
    {
        $this->subscriber = $subscriber;

        return $this;
    }

    /**
     * Get subscriber
     *
     * @return \NvCarga\Bundle\Entity\Subscriber
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * Set plan
     *
     * @param \NvCarga\Bundle\Entity\Plan $plan
     *
     * @return Maincompany
     */
    public function setPlan(\NvCarga\Bundle\Entity\Plan $plan = null)
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * Get plan
     *
     * @return \NvCarga\Bundle\Entity\Plan
     */
    public function getPlan()
    {
        return $this->plan;
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
     * @return Maincompany
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
     * Set dimfactor
     *
     * @param integer $dimfactor
     *
     * @return Maincompany
     */
    public function setDimfactor($dimfactor)
    {
        $this->dimfactor = $dimfactor;

        return $this;
    }

    /**
     * Get dimfactor
     *
     * @return integer
     */
    public function getDimfactor()
    {
        return $this->dimfactor;
    }

    /**
     * Set acronym
     *
     * @param string $acronym
     *
     * @return Maincompany
     */
    public function setAcronym($acronym)
    {
        $this->acronym = $acronym;

        return $this;
    }

    /**
     * Get acronym
     *
     * @return string
     */
    public function getAcronym()
    {
        return $this->acronym;
    }

    /**
     * Set prefixguide
     *
     * @param string $prefixguide
     *
     * @return Maincompany
     */
    public function setPrefixguide($prefixguide)
    {
        $this->prefixguide = $prefixguide;

        return $this;
    }

    /**
     * Get prefixguide
     *
     * @return string
     */
    public function getPrefixguide()
    {
        return $this->prefixguide;
    }

    /**
     * Set prefixpobox
     *
     * @param string $prefixpobox
     *
     * @return Maincompany
     */
    public function setPrefixpobox($prefixpobox)
    {
        $this->prefixpobox = $prefixpobox;

        return $this;
    }

    /**
     * Get prefixpobox
     *
     * @return string
     */
    public function getPrefixpobox()
    {
        return $this->prefixpobox;
    }

    /**
     * Set prefixconsol
     *
     * @param string $prefixconsol
     *
     * @return Maincompany
     */
    public function setPrefixconsol($prefixconsol)
    {
        $this->prefixconsol = $prefixconsol;

        return $this;
    }

    /**
     * Get prefixconsol
     *
     * @return string
     */
    public function getPrefixconsol()
    {
        return $this->prefixconsol;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Maincompany
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set homepage
     *
     * @param string $homepage
     *
     * @return Maincompany
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;

        return $this;
    }

    /**
     * Get homepage
     *
     * @return string
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Maincompany
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set poboxmsg
     *
     * @param string $poboxmsg
     *
     * @return Maincompany
     */
    public function setPoboxmsg($poboxmsg)
    {
        $this->poboxmsg = $poboxmsg;

        return $this;
    }

    /**
     * Get poboxmsg
     *
     * @return string
     */
    public function getPoboxmsg()
    {
        return $this->poboxmsg;
    }

    /**
     * Set roundweight
     *
     * @param string $roundweight
     *
     * @return Maincompany
     */
    public function setRoundweight($roundweight)
    {
        $this->roundweight = $roundweight;

        return $this;
    }

    /**
     * Get roundweight
     *
     * @return string
     */
    public function getRoundweight()
    {
        return $this->roundweight;
    }

    /**
     * Set roundvol
     *
     * @param string $roundvol
     *
     * @return Maincompany
     */
    public function setRoundvol($roundvol)
    {
        $this->roundvol = $roundvol;

        return $this;
    }

    /**
     * Get roundvol
     *
     * @return string
     */
    public function getRoundvol()
    {
        return $this->roundvol;
    }

    /**
     * Set roundtotal
     *
     * @param boolean $roundtotal
     *
     * @return Maincompany
     */
    public function setRoundtotal($roundtotal)
    {
        $this->roundtotal = $roundtotal;

        return $this;
    }

    /**
     * Get roundtotal
     *
     * @return boolean
     */
    public function getRoundtotal()
    {
        return $this->roundtotal;
    }

    /**
     * Set customername
     *
     * @param boolean $customername
     *
     * @return Maincompany
     */
    public function setCustomername($customername)
    {
        $this->customername = $customername;

        return $this;
    }

    /**
     * Get customername
     *
     * @return boolean
     */
    public function getCustomername()
    {
        return $this->customername;
    }

    /**
     * Set companyname
     *
     * @param boolean $companyname
     *
     * @return Maincompany
     */
    public function setCompanyname($companyname)
    {
        $this->companyname = $companyname;

        return $this;
    }

    /**
     * Get companyname
     *
     * @return boolean
     */
    public function getCompanyname()
    {
        return $this->companyname;
    }

    /**
     * Set numbername
     *
     * @param boolean $numbername
     *
     * @return Maincompany
     */
    public function setNumbername($numbername)
    {
        $this->numbername = $numbername;

        return $this;
    }

    /**
     * Get numbername
     *
     * @return boolean
     */
    public function getNumbername()
    {
        return $this->numbername;
    }

    /**
     * Set ininum
     *
     * @param integer $ininum
     *
     * @return Maincompany
     */
    public function setIninum($ininum)
    {
        $this->ininum = $ininum;

        return $this;
    }

    /**
     * Get ininum
     *
     * @return integer
     */
    public function getIninum()
    {
        return $this->ininum;
    }

    /**
     * Set iniguide
     *
     * @param integer $iniguide
     *
     * @return Maincompany
     */
    public function setIniguide($iniguide)
    {
        $this->iniguide = $iniguide;

        return $this;
    }

    /**
     * Get iniguide
     *
     * @return integer
     */
    public function getIniguide()
    {
        return $this->iniguide;
    }

    /**
     * Set convertvol
     *
     * @param string $convertvol
     *
     * @return Maincompany
     */
    public function setConvertvol($convertvol)
    {
        $this->convertvol = $convertvol;

        return $this;
    }

    /**
     * Get convertvol
     *
     * @return string
     */
    public function getConvertvol()
    {
        return $this->convertvol;
    }

    /**
     * Set countguides
     *
     * @param integer $countguides
     *
     * @return Maincompany
     */
    public function setCountguides($countguides)
    {
        $this->countguides = $countguides;

        return $this;
    }

    /**
     * Get countguides
     *
     * @return integer
     */
    public function getCountguides()
    {
        return $this->countguides;
    }

    /**
     * Set countreceipts
     *
     * @param integer $countreceipts
     *
     * @return Maincompany
     */
    public function setCountreceipts($countreceipts)
    {
        $this->countreceipts = $countreceipts;

        return $this;
    }

    /**
     * Get countreceipts
     *
     * @return integer
     */
    public function getCountreceipts()
    {
        return $this->countreceipts;
    }

    /**
     * Set countbills
     *
     * @param integer $countbills
     *
     * @return Maincompany
     */
    public function setCountbills($countbills)
    {
        $this->countbills = $countbills;

        return $this;
    }

    /**
     * Get countbills
     *
     * @return integer
     */
    public function getCountbills()
    {
        return $this->countbills;
    }

    /**
     * Set countbags
     *
     * @param integer $countbags
     *
     * @return Maincompany
     */
    public function setCountbags($countbags)
    {
        $this->countbags = $countbags;

        return $this;
    }

    /**
     * Get countbags
     *
     * @return integer
     */
    public function getCountbags()
    {
        return $this->countbags;
    }

    /**
     * Set countpoboxes
     *
     * @param integer $countpoboxes
     *
     * @return Maincompany
     */
    public function setCountpoboxes($countpoboxes)
    {
        $this->countpoboxes = $countpoboxes;

        return $this;
    }

    /**
     * Get countpoboxes
     *
     * @return integer
     */
    public function getCountpoboxes()
    {
        return $this->countpoboxes;
    }

    /**
     * Set countcustomers
     *
     * @param integer $countcustomers
     *
     * @return Maincompany
     */
    public function setCountcustomers($countcustomers)
    {
        $this->countcustomers = $countcustomers;

        return $this;
    }

    /**
     * Get countcustomers
     *
     * @return integer
     */
    public function getCountcustomers()
    {
        return $this->countcustomers;
    }

    /**
     * Set countconsolidates
     *
     * @param integer $countconsolidates
     *
     * @return Maincompany
     */
    public function setCountconsolidates($countconsolidates)
    {
        $this->countconsolidates = $countconsolidates;

        return $this;
    }

    /**
     * Get countconsolidates
     *
     * @return integer
     */
    public function getCountconsolidates()
    {
        return $this->countconsolidates;
    }

    /**
     * Set countusers
     *
     * @param integer $countusers
     *
     * @return Maincompany
     */
    public function setCountusers($countusers)
    {
        $this->countusers = $countusers;

        return $this;
    }

    /**
     * Get countusers
     *
     * @return integer
     */
    public function getCountusers()
    {
        return $this->countusers;
    }

    /**
     * Set countagencies
     *
     * @param integer $countagencies
     *
     * @return Maincompany
     */
    public function setCountagencies($countagencies)
    {
        $this->countagencies = $countagencies;

        return $this;
    }

    /**
     * Get countagencies
     *
     * @return integer
     */
    public function getCountagencies()
    {
        return $this->countagencies;
    }

    /**
     * Set countaccounts
     *
     * @param integer $countaccounts
     *
     * @return Maincompany
     */
    public function setCountaccounts($countaccounts)
    {
        $this->countaccounts = $countaccounts;

        return $this;
    }

    /**
     * Get countaccounts
     *
     * @return integer
     */
    public function getCountaccounts()
    {
        return $this->countaccounts;
    }

    /**
     * Set countalerts
     *
     * @param integer $countalerts
     *
     * @return Maincompany
     */
    public function setCountalerts($countalerts)
    {
        $this->countalerts = $countalerts;

        return $this;
    }

    /**
     * Get countalerts
     *
     * @return integer
     */
    public function getCountalerts()
    {
        return $this->countalerts;
    }

    /**
     * Set countadservices
     *
     * @param integer $countadservices
     *
     * @return Maincompany
     */
    public function setCountadservices($countadservices)
    {
        $this->countadservices = $countadservices;

        return $this;
    }

    /**
     * Get countadservices
     *
     * @return integer
     */
    public function getCountadservices()
    {
        return $this->countadservices;
    }

    /**
     * Set countcompanies
     *
     * @param integer $countcompanies
     *
     * @return Maincompany
     */
    public function setCountcompanies($countcompanies)
    {
        $this->countcompanies = $countcompanies;

        return $this;
    }

    /**
     * Get countcompanies
     *
     * @return integer
     */
    public function getCountcompanies()
    {
        return $this->countcompanies;
    }

    /**
     * Add agency
     *
     * @param \NvCarga\Bundle\Entity\Agency $agency
     *
     * @return Maincompany
     */
    public function addAgency(\NvCarga\Bundle\Entity\Agency $agency)
    {
        $this->agencies[] = $agency;

        return $this;
    }

    /**
     * Remove agency
     *
     * @param \NvCarga\Bundle\Entity\Agency $agency
     */
    public function removeAgency(\NvCarga\Bundle\Entity\Agency $agency)
    {
        $this->agencies->removeElement($agency);
    }

    /**
     * Get agencies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAgencies()
    {
        return $this->agencies;
    }

    /**
     * Add user
     *
     * @param \NvCarga\Bundle\Entity\User $user
     *
     * @return Maincompany
     */
    public function addUser(\NvCarga\Bundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \NvCarga\Bundle\Entity\User $user
     */
    public function removeUser(\NvCarga\Bundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add country
     *
     * @param \NvCarga\Bundle\Entity\Country $country
     *
     * @return Maincompany
     */
    public function addCountry(\NvCarga\Bundle\Entity\Country $country)
    {
        $this->countries[] = $country;

        return $this;
    }

    /**
     * Remove country
     *
     * @param \NvCarga\Bundle\Entity\Country $country
     */
    public function removeCountry(\NvCarga\Bundle\Entity\Country $country)
    {
        $this->countries->removeElement($country);
    }

    /**
     * Get countries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * Set format
     *
     * @param \NvCarga\Bundle\Entity\Format $format
     *
     * @return Maincompany
     */
    public function setFormat(\NvCarga\Bundle\Entity\Format $format = null)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get format
     *
     * @return \NvCarga\Bundle\Entity\Format
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set countadmins
     *
     * @param integer $countadmins
     *
     * @return Maincompany
     */
    public function setCountadmins($countadmins)
    {
        $this->countadmins = $countadmins;

        return $this;
    }

    /**
     * Get countadmins
     *
     * @return integer
     */
    public function getCountadmins()
    {
        return $this->countadmins;
    }

    /**
     * Set companyuser
     *
     * @param \NvCarga\Bundle\Entity\User $companyuser
     *
     * @return Maincompany
     */
    public function setCompanyuser(\NvCarga\Bundle\Entity\User $companyuser = null)
    {
        $this->companyuser = $companyuser;

        return $this;
    }

    /**
     * Get companyuser
     *
     * @return \NvCarga\Bundle\Entity\User
     */
    public function getCompanyuser()
    {
        return $this->companyuser;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return Maincompany
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set logomain
     *
     * @param string $logomain
     *
     * @return Maincompany
     */
    public function setLogomain($logomain)
    {
        $this->logomain = $logomain;

        return $this;
    }

    /**
     * Get logomain
     *
     * @return string
     */
    public function getLogomain()
    {
        return $this->logomain;
    }

    /**
     * Set background
     *
     * @param string $background
     *
     * @return Maincompany
     */
    public function setBackground($background)
    {
        $this->background = $background;

        return $this;
    }

    /**
     * Get background
     *
     * @return string
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * Set homepageAux
     *
     * @param string $homepageAux
     *
     * @return Maincompany
     */
    public function setHomepageAux($homepageAux)
    {
        $this->homepage_aux = $homepageAux;

        return $this;
    }

    /**
     * Get homepageAux
     *
     * @return string
     */
    public function getHomepageAux()
    {
        return $this->homepage_aux;
    }

    /**
     * Set poboxheader
     *
     * @param string $poboxheader
     *
     * @return Maincompany
     */
    public function setPoboxheader($poboxheader)
    {
        $this->poboxheader = $poboxheader;

        return $this;
    }

    /**
     * Get poboxheader
     *
     * @return string
     */
    public function getPoboxheader()
    {
        return $this->poboxheader;
    }

    /**
     * Set inactive
     *
     * @param boolean $inactive
     *
     * @return Maincompany
     */
    public function setInactive($inactive)
    {
        $this->inactive = $inactive;

        return $this;
    }

    /**
     * Get inactive
     *
     * @return boolean
     */
    public function getInactive()
    {
        return $this->inactive;
    }

    /**
     * Set billurl
     *
     * @param string $billurl
     *
     * @return Maincompany
     */
    public function setBillurl($billurl)
    {
        $this->billurl = $billurl;

        return $this;
    }

    /**
     * Get billurl
     *
     * @return string
     */
    public function getBillurl()
    {
        return $this->billurl;
    }

    /**
     * Set firststatus
     *
     * @param boolean $firststatus
     *
     * @return Maincompany
     */
    public function setFirststatus($firststatus)
    {
        $this->firststatus = $firststatus;

        return $this;
    }

    /**
     * Get firststatus
     *
     * @return boolean
     */
    public function getFirststatus()
    {
        return $this->firststatus;
    }

    /**
     * Set showalltariffs
     *
     * @param boolean $showalltariffs
     *
     * @return Maincompany
     */
    public function setShowalltariffs($showalltariffs)
    {
        $this->showalltariffs = $showalltariffs;

        return $this;
    }

    /**
     * Get showalltariffs
     *
     * @return boolean
     */
    public function getShowalltariffs()
    {
        return $this->showalltariffs;
    }
}
