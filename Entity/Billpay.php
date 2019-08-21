<?php
// src/NvCarga/Bundle/Entity/Billpay.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="billpay")
 * @ORM\HasLifecycleCallbacks()
 */
class Billpay
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id; // Identificador de la entidad
    /**
     * @ORM\Column(type="string", length=40, unique=false, nullable=false)
     */
    protected $number; // Número(Código) del pago
    /**
     * @ORM\ManyToOne(targetEntity="Bill", inversedBy="payments")
     * @ORM\JoinColumn(name="bill_id", referencedColumnName="id")
     */
    protected $bill; // Identificador de la factura con la que está asociado el pago
     /**
     * @Assert\NotBlank(message = "Debe asignar la fecha de pago")
     * @ORM\Column(type="datetime")
     */
    protected $paydate; // Fecha de creacón del pago
     /**
     * @ORM\Column(type="datetime")
     */
    protected $lastupdate; // Fecha de actualización 
    /**
     * @ORM\ManyToOne(targetEntity="Paidtype")
     * @ORM\JoinColumn(name="paidtype_id", referencedColumnName="id")
     */
    protected $paidtype; // Forma de pago, como se realizó el pago
    /**
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     */
    protected $account; // Cuando es depósito o transferencia, en que cuenta se hizo
   /**
     * @Assert\NotBlank(message = "Debe asignar la cantidad pagada")
     * @Assert\Range(
     *      min = 0.01,
     *      minMessage = "La cantidad pagada debe ser igual o mayor a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $amount; // Cantidad asociado al pago
     /**
     * @ORM\Column(type="boolean", nullable=false )
     */
    protected $verified; // Variable que sirve para validar si el pago ha sido verificado
     /**
     * @ORM\Column(type="boolean", nullable=false )
     */
    protected $refund; // variable lógica para indicar si un pago ha sido devuelto
    /**
     * @ORM\ManyToOne(targetEntity="Maincompany")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;
    /**
     * @ORM\ManyToOne(targetEntity="Currency")
     * @ORM\JoinColumn(name="cuurency_id", referencedColumnName="id")
     */
    protected $currency;
    /**
     * @Assert\NotBlank(message = "Debe asignar el valor de conversión")
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "El valor de conversión debe ser igual o mayor a {{ limit }}",
     * )
     * @ORM\Column(type="decimal", precision=15, scale=8, nullable=false)
     */
    protected $conversion; // Cantidad asociado al conversion de la modena
    /**
     * @ORM\Column(type="text", length=255, nullable=true)
     */
    protected $note;

    public function __toString() {
        return (string) ($this->getId());
    }
    public function __construct() {
        $this->verified = false;
        $this->refund = false;
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
     * @return Billpay
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
     * Set amount
     *
     * @param string $amount
     *
     * @return Billpay
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
     * @return Billpay
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
     * Set refund
     *
     * @param boolean $refund
     *
     * @return Billpay
     */
    public function setRefund($refund)
    {
        $this->refund = $refund;

        return $this;
    }

    /**
     * Get refund
     *
     * @return boolean
     */
    public function getRefund()
    {
        return $this->refund;
    }

    /**
     * Set bill
     *
     * @param \NvCarga\Bundle\Entity\Bill $bill
     *
     * @return Billpay
     */
    public function setBill(\NvCarga\Bundle\Entity\Bill $bill = null)
    {
        $this->bill = $bill;

        return $this;
    }

    /**
     * Get bill
     *
     * @return \NvCarga\Bundle\Entity\Bill
     */
    public function getBill()
    {
        return $this->bill;
    }

    /**
     * Set paidtype
     *
     * @param \NvCarga\Bundle\Entity\Paidtype $paidtype
     *
     * @return Billpay
     */
    public function setPaidtype(\NvCarga\Bundle\Entity\Paidtype $paidtype = null)
    {
        $this->paidtype = $paidtype;

        return $this;
    }

    /**
     * Get paidtype
     *
     * @return \NvCarga\Bundle\Entity\Paidtype
     */
    public function getPaidtype()
    {
        return $this->paidtype;
    }

    /**
     * Set account
     *
     * @param \NvCarga\Bundle\Entity\Account $account
     *
     * @return Billpay
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

    /**
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Billpay
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
     * Set conversion
     *
     * @param string $conversion
     *
     * @return Billpay
     */
    public function setConversion($conversion)
    {
        $this->conversion = $conversion;

        return $this;
    }

    /**
     * Get conversion
     *
     * @return string
     */
    public function getConversion()
    {
        return $this->conversion;
    }

    /**
     * Set currency
     *
     * @param \NvCarga\Bundle\Entity\Currency $currency
     *
     * @return Billpay
     */
    public function setCurrency(\NvCarga\Bundle\Entity\Currency $currency = null)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return \NvCarga\Bundle\Entity\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }


    /**
     * Set lastupdate
     *
     * @param \DateTime $lastupdate
     *
     * @return Billpay
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
     * Set number
     *
     * @param string $number
     *
     * @return Billpay
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return Billpay
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }
}
