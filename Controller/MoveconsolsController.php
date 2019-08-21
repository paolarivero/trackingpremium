<?php

namespace NvCarga\Bundle\Controller;

use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Consolidated;
use NvCarga\Bundle\Entity\Moveconsols;
use NvCarga\Bundle\Entity\Moveguides;
use NvCarga\Bundle\Entity\Guide;
use NvCarga\Bundle\Entity\Guidestatus;
use NvCarga\Bundle\Form\MoveconsolsType;


/**
 * Moveconsols controller.
 *
 * @Route("/moveconsols")
 */
class MoveconsolsController extends Controller
{
    /**
     * Creates a new Moveconsols entity.
     *
     * @Route("/{id}/create", name="moveconsols_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Moveconsols:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_MOVECONSOLS') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request, $id)
    {
        $entity = new Moveconsols();
        $em = $this->getDoctrine()->getManager();
        $consol = $em->getRepository('NvCargaBundle:Consolidated')->find($id);
        $translator = $this->get('translator');
        $maincompany = $this->getUser()->getMaincompany();
        $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
        $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');

        if (!$consol) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Consolidado'));
        }

        if ($consol->getIsopen()) { 
            throw $this->createNotFoundException('Está abierto, no se puede cambiar status');
        }
        $form = $this->createCreateForm($entity, $id);
       
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (count($consol->getMoves()) > 0) {
                $consolstates= $em->getRepository('NvCargaBundle:Consolidatedstatus')->findBy(array(), array('position' => 'ASC'));
                $laststatus = end($consolstates);
                $lastmove = $consol->getMoves()->last();
                if (!$entity->getPercentage()) {
                    $pos = $lastmove->getStatus()->getPosition();
                    $posult = $laststatus->getPosition();
                    if ($posult - $pos > 0) { 
                        $percentage = $lastmove->getPercentage() + (100 - $lastmove->getPercentage())/($posult-$pos);
                        $entity->setPercentage($percentage);
                    } else {
                        $entity->setPercentage(100);
                    }
                    
                }
                $guides=$consol->getGuides();
                $company = $em->getRepository('NvCargaBundle:Localcompany')->findOneByName('Empresa');
                $last = $consol->getMoves()->last()->getStatus()->getPosition();
                $current = $entity->getStatus()->getPosition();
                
                $now = new \DateTime();
                $movdate = $entity->getMovdate();//->format('Y-m-d\TH:i:s');
                $clock = $form['clock']->getData(); //$now->format('H:i:s');
                $rdate = substr($movdate,0,10) . 'T' . $clock;
                $entity->setMovdate(new \DateTime($rdate));
                
                
                // exit(\Doctrine\Common\Util\Debug::dump($rdate));
                for ($ii=$last+1;$ii<$current;$ii++) {
                    $status = $em->getRepository('NvCargaBundle:Consolidatedstatus')->findOneByPosition($ii);
                    $move = new Moveconsols();
                    $move->setMovdate($entity->getMovdate());
                    $move->setComment($entity->getComment());
                    $move->setPercentage($entity->getPercentage());
                    $move->setStatus($status);
                    $move->setConsolidated($consol);
                    $consol->addMove($move);
                    $em->persist($move);	
                    if ($status->getInherited()) {
                        $gstatus = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName($status->getName());
                        foreach ($guides as $guide) {
                            $moveg = new Moveguides($this->get('translator'), $this->get('mailer'), $setfrom, $fromname,  $em, $this->getUser());
                            $moveg->setGuide($guide);
                            $moveg->setStatus($gstatus);
                            $moveg->setMovdate($move->getMovdate());
                            $moveg->setComment($move->getComment());
                            $moveg->setCompany($company);
                            $moveg->setPercentage(70);
                            $moveg->setTrack('');
                            if (count($guide->getMoves()) > 0) {
                                $guide->addMove($moveg);
                                $em->persist($moveg);
                            }
                        }
                        $em->flush();
                    }
                }
                

                if ($entity->getStatus()->getName() == $laststatus->getName()) { 
                    $entity->setPercentage(100);
                    $lastpost = $laststatus->getPosition() - 1;
                    $lcstatus = $em->getRepository('NvCargaBundle:Consolidatedstatus')->findOneByPosition($lastpost);
                    $lgstatus = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName($lcstatus->getName());
                    $gstatus = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByPosition($lgstatus->getPosition() + 1);
                    
                    foreach ($guides as $guide) {
                        $moveg = new Moveguides($this->get('translator'), $this->get('mailer'), $setfrom, $fromname,  $em, $this->getUser());
                        $moveg->setGuide($guide);
                        $moveg->setStatus($gstatus);
                        $moveg->setMovdate($entity->getMovdate());
                        $moveg->setComment('Listo para ser despachada en el país destino');
                        $moveg->setCompany($company);
                        $moveg->setPercentage(80);
                        $moveg->setTrack('');
                        if (count($guide->getMoves()) > 0) {
                            $guide->addMove($moveg);
                            $em->persist($moveg);	
                            $moveg->Sendemail();
                        }
                    }
                    $em->flush();			
                }
            
                if ($entity->getStatus()->getInherited()) {
                    $gstatus = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName($entity->getStatus()->getName());
                    foreach ($guides as $guide) {
                        $moveg = new Moveguides($this->get('translator'), $this->get('mailer'), $setfrom, $fromname, $em, $this->getUser());
                        $moveg->setGuide($guide);
                        $moveg->setStatus($gstatus);
                        $moveg->setMovdate($entity->getMovdate());
                        $moveg->setComment($entity->getComment());
                        $moveg->setCompany($company);
                        $moveg->setPercentage(70);
                        $moveg->setTrack('');
                        if (count($guide->getMoves()) > 0) {
                            $guide->addMove($moveg);
                            $em->persist($moveg);	
                            $moveg->Sendemail();
                        }	
                    }
                    $em->flush();
                }
                $entity->setConsolidated($consol);
                $consol->addMove($entity);
                $em->persist($entity);
                $em->flush();
            }

            return $this->redirect($this->generateUrl('consolidated_show', array('id' => $consol->getId())));
        }

        return array(
            'consol' => $consol,
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Moveconsols entity.
     *
     * @param Moveconsols $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Moveconsols $entity, $consolid)
    {
        $form = $this->createForm(new MoveconsolsType(), $entity, array(
            'action' => $this->generateUrl('moveconsols_create', array('id' => $consolid)),
            'method' => 'POST',
        ));
        $em = $this->getDoctrine()->getManager();
        $allstatus = $em->getRepository('NvCargaBundle:Consolidatedstatus')->findAll();
        $consol = $em->getRepository('NvCargaBundle:Consolidated')->find($consolid);
        
        $moves = $consol->getMoves();

        $currentstatus = array();
        foreach ($moves as $move) {
            $currentstatus[] = $move->getStatus();
        }
        $readystatus = new \Doctrine\Common\Collections\ArrayCollection($currentstatus);

        $statuslist = array();
        foreach ($allstatus as $status) {
            if (!$readystatus->contains($status)){
                $statuslist[] = $status;
            }
        }

        $form->remove('status');
        $choiceList = new ObjectChoiceList($statuslist, null, array(), null, 'id');

        $form->add('status', 'choice', array('label' => 'Status',
                    'choice_list'   => $choiceList,
                    'multiple'  => false));
        $form->add('submit', 'submit', array('label' => 'Crear'));
        $form->add('clock', 'hidden', array('mapped' => false));

        return $form;
    }

    /**
     * Displays a form to create a new Moveconsols entity.
     *
     * @Route("/{id}/new", name="moveconsols_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_MOVECONSOLS') or has_role('ROLE_ADMIN')")
     */
    public function newAction($id)
    {
        $entity = new Moveconsols();
	$em = $this->getDoctrine()->getManager();
	$consol = $em->getRepository('NvCargaBundle:Consolidated')->find($id);
	$translator = $this->get('translator');

        if (!$consol) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Consolidado'));
        }

