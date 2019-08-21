<?php
// src/NvCarga/Bundle/Entity/Customertype.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="customertype")
 * @ORM\HasLifecycleCallbacks()
 */
class Customertype
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", length=30, unique=true, nullable=false)
     */
    protected $name;
    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected $description;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\OneToMany(targetEntity="Customer", mappedBy="type")
     */
    protected $customers;

    public function __toString() {
        return (string) $this->getName();
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->customers = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Customertype
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
     * Set description
     *
     * @param string $description
     *
     * @return Customertype
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
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Customertype
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
     * Add customer
     *
     * @param \NvCarga\Bundle\Entity\Customer $customer
     *
     * @return Customertype
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
}
