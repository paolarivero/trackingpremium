<?php
// src/NvCarga/Bundle/Entity/Pobox.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="pobox")
 * @ORM\HasLifecycleCallbacks()
 */
class Pobox
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
     /**
     * @ORM\Column(type="string", length=30, unique=false,nullable=false)
     */
    protected $number;
    /**
     * @ORM\OneToOne(targetEntity="Customer", inversedBy="pobox")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\ManyToOne(targetEntity="Poboxtype", inversedBy="poboxs")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $type;
    /**
     * @ORM\ManyToOne(targetEntity="Poboxstatus", inversedBy="poboxs")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;
    /**
     * @ORM\ManyToOne(targetEntity="Warehouse", inversedBy="poboxs"))
     * @ORM\JoinColumn(name="warehouse_id", referencedColumnName="id")
     */
    protected $warehouse;
    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="pobox")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="createby_id", referencedColumnName="id")
     */
    protected $createby;
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;

    protected $password= null;

    public function __toString() {
        return (string) ($this->getNumber());
    }
    
   /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }     

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
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
     * @return Pobox
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
     * Set customer
     *
     * @param \NvCarga\Bundle\Entity\Customer $customer
     *
     * @return Pobox
     */
    public function setCustomer(\NvCarga\Bundle\Entity\Customer $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \NvCarga\Bundle\Entity\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set type
     *
     * @param \NvCarga\Bundle\Entity\Poboxtype $type
     *
     * @return Pobox
     */
    public function setType(\NvCarga\Bundle\Entity\Poboxtype $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \NvCarga\Bundle\Entity\Poboxtype
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set status
     *
     * @param \NvCarga\Bundle\Entity\Poboxstatus $status
     *
     * @return Pobox
     */
    public function setStatus(\NvCarga\Bundle\Entity\Poboxstatus $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \NvCarga\Bundle\Entity\Poboxstatus
     */
    public function getStatus()
    {
        return $this->status;
    }
    /**
     * Set user
     *
     * @param \NvCarga\Bundle\Entity\User $user
     *
     * @return Pobox
     */
    public function setUser(\NvCarga\Bundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \NvCarga\Bundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set number
     *
     * @param string $number
     *
     * @return Pobox
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
     * Set warehouse
     *
     * @param \NvCarga\Bundle\Entity\Warehouse $warehouse
     *
     * @return Pobox
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
     * Set createby
     *
     * @param \NvCarga\Bundle\Entity\User $createby
     *
     * @return Pobox
     */
    public function setCreateby(\NvCarga\Bundle\Entity\User $createby = null)
    {
        $this->createby = $createby;

        return $this;
    }

    /**
     * Get createby
     *
     * @return \NvCarga\Bundle\Entity\User
     */
    public function getCreateby()
    {
        return $this->createby;
    }

    /**
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Pobox
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
