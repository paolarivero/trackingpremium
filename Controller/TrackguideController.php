<?php

namespace NvCarga\Bundle\Controller;

use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Trackguide;
use NvCarga\Bundle\Form\TrackguideType;
use NvCarga\Bundle\Entity\City;


/**
 * Moveguides controller.
 *
 * @Route("/trackguide")
 */
class TrackguideController extends Controller
{
    /**
     * Creates a new Moveguides entity.
     *
     * @Route("/{id}/create", name="trackguide_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Trackguide:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
        $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');
        $entity = new Trackguide($this->get('translator'), $this->get('mailer'), $setfrom, $fromname, $em, $this->getUser());
   
        $guide = $em->getRepository('NvCargaBundle:Guide')->find($id);

        $translator = $this->get('translator');
        if (!$guide) {
            throw $this->createNotFoundException('No existe ' . $translator->trans('Guía'));
        }
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }

        $form   = $this->createCreateForm($entity, $id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $city_track = null;
            $cityid = $form['cityid_track']->getData();
            if ($cityid != 0) {
                $city_track = $em->getRepository('NvCargaBundle:City')->find($cityid);
            }
            if (!$city_track) {
                $city_track = $em->getRepository('NvCargaBundle:City')->findOneBy(['name'=>$form['cityname_track']->getData(), 'state' => $form['state_track']->getData()]);
            }

            if (!$city_track) {
                $city_track =  new City();
                $city_track->setName($form['cityname_track']->getData());
                $city_track->setActive(false);
                $city_track->setState($form['state_track']->getData());
                $em->persist($city_track);
            }
            /*
            if (!$guide->getMovealone()) {
                $beforetrack = new Trackguide($this->get('translator'), $this->get('mailer'), $setfrom, $fromname, $em, $this->getUser());
                $guide->setMovealone(true);
                $beforetrack->setGuide($guide);
                $beforetrack->setMessage('Guía creada en la agencia: '. $guide->getAgency()->getName());
                $beforetrack->setPlace($guide->getAgency()->getCity());
                $beforetrack->setTrackdate($guide->getCreationdate());
                $em->persist($beforetrack);
            }
            */
            $now = new \DateTime();
            $movdate = $entity->getTrackdate(); //->format('Y-m-d\TH:i:s');
            $clock = $form['clock']->getData(); //$now->format('H:i:s');
            $rdate = substr($movdate,0,10) . 'T' . $clock;
            $entity->setTrackdate(new \DateTime($rdate));
            $entity->setPlace($city_track);
	    
            $guide->addTrack($entity);
            $entity->setGuide($guide);
            $em->persist($entity);
            $em->flush();
            $entity->Sendemail();
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
    private function createCreateForm(Trackguide $entity, $guideid)
    {
        $form = $this->createForm(new TrackguideType($this->getDoctrine()->getManager(), $this->getUser()->getMaincompany()), 
            $entity, array(
            'action' => $this->generateUrl('trackguide_create', array('id' => $guideid)),
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Moveguides entity.
     *
     * @Route("/{id}/new", name="trackguide_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function newAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
        $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');
        $entity = new Trackguide($this->get('translator'), $this->get('mailer'), $setfrom, $fromname, $em, $this->getUser());
   
        $guide = $em->getRepository('NvCargaBundle:Guide')->find($id);
        
        
        $translator = $this->get('translator');
        if (!$guide) {
            throw $this->createNotFoundException('No existe ' . $translator->trans('Guía'));
        }
        
        $form   = $this->createCreateForm($entity, $id);
        
        //exit(\Doctrine\Common\Util\Debug::dump($guide)); 
        return array(
            'entity' => $entity,
            'guide' => $guide,
            'form'  => $form->createView(),
        );
    }
}
