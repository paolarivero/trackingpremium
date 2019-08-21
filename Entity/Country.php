<?php
// src/NvCarga/Bundle/Entity/Country.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Entity
 * @ORM\Table(name="country")
 * @ORM\HasLifecycleCallbacks()
 * @ExclusionPolicy("all")
 */
class Country
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;
    /**
     * @Assert\NotBlank(message = "El Nombre de la país no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "El nombre del país debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre del país no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=50, unique=true, nullable=false)
     * @Expose
     */
    protected $name;
    /**
     * @Assert\NotBlank(message = "El código de la país no puede estar vacío")
     * @Assert\Length(
     *      min = 2,
     *      max = 2,
     *      minMessage = "El código del país debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El código del país no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=2, unique=true, nullable=false)
     * @Expose
     */
    protected $code;
    /**
     * @ORM\OneToMany(targetEntity="State", mappedBy="country")
     */
    protected $states;
    /**
     * @ORM\ManyToMany(targetEntity="Maincompany", mappedBy="countries")
     */
    protected $maincompanies;

    public function __toString() {
        return (string) $this->getName();
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->states = new \Doctrine\Common\Collections\ArrayCollection();
        $this->companies = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Country
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
     * Set code
     *
     * @param string $code
     *
     * @return Country
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Add state
     *
     * @param \NvCarga\Bundle\Entity\State $state
     *
     * @return Country
     */
    public function addState(\NvCarga\Bundle\Entity\State $state)
    {
        $this->states[] = $state;

        return $this;
    }

    /**
     * Remove state
     *
     * @param \NvCarga\Bundle\Entity\State $state
     */
    public function removeState(\NvCarga\Bundle\Entity\State $state)
    {
        $this->states->removeElement($state);
    }

    /**
     * Get states
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * Add maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Country
     */
    public function addMaincompany(\NvCarga\Bundle\Entity\Maincompany $maincompany)
    {
        $this->maincompanies[] = $maincompany;

        return $this;
    }

    /**
     * Remove maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     */
    public function removeMaincompany(\NvCarga\Bundle\Entity\Maincompany $maincompany)
    {
        $this->maincompanies->removeElement($maincompany);
    }

    /**
     * Get maincompanies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMaincompanies()
    {
        return $this->maincompanies;
    }
    
    public function getNumcities()
    {
        $count = 0;
        foreach ($this->getStates() as $state) {
            $count+=$state->getNumcities();
        }
        return $count;
    }
}
