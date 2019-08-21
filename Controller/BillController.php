<?php

namespace NvCarga\Bundle\Controller;

use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Bill;
use NvCarga\Bundle\Entity\Billpay;
use NvCarga\Bundle\Entity\Message;
use NvCarga\Bundle\Form\BillType;

use Symfony\Component\Validator\Constraints\NotBlank;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * Bill controller.
 *
 * @Route("/bill")
 */
class BillController extends Controller
{
    /**
     * Lists all Bill entities.
     *
     * @Route("/index", name="bill_index")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_BILL') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_BILL')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $agency = $this->getUser()->getAgency();
        $maincompany =  $this->getUser()->getMaincompany();
        $type = $agency->getType();
        // $all = $em->getRepository('NvCargaBundle:Bill')->findBy(['canceled'=>FALSE, 'maincompany'=> $maincompany])
        $all = $em->getRepository('NvCargaBundle:Bill')->createQueryBuilder('b')
                                            ->where('b.status != :status')
                                            ->andwhere('b.maincompany = :maincompany')
                                            ->setParameters(array('maincompany' => $maincompany, 'status' => 'ANULADA'))
                                            ->orderBy('b.number', 'DESC')
                                            ->setMaxResults(500)
                                            ->getQuery()
                                            ->getResult();
        if ($type == "MASTER") {
            $entities = $all;
        } else {
            $entities = [];
            foreach ($all as $entity) {
                $guide = $entity->getGuides()->last();
                if ($guide->getAgency() === $agency()) {
                    $entities[] = $entity;
                }
            }
        }
        return array(
                'entities' => $entities,
        );

    }

   /**
     * Lists all Canceled Bill entities.
     *
     * @Route("/indexcancel", name="bill_canceled")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_BILL') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_BILL')")
     */
    public function indexcancelAction()
    {
        $em = $this->getDoctrine()->getManager();
        $agency = $this->getUser()->getAgency();
        $maincompany =  $this->getUser()->getMaincompany();
        $type = $agency->getType();
        $all = $em->getRepository('NvCargaBundle:Bill')->findBy(['status'=>'ANULADA', 'maincompany'=> $maincompany]);
        if ($type == "MASTER") {
            $entities = $all;
        } else {
            $entities = [];
            foreach ($all as $entity) {
                $guide = $entity->getGuides()->last();
                if ($guide->getAgency() === $agency()) {
                    $entities[] = $entity;
                }
            }
        }
        return array(
            'entities' => $entities,
        );

    }
    /**
     * Lists all Bill entities.
     *
     * @Route("/list", name="bill_list")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_BILL') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_BILL')")
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $agency = $this->getUser()->getAgency();
        $maincompany =  $this->getUser()->getMaincompany();
        $type = $agency->getType();

        if ($type == "MASTER") {
            $entities = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                                            ->where('g.bill IS NULL')
                                            ->andwhere('g.maincompany = :maincompany')
                                            ->setParameters(array('maincompany' => $maincompany))
                                            ->orderBy('g.number', 'ASC')
                                            ->setMaxResults(1000)
                                            ->getQuery()
                                            ->getResult();
            $agencies = $em->getRepository('NvCargaBundle:Agency')->findByMaincompany($maincompany);
        } else {
            $entities = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                                            ->where('g.agency = :ag' )
                                            ->andwhere('g.bill IS NULL')
                                            ->setParameters(array('ag' => $agency))
                                            ->orderBy('g.number', 'ASC')
                                            ->setMaxResults(1000)
                                            ->getQuery()
                                            ->getResult();
            $agencies = null;
        }
        return array(
            'entities' => $entities,
            'agencies' => $agencies,
        );
    }

    /**
     * Lists all Bill entities.
     *
     * @Route("/wlist", name="bill_wlist")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_BILL') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_BILL')")
     */
    public function wlistAction()
    {
        $em = $this->getDoctrine()->getManager();
        $agency = $this->getUser()->getAgency();
        $maincompany =  $this->getUser()->getMaincompany();
        $type = $agency->getType();

        if ($type == "MASTER") {
            $entities = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                                            ->where('g.bill IS NOT NULL')
                                            ->andwhere('g.maincompany = :maincompany')
                                            ->setParameters(array('maincompany' => $maincompany))
                                            ->orderBy('g.creationdate', 'DESC')
                                            ->setMaxResults(200)
                                            ->setFirstResult(0)
                                            ->getQuery()
                                            ->getResult();
            $agencies = $em->getRepository('NvCargaBundle:Agency')->findByMaincompany($maincompany);
        } else {
            $entities = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                                            ->where('g.agency = :ag' )
                                            ->andwhere('g.bill IS NOT NULL')
                                            ->setParameters(array('ag' => $agency))
                                            ->orderBy('g.creationdate', 'DESC')
                                            ->setMaxResults(200)
                                            ->setFirstResult(0)
                                            ->getQuery()
                                            ->getResult();
            $agencies = null;
        }
        return array(
            'entities' => $entities,
            'agencies' => $agencies,
        );
    }
    /**
     * Creates a form to create a Bill entity.
     *
     * @param Bill $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createGuideForm(Bill $entity, ObjectChoiceList $choiceList)
    {
        $form = $this->createForm(new BillType(), $entity, array(
            'action' => $this->generateUrl('bill_create'),
            'method' => 'POST',
        ));
        $form->add('lguides', 'choice', array('label' => 'labels.guides', 'choice_list'=> $choiceList, 'multiple'  => true, 'mapped' => false));
        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }
    /**
     * Creates a form to create a Bill entity.
     *
     * @param Bill $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm($fromid, $toid)
    {
	$form = $this->get('form.factory')->createNamedBuilder('bill_type','form', null, array())
	    ->add('paidtype', 'entity', array('label' => 'Tipo de pago ',  'class'=> 'NvCargaBundle:Paidtype', 'query_builder' => function (EntityRepository $er) {
        			return $er->createQueryBuilder('p')
					->where('p.active = true')
					->andwhere('p.deleted = false')
            				->orderBy('p.name', 'ASC');
    				}, 'required' => true,
						'constraints' => array(new NotBlank(["message" => "Seleccione el tipo de pago"]))))
	    ->add('billto', 'choice', array('choices'  => array('Remitente' => $toid, 'Destinatario' => $fromid),
    			'choices_as_values' => true, 'mapped' => false, 'required' => true, 'label'=>'Facturar a:',
						'constraints' => array(new NotBlank(["message" => "Seleccione a quien se factura"]))))
            ->add('submit', 'submit', array('label' => 'Facturar'))
            ->getForm()
        ;
	return $form;
    }
    private function createMultForm()
    {
	$form = $this->get('form.factory')->createNamedBuilder('bill_type','form', null, array())
	    ->add('paidtype', 'entity', array('label' => 'Tipo de pago ',  'class'=> 'NvCargaBundle:Paidtype', 'query_builder' => function (EntityRepository $er) {
        			return $er->createQueryBuilder('p')
					->where('p.active = true')
					->andwhere('p.deleted = false')
            				->orderBy('p.name', 'ASC');
    				}, 'required' => true,
						'constraints' => array(new NotBlank(["message" => "Seleccione el tipo de pago"]))))
	    ->add('billto', 'choice', array('choices'  => array('Remitente' => 1, 'Destinatario' => 2),
    			'choices_as_values' => true, 'mapped' => false, 'required' => true, 'label'=>'Facturar a:',
						'constraints' => array(new NotBlank(["message" => "Seleccione a quien se factura"]))))
            ->getForm()
        ;
	return $form;
    }
    /**
     * Displays a form to create a new Bill entity.
     *
     * @Route("/new", name="bill_new")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_BILL') or has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $idguide = $request->query->get('idguide');
        $agency = $this->getUser()->getAgency();
        $em = $this->getDoctrine()->getManager();
        $maincompany =  $this->getUser()->getMaincompany();

        $guide = null;
        $translator = $this->get('translator');
        if ($idguide) {
            $guide = $em->getRepository('NvCargaBundle:Guide')->find($idguide);
            // exit(\Doctrine\Common\Util\Debug::dump($guide->getNumber()));
            if (!$guide) {
                throw $this->createNotFoundException('No existe ' .  $translator->trans('Guía') . ' a facturar');
            }
        }
        if ($guide) {
            $guides = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                                        ->where('g.id != :id')
                                        ->andwhere('g.bill IS NULL')
                                        ->andwhere('g.maincompany = :thecompany')
                                        ->andwhere('g.sender = :sender OR g.addressee = :addr')
                                        ->setParameters(array('id' => $guide->getId(), //'ag' => $agency,
                                                    'sender' => $guide->getSender(),
                                                    'addr' => $guide->getAddressee(),
                                                    'thecompany' => $maincompany))
                                        ->orderBy('g.number', 'ASC')
                                        ->getQuery()
                                        ->getResult();
        } else {
            $guides = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                                        ->where('g.agency = :ag')
                                        ->andwhere('g.bill IS NULL')
                                        ->setParameters(array('ag' => $agency))
                                        ->orderBy('g.number', 'ASC')
                                        ->getQuery()
                                        ->getResult();
        }

        // $paidtypes = $em->getRepository('NvCargaBundle:Paidtype')->findBy(array('maincompany' => $maincompany, 'active'=> true, 'deleted'=>false));
        $paidtypes = $em->getRepository('NvCargaBundle:Paidtype')->findBy(array('maincompany'=>$maincompany,'active'=> true, 'deleted'=>false));
        $countries = $maincompany->getCountries()->toArray();
        $list = array();
        foreach ($countries as $country) {
            $list[] = $country->getName();
        }
        $currency = $em->getRepository('NvCargaBundle:Currency')->findByCountry($list, array('country'=>'ASC'));
        $accounts = $em->getRepository('NvCargaBundle:Account')->findBy(array('maincompany'=>$maincompany,'isactive'=> true));

        return array(
                'paidtypes' => $paidtypes,
                'currency' => $currency,
                'guide' => $guide,
                'guides' => $guides,
                'accounts' => $accounts,
        );
    }

    /**
     * cancel a Bill entity.
     *
     * @Route("/cancel/{id}", name="bill_cancel")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_BILL') or has_role('ROLE_ADMIN')")
     */
    public function cancelAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $maincompany =  $this->getUser()->getMaincompany();
        $bill = $em->getRepository('NvCargaBundle:Bill')->createQueryBuilder('b')
                                            ->where('b.id = :id')
                                            ->andwhere('b.status != :status')
                                            ->andwhere('b.maincompany = :maincompany')
                                            ->setParameters(array('id'=>$id, 'maincompany' => $maincompany, 'status' => 'CANCELADA'))
                                            ->orderBy('b.number', 'ASC')
                                            ->getQuery()
                                            ->getSingleResult();
        if (!$bill) {
            throw $this->createNotFoundException('No existe la factura o ya está cancelada');
        }
        $customer=$bill->getCustomer();
        $guides = $bill->getGuides();
        foreach ($guides as $guide) {
            $bill->removeGuide($guide);
            $guide->setBill(null);
        }
        $refund = 0;
        foreach ($bill->getPayments()  as $payment) {
            if ($payment->getVerified() && !$payment->getRefund()) {
                $payment->setRefund(true);
                $refund += $payment->getAmount();
            }
        }
        $customer->setRefunded($customer->getRefunded() + $refund);
        $bill->setStatus('CANCELADA');
        $bill->setBalance(0);
        $em->flush();

