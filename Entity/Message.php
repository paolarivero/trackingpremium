<?php
// src/NvCarga/Bundle/Entity/Message.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="message")
 * @ORM\HasLifecycleCallbacks()
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id")
     */
    protected $sender;
   /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="messages")
     * @ORM\JoinColumn(name="receiver_id", referencedColumnName="id")
     */
    protected $receiver;
     /**
     * @Assert\NotBlank(message = "El subject no puede estar vacío")
     * @Assert\Length(
     *      min = 3,
     *      max = 100,
     *      minMessage = "El subject debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El subject no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $subject;
    /**
     * @Assert\NotBlank(message = "El cuerpo del mensaje no puede estar vacío")
     * @Assert\Length(
     *      min = 5,
     *      minMessage = "El cuerpo del mensaje debe tener al menos {{ limit }} caracteres",
     * )
     * @ORM\Column(type="text", nullable=false)
     */
    protected $body;
     /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $isread;

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
     * Set subject
     *
     * @param string $subject
     *
     * @return Message
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set body
     *
     * @param string $body
     *
     * @return Message
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Message
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
     * Set isread
     *
     * @param boolean $isread
     *
     * @return Message
     */
    public function setIsread($isread)
    {
        $this->isread = $isread;

        return $this;
    }

    /**
     * Get isread
     *
     * @return boolean
     */
    public function getIsread()
    {
        return $this->isread;
    }

    /**
     * Set sender
     *
     * @param \NvCarga\Bundle\Entity\User $sender
     *
     * @return Message
     */
    public function setSender(\NvCarga\Bundle\Entity\User $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return \NvCarga\Bundle\Entity\User
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set receiver
     *
     * @param \NvCarga\Bundle\Entity\User $receiver
     *
     * @return Message
     */
    public function setReceiver(\NvCarga\Bundle\Entity\User $receiver = null)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get receiver
     *
     * @return \NvCarga\Bundle\Entity\User
     */
    public function getReceiver()
    {
        return $this->receiver;
    }
}
