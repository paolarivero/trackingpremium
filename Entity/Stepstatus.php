<?php
// src/NvCarga/Bundle/Entity/Stepstatus.php

namespace NvCarga\Bundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="stepstatus")
 * @ORM\HasLifecycleCallbacks()
 */
class Stepstatus
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @Assert\NotBlank(message = "El nombre no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 80,
     *      minMessage = "El nombre debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=80, nullable=false)
     */
    protected $name;
    /**
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "El porcentaje debe ser por lo menos {{ limit }}",
     *      max = 100,
     *      maxMessage = "El porcentaje no puede mayor a {{ limit }}",
     * )
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $percentage;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;
    /**
     @ORM\Column(type="boolean", options={"default":true})
     */
    protected $active;

    public function __toString() {
        return (string) ($this->getName());
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
     * @return Stepstatus
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
     * Set percentage
     *
     * @param integer $percentage
     *
     * @return Stepstatus
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;

        return $this;
    }

    /**
     * Get percentage
     *
     * @return integer
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Stepstatus
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
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Stepstatus
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
     * @return Stepstatus
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