        return $this->redirect($this->generateUrl('bill_index'));

    }


    /**
     * Finds and displays a Bill entity.
     *
     * @Route("/{id}/show", name="bill_show")
     * @Method("GET")
     * @Template()
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany =  $this->getUser()->getMaincompany();
        $user = $this->getUser();
        $pobox = $user->getPobox();

        $admin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ADMIN_BILL') || $this->isGranted('ROLE_VIEW_BILL');

        $entity = $em->getRepository('NvCargaBundle:Bill')->createQueryBuilder('b')
                                            ->where('b.id = :id')
                                            ->andwhere('b.status != :status')
                                            ->andwhere('b.maincompany = :maincompany')
                                            ->setParameters(array('id'=>$id, 'maincompany' => $maincompany, 'status' => 'CANCELADA'))
                                            ->orderBy('b.number', 'ASC')
                                            ->getQuery()
                                            ->getSingleResult();
        if (!$entity) {
            throw $this->createNotFoundException('La factura no existe o ha sido cancelada');
        }
        $owner = null;
        if ($pobox) {
            $customer = $pobox->getCustomer();
            $owner = $customer === $entity->getCustomer();
        }
        if ((!$admin) && (!$owner)) {
            throw $this->createNotFoundException('No tiene permiso para ver la Factura');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Bill entity.
     *
     * @Route("/{id}/edit", name="bill_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_BILL') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany =  $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Bill')->createQueryBuilder('b')
                                            ->where('b.id = :id')
                                            ->andwhere('b.maincompany = :maincompany')
                                            ->setParameters(array('id'=>$id, 'maincompany' => $maincompany))
                                            ->orderBy('b.number', 'ASC')
                                            ->getQuery()
                                            ->getSingleResult();

        if (!$entity) {
            throw $this->createNotFoundException('No existe la factura o no puede ser EDITADA');
        }

        $guides = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                                        ->where('g.id not IN (:ids)')
                                        ->andwhere('g.bill IS NULL')
                                        ->andwhere('g.maincompany = :thecompany')
                                        ->andwhere('g.sender = :sender OR g.addressee IN (:addrs)')
                                        ->setParameters(array('ids' => $entity->getGuides(), //'ag' => $agency,
                                                    'sender' => $entity->getCustomer(),
                                                    'addrs' => $entity->getCustomer()->getBaddress(),
                                                    'thecompany' => $maincompany))
                                        ->orderBy('g.number', 'ASC')
                                        ->getQuery()
                                        ->getResult();
        //$paidtypes = $em->getRepository('NvCargaBundle:Paidtype')->findBy(array('active'=> true, 'deleted'=>false));
        return array(
                //'paidtypes' => $paidtypes,
                'guides' => $guides,
                'entity' => $entity,
        );

    }

    /**
     * @Route("/{id}/update", name="bill_update")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function updateAction(Request $request,$id)
    {
        $user = $this->getUser();
        $agency = $user->getAgency();
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get('translator');
        $maincompany = $user->getMaincompany();

        $bill = $em->getRepository('NvCargaBundle:Bill')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$bill) {
            throw $this->createNotFoundException('No existe la Factura a actualizar');
        }

        $guidein = $request->query->get('guidein');
        $guideout = $request->query->get('guideout');
        //$paidtype = $request->query->get('paidtype');
        $expdate = $request->query->get('expdate');
        //$status = $request->query->get('status');
        $user = $this->getUser();
        $maincompany = $user->getMainCompany();



        if ($guidein)  {
            $gin=json_decode($guidein);
        } else {
            $gin = [-1];
        }
        if ($guideout)  {
            $gout=json_decode($guideout);
        } else {
            $gout = [-1];
        }


        $lin = $em->getRepository('NvCargaBundle:Guide')->findBy(['id'=>$gin,'maincompany'=>$maincompany]);

        $lout = $em->getRepository('NvCargaBundle:Guide')->findBy(['id'=>$gout,'maincompany'=>$maincompany]);
        /*
        $typeofpaid = $em->getRepository('NvCargaBundle:Paidtype')->find($paidtype);
        if (!$typeofpaid) {
            throw $this->createNotFoundException('No existe el tipo de pago seleccionado');
        }
        */
        //$bill->setPaidtype($typeofpaid);
        //$bill->setStatus($status);
        $typeofpaid = $bill->getPaidtype();
        $bill->setExpdate(new \DateTime($expdate));

        $total = $bill->getTotal();
        $balance = $bill->getBalance();
        $total_old = $total;
        $balance = $bill->getBalance();

        foreach ($lout as $oguide) {
            $bill->removeGuide($oguide);
            $oguide->setBill(null);
            $oguide->setPaidtype(null);
            $total-=$oguide->getTotalpaid();
        }

        foreach ($lin as $guide) {
            $guide->setBill($bill);
            $guide->setPaidtype($typeofpaid);
            $bill->addGuide($guide);
            $total+=$guide->getTotalpaid();
        }
        $bill->setTotal($total);
        if ($balance >= $total) {
            $bill->setStatus('COBRADA');
            $diff = $balance - $total;
            $difft = $diff;
            $customer = $bill->getCustomer();
            $payments = $bill->getPayments();
            $pos = 0;
            while (($diff > 0) && ($pos < count($payments))) {
                $payment = $payments[$pos];
                if ($payment->getVerified() && !$payment->getRefund()) {
                    if  ($payment->getAmount()  > $diff) {
                        $payment->setAmount($payment->getAmount - $diff);
                        $diff = 0;
                    } else {
                        $diff = $diff - $payment->getAmount();
                        $payment->setRefund(true);
                    }
                }
                $pos++;
            }
            $customer->setRefunded($customer->getRefunded() + $difft);
            $bill->setBalance($total);
        } else {
            $bill->setStatus('PENDIENTE');
        }

        $em->flush();

        return $this->redirect($this->generateUrl('bill_show', array('id' => $bill->getId())));

    }
    /**
     * Deletes a Bill entity.
     *
     * @Route("/{id}/delete", name="bill_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_BILL') or has_role('ROLE_ADMIN')")
     */

    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $maincompany =  $this->getUser()->getMaincompany();
            $entity = $em->getRepository('NvCargaBundle:Bill')->findOneBy(array('maincompany' => $maincompany, 'id'=>$id));

            if (!$entity) {
                throw $this->createNotFoundException('No existe la factura');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('bill'));
    }

    /**
     * Creates a form to delete a Bill entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('bill_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
     /**
     * Generate  a bill for printer
     *
     * @Route("/{id}/print", name="bill_print")
     * @Method("GET")
     * @Template()
     */
    public function printAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $admin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ADMIN_BILL') || $this->isGranted('ROLE_VIEW_BILL');
        $maincompany =  $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Bill')->createQueryBuilder('b')
                                            ->where('b.id = :id')
                                            ->andwhere('b.status != :status')
                                            ->andwhere('b.maincompany = :maincompany')
                                            ->setParameters(array('id'=>$id, 'maincompany' => $maincompany, 'status' => 'CANCELADA'))
                                            ->orderBy('b.number', 'ASC')
                                            ->getQuery()
                                            ->getSingleResult();

        if (!$entity) {
            throw $this->createNotFoundException('No existe la factura o ha sido cancelada');
        }
        $user = $this->getUser();
        $pobox = $user->getPobox();
        if ($pobox) {
            $customer=$pobox->getCustomer();
        } else {
            $customer=null;
        }
        $admin = ($admin) && ($entity->getMaincompany() == $maincompany);
        if (($customer != $entity->getCustomer()) && (!$admin)) {
            throw $this->createNotFoundException('No puede imprimir la factura');
        }
        $lguides = array();
        foreach ($entity->getGuides() as $guide) {
            $lguides[] = $guide->getId();
        }
        $services = $em->getRepository('NvCargaBundle:Servtoguide')->findBy(array('guide' => $lguides));

        $termcond = $em->getRepository('NvCargaBundle:Termcond')->findOneBy(array('maincompany' => $maincompany, 'tableclass' => 'Bill', 'active' => true));
        // $termcond = $em->getRepository('NvCargaBundle:Termcond')->findOneBy(array('tableclass' => 'Bill', 'active' => true));
        // exit(\Doctrine\Common\Util\Debug::dump($termcond));
        if ($termcond) {
            $message = $termcond->getMessage();
        } else {
            $message = null;
        }
        $accounts = $em->getRepository('NvCargaBundle:Account')->findBy(array('maincompany' => $maincompany, 'isactive' => true));
        if ($accounts) {
            $account=$accounts[0];
        } else {
            $account= null;
        }
        $viewoption = $this->getView('Bill', 'print');

        return  $this->render($viewoption,array(
                'entity'   => $entity,
                'services' => $services,
                'termcond' => $message,
                'account' => $account));
                /*
        return array(
                'entity'   => $entity,
                'services' => $services,
                'termcond' => $message,
                'account' => $account,
        );
        */
    }
    /**
     * Export to PDF
     *
     * @Route("/{id}/printpdf", name="bill_printpdf")
     */
    public function printfpdfAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany =  $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Bill')->createQueryBuilder('b')
                                            ->where('b.id = :id')
                                            ->andwhere('b.status != :status')
                                            ->andwhere('b.maincompany = :maincompany')
                                            ->setParameters(array('id'=>$id, 'maincompany' => $maincompany, 'status' => 'CANCELADA'))
                                            ->orderBy('b.number', 'ASC')
                                            ->getQuery()
                                            ->getSingleResult();

        if (!$entity) {
            throw $this->createNotFoundException('No existe la factura');
        }
        $lguides = array();
        foreach ($entity->getGuides() as $guide) {
            $lguides[] = $guide->getId();
        }
        $services = $em->getRepository('NvCargaBundle:Servtoguide')->findBy(array('guide' => $lguides));

        $termcond = $em->getRepository('NvCargaBundle:Termcond')->findOneBy(array('maincompany' => $maincompany,'tableclass' => 'Bill', 'active' => true));

        if ($termcond) {
            $message = $termcond->getMessage();
        } else {
            $message = null;
        }

        $accounts = $em->getRepository('NvCargaBundle:Account')->findBy(array('maincompany' => $maincompany, 'isactive' => true));
        if ($accounts) {
            $account=$accounts[0];
        } else {
            $account= null;
        }
        // exit(\Doctrine\Common\Util\Debug::dump($accounts));
        /*
        if ($accounts) {
            $account = $accounts->first();
        } else {
            $account = null;
        }
        */
        $user = $this->getUser();
        $pobox = $user->getPobox();
        if ($pobox) {
            $customer=$pobox->getCustomer();
        } else {
            $customer=null;
        }
        $admin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ADMIN_BILL') || $this->isGranted('ROLE_VIEW_BILL');
        $admin = ($admin) && ($entity->getMaincompany() == $maincompany);
        if (($customer != $entity->getCustomer()) && (!$admin)) {
            throw $this->createNotFoundException('No puede imprimir la factura');
        }
        $viewoption = $this->getView('Bill', 'print');

        $html = $this->renderView($viewoption,
                            array('entity' => $entity,
                            'services' => $services,
                            'termcond' => $message,
                            'account' => $account));
        // exit(\Doctrine\Common\Util\Debug::dump($html));
        $number = $entity->getNumber();

        $filename = sprintf('print-bill-%s.pdf', transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $number));

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html,
            array(
                'orientation' => 'portrait',
                'margin-top'    => 20,
                'margin-right'  => 12,
                'margin-bottom' => 20,
                'margin-left'   => 12,
                'enable-javascript' => true,
                'javascript-delay' => 1000,
                'no-stop-slow-scripts' => true,
                'no-background' => false,
                'lowquality' => false,
                'encoding' => 'utf-8',
                'images' => true,
                'cookie' => array(),
                'page-size' => 'A4',
                'dpi' => 300,
                'image-dpi' => 300,
                'disable-external-links' => true,
                'disable-internal-links' => true,
            )
            ),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
            ]
        );

    }
   /**
     * Export to PDF
     *
     * @Route("/{id}/email", name="bill_email")
     * @Security("has_role('ROLE_ADMIN_BILL') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_BILL')")
     */
    public function emailAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany =  $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Bill')->findOneBy(array('maincompany' => $maincompany, 'id'=>$id, 'canceled' => false));

        if (!$entity) {
            throw $this->createNotFoundException('No existe la factura');
        }

        $number = $entity->getNumber();
        $file = sprintf('factura-%s.pdf', transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $number));
        //$file = 'factura.pdf';
        $dir = '/tmp';
        $filename = sprintf('%s/%s', $dir, $file);

        $filename = $filename . '';
        // exit(\Doctrine\Common\Util\Debug::dump($filename));

        $listguide = array();
        foreach ($entity->getGuides() as $guide) {
            $listguide[] = $guide->getId();
        }
        $services = $em->getRepository('NvCargaBundle:Servtoguide')->findBy(array('guide' => $listguide));
        $termcond = $em->getRepository('NvCargaBundle:Termcond')->findOneBy(array('maincompany' => $maincompany,'tableclass' => 'Bill', 'active' => true));

        if ($termcond) {
            $message = $termcond->getMessage();
        } else {
            $message = null;
        }
        $accounts = $em->getRepository('NvCargaBundle:Account')->findBy(array('maincompany' => $maincompany, 'isactive' => true));
        if ($accounts) {
            $account=$accounts[0];
        } else {
            $account= null;
        }

        $this->get('knp_snappy.pdf')->generateFromHtml($this->renderView('NvCargaBundle:Bill:print.html.twig',
					array('entity' => $entity,
						'services' => $services,
						'termcond' => $message,
						'account' => $account)), $filename,  array(
                        'orientation' => 'portrait',
                        'margin-top'    => 20,
                        'margin-right'  => 12,
                        'margin-bottom' => 20,
                        'margin-left'   => 12,
                        'enable-javascript' => true,
                        'javascript-delay' => 1000,
                        'no-stop-slow-scripts' => true,
                        'no-background' => false,
                        'lowquality' => false,
                        'encoding' => 'utf-8',
                        'images' => true,
                        'cookie' => array(),
                        'page-size' => 'A4',
                        'dpi' => 300,
                        'image-dpi' => 300,
                        'enable-external-links' => true,
                        'enable-internal-links' => true
                    ), true);

        $body = "En archivo anexo econtrará la factura emitida por el envió de su Guía. Gracias por usar nuestros servicios.";
        if ($maincompany->getBillurl()) {
                $button = '<html>' .
                ' <head></head>' .
                ' <body> <br>' .
                ' <center>' .
                '<table> <tr> ' .
                ' <td style="background-color: #4ecdc4;border-color: #4c5764;border: 2px solid #45b7af;padding: 10px;text-align: center;"> '.
                ' <a style="display: block;color: #ffffff;font-size: 12px;text-decoration: none;text-transform: uppercase;" href="' . $maincompany->getBillurl().  '">' .
                ' PAGAR </a> </td> </tr> </table> . ' .
                ' </center>' .
                ' </body>' .
                '</html>';
                $body = $body . $button;
            }

        // $maincompany = $this->getUser()->getMaincompany();
        $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
        $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');
        $message = \Swift_Message::newInstance()
            ->setSubject('Factura número '. $entity->getNumber())
            ->setContentType("text/html")
            //->setTo('fhidrobo@hotmail.com')
            ->attach(\Swift_Attachment::fromPath($filename)->setFilename($file))
            ->setBody($body);
        $send =0;
        try {
            $message->setTo($entity->getCustomer()->getEmail());
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

        $send = $this->get('mailer')->send($message); // QUITAR COMENTARIO PARA ENVIAR EL EMAIL

        out:
        if ($send < 0) {
            $em = $this->getDoctrine()->getManager();
            $head = "<b>No se pudo enviar el EMAIl <br> ";
            if ($send == -1) {
                $head = $head . "La dirección DESTINO: " . $entity->getSender()->getEmail() . ' no es correcta (RFC 2822)</b><br>';
            } else {
                $head = $head . "La dirección REMITENTE: " . $setfrom . ' no es correcta (RFC 2822)</b><br>';
            }
            $msg = new Message();
            $msg->setSender($this->getUser());
            $msg->setReceiver($this->getUser());
            $msg->setSubject('Error enviando email (Envío de Factura)');
            $msg->setBody($head);
            $msg->setCreationdate(new \DateTime());
            $msg->setIsread(FALSE);
            $em->persist($msg);

            $em->flush();
        }
        return $this->render('NvCargaBundle:Bill:show.html.twig' , array('entity' => $entity, 'enviado'=>1));
        //return $this->redirect($this->generateUrl('bill_show', array('id' => $id)));
    }
    /**
     * Lists all Bill for a pobox.
     *
     * @Route("/pobox", name="bill_pobox")
     * @Template()
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function poboxAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $pobox = $user->getPobox();
        $customer = null;

        if ($pobox) {
            $customer = $pobox->getCustomer();
        }
        if (!$customer) {
            throw $this->createNotFoundException('No está registrado como cliente');
        }
        // $entities = $em->getRepository('NvCargaBundle:Bill')->findByCustomer($customer);
        $entities = $em->getRepository('NvCargaBundle:Bill')->findBy(array('customer'=>$customer, 'status'=>['PENDIENTE','COBRADA']));
        return array(
            'customer' => $customer,
            'entities' => $entities,
        );
    }
    /**
     * @Route("/tobill", name="guide_tobill")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function tobillAction(Request $request)
    {
        $list = $request->query->get('guidelist');
        $paidtype = $request->query->get('paidtype');
        $expdate = $request->query->get('expdate');
        $billto = $request->query->get('billto');
        $status = $request->query->get('status');
        $clock = $request->query->get('clock');
        $abono = $request->query->get('abono');
        $currency = $request->query->get('currency');
        $conversion = $request->query->get('conversion');
        $account = $request->query->get('account');
        $note = htmlspecialchars($request->query->get('note'));

        $user = $this->getUser();
        $maincompany = $user->getMainCompany();

        if ((!$list) || (!$paidtype) || (!$billto)) {
            throw $this->createNotFoundException('Error en los parámetros del método');
        }
        $guidelist=json_decode($list);
        $user = $this->getUser();
        $agency = $user->getAgency();
        $total=count($guidelist);
        $em = $this->getDoctrine()->getManager();
        $customer = $em->getRepository('NvCargaBundle:Customer')->findOneBy(['id'=>$billto,'maincompany'=>$maincompany]);
        if (!$customer) {
            throw $this->createNotFoundException('El cliente a facturar no existe..');
        }
        $typeofpaid = $em->getRepository('NvCargaBundle:Paidtype')->find($paidtype);
        if (!$typeofpaid) {
            throw $this->createNotFoundException('No existe el tipo de pago seleccionado');
        }
        $guides = $em->getRepository('NvCargaBundle:Guide')->findBy(['id'=>$guidelist,'maincompany'=>$maincompany]);
        $translator = $this->get('translator');
        if (count($guides) == 0 )  {
            throw $this->createNotFoundException('No existen los ' . $translator->trans('Guías'). ' que se quieren facturar');
        }
        $cod = $em->getRepository('NvCargaBundle:COD')->findOneByName('C.O.D');
        $countbill =  $maincompany->getCountbills();

        $plan = $maincompany->getPlan();
        if (($plan->getBills()) && ($countbill >= $plan->getMaxbills())) {
            $message = 'Ha llegado al número máximo de ' . $translator->trans('Recibos')  .' permitidos. Para crear más debe actualizar su plan a uno superior.';
            if ($this->isGranted('ROLE_ADMIN')) {
                $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
            }
            throw $this->createNotFoundException($message);
        }
        $bill = new Bill();
        $hid = $countbill;
        $hid++;
        $maincompany->setCountbills($hid);
        $number = 'BILL-' . $maincompany->getAcronym() . '-'. $hid;
        $bill->setNumber($number);
        $bill->setCustomer($customer);
        $movdate = new \DateTime();
        $rdate = substr($movdate->format('Y-m-d'),0,10) . 'T' . $clock;
        $bill->setCreationdate(new \DateTime($rdate));
        $bill->setExpdate(new \DateTime($expdate));
        $bill->setCod($cod);
        $bill->setMaincompany($maincompany);
        $bill->setPaidtype($typeofpaid);
        $bill->setStatus($status);

        $total = 0;
        foreach ($guides as $guide) {
            $guide->setBill($bill);
            $guide->setPaidtype($typeofpaid);
            $bill->addGuide($guide);
            $total+= $guide->getTotalpaid();
        }
        $bill->setTotal($total);

        // exit(\Doctrine\Common\Util\Debug::dump($note));

        if ($abono > 0) {
            $payment = new Billpay();
            $bill->addPayment($payment);
            $payment->setBill($bill);
            $payment->setLastupdate($bill->getCreationdate());
            $payment->setPaydate($bill->getCreationdate());
            $payment->setPaidtype($typeofpaid);
            $payment->setAmount($abono);
            $payment->setVerified(true);
            $payment->setMaincompany($maincompany);
            $payment->setConversion($conversion);
            $thecurrency = $em->getRepository('NvCargaBundle:Currency')->find($currency);
            $payment->setCurrency($thecurrency);
            $payment->setNote($note);
            $bill->setBalance($abono);
            $theaccount = $em->getRepository('NvCargaBundle:Account')->find($account);
            if ($theaccount) {
                $payment->setAccount($theaccount);
            }
            if ($typeofpaid->getName() == 'Saldo del cliente') {
                $customer->setRefunded($customer->getRefunded() - $abono);
            }
            if ($bill->getBalance() == $bill->getTotal()) {
                $bill->setStatus('COBRADA');
            } else {
                $bill->setStatus('PENDIENTE');
            }
            $highest_id = $em->getRepository('NvCargaBundle:Billpay')->createQueryBuilder('r')
                            ->select('MAX(r.id)')
                            ->where('r.maincompany =:thecompany')
                            ->setParameters(['thecompany'=>$maincompany])
                            ->getQuery()
                            ->getSingleScalarResult();
            if ($highest_id) {
                $billpay =  $em->getRepository('NvCargaBundle:Billpay')->find($highest_id);
                $numberhigh = $billpay->getNumber();
                $maxvalue = filter_var($numberhigh, FILTER_SANITIZE_NUMBER_INT);
                $maxvalue = str_replace('-', '', $maxvalue);
                $maxnumber = abs(intval($maxvalue));
            } else {
                $maxnumber = 0;
            }
            $maxnumber++;
            $number = 'PAYMENT-' . $maincompany->getAcronym() . $maxnumber;
            $payment->setNumber($number);
            $em->persist($payment);
        } else {
            $bill->setStatus('PENDIENTE');
        }
        $em->persist($bill);
        $em->flush();
        //$nb = str_pad($bill->getId(), 10, '0', STR_PAD_LEFT);
        //$bill->setNumber($nb);
        //$em->flush();
        return $this->redirect($this->generateUrl('bill_show', array('id' => $bill->getId())));

    }
    public function getView($table, $type) {
        $option = null;
        $maincompany = $this->getUser()->getMaincompany();
        //exit(\Doctrine\Common\Util\Debug::dump($maincompany->getFormat()));
        if ($maincompany->getFormat()) {
            $option = $maincompany->getFormat()->getBprint();
        }
        $viewoption = 'NvCargaBundle:' . $table . ':' . $type . $option . '.html.twig';
        if (!$this->get('templating')->exists($viewoption) ) {
            $viewoption = 'NvCargaBundle:' . $table . ':' . $type . '.html.twig';
        }
        return $viewoption;
    }
}