	if ($consol->getIsopen()) { 
		throw $this->createNotFoundException('Está abierto, no se puede cambiar status');
	}
	
	
        $form   = $this->createCreateForm($entity, $id);

        return array(
	    'consol' => $consol,
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Update a Consolidated Move 
     *
     * @Route("/updatemove", name="moveconsols_update")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CONSOLIDATED') or has_role('ROLE_ADMIN')")
     */
    public function updatemoveAction(Request $request) 
    {
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get('translator');
        $id = $request->query->get('idmove');
        $datestr = $request->query->get('date');
        $comment = $request->query->get('comment');
        $clock = $request->query->get('clock');
        
        $percentage = $request->query->get('percentage');
        $move = $em->getRepository('NvCargaBundle:Moveconsols')->find($id);
        if (!$move) {
            throw $this->createNotFoundException('No existe el movimiento del ' .  $translator->trans('Consolidado'));
        }
        $user = $this->getUser();
        $consol = $move->getConsolidated();
        $guides = $consol->getGuides();
        $agency = $consol->getAgency();
        if (($agency != $user->getAgency()) && ($user->getAgency()->getType() != 'MASTER')) {
            throw $this->createNotFoundException('No tiene permiso para ejecutar esta accón');
        }
        $namestatus = $move->getStatus()->getName();
        $change=false;
        if ($comment != $move->getComment() ) {
                $change = true;
                $move->setComment($comment);
                 foreach ($guides as $guide) {
                    foreach ($guide->getMoves() as $mguide) {
                        if ($mguide->getStatus()->getName() == $namestatus) {
                            $mguide->setComment($comment);
                        }
                    }
                 }
        }
        
        $pos = $move->getStatus()->getPosition();
        $dateorig = $move->getMovdate()->format('m/d/Y');
        
        if ($move->getPercentage() != $percentage) {
            $change = true;
            foreach ($consol->getMoves() as $tmove) {
                if (($tmove->getStatus()->getPosition() < $pos) && ($percentage < $tmove->getPercentage())) {
                    $tmove->setPercentage($percentage);
                }
                if (($tmove->getStatus()->getPosition() > $pos) && ($percentage > $tmove->getPercentage())) {
                    $tmove->setPercentage($percentage);
                }
            }
            $move->setPercentage($percentage);
        }
        
        if ($dateorig != $datestr) {
            $change = true;
            $statusl = null;
            $datenew = new \DateTime($datestr . 'T' . $clock);
            //exit(\Doctrine\Common\Util\Debug::dump($datenew));
            foreach ($consol->getMoves() as $tmove) {
                if (($tmove->getStatus()->getPosition() < $pos) && ($datenew < $tmove->getMovdate())) {
                    $tmove->setMovdate($datenew);
                    $sstatusl = $tmove->getStatus();
                }
                if (($tmove->getStatus()->getPosition() > $pos) && ($datenew > $tmove->getMovdate())) {
                    $tmove->setMovdate($datenew);
                    if (!$statusl) {
                        $statusl = $tmove->getStatus();
                    }
                }
            }
            $mpos = null;
            $move->setMovdate($datenew);
            foreach ($guides as $guide) {
                foreach ($guide->getMoves() as $mguide) {
                    if ($mguide->getStatus()->getName() == $namestatus) {
                            $mguide->setMovdate($datenew);
                            $mpos = $mguide->getStatus()->getPosition();
                    }
                }
            }
            if ($statusl) {
                if ($statusl->getInherited()) {
                    $namestatus = $statusl->getName();
                    foreach ($guides as $guide) {
                        foreach ($guide->getMoves() as $mguide) {
                            if ($mguide->getStatus()->getName() == $namestatus) {
                                $mguide->setMovdate($datenew);
                                $mpos = $mguide->getStatus()->getPosition();
                            }
                        }
                    }
                }
            }
            
            
            if ($mpos) {
                foreach ($guides as $guide) {
                    foreach ($guide->getMoves() as $mguide) {
                            if (($mguide->getStatus()->getPosition() < $mpos) && ($datenew < $mguide->getMovdate())) {
                                $mguide->setMovdate($datenew);
                            }
                            if (($mguide->getStatus()->getPosition() > $mpos) && ($datenew > $mguide->getMovdate())) {
                                $mguide->setMovdate($datenew);
                            }
                    }
                } 
            }
            
        }
        $em->flush();
        if ($change) {
            $flashBag->add('success', 'Se han realizado las modificaciones');
        }
        return $this->redirect($this->generateUrl('consolidated_show', array('id' => $consol->getId())));
    }
}
