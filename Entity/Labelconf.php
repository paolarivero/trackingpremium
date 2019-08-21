<?php
// src/NvCarga/Bundle/Entity/Labelconf.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="labelconf")
 * @ORM\HasLifecycleCallbacks()
 */
class Labelconf
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @Assert\NotBlank(message = "Debe escoger el elemento asociado a la etiqueta")
     * @ORM\Column(type="string", unique=false, columnDefinition="ENUM('Guide', 'Bill', 'Receipt', 'WHrec', 'Bag', 'Consolidated')", nullable=false)
     */
    protected $tableclass;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastupdate;
   /** 
     * @Assert\NotBlank(message = "Debe escoger el ancho")
     * @Assert\Range(
     *      min = 2,
     *      minMessage = "La etiqueta debe tener al menos {{ limit }} inch de ancho",
     * )
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $width;
   /** 
     * @Assert\NotBlank(message = "Debe escoger el alto")
     * @Assert\Range(
     *      min = 4,
     *      minMessage = "La etiqueta debe tener al menos {{ limit }} inch de alto",
     * )
     * @ORM\Column(type="integer", nullable=false) 
    */
    protected $height;
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;
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
     * Set tableclass
     *
     * @param string $tableclass
     *
     * @return Labelconf
     */
    public function setTableclass($tableclass)
    {
        $this->tableclass = $tableclass;

        return $this;
    }

    /**
     * Get tableclass
     *
     * @return string
     */
    public function getTableclass()
    {
        return $this->tableclass;
    }

    /**
     * Set lastupdate
     *
     * @param \DateTime $lastupdate
     *
     * @return Labelconf
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
     * Set width
     *
     * @param integer $width
     *
     * @return Labelconf
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     *
     * @return Labelconf
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Labelconf
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
