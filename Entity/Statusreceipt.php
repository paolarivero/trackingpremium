<?php
// src/NvCarga/Bundle/Entity/Statusreceipt.php

namespace NvCarga\Bundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="statusreceipt")
 * @ORM\HasLifecycleCallbacks()
 */
class Statusreceipt
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="Receipt", inversedBy="liststatus")
     * @ORM\JoinColumn(name="receipt_id", referencedColumnName="id")
     */
    protected $receipt;
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
     * @return Statusreceipt
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
     * @return Statusreceipt
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
     * Set receipt
     *
     * @param \NvCarga\Bundle\Entity\Receipt $receipt
     *
     * @return Statusreceipt
     */
    public function setReceipt(\NvCarga\Bundle\Entity\Receipt $receipt = null)
    {
        $this->receipt = $receipt;

        return $this;
    }

    /**
     * Get receipt
     *
     * @return \NvCarga\Bundle\Entity\Receipt
     */
    public function getReceipt()
    {
        return $this->receipt;
    }

    /**
     * Set step
     *
     * @param \NvCarga\Bundle\Entity\Stepstatus $step
     *
     * @return Statusreceipt
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
     * @return Statusreceipt
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
