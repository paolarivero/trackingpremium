<?php
// src/NvCarga/Bundle/Entity/Customer.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Egulias\EmailValidator\Validation\RFCValidation;

/**
 * @ORM\Entity
 * @ORM\Table(name="customer")
 * @ORM\HasLifecycleCallbacks()
 */
class Customer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
     /**
     * @Assert\NotBlank(message = "El Nombre del cliente no puede estar vacío")
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
     * @Assert\Email(
     *     message = "El email '{{ value }}' no es válido.",
     *     checkMX = true, 
     *     checkHost = true
     *     )
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    protected $email;
    /**
     * @ORM\ManyToOne(targetEntity="Customerstatus", inversedBy="customers")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;
    /**
     * @ORM\ManyToOne(targetEntity="Customertype", inversedBy="customers")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $type;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\OneToMany(targetEntity="Receipt", mappedBy="shipper")
     */
    protected $shipped;
    /**
     * @ORM\OneToMany(targetEntity="Guide", mappedBy="sender")
     */
    protected $sguides;
    /**
     * @ORM\OneToMany(targetEntity="Baddress", mappedBy="customer")
     */
    protected $baddress;
    /**
     * @ORM\OneToOne(targetEntity="Pobox", mappedBy="customer" )
     * @ORM\JoinColumn(name="pobox_id", referencedColumnName="id")
     */
    private $pobox;
    /**
     * @ORM\OneToOne(targetEntity="Baddress")
     * @ORM\JoinColumn(name="adrdefault_id", referencedColumnName="id")
     */
    private $adrdefault;
    /**
     * @ORM\OneToOne(targetEntity="Baddress")
     * @ORM\JoinColumn(name="adrmain_id", referencedColumnName="id")
     */
    private $adrmain;
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;
    /**
     * @ORM\ManyToOne(targetEntity="Agency", inversedBy="customers")
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="id")
     */
    protected $agency;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $active=true;
    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false, options={"default":0.0}) 
     */
    protected $refunded;
    
    public function __toString() {
        return (string) ($this->getName() . ' ' . $this->getLastname());
	// return (string) ($this->getEmail());
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->shipped = new \Doctrine\Common\Collections\ArrayCollection();
        
        $this->sguides = new \Doctrine\Common\Collections\ArrayCollection();
      
        $this->baddress = new \Doctrine\Common\Collections\ArrayCollection();
        $this->active = true;
        $this->refunded = 0.0;
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
     * @return Customer
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
     * Set lastname
     *
     * @param string $lastname
     *
     * @return Customer
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
     * Set email
     *
     * @param string $email
     *
     * @return Customer
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
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Customer
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
     * Set status
     *
     * @param \NvCarga\Bundle\Entity\Customerstatus $status
     *
     * @return Customer
     */
    public function setStatus(\NvCarga\Bundle\Entity\Customerstatus $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \NvCarga\Bundle\Entity\Customerstatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set type
     *
     * @param \NvCarga\Bundle\Entity\Customertype $type
     *
     * @return Customer
     */
    public function setType(\NvCarga\Bundle\Entity\Customertype $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \NvCarga\Bundle\Entity\Customertype
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add shipped
     *
     * @param \NvCarga\Bundle\Entity\Receipt $shipped
     *
     * @return Customer
     */
    public function addShipped(\NvCarga\Bundle\Entity\Receipt $shipped)
    {
        $this->shipped[] = $shipped;

        return $this;
    }

    /**
     * Remove shipped
     *
     * @param \NvCarga\Bundle\Entity\Receipt $shipped
     */
    public function removeShipped(\NvCarga\Bundle\Entity\Receipt $shipped)
    {
        $this->shipped->removeElement($shipped);
    }

    /**
     * Get shipped
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getShipped()
    {
        return $this->shipped;
    }

    /**
     * Add sguide
     *
     * @param \NvCarga\Bundle\Entity\Guide $sguide
     *
     * @return Customer
     */
    public function addSguide(\NvCarga\Bundle\Entity\Guide $sguide)
    {
        $this->sguides[] = $sguide;

        return $this;
    }

    /**
     * Remove sguide
     *
     * @param \NvCarga\Bundle\Entity\Guide $sguide
     */
    public function removeSguide(\NvCarga\Bundle\Entity\Guide $sguide)
    {
        $this->sguides->removeElement($sguide);
    }

    /**
     * Get sguides
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSguides()
    {
        return $this->sguides;
    }



    /**
     * Set pobox
     *
     * @param \NvCarga\Bundle\Entity\Pobox $pobox
     *
     * @return Customer
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
     * Set adrdefault
     *
     * @param \NvCarga\Bundle\Entity\Baddress $adrdefault
     *
     * @return Customer
     */
    public function setAdrdefault(\NvCarga\Bundle\Entity\Baddress $adrdefault = null)
    {
        $this->adrdefault = $adrdefault;

        return $this;
    }

    /**
     * Get adrdefault
     *
     * @return \NvCarga\Bundle\Entity\Pobox
     */
    public function getAdrdefault()
    {
        return $this->adrdefault;
    }

    /**
     * Add baddress
     *
     * @param \NvCarga\Bundle\Entity\Baddress $baddress
     *
     * @return Customer
     */
    public function addBaddress(\NvCarga\Bundle\Entity\Baddress $baddress)
    {
        $this->baddress[] = $baddress;

        return $this;
    }

    /**
     * Remove baddress
     *
     * @param \NvCarga\Bundle\Entity\Baddress $baddress
     */
    public function removeBaddress(\NvCarga\Bundle\Entity\Baddress $baddress)
    {
        $this->baddress->removeElement($baddress);
    }

    /**
     * Get baddress
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBaddress()
    {
        return $this->baddress;
    }

    /**
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Customer
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
     * Set agency
     *
     * @param \NvCarga\Bundle\Entity\Agency $agency
     *
     * @return Customer
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
     * Set active
     *
     * @param boolean $active
     *
     * @return Customer
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
     * Set adrmain
     *
     * @param \NvCarga\Bundle\Entity\Baddress $adrmain
     *
     * @return Customer
     */
    public function setAdrmain(\NvCarga\Bundle\Entity\Baddress $adrmain = null)
    {
        $this->adrmain = $adrmain;

        return $this;
    }

    /**
     * Get adrmain
     *
     * @return \NvCarga\Bundle\Entity\Baddress
     */
    public function getAdrmain()
    {
        return $this->adrmain;
    }

    /**
     * Set refunded
     *
     * @param string $refunded
     *
     * @return Customer
     */
    public function setRefunded($refunded)
    {
        $this->refunded = $refunded;

        return $this;
    }

    /**
     * Get refunded
     *
     * @return string
     */
    public function getRefunded()
    {
        return $this->refunded;
    }
}
