<?php

namespace NvCarga\Bundle\Controller;

use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Moveguides;
use NvCarga\Bundle\Form\MoveguidesType;

/**
 * Moveguides controller.
 *
 * @Route("/moveguides")
 */
class MoveguidesController extends Controller
{
    /**
     * Creates a new Moveguides entity.
     *
     * @Route("/{id}/create", name="moveguides_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Moveguides:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_MOVEGUIDES') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
        $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');
        $entity = new Moveguides($this->get('translator'), $this->get('mailer'), $setfrom, $fromname, $em, $this->getUser());
   
        $guide = $em->getRepository('NvCargaBundle:Guide')->find($id);

        $translator = $this->get('translator');
        if (!$guide) {
            throw $this->createNotFoundException('No existe ' . $translator->trans('Guía'));
        }
        $last = $guide->getMoves()->last()->getStatus()->getName();
        if ($last == "Entregada") { 
            throw $this->createNotFoundException('Ya ha sido entregada');
        }

        $form   = $this->createCreateForm($entity, $id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (count($guide->getMoves()) > 0) {
                $guidestates= $em->getRepository('NvCargaBundle:Guidestatus')->findBy(array(), array('position' => 'ASC'));
                $laststatus = end($guidestates);
                $lastmove = $guide->getMoves()->last();
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
                $em = $this->getDoctrine()->getManager();
                if (!$entity->getTrack()) {
                    $entity->setTrack('');
                }
                $last = $guide->getMoves()->last()->getStatus()->getPosition();
                $current = $entity->getStatus()->getPosition();
                $now = new \DateTime();
                $movdate = $entity->getMovdate(); //->format('Y-m-d\TH:i:s');
                $clock = $now->format('H:i:s');
                $rdate = substr($movdate,0,10) . 'T' . $clock;
                $entity->setMovdate(new \DateTime($rdate));
            
                $maincompany = $this->getUser()->getMaincompany();
                $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
                $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');
                for ($ii=$last+1;$ii<$current;$ii++) {
                    $status = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByPosition($ii);
                    $move = new Moveguides($this->get('translator'), $this->get('mailer'), $setfrom, $fromname, $em, $this->getUser());
                    $move->setMovdate($entity->getMovdate());
                    $move->setComment($entity->getComment());
                    $move->setPercentage($entity->getPercentage());
                    $move->setStatus($status);
                    $move->setCompany($entity->getCompany());
                    $move->setTrack($entity->getTrack());
                    $move->setGuide($guide);
                    $guide->addMove($move);
                    $em->persist($move);
                }
                if ($entity->getStatus()->getPosition() == $laststatus->getPosition() ) { //OJO MODIFICAR
                    $entity->setPercentage(100);
                }
                $guide->addMove($entity);
                $entity->setGuide($guide);
                $em->persist($entity);
                $em->flush();
                $entity->Sendemail();
            }
            return $this->redirect($this->generateUrl('guide_show', array('id' => $guide->getId())));
        }

        return array(
            'entity' => $entity,
            'guide' => $guide,
            'form'  => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Moveguides entity.
     *
     * @param Moveguides $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Moveguides $entity, $guideid)
    {
        $form = $this->createForm(new MoveguidesType(), $entity, array(
            'action' => $this->generateUrl('moveguides_create', array('id' => $guideid)),
            'method' => 'POST',
        ));
	$em = $this->getDoctrine()->getManager();
	$allstatus = $em->getRepository('NvCargaBundle:Guidestatus')->findAll();
	$guide = $em->getRepository('NvCargaBundle:Guide')->find($guideid);
	
	$moves = $guide->getMoves();

	$currentstatus = array();
	$fmove = $moves->first();
	/*
	if ($fmove->getStatus()->getName() != 'En Sucursal') {		
		$currentstatus[] = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName('En Sucursal');
	}
	*/

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
	$companies = $em->getRepository('NvCargaBundle:Localcompany')->findBy(['maincompany'=>$this->getUser()->getMaincompany(), 'active'=>true]);

	$form->add('status', 'choice', array('label' => 'Status',
                    'choice_list'   => $choiceList,
                    'multiple'  => false));
    $form->add('submit', 'submit', array('label' => 'Crear'));
    $form->add('company', 'entity', array('label'=> 'Compañía local ', 'class' => 'NvCargaBundle:Localcompany',
    'choices' => $companies));

        return $form;
    }

    /**
     * Displays a form to create a new Moveguides entity.
     *
     * @Route("/{id}/new", name="moveguides_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_MOVEGUIDES') or has_role('ROLE_ADMIN')")
     */
    public function newAction($id)
    {
        
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
        $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');
        $entity = new Moveguides($this->get('translator'), $this->get('mailer'), $setfrom, $fromname, $em, $this->getUser());
   
        $guide = $em->getRepository('NvCargaBundle:Guide')->find($id);
        $translator = $this->get('translator');
        if (!$guide) {
            throw $this->createNotFoundException('No existe ' . $translator->trans('Guía'));
        }
        $last = $guide->getMoves()->last()->getStatus()->getName();
        if ($last == "Entregada") { 
            throw $this->createNotFoundException('Ya ha sido entregada');
        }
		
        $form   = $this->createCreateForm($entity, $id);

        return array(
            'entity' => $entity,
            'guide' => $guide,
            'form'  => $form->createView(),
        );
    }
}
