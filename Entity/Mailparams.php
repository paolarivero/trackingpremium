<?php
// src/NvCarga/Bundle/Entity/Mailparams.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Egulias\EmailValidator\Validation\RFCValidation;

/**
 * @ORM\Entity
 * @ORM\Table(name="mailparams")
 * @ORM\HasLifecycleCallbacks()
 */
class Mailparams
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\OneToOne(targetEntity="Maincompany", inversedBy = "mailparams")
     * @ORM\JoinColumn(name="maincompany_id", referencedColumnName="id")
     */
    protected $maincompany;
    /** 
     * @ORM\Column(type="string", length=50, nullable=true) 
    */
    protected $user; //Dirección de email o usuario en el sistema de correo
    /** 
     * @ORM\Column(type="string", length=50, nullable=true) 
    */
    protected $username; //Nombre o descripción del usuario
    /** 
     * @ORM\Column(type="string", length=50, nullable=true) 
    */
    protected $password; //Clave de acceso del correo
    /** 
     * @ORM\Column(type="string", length=50, nullable=true) 
    */
    protected $host; //Servidor del sistema de correo
    /** 
    * @ORM\Column(type="string", columnDefinition="ENUM('smtp', 'mail', 'sendmail', 'gmail')") 
    */ 
    protected $transport; // Tipo de transporte usado
    /** 
     * @ORM\Column(type="integer", nullable=true) 
    */
    protected $port; //Puerto
    /** 
    * @ORM\Column(type="string", columnDefinition="ENUM('tls', 'ssl')") 
    */
    protected $encryption; // Tipo de encriptado

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
     * Set user
     *
     * @param string $user
     *
     * @return Mailparams
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return Mailparams
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Mailparams
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set host
     *
     * @param string $host
     *
     * @return Mailparams
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set transport
     *
     * @param string $transport
     *
     * @return Mailparams
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * Get transport
     *
     * @return string
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Set port
     *
     * @param integer $port
     *
     * @return Mailparams
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port
     *
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set encryption
     *
     * @param string $encryption
     *
     * @return Mailparams
     */
    public function setEncryption($encryption)
    {
        $this->encryption = $encryption;

        return $this;
    }

    /**
     * Get encryption
     *
     * @return string
     */
    public function getEncryption()
    {
        return $this->encryption;
    }

    /**
     * Set maincompany
     *
     * @param \NvCarga\Bundle\Entity\Maincompany $maincompany
     *
     * @return Mailparams
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
