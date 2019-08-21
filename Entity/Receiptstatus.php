<?php
// src/NvCarga/Bundle/Entity/Receiptstatus.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="receiptstatus")
 * @ORM\HasLifecycleCallbacks()
 */
class Receiptstatus
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     @ORM\Column(type="string", length=30, unique=true, nullable=false)
     */
    protected $name;
    /**
     * @ORM\Column(type="text")
     */
    protected $description;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\OneToMany(targetEntity="Receipt", mappedBy="status")
     */
    protected $receipts;

    public function __toString() {
        return (string) $this->getName();
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->receipts = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Receiptstatus
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
     * @return Receiptstatus
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
     * @return Receiptstatus
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
     * Add receipt
     *
     * @param \NvCarga\Bundle\Entity\Receipt $receipt
     *
     * @return Receiptstatus
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
}
