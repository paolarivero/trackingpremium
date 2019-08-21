<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Statusreceipt;
use NvCarga\Bundle\Entity\Receipt;
use NvCarga\Bundle\Entity\City;
use NvCarga\Bundle\Form\StatusreceiptType;
use NvCarga\Bundle\Entity\Statuswhrec;
use NvCarga\Bundle\Entity\WHrec;
use NvCarga\Bundle\Form\StatuswhrecType;
use NvCarga\Bundle\Entity\Statusguide;
use NvCarga\Bundle\Entity\Guide;
use NvCarga\Bundle\Form\StatusguideType;
use NvCarga\Bundle\Entity\Statusconsol;
use NvCarga\Bundle\Entity\Consolidated;
use NvCarga\Bundle\Form\StatusconsolType;
/**
 * Statusreceipt controller.
 *
 * @Route("/status")
 */
class StatusController extends Controller
{
    /**
     *
     * @Route("/{id}/createreceipt", name="statusreceipt_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Statusreceipt:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function createreceiptAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$receipt) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }
        $entity = new Statusreceipt();
        $form   = $this->FormStatusreceipt($entity, $id);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $form->handleRequest($request);

        if ($form->isValid()) {
            /*
            $status = $em->getRepository('NvCargaBundle:Statusreceipt')->findOneBy(['receipt' => $receipt,'step'=>$entity->getStep()]);
            if ($status) {
                $message = 'El ' . $translator->trans('Recibo') . ' ' . $receipt->getNumber() . ' ya tiene un status registrado con el ' . $translator->trans('Paso') . ' ' . $entity->getStep;
                $flashBag->add('notice',$message);
                goto next;
            }
            */
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
            $clock = $form['clock']->getData();
            $arrivedate = $entity->getDate();
            $rdate = substr($arrivedate,0,10) . 'T' . $clock;
            $entity->setDate(new \DateTime($rdate));
            $entity->setPlace($city_track);
            $entity->setReceipt($receipt);
            $receipt->addListstatus($entity);
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('receipt_show', array('id' => $id)));
        }
        next:
        return array(
            'entity' => $entity,
            'receipt' => $receipt,
            'form'  => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Statusreceipt entity.
     *
     * @param Statusreceipt $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function FormStatusreceipt(Statusreceipt $entity, $receipt_id)
    {
        $form = $this->createForm(new StatusreceiptType($this->getDoctrine()->getManager(), $this->getUser()->getMaincompany()), $entity, array(
            'action' => $this->generateUrl('statusreceipt_create', array('id' => $receipt_id)),
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Crear'));
        return $form;
    }

    /**
     * Displays a form to create a new Statusreceipt entity.
     *
     * @Route("/{id}/newreceipt", name="statusreceipt_new")
     * @Method("GET")
     * @Template("NvCargaBundle:Statusreceipt:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function newreceiptAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$receipt) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }
        $entity = new Statusreceipt();
        $form   = $this->FormStatusreceipt($entity, $id);

        return array(
            'entity' => $entity,
            'receipt' => $receipt,
            'form'  => $form->createView(),
        );
    }
    /**
     * Displays a form to EDIT a new Statusreceipt entity.
     *
     * @Route("/{id}/editreceipt", name="statusreceipt_edit")
     * @Method("GET")
     * @Template("NvCargaBundle:Statusreceipt:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function editreceiptAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $entity = $em->getRepository('NvCargaBundle:Statusreceipt')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el Status');
        }
        $entity->setDate($entity->getDate()->format('m/d/Y'));
        $form   = $this->FormStatusreceiptedit($entity);
        return array(
            'edition' => true,
            'entity' => $entity,
            'form'  => $form->createView(),
        );
    }
     /**
     * Creates a form to create a Statusreceipt entity.
     *
     * @param Statusreceipt $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function FormStatusreceiptedit(Statusreceipt $entity)
    {
        $form = $this->createForm(new StatusreceiptType($this->getDoctrine()->getManager(), $this->getUser()->getMaincompany()),$entity, array(
            'action' => $this->generateUrl('statusreceipt_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));
        return $form;
    }
    /**
     *
     * @Route("/{id}/updatereceipt", name="statusreceipt_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Statusreceipt:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function updatereceiptAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $entity = $em->getRepository('NvCargaBundle:Statusreceipt')->findOneBy(['id'=>$id]);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el Status');
        }
        $oldstep = $entity->getStep();
        $olddate = $entity->getDate()->format('m/d/Y');
        $oldtime = $entity->getDate()->format('H:i:s');
        $entity->setDate($entity->getDate()->format('m/d/Y'));

        $form   = $this->FormStatusreceiptedit($entity);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            /*
            $status = $em->getRepository('NvCargaBundle:Statusreceipt')->findOneBy(['receipt' => $entity->getReceipt(),'step'=>$entity->getStep()]);
            if ($status && ($oldstep != $entity->getStep())) {
                $message = 'El ' . $translator->trans('Recibo') . ' ' . $receipt->getNumber() . ' ya tiene un status registrado con el ' . $translator->trans('Paso') . ' ' . $entity->getStep;
                $flashBag->add('notice',$message);
                goto next;
            }
            */
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
            if ($entity->getDate() != $olddate) {
                $clock = $form['clock']->getData();
            } else {
                $clock = $oldtime;
            }
            $arrivedate = $entity->getDate();
            $rdate = substr($arrivedate,0,10) . 'T' . $clock;
            $entity->setDate(new \DateTime($rdate));
            $entity->setPlace($city_track);
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('receipt_show', array('id' => $entity->getReceipt()->getId())));
        }
        next:
        return array(
            'edition' => false,
            'entity' => $entity,
            'form'  => $form->createView(),
        );
    }
    /**
     * Displays a form to EDIT a new Statusreceipt entity.
     *
     * @Route("/{id}/removereceipt", name="statusreceipt_remove")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function removereceiptAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $entity = $em->getRepository('NvCargaBundle:Statusreceipt')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el Status');
        }
        $receipt = $entity->getReceipt();
        $receipt->removeListstatus($entity);
        $em->remove($entity);
        $em->flush();
        return $this->redirect($this->generateUrl('receipt_show', array('id' => $receipt->getId())));
    }

    /**
     * Displays a form to create a new Statusguide entity.
     *
     * @Route("/{id}/receipt_whrecadd", name="receipt_whrecadd")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function receipt_whrecaddAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$receipt) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }
        $receipt->setStatuswhrec(true);
        $em->flush();
        return $this->redirect($this->generateUrl('receipt_show', array('id' => $receipt->getId())));
    }

    /**
     * Displays a form to create a new Statusguide entity.
     *
     * @Route("/{id}/receipt_whrecremove", name="receipt_whrecremove")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function receipt_whrecremoveAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$receipt) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }
        $receipt->setStatuswhrec(false);
        $em->flush();
        return $this->redirect($this->generateUrl('receipt_show', array('id' => $receipt->getId())));
    }
    /**
     * Displays a form to create a new Statusguide entity.
     *
     * @Route("/{id}/receipt_guideadd", name="receipt_guideadd")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function receipt_guideaddAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$receipt) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }
        $receipt->setStatusguide(true);
        $em->flush();
        return $this->redirect($this->generateUrl('receipt_show', array('id' => $receipt->getId())));
    }
    /**
     * Displays a form to create a new Statusguide entity.
     *
     * @Route("/{id}/receipt_guidermove", name="receipt_guideremove")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function receipt_guideremoveAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$receipt) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }
        $receipt->setStatusguide(false);
        $em->flush();
        return $this->redirect($this->generateUrl('receipt_show', array('id' => $receipt->getId())));
    }
    // METODOS PARA WHREC

    /**
     *
     * @Route("/{id}/createwhrec", name="statuswhrec_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Statuswhrec:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function createwhrecAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $whrec = $em->getRepository('NvCargaBundle:WHrec')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$whrec) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Warehouse') );
        }
        $entity = new Statuswhrec();
        $form   = $this->FormStatuswhrec($entity, $id);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $form->handleRequest($request);

        if ($form->isValid()) {
            /*
            $status = $em->getRepository('NvCargaBundle:Statuswhrec')->findOneBy(['whrec' => $whrec,'step'=>$entity->getStep()]);
            if ($status) {
                $message = 'El ' . $translator->trans('Warehouse') . ' ' . $whrec->getNumber() . ' ya tiene un status registrado con el ' . $translator->trans('Paso') . ' ' . $entity->getStep;
                $flashBag->add('notice',$message);
                goto next;
            }
            */
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
            $clock = $form['clock']->getData();
            $arrivedate = $entity->getDate();
            $rdate = substr($arrivedate,0,10) . 'T' . $clock;
            $entity->setDate(new \DateTime($rdate));
            $entity->setPlace($city_track);
            $entity->setWhrec($whrec);
            $whrec->addListstatus($entity);
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('whrec_show', array('id' => $id)));
        }
        next:
        return array(
            'entity' => $entity,
            'whrec' => $whrec,
            'form'  => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Statuswhrec entity.
     *
     * @param Statuswhrec $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function FormStatuswhrec(Statuswhrec $entity, $whrec_id)
    {
        $form = $this->createForm(new StatuswhrecType($this->getDoctrine()->getManager(), $this->getUser()->getMaincompany()), $entity, array(
            'action' => $this->generateUrl('statuswhrec_create', array('id' => $whrec_id)),
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Crear'));
        return $form;
    }

    /**
     * Displays a form to create a new Statuswhrec entity.
     *
     * @Route("/{id}/newwhrec", name="statuswhrec_new")
     * @Method("GET")
     * @Template("NvCargaBundle:Statuswhrec:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function newwhrecAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $whrec = $em->getRepository('NvCargaBundle:WHrec')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$whrec) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Warehouse') );
        }
        $entity = new Statuswhrec();
        $form   = $this->FormStatuswhrec($entity, $id);

        return array(
            'entity' => $entity,
            'whrec' => $whrec,
            'form'  => $form->createView(),
        );
    }
    /**
     * Displays a form to EDIT a new Statuswhrec entity.
     *
     * @Route("/{id}/editwhrec", name="statuswhrec_edit")
     * @Method("GET")
     * @Template("NvCargaBundle:Statuswhrec:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function editwhrecAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $entity = $em->getRepository('NvCargaBundle:Statuswhrec')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el Status');
        }
        $entity->setDate($entity->getDate()->format('m/d/Y'));
        $form   = $this->FormStatuswhrecedit($entity);
        return array(
            'edition' => true,
            'entity' => $entity,
            'form'  => $form->createView(),
        );
    }
     /**
     * Creates a form to create a Statuswhrec entity.
     *
     * @param Statuswhrec $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function FormStatuswhrecedit(Statuswhrec $entity)
    {
        $form = $this->createForm(new StatuswhrecType($this->getDoctrine()->getManager(), $this->getUser()->getMaincompany()),$entity, array(
            'action' => $this->generateUrl('statuswhrec_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));
        return $form;
    }
    /**
     *
     * @Route("/{id}/updatewhrec", name="statuswhrec_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Statuswhrec:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function updatewhrecAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $entity = $em->getRepository('NvCargaBundle:Statuswhrec')->findOneBy(['id'=>$id]);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el Status');
        }
        $oldstep = $entity->getStep();
        $olddate = $entity->getDate()->format('m/d/Y');
        $oldtime = $entity->getDate()->format('H:i:s');
        $entity->setDate($entity->getDate()->format('m/d/Y'));

        $form   = $this->FormStatuswhrecedit($entity);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            /*
            $status = $em->getRepository('NvCargaBundle:Statuswhrec')->findOneBy(['whrec' => $entity->getWhrec(),'step'=>$entity->getStep()]);
            if ($status && ($oldstep != $entity->getStep())) {
                $message = 'El ' . $translator->trans('Warehouse') . ' ' . $whrec->getNumber() . ' ya tiene un status registrado con el ' . $translator->trans('Paso') . ' ' . $entity->getStep;
                $flashBag->add('notice',$message);
                goto next;
            }
            */
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
            if ($entity->getDate() != $olddate) {
                $clock = $form['clock']->getData();
            } else {
                $clock = $oldtime;
            }
            $arrivedate = $entity->getDate();
            $rdate = substr($arrivedate,0,10) . 'T' . $clock;
            $entity->setDate(new \DateTime($rdate));
            $entity->setPlace($city_track);
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('whrec_show', array('id' => $entity->getWhrec()->getId())));
        }
        next:
        return array(
            'edition' => false,
            'entity' => $entity,
            'form'  => $form->createView(),
        );
    }
    /**
     * Displays a form to EDIT a new Statuswhrec entity.
     *
     * @Route("/{id}/removewhrec", name="statuswhrec_remove")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function removewhrecAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $entity = $em->getRepository('NvCargaBundle:Statuswhrec')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el Status');
        }
        $whrec = $entity->getWHrec();
        $whrec->removeListstatus($entity);
        $em->remove($entity);
        $em->flush();
        return $this->redirect($this->generateUrl('whrec_show', array('id' => $whrec->getId())));
    }
    /**
     * Displays a form to create a new Statusguide entity.
     *
     * @Route("/{id}/whrec_guideadd", name="whrec_guideadd")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function whrec_guideaddAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $whrec = $em->getRepository('NvCargaBundle:WHrec')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$whrec) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Warehouse') );
        }
        $whrec->setStatusguide(true);
        $em->flush();
        return $this->redirect($this->generateUrl('whrec_show', array('id' => $whrec->getId())));
    }
    /**
     * Displays a form to create a new Statusguide entity.
     *
     * @Route("/{id}/whrec_guideremove", name="whrec_guideremove")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function whrec_guideremoveAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $whrec = $em->getRepository('NvCargaBundle:WHrec')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$whrec) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Warehouse') );
        }
        $whrec->setStatusguide(false);
        $em->flush();
        return $this->redirect($this->generateUrl('whrec_show', array('id' => $whrec->getId())));
    }
    // METODOS PARA GUIAS
    /**
     * Displays a form to create a new Statusguide entity.
     *
     * @Route("/{id}/guide_consoladd", name="guide_consoladd")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function guide_consoladdAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $guide = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$guide) {
            throw $this->createNotFoundException('No existe la ' . $translator->trans('Guía') );
        }
        $guide->setStatusconsol(true);
        $em->flush();
        return $this->redirect($this->generateUrl('guide_show', array('id' => $guide->getId())));
    }

    /**
     * Displays a form to create a new Statusguide entity.
     *
     * @Route("/{id}/guide_consolremove", name="guide_consolremove")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function guide_consolremoveAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $guide = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$guide) {
            throw $this->createNotFoundException('No existe la ' . $translator->trans('Guía') );
        }
        $guide->setStatusconsol(false);
        $em->flush();
        return $this->redirect($this->generateUrl('guide_show', array('id' => $guide->getId())));
    }

    /**
     *
     * @Route("/{id}/createguide", name="statusguide_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Statusguide:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function createguideAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $guide = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$guide) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Guía') );
        }
        $entity = new Statusguide();
        $form   = $this->FormStatusguide($entity, $id);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
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
            $clock = $form['clock']->getData();
            $arrivedate = $entity->getDate();
            $rdate = substr($arrivedate,0,10) . 'T' . $clock;
            $entity->setDate(new \DateTime($rdate));
            $entity->setPlace($city_track);
            $entity->setGuide($guide);
            $guide->addListstatus($entity);
            $em->persist($entity);
            $em->flush();
            $this->Sendemail($guide,$entity);
            return $this->redirect($this->generateUrl('guide_show', array('id' => $id)));
        }
        next:
        return array(
            'entity' => $entity,
            'guide' => $guide,
            'form'  => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Statusguide entity.
     *
     * @param Statusguide $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function FormStatusguide(Statusguide $entity, $guide_id)
    {
        $form = $this->createForm(new StatusguideType($this->getDoctrine()->getManager(), $this->getUser()->getMaincompany()), $entity, array(
            'action' => $this->generateUrl('statusguide_create', array('id' => $guide_id)),
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Crear'));
        return $form;
    }

    /**
     * Displays a form to create a new Statusguide entity.
     *
     * @Route("/{id}/newguide", name="statusguide_new")
     * @Method("GET")
     * @Template("NvCargaBundle:Statusguide:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function newguideAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $guide = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$guide) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Guía') );
        }
        $entity = new Statusguide();
        $form   = $this->FormStatusguide($entity, $id);

        return array(
            'entity' => $entity,
            'guide' => $guide,
            'form'  => $form->createView(),
        );
    }
    /**
     * Displays a form to EDIT a new Statusguide entity.
     *
     * @Route("/{id}/editguide", name="statusguide_edit")
     * @Method("GET")
     * @Template("NvCargaBundle:Statusguide:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function editguideAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $entity = $em->getRepository('NvCargaBundle:Statusguide')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el Status');
        }
        $entity->setDate($entity->getDate()->format('m/d/Y'));
        $form   = $this->FormStatusguideedit($entity);
        return array(
            'edition' => true,
            'entity' => $entity,
            'form'  => $form->createView(),
        );
    }
     /**
     * Creates a form to create a Statusguide entity.
     *
     * @param Statusguide $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function FormStatusguideedit(Statusguide $entity)
    {
        $form = $this->createForm(new StatusguideType($this->getDoctrine()->getManager(), $this->getUser()->getMaincompany()),$entity, array(
            'action' => $this->generateUrl('statusguide_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));
        return $form;
    }
    /**
     *
     * @Route("/{id}/updateguide", name="statusguide_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Statusguide:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function updateguideAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $entity = $em->getRepository('NvCargaBundle:Statusguide')->findOneBy(['id'=>$id]);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el Status');
        }
        $oldstep = $entity->getStep();
        $olddate = $entity->getDate()->format('m/d/Y');
        $oldtime = $entity->getDate()->format('H:i:s');
        $entity->setDate($entity->getDate()->format('m/d/Y'));

        $form   = $this->FormStatusguideedit($entity);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            /*
            $status = $em->getRepository('NvCargaBundle:Statusguide')->findOneBy(['guide' => $entity->getGuide(),'step'=>$entity->getStep()]);
            if ($status && ($oldstep != $entity->getStep())) {
                $message = 'La ' . $translator->trans('Guía') . ' ' . $guide->getNumber() . ' ya tiene un status registrado con el ' . $translator->trans('Paso') . ' ' . $entity->getStep;
                $flashBag->add('notice',$message);
                goto next;
            }
            */
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
            if ($entity->getDate() != $olddate) {
                $clock = $form['clock']->getData();
            } else {
                $clock = $oldtime;
            }
            $arrivedate = $entity->getDate();
            $rdate = substr($arrivedate,0,10) . 'T' . $clock;
            $entity->setDate(new \DateTime($rdate));
            $entity->setPlace($city_track);
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('guide_show', array('id' => $entity->getGuide()->getId())));
        }
        next:
        return array(
            'edition' => false,
            'entity' => $entity,
            'form'  => $form->createView(),
        );
    }
    /**
     * Displays a form to EDIT a new Statusguide entity.
     *
     * @Route("/{id}/removeguide", name="statusguide_remove")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function removeguideAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $entity = $em->getRepository('NvCargaBundle:Statusguide')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el Status');
        }
        $guide = $entity->getGuide();
        $guide->removeListstatus($entity);
        $em->remove($entity);
        $em->flush();
        return $this->redirect($this->generateUrl('guide_show', array('id' => $guide->getId())));
    }

    // METODOS PARA CONSOLIDADOS

    /**
     *
     * @Route("/{id}/createconsol", name="statusconsol_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Statusconsol:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_CONSOLIDATED') or has_role('ROLE_ADMIN')")
     */
    public function createconsolAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $consol = $em->getRepository('NvCargaBundle:Consolidated')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$consol) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Consolidado') );
        }
        $entity = new Statusconsol();
        $form   = $this->FormStatusconsol($entity, $id);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $form->handleRequest($request);

        if ($form->isValid()) {
            /*
            $status = $em->getRepository('NvCargaBundle:Statusconsol')->findOneBy(['consol' => $consol,'step'=>$entity->getStep()]);
            if ($status) {
                $message = 'La ' . $translator->trans('Consolidado') . ' ' . $consol->getNumber() . ' ya tiene un status registrado con el ' . $translator->trans('Paso') . ' ' . $entity->getStep;
                $flashBag->add('notice',$message);
                goto next;
            }
            */
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
            $clock = $form['clock']->getData();
            $arrivedate = $entity->getDate();
            $rdate = substr($arrivedate,0,10) . 'T' . $clock;
            $entity->setDate(new \DateTime($rdate));
            $entity->setPlace($city_track);
            $entity->setConsol($consol);
            $consol->addListstatus($entity);
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('consolidated_show', array('id' => $id)));
        }
        next:
        return array(
            'entity' => $entity,
            'consol' => $consol,
            'form'  => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Statusconsol entity.
     *
     * @param Statusconsol $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function FormStatusconsol(Statusconsol $entity, $consol_id)
    {
        $form = $this->createForm(new StatusconsolType($this->getDoctrine()->getManager(), $this->getUser()->getMaincompany()), $entity, array(
            'action' => $this->generateUrl('statusconsol_create', array('id' => $consol_id)),
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Crear'));
        return $form;
    }

    /**
     * Displays a form to create a new Statusconsol entity.
     *
     * @Route("/{id}/newconsol", name="statusconsol_new")
     * @Method("GET")
     * @Template("NvCargaBundle:Statusconsol:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_CONSOLIDATED') or has_role('ROLE_ADMIN')")
     */
    public function newconsolAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $consol = $em->getRepository('NvCargaBundle:Consolidated')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$consol) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Consolidado') );
        }
        $entity = new Statusconsol();
        $form   = $this->FormStatusconsol($entity, $id);

        return array(
            'entity' => $entity,
            'consol' => $consol,
            'form'  => $form->createView(),
        );
    }
    /**
     * Displays a form to EDIT a new Statusconsol entity.
     *
     * @Route("/{id}/editconsol", name="statusconsol_edit")
     * @Method("GET")
     * @Template("NvCargaBundle:Statusconsol:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_CONSOLIDATED') or has_role('ROLE_ADMIN')")
     */
    public function editconsolAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $entity = $em->getRepository('NvCargaBundle:Statusconsol')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el Status');
        }
        $entity->setDate($entity->getDate()->format('m/d/Y'));
        $form   = $this->FormStatusconsoledit($entity);
        return array(
            'edition' => true,
            'entity' => $entity,
            'form'  => $form->createView(),
        );
    }
     /**
     * Creates a form to create a Statusconsol entity.
     *
     * @param Statusconsol $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function FormStatusconsoledit(Statusconsol $entity)
    {
        $form = $this->createForm(new StatusconsolType($this->getDoctrine()->getManager(), $this->getUser()->getMaincompany()),$entity, array(
            'action' => $this->generateUrl('statusconsol_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));
        return $form;
    }
    /**
     *
     * @Route("/{id}/updateconsol", name="statusconsol_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Statusconsol:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_CONSOLIDATED') or has_role('ROLE_ADMIN')")
     */
    public function updateconsolAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $entity = $em->getRepository('NvCargaBundle:Statusconsol')->findOneBy(['id'=>$id]);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el Status');
        }
        $oldstep = $entity->getStep();
        $olddate = $entity->getDate()->format('m/d/Y');
        $oldtime = $entity->getDate()->format('H:i:s');
        $entity->setDate($entity->getDate()->format('m/d/Y'));

        $form   = $this->FormStatusconsoledit($entity);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            /*
            $status = $em->getRepository('NvCargaBundle:Statusconsol')->findOneBy(['consol' => $entity->getConsol(),'step'=>$entity->getStep()]);
            if ($status && ($oldstep != $entity->getStep())) {
                $message = 'El ' . $translator->trans('Consolidado') . ' ' . $consol->getNumber() . ' ya tiene un status registrado con el ' . $translator->trans('Paso') . ' ' . $entity->getStep;
                $flashBag->add('notice',$message);
                goto next;
            }
            */
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
            if ($entity->getDate() != $olddate) {
                $clock = $form['clock']->getData();
            } else {
                $clock = $oldtime;
            }
            $arrivedate = $entity->getDate();
            $rdate = substr($arrivedate,0,10) . 'T' . $clock;
            $entity->setDate(new \DateTime($rdate));
            $entity->setPlace($city_track);
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('consolidated_show', array('id' => $entity->getConsol()->getId())));
        }
        next:
        return array(
            'edition' => false,
            'entity' => $entity,
            'form'  => $form->createView(),
        );
    }
    /**
     * Displays a form to EDIT a new Statusconsol entity.
     *
     * @Route("/{id}/removeconsol", name="statusconsol_remove")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function removeconsolAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $translator = $this->get('translator');
        $entity = $em->getRepository('NvCargaBundle:Statusconsol')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el Status');
        }
        $consol = $entity->getConsol();
        $consol->removeListstatus($entity);
        $em->remove($entity);
        $em->flush();
        return $this->redirect($this->generateUrl('consolidated_show', array('id' => $consol->getId())));
    }

    public function Sendemail($guide, $status)
    {
        $email = $guide->getSender()->getEmail(); //REMITENTE
        $pemail = 'cliente_' . strtolower($guide->getMaincompany()->getAcronym());
        $maincompany = $guide->getMainCompany();
        $pos = strpos($email, $pemail);
        $translator = $this->get('translator');

        if (($guide->getEmailnoti()) && ($pos === false) && ($email)) {
	        $body = '<p align="right">Ref:' . $guide->getNumber() . '</p><br>';
            $body = $body . '<b>' .  $guide->getSender()->getName() . ' '  . $guide->getSender()->getLastname() . '</b><br><br>';
            $body = $body . 'Su envío se encuentra <b>' . $status->getStep()->getName() . '<br><br>';
            $body = $body . 'Observación: ' . $status->getComment() . '</b><br><br>';
            $body = $body . 'Fecha: ' .  $status->getDate()->format('m/d/Y') . '<br>';
            $body = $body . 'Número: ' .  $guide->getNumber() . '<br>';
            $body = $body . 'Remitente: ' .   $guide->getSender()->getName() . ' '  . $guide->getSender()->getLastname() . '<br>';
            $body = $body . 'Destinatario: ' .   $guide->getAddressee()->getName() . ' '  . $guide->getAddressee()->getLastname() . '<br>';
            $body = $body . 'Bultos: ' .  $guide->getPieces() . '<br>';
            $body = $body . 'Peso: ' .  $guide->getRealweight() . '<br><br>';
            $body = $body . 'Si desea consultar detalles del envío y de su estatus, sírvase ingresar en nuestro ';
            $body = $body . '<a href="http://' . $_SERVER['SERVER_NAME'] . '/tracking/tracking"> ENLACE de Seguimiento de Guías</a>, usando el número de guía suministrado; o a través de su casillero personal. <br><br>';
            $body = $body . 'Gracias por su confianza y ser parte de la familia <b>"' . $maincompany->getName() . '"</b><br><br>';
            $color = $translator->trans('tailcolor');
            $body = $body . '<p style="font-size:20px; color:' . $color . ';"><b>**ESTE ES UN CORREO NO MONITOREADO, POR FAVOR NO RESPONDA AL MISMO**</b></p>';

            $setfrom = $maincompany->getEmail();
            $fromname = $fromname = 'Notificación de ' . $maincompany->getName();
            $message = \Swift_Message::newInstance()
                    ->setContentType("text/html")
                    ->setSubject('Seguimiento de su envío: '. $guide->getNumber())
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

            $send = $this->get('mailer')->send($message);

            out:
            if ($send < 0) {
                $em = $this->manager;
                $head = "<b>No se pudo enviar el EMAIl <br> ";
                if ($send == -1) {
                    $head = $head . "La dirección DESTINO: " . $email . ' no es correcta (RFC 2822)</b><br>';
                } else {
                    $head = $head . "La dirección REMITENTE: " . $setfrom . ' no es correcta (RFC 2822)</b><br>';
                }
                $msg = new Message();
                $msg->setSender($this->getUser());
                $msg->setReceiver($this->getUser());
                $msg->setSubject('Error enviando email (Status de Guía)');
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
