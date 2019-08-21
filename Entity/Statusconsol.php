<?php
// src/NvCarga/Bundle/Entity/Statusconsol.php

namespace NvCarga\Bundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="statusconsol")
 * @ORM\HasLifecycleCallbacks()
 */
class Statusconsol
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="Consolidated", inversedBy="liststatus")
     * @ORM\JoinColumn(name="consol_id", referencedColumnName="id")
     */
    protected $consol;
    /**
     * @ORM\ManyToOne(targetEntity="Stepstatus")
     * @ORM\JoinColumn(name="step_id", referencedColumnName="id")
     */
    protected $step;
     /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="place_id", referencedColumnName="id")
     */
    protected $place;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $comment;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Statusconsol
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Statusconsol
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
     * Set consol
     *
     * @param \NvCarga\Bundle\Entity\Consolidated $consol
     *
     * @return Statusconsol
     */
    public function setConsol(\NvCarga\Bundle\Entity\Consolidated $consol = null)
    {
        $this->consol = $consol;

        return $this;
    }

    /**
     * Get consol
     *
     * @return \NvCarga\Bundle\Entity\Consolidated
     */
    public function getConsol()
    {
        return $this->consol;
    }

    /**
     * Set step
     *
     * @param \NvCarga\Bundle\Entity\Stepstatus $step
     *
     * @return Statusconsol
     */
    public function setStep(\NvCarga\Bundle\Entity\Stepstatus $step = null)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return \NvCarga\Bundle\Entity\Stepstatus
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set place
     *
     * @param \NvCarga\Bundle\Entity\City $place
     *
     * @return Statusconsol
     */
    public function setPlace(\NvCarga\Bundle\Entity\City $place = null)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return \NvCarga\Bundle\Entity\City
     */
    public function getPlace()
    {
        return $this->place;
    }
}
