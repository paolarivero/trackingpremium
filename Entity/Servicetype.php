<?php
// src/NvCarga/Bundle/Entity/Servicetype.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity
 * @ORM\Table(name="servicetype")
 * @ORM\HasLifecycleCallbacks()
 */
class Servicetype
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
     /**
     * @Assert\NotBlank(message = "El Nombre del tipo de servicio no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "El nombre del tipo de servicio debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre del tipo de servicio no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    protected $name;
   /**
     * @ORM\ManyToOne(targetEntity="Shippingtype")
     * @ORM\JoinColumn(name="shippingtype_id", referencedColumnName="id")
     */
    protected $shippingtype;
   /**
     * @ORM\ManyToOne(targetEntity="Agency")
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="id")
     */
    protected $agency;
    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    protected $status; //Por ahora es una cadena... No se usa
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;

    public function __toString() {
        return (string) $this->getName();
    }

    /**
     * Set shippingtype
     *
     * @param \NvCarga\Bundle\Entity\Shippingtype $shippingtype
     *
     * @return Consolidated
     */
    public function setShippingtype(\NvCarga\Bundle\Entity\Shippingtype $shippingtype = null)
    {
        $this->shippingtype = $shippingtype;

        return $this;
    }

    /**
     * Get shippingtype
     *
     * @return \NvCarga\Bundle\Entity\Shippingtype
     */
    public function getShippingtype()
    {
        return $this->shippingtype;
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
     * @return Servicetype
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
     * Set status
     *
     * @param string $status
     *
     * @return Servicetype
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Servicetype
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
     * Set agency
     *
     * @param \NvCarga\Bundle\Entity\Agency $agency
     *
     * @return Servicetype
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
}
