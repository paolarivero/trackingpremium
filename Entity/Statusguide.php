<?php
// src/NvCarga/Bundle/Entity/Statusguide.php

namespace NvCarga\Bundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="statusguide")
 * @ORM\HasLifecycleCallbacks()
 */
class Statusguide
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="Guide", inversedBy="liststatus")
     * @ORM\JoinColumn(name="guide_id", referencedColumnName="id")
     */
    protected $guide;
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
     * @return Statusguide
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
     * @return Statusguide
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
     * Set guide
     *
     * @param \NvCarga\Bundle\Entity\Guide $guide
     *
     * @return Statusguide
     */
    public function setGuide(\NvCarga\Bundle\Entity\Guide $guide = null)
    {
        $this->guide = $guide;

        return $this;
    }

    /**
     * Get guide
     *
     * @return \NvCarga\Bundle\Entity\Guide
     */
    public function getGuide()
    {
        return $this->guide;
    }

    /**
     * Set step
     *
     * @param \NvCarga\Bundle\Entity\Stepstatus $step
     *
     * @return Statusguide
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
     * @return Statusguide
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
