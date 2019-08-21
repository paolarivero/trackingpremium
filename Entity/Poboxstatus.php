<?php
// src/NvCarga/Bundle/Entity/Poboxstatus.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="poboxstatus")
 * @ORM\HasLifecycleCallbacks()
 */
class Poboxstatus
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
     * @ORM\OneToMany(targetEntity="Pobox", mappedBy="status")
     */
    protected $poboxs;

    public function __toString() {
        return (string) $this->getName();
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->poboxs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Poboxstatus
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
     * @return Poboxstatus
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
     * @return Poboxstatus
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
     * Add pobox
     *
     * @param \NvCarga\Bundle\Entity\Pobox $pobox
     *
     * @return Poboxstatus
     */
    public function addPobox(\NvCarga\Bundle\Entity\Pobox $pobox)
    {
        $this->poboxs[] = $pobox;

        return $this;
    }

    /**
     * Remove pobox
     *
     * @param \NvCarga\Bundle\Entity\Pobox $pobox
     */
    public function removePobox(\NvCarga\Bundle\Entity\Pobox $pobox)
    {
        $this->poboxs->removeElement($pobox);
    }

    /**
     * Get poboxs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPoboxs()
    {
        return $this->poboxs;
    }
}
