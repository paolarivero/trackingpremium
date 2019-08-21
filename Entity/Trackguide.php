<?php
// src/NvCarga/Bundle/Entity/Trackguide.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityManager;
use NvCarga\Bundle\Entity\User;
use NvCarga\Bundle\Entity\Message;

/**
 * @ORM\Entity
 * @ORM\Table(name="trackguide")
 * @ORM\HasLifecycleCallbacks()
 */
class Trackguide
{
    protected $translator;
    protected $mailer;
    protected $cusemail;
    protected $cusname;
    protected $manager;
    protected $user;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="Guide", inversedBy="tracks"))
     * @ORM\JoinColumn(name="guide_id", referencedColumnName="id")
     */
    protected $guide;
    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    protected $place;
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $trackdate;
    /**
     * @Assert\NotBlank(message = "Debe colocar un mensaje")
     * @ORM\Column(type="text")
     */
    protected $message;

    public function __construct($translator, \Swift_Mailer $mailer, $cusemail, $cusname, EntityManager $manager, User $user)
    {
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->cusemail = $cusemail;
        $this->cusname = $cusname;
        $this->manager = $manager;
        $this->user = $user;
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
     * Set trackdate
     *
     * @param \DateTime $trackdate
     *
     * @return Trackguide
     */
    public function setTrackdate($trackdate)
    {
        $this->trackdate = $trackdate;

        return $this;
    }

    /**
     * Get trackdate
     *
     * @return \DateTime
     */
    public function getTrackdate()
    {
        return $this->trackdate;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Trackguide
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
     * Set guide
     *
     * @param \NvCarga\Bundle\Entity\Guide $guide
     *
     * @return Trackguide
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
     * Set place
     *
     * @param \NvCarga\Bundle\Entity\City $place
     *
     * @return Trackguide
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
    public function Sendemail()
    {
        $guide = $this->getGuide();
        $email = $guide->getSender()->getEmail(); //REMITENTE
        $pemail = 'cliente_' . strtolower($guide->getAgency()->getMaincompany()->getAcronym());
        $pos = strpos($email, $pemail);

        if (($guide->getEmailnoti()) && ($pos === false) && ($email)) {
	        $body = '<p align="right">Ref:' . $guide->getNumber() . '</p><br>';
            $body = $body . '<b>' .  $guide->getSender()->getName() . ' '  . $guide->getSender()->getLastname() . '</b><br><br>';
            $maincompany = $guide->getAgency()->getMainCompany();
            $body = $body . 'Mensaje: ' . $this->getMessage() . '</b><br><br>';
            $body = $body . 'Fecha: ' .  $this->getTrackdate()->format('m/d/Y') . '<br>'; //  $guide->getCreationdate()->format('m/d/Y') . '<br>';
            $body = $body . 'Número: ' .  $guide->getNumber() . '<br>';
            $body = $body . 'Remitente: ' .   $guide->getSender()->getName() . ' '  . $guide->getSender()->getLastname() . '<br>';
            $body = $body . 'Destinatario: ' .   $guide->getAddressee()->getName() . ' '  . $guide->getAddressee()->getLastname() . '<br>';
            $body = $body . 'Bultos: ' .  $guide->getPieces() . '<br>';
            $body = $body . 'Peso: ' .  $guide->getRealweight() . '<br><br>';
            $body = $body . 'Si desea consultar detalles del envío y de su estatus, sírvase ingresar en nuestro '; 
            $body = $body . '<a href="http://' . $_SERVER['SERVER_NAME'] . '/tracking/tracking"> ENLACE de Seguimiento de Guías</a>, usando el número de guía suministrado; o a través de su casillero personal. <br><br>';
            $body = $body . 'Gracias por su confianza y ser parte de la familia <b>"' . $maincompany->getName() . '"</b><br><br>';
            $color = $this->translator->trans('tailcolor');
            $body = $body . '<p style="font-size:20px; color:' . $color . ';"><b>**ESTE ES UN CORREO NO MONITOREADO, POR FAVOR NO RESPONDA AL MISMO**</b></p>';
            
            $setfrom = $this->cusemail;
            $fromname = $this->cusname;
            $message = \Swift_Message::newInstance()
                //->setFrom(array($setfrom => $fromname))
                //->setBcc($setfrom)
                    ->setContentType("text/html")
                    ->setSubject('Seguimiento de su envío: '. $guide->getNumber())
                    //->setTo($email)
                    ->setBody($body);
            $send = 0;
            try {
                $message->setTo($email);
            } catch (\Swift_RfcComplianceException $e) {
                //exit(\Doctrine\Common\Util\Debug::dump('Dirección MALA'));
                $send = -1;
                goto out;
            }
            try {
                $message->setFrom(array($setfrom => $fromname));
            } catch (\Swift_RfcComplianceException $e) {
                //exit(\Doctrine\Common\Util\Debug::dump('Dirección MALA'));
                $send = -2;
                goto out;
            }
            
            $send = $this->mailer->send($message);

            out:
            if ($send < 0) {
                $em = $this->manager;
                $head = "<b>No se pudo enviar el EMAIl <br> "; 
                if ($send == -1) {
                    $head = $head . "La dirección DESTINO: " . $entity->getSender()->getEmail() . ' no es correcta (RFC 2822)</b><br>';
                } else {
                    $head = $head . "La dirección REMITENTE: " . $setfrom . ' no es correcta (RFC 2822)</b><br>';
                }
                $msg = new Message();
                $msg->setSender($this->user);
                $msg->setReceiver($this->user);
                $msg->setSubject('Error enviando email (Movimiento de Guía)');
                $msg->setBody($head);
                $msg->setCreationdate(new \DateTime());
                $msg->setIsread(FALSE);
                $em->persist($msg);
        
                $em->flush();
            }
        } else {
            // exit(\Doctrine\Common\Util\Debug::dump('No envio. ' . $guide->getNumber() . ' '. $email . ' ' . $status->getName() ));
            $send = 0;
        }
        return $send;
    }
}
