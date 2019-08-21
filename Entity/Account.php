<?php
// src/NvCarga/Bundle/Entity/Account.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="account")
 * @ORM\HasLifecycleCallbacks()
 */
class Account
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
     /**
     * @Assert\NotBlank(message = "Debe asignar el número de la cuenta")
     * @Assert\Length(
     *      min = 5,
     *      max = 20,
     *      minMessage = "La longitud mínima del número de la cuenta debe ser igual o mayor a {{ limit }}",
     *      maxMessage = "La longitud del número de la cuenta debe ser menor a {{ limit }}"
     * )
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    protected $number;
    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    protected $city;
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
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $rtn;
     /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $swift;
        /**
     * @Assert\NotBlank(message = "Debe suministrar un email")
     * @Assert\Email(
     *     message = "El email '{{ value }}' no es válido.",
     *     checkMX = true, 
     *     checkHost = true
     *     )
     * @ORM\Column(type="string", length=60, nullable=false)
     */
    protected $email;
     /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $docid;
    /**
     * @ORM\Column(type="text", length=120, nullable=true))
     */
    protected $address;
    /**
     * @Assert\NotBlank(message = "El nombre del banco no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 100,
     *      minMessage = "El nombre del banco debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre del banco no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=100, nullable=false))
     */
    protected $bankname;
    /**
     * @Assert\NotBlank(message = "El nombre del titular de la cuenta no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 100,
     *      minMessage = "El nombre del titular de la cuenta debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre del titular de la cuenta no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=100, nullable=false))
     */
    protected $holdername;
    /**
     * @ORM\Column(type="boolean", nullable=false )
     */
    protected $isactive = true;

    public function __toString() {
        return (string) ('Banco: ' . $this->getBankname() . ' Cuenta:' . $this->getNumber());
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
     * Set number
     *
     * @param string $number
     *
     * @return Account
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
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Account
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
     * Set rtn
     *
     * @param string $rtn
     *
     * @return Account
     */
    public function setRtn($rtn)
    {
        $this->rtn = $rtn;

        return $this;
    }

    /**
     * Get rtn
     *
     * @return string
     */
    public function getRtn()
    {
        return $this->rtn;
    }

    /**
     * Set swift
     *
     * @param string $swift
     *
     * @return Account
     */
    public function setSwift($swift)
    {
        $this->swift = $swift;

        return $this;
    }

    /**
     * Get swift
     *
     * @return string
     */
    public function getSwift()
    {
        return $this->swift;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Account
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set docid
     *
     * @param string $docid
     *
     * @return Account
     */
    public function setDocid($docid)
    {
        $this->docid = $docid;

        return $this;
    }

    /**
     * Get docid
     *
     * @return string
     */
    public function getDocid()
    {
        return $this->docid;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Account
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set bankname
     *
     * @param string $bankname
     *
     * @return Account
     */
    public function setBankname($bankname)
    {
        $this->bankname = $bankname;

        return $this;
    }

    /**
     * Get bankname
     *
     * @return string
     */
    public function getBankname()
    {
        return $this->bankname;
    }

    /**
     * Set holdername
     *
     * @param string $holdername
     *
     * @return Account
     */
    public function setHoldername($holdername)
    {
        $this->holdername = $holdername;

        return $this;
    }

    /**
     * Get holdername
     *
     * @return string
     */
    public function getHoldername()
    {
        return $this->holdername;
    }

    /**
     * Set city
     *
     * @param \NvCarga\Bundle\Entity\City $city
     *
     * @return Account
     */
    public function setCity(\NvCarga\Bundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \NvCarga\Bundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set company
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $company
     *
     * @return Account
     */
    public function setMaincompany(\NvCarga\Bundle\Entity\Maincompany $maincompany = null)
    {
        $this->maincompany = $maincompany;

        return $this;
    }

    /**
     * Get company
     *
     * @return \NvCarga\Bundle\Entity\Maincompany
     */
    public function getMaincompany()
    {
        return $this->maincompany;
    }
    /**
     * Set isactive
     *
     * @param boolean $isactive
     *
     * @return Payment
     */
    public function setIsactive($isactive)
    {
        $this->isactive = $isactive;

        return $this;
    }

    /**
     * Get isactive
     *
     * @return boolean
     */
    public function getIsactive()
    {
        return $this->isactive;
    }
}
