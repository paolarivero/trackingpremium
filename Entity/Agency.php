<?php
// src/NvCarga/Bundle/Entity/Agency.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Entity
 * @ORM\Table(name="agency")
 * @ORM\HasLifecycleCallbacks()
 * @ExclusionPolicy("all")
 */
class Agency
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;
     /**
     * @Assert\NotBlank(message = "El Nombre de la agencia no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 40,
     *      minMessage = "El nombre de la agencia debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre de la agencia no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=40, nullable=false)
     * @Expose
     */
    protected $name;
     /**
     * @Assert\NotBlank(message = "El Teléfono de la agencia no puede estar vacío")
     * @Assert\Length(
     *      min = 10,
     *      max = 30,
     *      minMessage = "El teléfono de la agencia debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El teléfono de la agencia no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=30, nullable=false)
     * @Expose
     */
    protected $phone;
     /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Expose
     */
    protected $fax;
     /**
     * @Assert\NotBlank(message = "La dirección de la agencia no puede estar vacía")
     * @Assert\Length(
     *      min = 5,
     *      minMessage = "El dirección de la agencia debe tener al menos {{ limit }} caracteres",
     * )
     * @ORM\Column(type="text", nullable=false)
     * @Expose
     */
    protected $address;
     /**
     * @Assert\NotBlank(message = "Debe suministrar un email")
     * @Assert\Email(
     *     message = "El email '{{ value }}' no es válido.", 
     *     checkMX = true, 
     *     checkHost = true
     *     )
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Expose
     */
    protected $email;
    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     * @Expose
     */
    protected $webmaster;
   /**
     * @Assert\NotBlank(message = "El ZIP de la agencia no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 20,
     *      minMessage = "El ZIP de la agencia debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El ZIP de la agencia no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=20, nullable=false)
     * @Expose
     */
    protected $zip;
    /**
     * @ORM\ManyToOne(targetEntity="Agencystatus", inversedBy="agencies")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;
    /**
     * @ORM\ManyToOne(targetEntity="Agencytype", inversedBy="agencies")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $type;
   /**
     * @ORM\OneToOne(targetEntity="Warehouse", inversedBy="agency")
     * @ORM\JoinColumn(name="warehouse_id", referencedColumnName="id")
     */
    protected $warehouse;
    /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="agencies")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     * @Expose
     */
    protected $city; // La ciudad se asiga por la ciudad de la Bodega
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany", inversedBy="agencies")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany; // 
   /**
     * @Assert\NotBlank(message = "El nombre del manager de la agencia no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "El nombre del manager de la agencia debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre del manager de la agencia no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    protected $manager;
     /**
     * @ORM\Column(type="boolean")
     */
    protected $guidecopies;
     /**
     * @ORM\Column(type="boolean")
     */
    protected $sharecustomer;
     /**
     * @ORM\Column(type="boolean")
     * @Expose
     */
    protected $poboxs;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastupdate;
    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="agency")
     */
    protected $users;
    /**
     * @ORM\OneToMany(targetEntity="Customer", mappedBy="agency")
     */
    protected $customers;
    /**
     * @ORM\OneToMany(targetEntity="Agency", mappedBy="parent")
     */
    private $children;
    /**
     * @ORM\ManyToOne(targetEntity="Agency", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    public function __toString() {
        return (string) ($this->getName());
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->customers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sharecustomer = true;
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
     * @return Agency
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
     * Set phone
     *
     * @param string $phone
     *
     * @return Agency
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set fax
     *
     * @param string $fax
     *
     * @return Agency
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Agency
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Agency
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
     * Set webmaster
     *
     * @param string $webmaster
     *
     * @return Agency
     */
    public function setWebmaster($webmaster)
    {
        $this->webmaster = $webmaster;

        return $this;
    }

    /**
     * Get webmaster
     *
     * @return string
     */
    public function getWebmaster()
    {
        return $this->webmaster;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return Agency
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set manager
     *
     * @param string $manager
     *
     * @return Agency
     */
    public function setManager($manager)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Get manager
     *
     * @return string
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Set guidecopies
     *
     * @param boolean $guidecopies
     *
     * @return Agency
     */
    public function setGuidecopies($guidecopies)
    {
        $this->guidecopies = $guidecopies;

        return $this;
    }

    /**
     * Get guidecopies
     *
     * @return boolean
     */
    public function getGuidecopies()
    {
        return $this->guidecopies;
    }
    /**
     * Set poboxs
     *
     * @param boolean $poboxs
     *
     * @return Agency
     */
    public function setPoboxs($poboxs)
    {
        $this->poboxs = $poboxs;

        return $this;
    }

    /**
     * Get poboxs
     *
     * @return boolean
     */
    public function getPoboxs()
    {
        return $this->poboxs;
    }

    /**
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Agency
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
     * Set lastupdate
     *
     * @param \DateTime $lastupdate
     *
     * @return Agency
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
     * Set status
     *
     * @param \NvCarga\Bundle\Entity\Agencystatus $status
     *
     * @return Agency
     */
    public function setStatus(\NvCarga\Bundle\Entity\Agencystatus $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \NvCarga\Bundle\Entity\Agencystatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set type
     *
     * @param \NvCarga\Bundle\Entity\Agencytype $type
     *
     * @return Agency
     */
    public function setType(\NvCarga\Bundle\Entity\Agencytype $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \NvCarga\Bundle\Entity\Agencytype
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set warehouse
     *
     * @param \NvCarga\Bundle\Entity\Warehouse $warehouse
     *
     * @return Agency
     */
    public function setWarehouse(\NvCarga\Bundle\Entity\Warehouse $warehouse = null)
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * Get warehouse
     *
     * @return \NvCarga\Bundle\Entity\Warehouse
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * Set city
     *
     * @param \NvCarga\Bundle\Entity\City $city
     *
     * @return Agency
     */
    public function setCity(\NvCarga\Bundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \NvCarga\Bundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Add user
     *
     * @param \NvCarga\Bundle\Entity\User $user
     *
     * @return Agency
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
     * Add child
     *
     * @param \NvCarga\Bundle\Entity\Agency $child
     *
     * @return Agency
     */
    public function addChild(\NvCarga\Bundle\Entity\Agency $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \NvCarga\Bundle\Entity\Agency $child
     */
    public function removeChild(\NvCarga\Bundle\Entity\Agency $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \NvCarga\Bundle\Entity\Agency $parent
     *
     * @return Agency
     */
    public function setParent(\NvCarga\Bundle\Entity\Agency $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \NvCarga\Bundle\Entity\Agency
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Agency
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
     * Add customer
     *
     * @param \NvCarga\Bundle\Entity\Customer $customer
     *
     * @return Agency
     */
    public function addCustomer(\NvCarga\Bundle\Entity\Customer $customer)
    {
        $this->customers[] = $customer;

        return $this;
    }

    /**
     * Remove customer
     *
     * @param \NvCarga\Bundle\Entity\Customer $customer
     */
    public function removeCustomer(\NvCarga\Bundle\Entity\Customer $customer)
    {
        $this->customers->removeElement($customer);
    }

    /**
     * Get customers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    /**
     * Set sharecustomer
     *
     * @param boolean $sharecustomer
     *
     * @return Agency
     */
    public function setSharecustomer($sharecustomer)
    {
        $this->sharecustomer = $sharecustomer;

        return $this;
    }

    /**
     * Get sharecustomer
     *
     * @return boolean
     */
    public function getSharecustomer()
    {
        return $this->sharecustomer;
    }
}
