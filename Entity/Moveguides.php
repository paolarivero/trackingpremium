<?php
// src/NvCarga/Bundle/Entity/Moveguides.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityManager;
use NvCarga\Bundle\Entity\User;
use NvCarga\Bundle\Entity\Message;


/**
 * @ORM\Entity
 * @ORM\Table(name="moveguides")
 * @ORM\HasLifecycleCallbacks()
 */
class Moveguides
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
     * @ORM\ManyToOne(targetEntity="Guide", inversedBy="moves"))
     * @ORM\JoinColumn(name="guide_id", referencedColumnName="id")
     */
    protected $guide;
    /**
     * @ORM\ManyToOne(targetEntity="Guidestatus")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $movdate;
    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $track;
    /**
     * @ORM\ManyToOne(targetEntity="Localcompany")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    protected $company;
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
     * Set movdate
     *
     * @param \DateTime $movdate
     *
     * @return Moveguides
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
     * Set track
     *
     * @param string $track
     *
     * @return Moveguides
     */
    public function setTrack($track)
    {
        $this->track = $track;

        return $this;
    }

    /**
     * Get track
     *
     * @return string
     */
    public function getTrack()
    {
        return $this->track;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Moveguides
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
     * @return Moveguides
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
     * Set guide
     *
     * @param \NvCarga\Bundle\Entity\Guide $guide
     *
     * @return Moveguides
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
     * Set status
     *
     * @param \NvCarga\Bundle\Entity\Guidestatus $status
     *
     * @return Moveguides
     */
    public function setStatus(\NvCarga\Bundle\Entity\Guidestatus $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \NvCarga\Bundle\Entity\Guidestatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set company
     *
     * @param \NvCarga\Bundle\Entity\Localcompany $company
     *
     * @return Moveguides
     */
    public function setCompany(\NvCarga\Bundle\Entity\Localcompany $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \NvCarga\Bundle\Entity\Localcompany
     */
    public function getCompany()
    {
        return $this->company;
    }
    public function Sendemail()
    {
        $guide = $this->getGuide();
        $status = $this->getStatus();
        // $email = $guide->getAddressee()->getCustomer()->getEmail(); //DESTINATARIO
        $email = $guide->getSender()->getEmail(); //REMITENTE
        $pemail = 'cliente_' . strtolower($guide->getAgency()->getMaincompany()->getAcronym());
        $pos = strpos($email, $pemail);
        // exit(\Doctrine\Common\Util\Debug::dump($guide->getNumber() . ' '. $email . 'Status: ' . $status->getName() ));

        if (($guide->getEmailnoti()) && ($pos === false) && ($email)) {
	        $body = '<p align="right">Ref:' . $guide->getNumber() . '</p><br>';
            $body = $body . '<b>' .  $guide->getSender()->getName() . ' '  . $guide->getSender()->getLastname() . '</b><br><br>';
            $maincompany = $guide->getAgency()->getMainCompany();
            $body = $body . 'Su envío se encuentra <b>' . $status->getName() . '<br><br>';
            $body = $body . 'Observación: ' . $this->comment . '</b><br><br>';
            $body = $body . 'Fecha: ' .  $this->getMovdate()->format('m/d/Y') . '<br>'; //  $guide->getCreationdate()->format('m/d/Y') . '<br>';
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
