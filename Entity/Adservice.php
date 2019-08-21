<?php
// src/NvCarga/Bundle/Entity/Adservice.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="adservice")
 * @ORM\HasLifecycleCallbacks()
 */
class Adservice
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
     /**
     * @Assert\NotBlank(message = "El Nombre del servicio no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 80,
     *      minMessage = "El nombre del servicio debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre del servicio no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=80, unique=false, nullable=false)
     */
    protected $name;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
   /** 
    * @ORM\Column(type="string", columnDefinition="ENUM('Lb', 'Kg', 'CF', 'M3', 'Unit', 'Miles', '%', 'N/A')") 
   */ 
    protected $measure;	
   /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $brand;   
   /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description; 
   /**
     * @Assert\NotBlank(message = "Debe asignar el precio del servicio")
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "La precio mínimo debe ser igual o mayor a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $price;  
    /**
     * Many Adservices have Many Adservices.
     * @ORM\ManyToMany(targetEntity="Adservice", mappedBy="dependofMe")
     */
    private $meDependof;

    /**
     * Many Adservices have many Adservices.
     * @ORM\ManyToMany(targetEntity="Adservice", inversedBy="meDependof")
     * @ORM\JoinTable(name="servdepend")
     */
    private $dependofMe;   
    /**
     * @ORM\Column(type="boolean", nullable=false )
     */
    protected $isactive = true;
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;

    public function __toString() {
        return (string) $this->getName();
    } 
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->meDependof = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dependofMe = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Adservice
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
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Adservice
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
     * Set measure
     *
     * @param string $measure
     *
     * @return Adservice
     */
    public function setMeasure($measure)
    {
        $this->measure = $measure;

        return $this;
    }

    /**
     * Get measure
     *
     * @return string
     */
    public function getMeasure()
    {
        return $this->measure;
    }

    /**
     * Set brand
     *
     * @param string $brand
     *
     * @return Adservice
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand
     *
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Adservice
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
     * Set price
     *
     * @param string $price
     *
     * @return Adservice
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Add meDependof
     *
     * @param \NvCarga\Bundle\Entity\Adservice $meDependof
     *
     * @return Adservice
     */
    public function addMeDependof(\NvCarga\Bundle\Entity\Adservice $meDependof)
    {
        $this->meDependof[] = $meDependof;

        return $this;
    }

    /**
     * Remove meDependof
     *
     * @param \NvCarga\Bundle\Entity\Adservice $meDependof
     */
    public function removeMeDependof(\NvCarga\Bundle\Entity\Adservice $meDependof)
    {
        $this->meDependof->removeElement($meDependof);
    }

    /**
     * Get meDependof
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMeDependof()
    {
        return $this->meDependof;
    }

    /**
     * Add dependofMe
     *
     * @param \NvCarga\Bundle\Entity\Adservice $dependofMe
     *
     * @return Adservice
     */
    public function addDependofMe(\NvCarga\Bundle\Entity\Adservice $dependofMe)
    {
        $this->dependofMe[] = $dependofMe;

        return $this;
    }

    /**
     * Remove dependofMe
     *
     * @param \NvCarga\Bundle\Entity\Adservice $dependofMe
     */
    public function removeDependofMe(\NvCarga\Bundle\Entity\Adservice $dependofMe)
    {
        $this->dependofMe->removeElement($dependofMe);
    }

    /**
     * Get dependofMe
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDependofMe()
    {
        return $this->dependofMe;
    }
   /**
     * Set isactive
     *
     * @param boolean $isactive
     *
     * @return Payment
     */
    public function setIsactive($isactive)
    {
        $this->isactive = $isactive;

        return $this;
    }

    /**
     * Get isactive
     *
     * @return boolean
     */
    public function getIsactive()
    {
        return $this->isactive;
    }

    /**
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Adservice
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
