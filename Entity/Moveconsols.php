<?php
// src/NvCarga/Bundle/Entity/Moveconsols.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="moveconsols")
 * @ORM\HasLifecycleCallbacks()
 */
class Moveconsols
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="Consolidated", inversedBy="moves")
     * @ORM\JoinColumn(name="guide_id", referencedColumnName="id")
     */
    protected $consolidated;
    /**
     * @ORM\ManyToOne(targetEntity="Consolidatedstatus")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $movdate;
    /**
     * @Assert\NotBlank(message = "Debe colocar un comentario")
     * @ORM\Column(type="text")
     */
    protected $comment;
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
    
    /*
    public function __construct()
    {
        $this->percentage = 50;
    }
    */
    
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
     * Set movdate
     *
     * @param \DateTime $movdate
     *
     * @return Moveconsols
     */
    public function setMovdate($movdate)
    {
        $this->movdate = $movdate;

        return $this;
    }

    /**
     * Get movdate
     *
     * @return \DateTime
     */
    public function getMovdate()
    {
        return $this->movdate;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Moveconsols
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
     * Set percentage
     *
     * @param integer $percentage
     *
     * @return Moveconsols
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
     * Set consolidated
     *
     * @param \NvCarga\Bundle\Entity\Consolidated $consolidated
     *
     * @return Moveconsols
     */
    public function setConsolidated(\NvCarga\Bundle\Entity\Consolidated $consolidated = null)
    {
        $this->consolidated = $consolidated;

        return $this;
    }

    /**
     * Get consolidated
     *
     * @return \NvCarga\Bundle\Entity\Consolidated
     */
    public function getConsolidated()
    {
        return $this->consolidated;
    }

    /**
     * Set status
     *
     * @param \NvCarga\Bundle\Entity\Consolidatedstatus $status
     *
     * @return Moveconsols
     */
    public function setStatus(\NvCarga\Bundle\Entity\Consolidatedstatus $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \NvCarga\Bundle\Entity\Consolidatedstatus
     */
    public function getStatus()
    {
        return $this->status;
    }
}
