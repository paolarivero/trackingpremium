<?php
// src/NvCarga/Bundle/Entity/Payment.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="payment")
 * @ORM\HasLifecycleCallbacks()
 */
class Payment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="Customer")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    protected $customer;
     /**
     * @ORM\Column(type="datetime")
     */
    protected $paydate;
     /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\OneToOne(targetEntity="Guide")
     * @ORM\JoinColumn(name="guide_id", referencedColumnName="id")
     */
    protected $guide;
    /**
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     */
    protected $account;
   /**
     * @Assert\NotBlank(message = "Debe asignar la cantidad pagada")
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "La cantidad pagada debe ser igual o mayor a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $amount;
     /**
     * @ORM\Column(type="boolean", nullable=false )
     */
    protected $verified = false;

    public function __toString() {
        return (string) ($this->getId());
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
     * Set paydate
     *
     * @param \DateTime $paydate
     *
     * @return Payment
     */
    public function setPaydate($paydate)
    {
        $this->paydate = $paydate;

        return $this;
    }

    /**
     * Get paydate
     *
     * @return \DateTime
     */
    public function getPaydate()
    {
        return $this->paydate;
    }

    /**
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Payment
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
     * Set amount
     *
     * @param string $amount
     *
     * @return Payment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set verified
     *
     * @param boolean $verified
     *
     * @return Payment
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * Get verified
     *
     * @return boolean
     */
    public function getVerified()
    {
        return $this->verified;
    }

    /**
     * Set customer
     *
     * @param \NvCarga\Bundle\Entity\Customer $customer
     *
     * @return Payment
     */
    public function setCustomer(\NvCarga\Bundle\Entity\Customer $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \NvCarga\Bundle\Entity\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set guide
     *
     * @param \NvCarga\Bundle\Entity\Guide $guide
     *
     * @return Payment
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
     * Set account
     *
     * @param \NvCarga\Bundle\Entity\Account $account
     *
     * @return Payment
     */
    public function setAccount(\NvCarga\Bundle\Entity\Account $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return \NvCarga\Bundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }
}
