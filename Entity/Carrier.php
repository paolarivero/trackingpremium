<?php
// src/NvCarga/Bundle/Entity/Carrier.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="carrier")
 * @ORM\HasLifecycleCallbacks()
 */
class Carrier
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
     /**
     * @Assert\NotBlank(message = "El Nombre del carrier no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 40,
     *      minMessage = "El nombre del carrier debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre del carrier no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=40, unique=false, nullable=false)
     */
    protected $name;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\OneToMany(targetEntity="Receipt", mappedBy="carrier")
     */
    protected $receipts;
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;
    /**
     * @ORM\Column(type="boolean", options={"default":true})
     */
    protected $active=true;
    
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
     * @return Carrier
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
     * @return Carrier
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
     * @return Carrier
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
     * @return Carrier
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
     * @return Carrier
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
     * Set active
     *
     * @param boolean $active
     *
     * @return Carrier
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
}
