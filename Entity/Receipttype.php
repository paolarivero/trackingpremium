<?php
// src/NvCarga/Bundle/Entity/Receipttype.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="receipttype")
 * @ORM\HasLifecycleCallbacks()
 */
class Receipttype
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", length=30)
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
     * @ORM\OneToMany(targetEntity="Receipt", mappedBy="type")
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
     * @return Receipttype
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
     * @return Receipttype
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
     * @return Receipttype
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
     * @return Receipttype
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
