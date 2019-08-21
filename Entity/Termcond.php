<?php
// src/NvCarga/Bundle/Entity/Termcond.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="termcond")
 * @ORM\HasLifecycleCallbacks()
 */
class Termcond
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @Assert\NotBlank(message = "El mensaje de TERMINOS y CONDICIONES no puede estar vacío")
     * @ORM\Column(type="text", nullable=false)
     */
    protected $message;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $active;
    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('Subscriber', 'Pobox', 'Guide', 'Bill', 'Receipt', 'Adservice', 'Alert', 'Consolidated')", nullable=false)
     */
    protected $tableclass;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastupdate;
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
     * Set message
     *
     * @param string $message
     *
     * @return Termcond
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Termcond
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

    /**
     * Set tableclass
     *
     * @param string $tableclass
     *
     * @return Termcond
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
     * @return Termcond
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
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Termcond
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
