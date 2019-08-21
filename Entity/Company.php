<?php
// src/NvCarga/Bundle/Entity/Company.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Entity
 * @ORM\Table(name="company")
 * @ORM\HasLifecycleCallbacks()
 * @ExclusionPolicy("all")
 */
class Company
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;
     /**
     * @Assert\NotBlank(message = "El Nombre de la empresa no puede estar vacío")
     * @Assert\Length(
     *      min = 5,
     *      max = 60,
     *      minMessage = "El nombre de la empresa debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre de la empresa no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=60, unique=true, nullable=false)
     * @Expose
     */
    protected $name;
    /**
     * @Assert\NotBlank(message = "El país no puede estar vacío")
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $country;
    /**
     * @ORM\Column(type="text")
     * @Expose
     */
    protected $comment;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;
    /**
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=false)
     */
    protected $latitude;
    /**
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=false)
     */
    protected $longitude;
    public function __construct() {
        $this->latitude = 0.0;
        $this->longitude = 0.0;
    }

    public function __toString() {
        return (string) $this->getName();
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
     * @return Company
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
     * Set comment
     *
     * @param string $comment
     *
     * @return Company
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Company
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
     * Set country
     *
     * @param \NvCarga\Bundle\Entity\Country $country
     *
     * @return Localcompany
     */
    public function setCountry(\NvCarga\Bundle\Entity\Country $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return \NvCarga\Bundle\Entity\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Company
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
     * Set latitude
     *
     * @param string $latitude
     *
     * @return Company
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return Company
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
}
