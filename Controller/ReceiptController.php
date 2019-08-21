<?php

namespace NvCarga\Bundle\Controller;

use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use NvCarga\Bundle\Entity\Customer;
use NvCarga\Bundle\Entity\Receipt;
use NvCarga\Bundle\Entity\Package;
use NvCarga\Bundle\Entity\Baddress;
use NvCarga\Bundle\Entity\City;
use NvCarga\Bundle\Form\ReceiptType;
use NvCarga\Bundle\Form\PackageType;
use NvCarga\Bundle\Entity\Statusreceipt;


/**
 * Receipt controller.
 *
 * @Route("/receipt")
 */
class ReceiptController extends Controller
{

    /**
     * Lists all Receipt without guides.
     *
     * @Route("/index_wg", name="receipt_wg")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    public function index_wgAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();
        $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Por procesar');
        
        $all = $em->getRepository('NvCargaBundle:Receipt')->findBy(['status'=>$status,'maincompany'=>$maincompany]);
        
        if ($user->getAgency()->getType() != 'MASTER') {
            $entities = array();
            foreach ($all as $receipt) {
                $countryrec = $receipt->getReceiver()->getCity()->getState()->getCountry();
                if (($countryrec == $countryto) || ($receipt->getAgency() == $agency))  {
                    $entities[] = $receipt;
                }
            }
        } else {
            $entities = $all;
        }
        return array(
            'entities' => $entities,
        );
    }

    /**
     * Lists all Receipt entities.
     *
     * @Route("/index", name="receipt")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();
        $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Anulado');
        $agencies = $em->getRepository('NvCargaBundle:Agency')->findByMaincompany($maincompany);
        
        $today = new \DateTime();
        $today->modify('+59 minutes');
        $today->modify('+23 hours');
        
        $before = new \DateTime();
        $before->modify('-6 months');
        $all = $em->getRepository('NvCargaBundle:Receipt')->createQueryBuilder('r')
                ->where('r.creationdate BETWEEN :dateini AND :dateend')
                ->andWhere('r.maincompany =:maincompany')
                ->andwhere('r.status != :status')
                ->setParameters(['dateini'=>$before, 'dateend'=>$today, 'maincompany'=>$maincompany, 'status'=>$status])
                ->getQuery()
                ->getResult();
        /*
        $all= $em->getRepository('NvCargaBundle:Receipt')->createQueryBuilder('r')
                //->where('r.agency = :ag')
                ->where('r.status != :status')
                ->andWhere('r.maincompany = :thecompany')
                // ->setParameters(array('ag'=> $agency, 
                //		      'status' => $status))
                ->setParameters(array('status' => $status,'thecompany'=>$maincompany))
                ->orderBy('r.number', 'ASC')
                ->getQuery()
                ->getResult();
        */
                
        if ($user->getAgency()->getType() != 'MASTER') {
        	$entities = array();
            foreach ($all as $receipt) {
                $countryrec = $receipt->getReceiver()->getCity()->getState()->getCountry();
                if (($countryrec == $countryto) || ($receipt->getAgency() == $agency))  {
                        $entities[] = $receipt;
                }
            }
        } else {
            $entities = $all;
        }
        $status = array();
        $estados =  $em->getRepository('NvCargaBundle:Stepstatus')->findByMaincompany($maincompany);
        foreach ($estados as $estado) {
            $status[] = $estado->getName();
        }
        return array(
            'entities' => $entities,
            'agencies'=>$agencies,
            'status' => $status,
        );
    }
    /**
     * Lists all Receipt entities in warehouse that aren't in transit
     *
     * @Route("/{id}/pbindex", name="receipt_pbindex")
     * @Template()
     */
    public function pbindexAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $pobox = $em->getRepository('NvCargaBundle:Pobox')->find($id);
        if (!$pobox) {
            throw $this->createNotFoundException('No existe el casillero');
        }
        $userpobox = $pobox->getUser();
        if ($userpobox != $user) {
            throw $this->createNotFoundException('No tiene permiso para ver este casillero');
        }
        //$status1 = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Por procesar');
        //$status2 = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Procesado');
        $cancelstatus = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Anulado');
        
        $entities= $em->getRepository('NvCargaBundle:Receipt')->createQueryBuilder('r')
                    ->where('r.receiver IN (:receiver)')
                    ->andwhere('r.status != :status')
                    ->setParameters(array('receiver'=> $pobox->getCustomer()->getBaddress(), 
                            'status' => $cancelstatus))
                    ->orderBy('r.number', 'ASC')
                    ->getQuery()
                    ->getResult();
        /*            
        foreach ($entities as $key => $entity) {
            $guide = $entity->getGuide();
            if ($guide) {
                $lastpos = $guide->getMoves()->last()->getStatus()->getPosition();
                if ($lastpos > 3) {
                    unset($entities[$key]);
                }
            }
        }
        */
        return array(
            'entity' => $pobox,
            'entities' => $entities,
        );
    }
    /**
     * Lists all Receipt entities in warehouse that aren't in transit
     *
     * @Route("/{id}/sendpbindex", name="receipt_sendpbindex")
     * @Template()
     */
    public function sendpbindexAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $pobox = $em->getRepository('NvCargaBundle:Pobox')->find($id);
        if (!$pobox) {
            throw $this->createNotFoundException('No existe el casillero');
        }
        $userpobox = $pobox->getUser();
        if ($userpobox != $user ) {
            throw $this->createNotFoundException('No tiene permiso para ver este casillero');
        }
        $cancelstatus = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Anulado');
        
        $entities= $em->getRepository('NvCargaBundle:Receipt')->createQueryBuilder('r')
                    ->where('r.shipper = :shipper')
                    ->andwhere('r.status != :status')
                    ->setParameters(array('shipper'=> $pobox->getCustomer(), 'status' => $cancelstatus))
                    ->orderBy('r.number', 'ASC')
                    ->getQuery()
                    ->getResult();

        return array(
            'entity' => $pobox,
            'entities' => $entities
        );
    }
    /**
     * Creates a new Receipt entity.
     *
     * @Route("/create", name="receipt_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Receipt:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Receipt();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        
        // $entity->addPackage(new Package());
        
        $form = $this->createCreateForm($entity);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $translator = $this->get('translator');
        $countreceipt =  $maincompany->getCountreceipts(); 
        $plan = $maincompany->getPlan();
        if (($plan->getReceipts()) && ($countreceipt >= $plan->getMaxreceipts())) {
            $message = 'Ha llegado al número máximo de ' . $translator->trans('Recibos')  .' permitidos. Para crear más debe actualizar su plan a uno superior.';
            if ($this->isGranted('ROLE_ADMIN')) {
                $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
            }
            $flashBag->add('notice',$message);
            goto next;
        }
        $form->handleRequest($request);
        
        // exit(\Doctrine\Common\Util\Debug::dump($entity->getPackages()));	

        if ($form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $packages = $entity->getPackages();
            // exit(\Doctrine\Common\Util\Debug::dump($packages)); 
            $entity->setReceiptdBy($user);
            $entity->setAgency($user->getAgency());
            $noti_sender = $form['noti_sender']->getData();
            $noti_addr = $form['noti_addr']->getData();
            
            $addcus = $this->forward('NvCargaBundle:Customer:addCustomer', array('form'=>$form, 'entity'=> $entity));
            // $error = $addcus['error'];
            $data = explode(':',$addcus->getContent());
            $error = intval($data[0]);
            $idsender = intval($data[1]);
            $idaddr = intval($data[2]);
            // exit(\Doctrine\Common\Util\Debug::dump(count($packages)));
            if ($error) {
                goto next;
            }
            $sender = $em->getRepository('NvCargaBundle:Customer')->find($idsender);
            $rebaddr = $em->getRepository('NvCargaBundle:Baddress')->find($idaddr);
            $entity->setShipper($sender);
            $sender->addShipped($entity);
            $entity->setReceiver($rebaddr);
            
            // $addr->addReceived($entity);  
            $addr = $rebaddr->getCustomer();
            
            if ($addr->getPobox()) { // el destinatario tiene casillero.... ALERTAS DE PAQUETES DE RECIBIDOS
                $rtipo= "Casillero"; // se puede crear una alerta, si no exite para el paquete, y/o enviar email
            } else {
                $rtipo= "Pickup";
            }
            $highest_id = $em->getRepository('NvCargaBundle:Receipt')->createQueryBuilder('r')
                            ->select('MAX(r.id)')
                            ->where('r.maincompany =:thecompany')
                            ->setParameters(['thecompany'=>$maincompany])
                            ->getQuery()
                            ->getSingleScalarResult();
            $highest_id++;
            $typerec = $em->getRepository('NvCargaBundle:Receipttype')->findOneBy(array('name'=>$rtipo));
            $statusrec = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneBy(array('name'=>'Por procesar'));
            
            $comcod = str_pad($maincompany->getId(), 2, '0', STR_PAD_LEFT);
            $number = "PKG" . $comcod . $highest_id . 'P1';
            
            $track = $entity->getTracking();
            $carrier =  $entity->getCarrier();
            if ($track) {
                $alert = $em->getRepository('NvCargaBundle:Alert')->findOneBy(array('tracking'=>$track, 'carrier' => $carrier));
            } else {
                $alert = null;
            }
            if ($alert) {
                $alert->setReceipt($entity); // se podría validar que el destinatario sea el cliente del casillero
            }
            $entity->setType($typerec); 
            $entity->setStatus($statusrec); 
            $entity->setNumber($number); 
            $movdate = new \DateTime();
            $clock = $form['clock']->getData(); 
            $rdate = substr($movdate->format('m/d/Y'),0,10) . 'T' . $clock;
            $entity->setCreationdate(new \DateTime($rdate));
            $arrivedate = $entity->getArrivedate();
            $rdate = substr($arrivedate,0,10) . 'T' . $clock;
            $entity->setArrivedate(new \DateTime($rdate));
            $entity->setMaincompany($maincompany);
            
            if ($maincompany->getFirststatus()) {
                $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                $newstatus = new Statusreceipt();
                $newstatus->setReceipt($entity);
                $newstatus->setDate($entity->getCreationdate());
                $newstatus->setPlace($entity->getAgency()->getCity());
                $newstatus->setStep($step);
                $newstatus->setComment($translator->trans('Recibo') . ' creado');
                $entity->addListstatus($newstatus);
                $em->persist($newstatus);
            }
            $em->persist($entity);
            $mespack = '';
            $mespack = $mespack . '<br><b>Paquete 1 </b><br>';
            if ($entity->getTracking() ) {
                $mespack = $mespack . 'Tracking: ' .   $entity->getTracking() . '<br>';
            }
            $mespack = $mespack . 'Courrier: ' .   $entity->getCarrier() . '<br>';
            $mespack = $mespack . 'Descripcion: ' .   $entity->getDescription() . '<br>';
            $mespack = $mespack . 'Bultos: ' .   $entity->getQuantity() . '<br>';
            $mespack = $mespack . 'Dimensiones: ' .   $entity->getLength() . 'X'. $entity->getWidth() .  'X'. $entity->getHeight() .'<br>';
            $mespack = $mespack . 'Peso: ' .   $entity->getWeight() .'<br>';

            $count = 2;
            $countreceipt++;
            //$countreceipt = $maincompany->getCountreceipts();
            $plan = $maincompany->getPlan();
            if (($plan->getReceipts()) && ($countreceipt + count($packages) > $plan->getMaxreceipts())) {
                $message = 'Ha llegado al número máximo de ' . $translator->trans('Recibos')  .' permitidos. Para crear más debe actualizar su plan a uno superior.';
                if ($this->isGranted('ROLE_ADMIN')) {
                    $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                }
                $flashBag->add('notice',$message);
                goto next;
            }
            foreach ($packages as $package) {
                $thispack = $count;
                
                $nrec = new Receipt();
                // $highest_id ++;
                $number = "PKG" . $comcod . $highest_id . 'P' . $count++;
                $nrec->setNumber($number);
                $nrec->setAgency($entity->getAgency());
                $nrec->setReceiptdBy($entity->getReceiptdBy());
                $nrec->setShipper($entity->getShipper());
                $nrec->setReceiver($entity->getReceiver());
                $nrec->setNote($entity->getNote());
                $nrec->setType($entity->getType());
                $nrec->setStatus($entity->getStatus());
                // $addr->addReceived($nrec);
                $sender->addShipped($nrec);
                $nrec->setCarrier($package->getCarrier());
                $arrivedate = $package->getArrivedate();
                $rdate = substr($arrivedate,0,10) . 'T' . $clock;
                $nrec->setArrivedate(new \DateTime($rdate));
                $nrec->setCreationdate($entity->getCreationdate());
                $nrec->setTracking($package->getTracking());
                $nrec->setReference($package->getReference());
                $nrec->setDescription($package->getDescription());
                $nrec->setQuantity($package->getQuantity());
                $nrec->setWeight($package->getWeight());
                $nrec->setLength($package->getLength());
                $nrec->setWidth($package->getWidth());
                $nrec->setHeight($package->getHeight());
                $nrec->setValue($package->getValue());
                if (!$package->getNpack()) {
                    $nrec->setNpack(1);
                } else {
                    $nrec->setNpack($package->getNpack());
                }
                $nrec->setPacktype($package->getPacktype());
                $track = $nrec->getTracking();
                $carrier = $nrec->getCarrier();
                if ($track) {
                    $alert = $em->getRepository('NvCargaBundle:Alert')->findOneBy(array('maincompany'=>$maincompany, 'tracking'=>$track, 'carrier' => $carrier));
                } else {
                    $alert = null;
                }
                if ($alert) {
                    $alert->setReceipt($nrec); // se podría validar que el destinatario sea el cliente del casillero
                }
                $nrec->setMaincompany($maincompany);
                if ($maincompany->getFirststatus()) {
                    $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                    $newstatus = new Statusreceipt();
                    $newstatus->setReceipt($nrec);
                    $newstatus->setDate($nrec->getCreationdate());
                    $newstatus->setPlace($nrec->getAgency()->getCity());
                    $newstatus->setStep($step);
                    $newstatus->setComment($translator->trans('Recibo') . ' creado');
                    $nrec->addListstatus($newstatus);
                    $em->persist($newstatus);
                }
                $em->persist($nrec);
                $countreceipt++;
                $mespack = $mespack . '<br><b>Paquete '. $thispack . '</b><br>';
                if ($nrec->getTracking() ) {
                    $mespack = $mespack . 'Tracking: ' .   $nrec->getTracking() . '<br>';
                }
                $mespack = $mespack . 'Courrier: ' .   $nrec->getCarrier() . '<br>';
                $mespack = $mespack . 'Descripcion: ' .   $nrec->getDescription() . '<br>';
                $mespack = $mespack . 'Bultos: ' .   $nrec->getQuantity() . '<br>';
                $mespack = $mespack . 'Dimensiones: ' .   $nrec->getLength() . 'X'. $nrec->getWidth() .  'X'. $nrec->getHeight() .'<br>';
                $mespack = $mespack . 'Peso: ' .   $nrec->getWeight() .'<br>';
            }
            $maincompany->setCountreceipts($countreceipt);
            $em->flush();
            $email_sender = $form['email_sender']->getData();
            $email_addr = $form['email_addr']->getData();
            $sent_sender = false;
            if (($noti_sender) && ($email_sender)) {
                $this->sendemail($entity, $mespack, 1);
                $sent_sender = true;
            }
            $sent_addr = ((!$sent_sender) || ($email_sender != $email_addr));
            if (($noti_addr) && ($email_addr) && ($sent_addr)) {
                $this->sendemail($entity, $mespack, 2);
            }	
            return $this->redirect($this->generateUrl('receipt_search'));
        } else {
            // exit(\Doctrine\Common\Util\Debug::dump($form['address_sender']->getData())); 
            $flashBag = $this->get('session')->getFlashBag();
            foreach ($flashBag->keys() as $type) {
                        $flashBag->set($type, array());
            }
            
            $translator = $this->get('translator');
            $message = 'Hay errores en la creación del ' . $translator->trans('Recibo') . ', por favor, revisar los mensajes de los campos. '; 
            
            if (!($form['id_sender']->getData())) {
                $message = $message . 'Debe seleccionar el REMITENTE. '; 
            }
            if (!($form['id_addr']->getData())) {
                $message = $message . 'Debe seleccionar el DESTINATARIO.'; 
            }
            $this->get('session')->getFlashBag()->add(
                            'notice',
                            $message);
        }
        next:
            return array(
                'entity' => $entity,
                'form'   => $form->createView(),
                'edition' => false,
                'nameform' => 'Crear ' . $translator->trans('Recibo'), 
            );
    }

    /**
     * Creates a form to create a Receipt entity.
     *
     * @param Receipt $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Receipt $entity)
    {
        $form = $this->createForm(new ReceiptType($this->getDoctrine()->getManager(), $this->getUser()), $entity, array(
            'action' => $this->generateUrl('receipt_create'),
            'method' => 'POST',
        ));
        
        $form->add('clock', 'hidden', array('mapped' => false));
        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Receipt entity.
     *
     * @Route("/new", name="receipt_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Receipt();
        
        $form   = $this->createCreateForm($entity);
        $translator = $this->get('translator');
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $translator = $this->get('translator');
        $maincompany = $this->getUser()->getMainCompany();
        $countreceipt =  $maincompany->getCountreceipts(); 
        $plan = $maincompany->getPlan();
        if (($plan->getReceipts()) && ($countreceipt >= $plan->getMaxreceipts())) {
            $message = 'Ha llegado al número máximo de ' . $translator->trans('Recibos')  .' permitidos. Para crear más debe actualizar su plan a uno superior.';
            if ($this->isGranted('ROLE_ADMIN')) {
                $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
            }
            $flashBag->add('notice',$message);
        }
        
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'edition' => false,
            'nameform' => 'Crear ' . $translator->trans('Recibo') ,
        );
    }


    /**
     * Finds and displays a Receipt entity.
     *
     * @Route("/{id}/show", name="receipt_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();

        $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        
        if (!$receipt) {
                throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }
        if ($user->getAgency()->getType() != 'MASTER') {
            $countryrec = $receipt->getReceiver()->getCity()->getState()->getCountry();
            if (($countryrec == $countryto) || ($receipt->getAgency() == $agency))  {
                $entity = $receipt;
            } else {
                throw $this->createNotFoundException('No tiene permiso para ver este ' . $translator->trans('Recibo'));
            }
        } else {
            $entity = $receipt;
        }
        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            // 'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Receipt entity.
     *
     * @Route("/{id}/edit", name="receipt_edit")
     * @Method("GET")
     * @Template("NvCargaBundle:Receipt:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$entity) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }
        if ($entity->getGuide()) {
            throw $this->createNotFoundException('No se puede editar un ' .  $translator->trans('Recibo') . 'que ya tiene ' . $translator->trans('Guía'));
        }
        $entity->setArrivedate($entity->getArrivedate()->format('m/d/Y'));
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'form'  => $editForm->createView(),
            'edition' => true,
            'nameform' => 'Editar ' . $translator->trans('Recibo'), 
            // 'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Displays a form to add pack for an existing Receipt entity.
     *
     * @Route("/{id}/addpacks", name="receipt_addpacks")
     * @Method("GET")
     * @Template("NvCargaBundle:Receipt:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function addpacksAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$entity) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }
        if ($entity->getGuide()) {
            throw $this->createNotFoundException('No se puede agregar piezas a un ' .  $translator->trans('Recibo') . 'que ya tiene ' . $translator->trans('Guía'));
        }
        $entity->setPackages(new ArrayCollection());
        $entity->setArrivedate($entity->getArrivedate()->format('m/d/Y'));
	
        $editForm = $this->createAddpackForm($entity);
        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'edition' =>true,
            'nameform' => 'Agregar paquetes',
            'readonly' => true,
        );
    }

    /**
    * Creates a form to edit a Receipt entity.
    *
    * @param Receipt $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Receipt $entity)
    {
        $form = $this->createForm(new ReceiptType($this->getDoctrine()->getManager(), $this->getUser()), $entity, array(
            'action' => $this->generateUrl('receipt_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        
        $form->add('submit', 'submit', array('label' => 'Actualizar'));
        $form->remove('packages');

        return $form;
    }
    /**
    * Creates a form to edit a Receipt entity.
    *
    * @param Receipt $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createReempackForm(Receipt $entity)
    {
        $form = $this->createForm(new ReceiptType($this->getDoctrine()->getManager(), $this->getUser()), $entity, array());
        //    'action' => $this->generateUrl('receipt_doreempack', array('id' => $entity->getId())),
        //    'method' => 'PUT',
        //));
        $form->add('submit', 'submit', array('label' => 'Reempacar'));
        $form->remove('packages');

        return $form;
    }
   /**
    * Creates a form to add packs a Receipt entity.
    *
    * @param Receipt $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createAddpackForm(Receipt $entity)
    {
        $form = $this->createForm(new ReceiptType($this->getDoctrine()->getManager(), $this->getUser()), $entity, array(
	    'action' => $this->generateUrl('receipt_updatepacks', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Confirmar'));

        return $form;
    }
    /**
     * Edits an existing Receipt entity.
     *
     * @Route("/{id}/update", name="receipt_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Receipt:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        
        $translator = $this->get('translator');
        if (!$entity) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }
        if ($entity->getGuide()) {
            throw $this->createNotFoundException('No se puede editar un ' .  $translator->trans('Recibo') . 'que ya tiene ' . $translator->trans('Guía'));
        }
       
        $entity->setArrivedate($entity->getArrivedate()->format('Y-m-d'));
        // $deleteForm = $this->createDeleteForm($id);

        $form = $this->createEditForm($entity);
        $form->handleRequest($request);
	

        if ($form->isValid()) {
            // exit(\Doctrine\Common\Util\Debug::dump($packages)); 
            $addcus = $this->forward('NvCargaBundle:Customer:addCustomer', array('form'=>$form, 'entity'=> $entity));
            // $error = $addcus['error'];
            $data = explode(':',$addcus->getContent());
            $error = intval($data[0]);
            $idsender = intval($data[1]);
            $idaddr = intval($data[2]);
            // exit(\Doctrine\Common\Util\Debug::dump('NO EXISTE EL REMITENTE: ' . $idsender));
            if ($error) {
                goto next;
            }
            $sender = $em->getRepository('NvCargaBundle:Customer')->find($idsender);
            $rebaddr = $em->getRepository('NvCargaBundle:Baddress')->find($idaddr);
            $entity->getShipper()->removeShipped($entity);
            $entity->setShipper($sender);
            $sender->addShipped($entity);
            $entity->setReceiver($rebaddr);
           
            $track = $entity->getTracking();
            $carrier =  $entity->getCarrier();
            if ($track) {
                $alert = $em->getRepository('NvCargaBundle:Alert')->findOneByReceipt($entity);
            } else {
                $alert = null;
            }
            if ($alert) {
                $alert->setReceipt(null);
            }
            $alert = $em->getRepository('NvCargaBundle:Alert')->findOneBy(array('maincompany'=>$maincompany, 'tracking'=>$track, 'carrier' => $carrier));
            if ($alert) {
                $alert->setReceipt($entity); // se podría validar que el destinatario sea el cliente del casillero
            }  
            $entity->setArrivedate(new \DateTime($entity->getArrivedate()));
            $em->flush();

            return $this->redirect($this->generateUrl('receipt_show', array('id' => $id)));
        } else {
            $flashBag = $this->get('session')->getFlashBag();
            foreach ($flashBag->keys() as $type) {
                    $flashBag->set($type, array());
            }
            $translator = $this->get('translator');
            $message = 'Hay errores en la creación del ' . $translator->trans('Recibo') . ', por favor, revisar los mensajes de los campos. '; 
            
            if (!($form['id_sender']->getData())) {
                $message = $message . 'Debe seleccionar el REMITENTE. '; 
            }
            if (!($form['id_addr']->getData())) {
                $message = $message . 'Debe seleccionar el DESTINATARIO.'; 
            }
            $this->get('session')->getFlashBag()->add(
                            'notice',
                            $message);
        }
        next:
            return array(
                'entity'      => $entity,
                'form'   => $form->createView(),
                'edition' => false, 
                'nameform' => 'Editar ' . $translator->trans('Recibo'),
            );
    }
    
    /**
     * Edits an existing Receipt entity.
     * @Method("PUT")
     * @Route("/{id}/updatepacks", name="receipt_updatepacks")
     * @Template("NvCargaBundle:Receipt:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function updatepacksAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
       

        $translator = $this->get('translator');
        if (!$entity) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }

        // $deleteForm = $this->createDeleteForm($id);

        $entity->setPackages(new ArrayCollection());
        $thiscarrier = $entity->getCarrier();
        $entity->setArrivedate($entity->getArrivedate()->format('m/d/Y'));
        $editForm = $this->createAddpackForm($entity);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $editForm->handleRequest($request);
        
        // exit(\Doctrine\Common\Util\Debug::dump($entity->getPackages()));
        if ($editForm->isValid()) {
            // $entity->setCarrier($thiscarrier);
            $packages = $entity->getPackages();
            $entity->setArrivedate(new \DateTime($entity->getArrivedate()));
            $number = $entity->getNumber();
            $pos = strrpos($number, 'P');
            if ($pos !== false) {
                $number = substr($number, 0, $pos);
            }
            $pack = $em->createQueryBuilder()
                        ->select('Count(e.id)')
                        ->from('NvCargaBundle:Receipt', 'e')
                        ->where('e.number LIKE :number')
                        ->andWhere('e.maincompany = :thecompany')
                        ->setParameters(['number'=>'%'.$number.'%','thecompany'=>$maincompany])
                        ->getQuery()
                        ->getSingleScalarResult();
	    
            $pack++;
            // exit(\Doctrine\Common\Util\Debug::dump(' ' . $pack . ' '. $number ));
            $countreceipt = $maincompany->getCountreceipts();
            $plan = $maincompany->getPlan();
            if (($plan->getReceipts()) && ($countreceipt + count($packages) > $plan->getMaxreceipts())) {
                $message = 'Ha llegado al número máximo de ' . $translator->trans('Recibos')  .' permitidos. Para crear más debe actualizar su plan a uno superior.';
                if ($this->isGranted('ROLE_ADMIN')) {
                    $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                }
                $flashBag->add('notice',$message);
                goto next;
            }
            foreach ($packages as $package) {
                $countreceipt++;
                $nrec = new Receipt();
                $recnumber = $number . 'P' . $pack++;
                $nrec->setNumber($recnumber);
                $nrec->setAgency($entity->getAgency());
                $nrec->setReceiptdBy($this->getUser());
                $nrec->setShipper($entity->getShipper());
                $nrec->setReceiver($entity->getReceiver());
                $nrec->setNote($entity->getNote());
                $nrec->setType($entity->getType());
                $nrec->setStatus($entity->getStatus());
                //$entity->getReceiver()->addReceived($nrec);
                $entity->getShipper()->addShipped($nrec);
                $nrec->setCarrier($package->getCarrier());
                $nrec->setArrivedate(new \DateTime($package->getArrivedate()));
                $nrec->setCreationdate(new \DateTime());
                $nrec->setTracking($package->getTracking());
                $nrec->setReference($package->getReference());
                $nrec->setDescription($package->getDescription());
                $nrec->setQuantity($package->getQuantity());
                $nrec->setWeight($package->getWeight());
                $nrec->setLength($package->getLength());
                $nrec->setWidth($package->getWidth());
                $nrec->setHeight($package->getHeight());
                $nrec->setValue($package->getValue());
                $nrec->setMaincompany($maincompany);
                if ($package->getNpack())  {
                    $nrec->setNpack($package->getNpack());
                } else {
                    $nrec->setNpack(1);
                }
                $track = $nrec->getTracking();
                $carrier = $nrec->getCarrier();
                if ($track) {
                    $alert = $em->getRepository('NvCargaBundle:Alert')->findOneBy(array('maincompany'=>$maincompany,'tracking'=>$track, 'carrier' => $carrier));
                } else {
                    $alert = null; 
                }
                if ($alert) {
                    $alert->setReceipt($nrec); // se podría validar que el destinatario sea el cliente del casillero
                }
                if ($maincompany->getFirststatus()) {
                    $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                    $newstatus = new Statusreceipt();
                    $newstatus->setReceipt($nrec);
                    $newstatus->setDate($nrec->getCreationdate());
                    $newstatus->setPlace($nrec->getAgency()->getCity());
                    $newstatus->setStep($step);
                    $newstatus->setComment($translator->trans('Recibo') . ' creado');
                    $nrec->addListstatus($newstatus);
                    $em->persist($newstatus);
                }
                $em->persist($nrec);
            }
            $maincompany->setCountreceipts($countreceipt);
            // exit(\Doctrine\Common\Util\Debug::dump($nrec));
            
            $em->flush();
            return $this->redirect($this->generateUrl('receipt_show', array('id' => $id)));
        } else {
            // exit(\Doctrine\Common\Util\Debug::dump($entity->getPackages())); 
            $flashBag = $this->get('session')->getFlashBag();
            foreach ($flashBag->keys() as $type) {
                        $flashBag->set($type, array());
            }
            $translator = $this->get('translator');
            $this->get('session')->getFlashBag()->add(
                            'notice',
                            'Hay errores en los paquetes adicionales del  ' . $translator->trans('Recibo') . ', por favor, revisar los mensajes de los campos');
        }

        next:
        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            // 'delete_form' => $deleteForm->createView(),
            'nameform' => 'Agregar Paquetes',
            'edition' => false,
            'readonly' => true,
        );
    }
    /**
     * Deletes a Receipt entity.
     *
     * @Route("/{id}/delete", name="receipt_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
   /* public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Receipt')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Receipt entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('receipt'));
    } */

    /**
     * Creates a form to delete a Receipt entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    /* private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('receipt_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    } */
     /**
     * Finds a Receipt entity.
     *
     * @Route("/find", name="receipt_find")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    public function findAction(Request $request)
    {
        
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $user = $this->getUser();
        $agency = $user->getAgency();
        $maincompany = $user->getMainCompany();
        $countryto = $agency->getCity()->getstate()->getCountry();

        $id = $request->query->get('receipt_id');
        $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        $result = array();
        if (!$receipt) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }
        if ($user->getAgency()->getType() != 'MASTER') {
            $countryrec = $receipt->getReceiver()->getCity()->getState()->getCountry();
            if (($countryrec == $countryto) || ($receipt->getAgency() == $agency))  {
                $entity = $receipt;
            } else {
                throw $this->createNotFoundException('No tiene permiso para ver este ' . $translator->trans('Recibo'));
            }
        } else {
            $entity = $receipt;
        }

        // Construccion de la respuesta a enviar.
        // esto también se puede hacer con un componente especial de JSON para SYMFONY
        $result['receiver'] = $entity->getReceiver()->getId();
        $result['shipper'] = $entity->getShipper()->getId();
        return new JsonResponse($result);      
    }
    /**
     * Creates a form to select receipts.
     *
     * @param ObjectChoiceList $choiceList List of receipts
     *
     * @return \Symfony\Component\Form\Form The form
     */
    
    private function createChoiceForm(ObjectChoiceList $choiceList)
    {
        //$form = $this->createForm(new GuideType($this->getDoctrine()->getManager()), $entity, array(
        //    'action' => $this->generateUrl('guide_newbyrec'),
        //    'method' => 'POST',
        //));
        // $data = array();
        $label = 'Crear ' . $translator->trans('Guía');
        $formBuilder = $this->createFormBuilder($choiceList)	
            ->add('receipts', 'choice', array('label' => 'labels.receipts ',
                    'choice_list'   => $choiceList,
                    'multiple'  => true,
                    'mapped' => false, ))
        // ->setAction($this->generateUrl('guide_receipts'))
            ->add('submit', 'submit', array('label' => $label));
        $form = $formBuilder->getForm();
        return $form;
    }
    /**
     * Export to PDF
     * 
     * @Route("/{id}/labelpdf", name="receipt_labelpdf")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    public function labelpdfAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();
        $maincompany = $user->getMainCompany();
        $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$receipt) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }
       
        if ($user->getAgency()->getType() != 'MASTER') {
            $countryrec = $receipt->getReceiver()->getCity()->getState()->getCountry();
            if (($countryrec == $countryto) || ($receipt->getAgency() == $agency))  {
                $entity = $receipt;
            } else {
                throw $this->createNotFoundException('No tiene permiso para ver etiqueta de este ' . $translator->trans('Recibo'));
            }
        } else {
            $entity = $receipt;
        }
        $viewoption = $this->getView('Receipt', 'label');
        $html = $this->renderView($viewoption, array('entity' => $entity));
        // exit(\Doctrine\Common\Util\Debug::dump($html));
        $number = $entity->getNumber(); 
        $filename = sprintf('label-receipt-%s.pdf', transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $number));
        $labeldata = $em->getRepository('NvCargaBundle:Labelconf')->findOneBy(array('tableclass' => 'Receipt'));
	
        if ($labeldata) {
            $width = $labeldata->getWidth();
            $height = $labeldata->getHeight();
        } else {    
            $width = 105;
            $height = 62;
        }
        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
                'orientation' => 'portrait',
                'margin-top'    => 10,
                'margin-right'  => 7,
                'margin-bottom' => 10,
                'margin-left'   => 7, 
                'enable-javascript' => true, 
                'javascript-delay' => 1000, 
                'no-stop-slow-scripts' => true, 
                'no-background' => false, 
                'lowquality' => false,
                'encoding' => 'utf-8',
                'images' => true,
                'cookie' => array(),
                // 'page-size' => 'A7', // 'A8',
                'page-width' => $width,
                'page-height' => $height,
                        'dpi' => 300,
                'image-dpi' => 300,
                'enable-external-links' => true,
                'enable-internal-links' => true
            )),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
            ]
        );
    }
    /**
     * Generate  a receipt label
     * 
     * @Route("/{id}/label", name="receipt_label")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    public function labelAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();
        $maincompany = $user->getMainCompany();
        $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$receipt) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }
        if ($user->getAgency()->getType() != 'MASTER') {
            $countryrec = $receipt->getReceiver()->getCity()->getState()->getCountry();
            if (($countryrec == $countryto) || ($receipt->getAgency() == $agency))  {
                $entity = $receipt;
            } else {
                throw $this->createNotFoundException('No tiene permiso para ver etiqueta de este ' . $translator->trans('Recibo'));
            }
        } else {
            $entity = $receipt;
        }

        return array(
            'entity'      => $entity,
        );
    }
     /**
     * Generate  a  receipt for printer
     * 
     * @Route("/{id}/print", name="receipt_print")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    public function printAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();
        $maincompany = $user->getMainCompany();
        $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$receipt) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }
        if ($user->getAgency()->getType() != 'MASTER') {
            $countryrec = $receipt->getReceiver()->getCity()->getState()->getCountry();
            if (($countryrec == $countryto) || ($receipt->getAgency() == $agency))  {
                $entity = $receipt;
            } else {
                throw $this->createNotFoundException('No tiene permiso para ver etiqueta de este ' . $translator->trans('Recibo'));
            }
        } else {
            $entity = $receipt;
        }
        $viewoption = $this->getView('Receipt', 'print');
        
        return  $this->render($viewoption,array('entity'=> $entity));
        /*
        return array(
            'entity'      => $entity,
        );
        */
    }
    /**
     * Export to PDF
     * 
     * @Route("/{id}/printpdf", name="receipt_printpdf")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function printpdfAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();
        $maincompany = $user->getMainCompany();
        $entity = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$entity) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Recibo') );
        }
        $admin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ADMIN_RECEIPT'); 
        $countryrec = $entity->getReceiver()->getCity()->getState()->getCountry();
        $roleview =  (($countryrec == $countryto) || ($entity->getAgency() == $agency)) && ($this->isGranted('ROLE_VIEW_RECEIPT')); 
        $admin = $admin || $roleview;
	
        if ($user->getPobox()) {
            $customer = $entity->getShipper() == $user->getPobox()->getCustomer();
            $badd = $user->getPobox()->getCustomer()->getBaddress();
                $recto = $entity->getReceiver()->getId();
            $receiver = false;
            foreach ($badd as $struct) {
                    if ($recto == $struct->getId()) {
                        $receiver = true;
                        break;
                    }
            }
        } else {
            $customer = false;
            $receiver = false;
        }
        if ((!$admin) && (!$customer) && (!$receiver)) {
            throw $this->createAccessDeniedException('');
        }
        $viewoption = $this->getView('Receipt', 'print');
        $html = $this->renderView($viewoption, array('entity' => $entity));
        // exit(\Doctrine\Common\Util\Debug::dump($html));
        $number = $entity->getNumber(); 
        $filename = sprintf('print-receipt-%s.pdf', transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $number));
        

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
                'orientation' => 'portrait',
                'margin-top'    => 10,
                'margin-right'  => 12,
                'margin-bottom' => 10,
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
            )),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
            ]
        );
    }
    /**
     * Creates a form to search unprocessed Receipt
     *
     *
     * @return \Symfony\Component\Form\Form The form
     */
    /* private function createSearchForm()
    {
	$formBuilder = $this->get('form.factory')->createNamedBuilder('receipt_type', 'form',  array())
	    ->add('pobox', 'text', array('label' => 'Casillero'))
	    ->add('namecustomer', 'text', array('label' => 'Nombre'))
	    ->add('lastnamecustomer', 'text', array('label' => 'Apellido'))
	    ->add('searchcustomer', 'button', array('label' => 'Buscar'))
	    ->add('searchpobox', 'button', array('label' => 'Buscar')) 
	    ->add('selpobox', 'hidden', array('label' => ' '))
	    ->add('selcustomer', 'hidden', array('label' => ' '))
            ->getForm()
        ;
	return $formBuilder;
    } */
    /**
     * Search all Receipt without guides.
     *
     * @Route("/search", name="receipt_search")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    public function searchAction()
    {
        // $searchForm = $this->createSearchForm();
        $em = $this->getDoctrine()->getManager();
    
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();
        $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Por procesar');
        if ($agency->getType() == 'MASTER') {
            $entities = $em->getRepository('NvCargaBundle:Receipt')->findBy(array('status' => $status, 'maincompany' => $maincompany, 'whrec' => null));
            $agencies = $em->getRepository('NvCargaBundle:Agency')->findBy(array('maincompany' => $maincompany));
        } else {
            $entities = $em->getRepository('NvCargaBundle:Receipt')->findBy(array('status' => $status, 'agency' => $agency, 'whrec'=> null));
            $agencies = null;
        }

        $status = array();
        $estados =  $em->getRepository('NvCargaBundle:Stepstatus')->findByMaincompany($maincompany);
        foreach ($estados as $estado) {
            $status[] = $estado->getName();
        }

        return array(
            'entities' => $entities,
            'agencies'=>$agencies,
            'status' => $status,
        );
    }
    /**
     * Search all Receipt without guides for sender or receiver.
     *
     * @Route("/customer", name="receipt_customer")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    public function customerAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        $id_sender = $request->query->get('sender_id');
        $id_receiver = $request->query->get('receiver_id');
        $em = $this->getDoctrine()->getManager();
        $sender=null;
        $receiver=null;
        if ($id_sender) {
            $sender = $em->getRepository('NvCargaBundle:Customer')->find($id_sender);
        }
        if ($id_receiver) {
            $receiver = $em->getRepository('NvCargaBundle:Customer')->find($id_receiver);
        }
        $user = $this->getUser();
        $agency = $user->getAgency();
        $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Por procesar');
        $entities = null;
        if (($sender) && ($receiver)) {
            $entities = $em->getRepository('NvCargaBundle:Receipt')->findBy(array('status' => $status, 
                                                'agency' => $agency, 
                                                'shipper' => $sender, 
                                                'receiver' => $receiver));
        } else {
            if ($sender) {
                $entities = $em->getRepository('NvCargaBundle:Receipt')->findBy(array('status' => $status, 
                                                'agency' => $agency, 'shipper' => $sender));
            } else {
                if ($receiver) {
                    $entities = $em->getRepository('NvCargaBundle:Receipt')->findBy(array('status' => $status, 
                                                'agency' => $agency, 'receiver' => $receiver));
                }
            }
        }
        $result=array();
        $counter = 0;
        foreach ($entities as $entity) {
            $result[$counter]['id'] = $entity->getId();
            $result[$counter]['number'] = $entity->getNumber();
            $result[$counter]['agency'] = $entity->getAgency()->getName();
            $result[$counter]['arrivedate'] = $entity->getArrivedate()->format('Y-m-d');
            $result[$counter]['shipper'] = strval($entity->getShipper());
            $result[$counter]['receiver'] = strval($entity->getReceiver());
            $result[$counter]['carrier'] = $entity->getCarrier()->getName();
            $result[$counter]['receiptdby'] = $entity->getReceiptdBy()->getUsername();
            $result[$counter]['weight'] = $entity->getWeight();
            $result[$counter]['value'] = $entity->getValue();
            $counter++;	
        }
        return new JsonResponse($result);    
    }
    /**
     * Search all Receipt without guides for pobox.
     *
     * @Route("/pobox", name="receipt_pobox")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    public function poboxAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        $id_pobox = $request->query->get('pobox_id');
        $em = $this->getDoctrine()->getManager();
        $pobox=null;
        $receiver=null;
        if ($id_pobox) {
            $pobox = $em->getRepository('NvCargaBundle:Pobox')->find($id_pobox);
            
        }
        if ($pobox) {
            $receiver = $pobox->getCustomer()->getBaddress();
        }
        $user = $this->getUser();
        $agency = $user->getAgency();
        $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Por procesar');
        $entities = null;
        if ($receiver) {
            $entities = $em->getRepository('NvCargaBundle:Receipt')->findBy(array('status' => $status, 
                                        'agency' => $agency, 'receiver' => $receiver));
        }
        $result=array();
        $counter = 0;
        foreach ($entities as $entity) {
            $result[$counter]['id'] = $entity->getId();
            $result[$counter]['number'] = $entity->getNumber();
            $result[$counter]['agency'] = $entity->getAgency()->getName();
            $result[$counter]['arrivedate'] = $entity->getArrivedate()->format('Y-m-d');
            $result[$counter]['shipper'] = strval($entity->getShipper());
            $result[$counter]['receiver'] = strval($entity->getReceiver());
            $result[$counter]['carrier'] = $entity->getCarrier()->getName();
            $result[$counter]['receiptdby'] = $entity->getReceiptdBy()->getUsername();
            $result[$counter]['weight'] = $entity->getWeight();
            $result[$counter]['value'] = $entity->getValue();
            $counter++;	
        }
        return new JsonResponse($result);    
    }
    /**
     * Search all Receipt without guides for pobox.
     * @Route("/unprocessed", name="receipts_unprocessed")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    /* public function unprocessedAction(Request $request)
    {
	if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
	$em = $this->getDoctrine()->getManager();
	$user = $this->getUser();
	$agency = $user->getAgency();
	$status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Por procesar');
	$entities = $em->getRepository('NvCargaBundle:Receipt')->findBy(array('status' => $status, 'agency' => $agency));
	$result=array();
        $counter = 0;
        foreach ($entities as $entity) {
		$result[$counter]['id'] = $entity->getId();
		$result[$counter]['number'] = $entity->getNumber();
		$result[$counter]['agency'] = $entity->getAgency()->getName();
		$result[$counter]['arrivedate'] = $entity->getArrivedate()->format('Y-m-d');
		$result[$counter]['shipper'] = strval($entity->getShipper());
                $result[$counter]['receiver'] = strval($entity->getReceiver());
		$pobox = $entity->getReceiver()->getPobox();
		if ($pobox) {
			$result[$counter]['pobox'] = $pobox->getNumber();
		} else {
			$result[$counter]['pobox'] = '';
		}
                $result[$counter]['carrier'] = $entity->getCarrier()->getName();
		$result[$counter]['receiptdby'] = $entity->getReceiptdBy()->getUsername();
		$result[$counter]['weight'] = $entity->getWeight();
		$result[$counter]['value'] = $entity->getValue();
		$counter++;	
	}
	return new JsonResponse($result);    
    } */

    /**
     * @Route("/showcancel", name="receipts_showcancel")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    public function showcancelAction(Request $request)
    {
        $list = $request->query->get('reclist');
        $reclist=json_decode($list);
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('NvCargaBundle:Receipt')->findById($reclist);

        $translator = $this->get('translator');        

        foreach ($entities as $entity) {
            if ($entity->getGuide()) {
                throw $this->createNotFoundException('El ' . $translator->trans('Recibo') . ' '. $id . ' ya tiene ' . $translator->trans('Guía') . ': (' . $receipt->getGuide()->getNumber() . '). No puede ser anulado');
            }		
        }
	
        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entities'      => $entities,
            // 'delete_form' => $deleteForm->createView(),
        );   
    }
    /**
     * @Route("/cancel", name="receipts_cancel")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    public function cancelAction(Request $request)
    {
        $maincompany = $this->getUser()->getMainCompany();
        $list = $request->query->get('reclist');
        $reclist=json_decode($list);
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('NvCargaBundle:Receipt')->findById($reclist);
        // exit(\Doctrine\Common\Util\Debug::dump($entities)); 
        
        $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Anulado');
        $procesado = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Procesado');
        $porprocesar = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Por Procesar');
        $rempacado = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Reempacado');

        $translator = $this->get('translator');   
        
        $countreceipt = $maincompany->getCountreceipts();
        foreach ($entities as $entity) {
            if ($entity->getGuide() || ($entity->getStatus() == $procesado) || ($entity->getStatus() == $rempacado)) {
                throw $this->createNotFoundException('El ' . $translator->trans('Recibo') . ' '. $id . ' NO PUEDE ser anulado');
            }
            $entity->setStatus($status);
            foreach ($entity->getListstatus() as $thestatus) {
                $thestatus->setReceipt(null);
                $entity->removeListstatus($thestatus);
                $em->remove($thestatus);
            }
            foreach ($entity->getReceipts() as $receipt) {
                $entity->removeReceipt($receipt);
                $receipt->setStatus($porprocesar);
                $receipt->setMaster(null);
            }
            $alert = $em->getRepository('NvCargaBundle:Alert')->findOneByReceipt($entity);
            if ($alert) {
                $alert->setReceipt(null);
            }
            $countreceipt--;
        }
        $maincompany->getCountreceipts($countreceipt);
        $em->flush();
        return $this->redirect($this->generateUrl('receipt_search'));
    }
    /**
     * Creates a new Receipt entity.
     *
     * @Route("/alert", name="receipt_alert")
     * @Template("NvCargaBundle:Receipt:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function alertAction(Request $request)
    {
        $user = $this->getUser();
        $maincompany = $user->getMainCompany(); 
        $alert = $request->query->get('alert');
        if (!$alert) {
            throw $this->createNotFoundException('Debe suministar un número de alerta');
        } 
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $user->getAgency();
        $thealert = $em->getRepository('NvCargaBundle:Alert')->findOneBy(['id'=>$alert,'maincompany'=>$maincompany]);
        $translator = $this->get('translator'); 
        if (!$thealert) {
            throw $this->createNotFoundException('No existe la alerta para crearle '. $translator->trans('Recibo'));
        }
        $translator = $this->get('translator');
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $countreceipt =  $maincompany->getCountreceipts(); /*$em->getRepository('NvCargaBundle:Receipt')->createQueryBuilder('r')
                            ->select('count(r.id)')
                            ->where('r.maincompany =:thecompany')
                            ->setParameters(['thecompany'=>$maincompany])
                            ->getQuery()
                            ->getSingleScalarResult();*/
        $plan = $maincompany->getPlan();
        if (($plan->getReceipts()) && ($countreceipt >= $plan->getMaxreceipts())) {
            $message = 'Ha llegado al número máximo de ' . $translator->trans('Recibos')  .' permitidos. Para crear más debe actualizar su plan a uno superior.';
            if ($this->isGranted('ROLE_ADMIN')) {
                $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
            }
            $flashBag->add('notice',$message);
            goto next;
        }
        $entity = new Receipt();
        $entity->setShipper($thealert->getPobox()->getCustomer());
        $entity->setReceiver($thealert->getBaddress());
        $entity->setTracking($thealert->getTracking());
        $entity->setLength(0.00);
        $entity->setwidth(0.00);
        $entity->setHeight(0.00);
        $entity->setWeight($thealert->getWeight());
        $entity->setQuantity($thealert->getPieces());
        $entity->setDescription($thealert->getDescription());
        $entity->setValue($thealert->getValue());
        $entity->setCarrier($thealert->getCarrier());
        $entity->setReference('Paquete alertado por el cliente de Casillero');
        $edition = true;
        $form = $this->createAlertForm($entity);
        $form->handleRequest($request);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
	
        // exit(\Doctrine\Common\Util\Debug::dump($packages));	

        if ($form->isValid()) {
            // $packages = $entity->getPackages();
            // exit(\Doctrine\Common\Util\Debug::dump($packages)); 
            $entity->setReceiptdBy($user);
            $entity->setCarrier($thealert->getCarrier());
            $entity->setAgency($user->getAgency());			
            $em = $this->getDoctrine()->getManager();
            
            $addcus = $this->forward('NvCargaBundle:Customer:addCustomer', array('form'=>$form, 'entity'=> $entity));
            // $error = $addcus['error'];
            $data = explode(':',$addcus->getContent());
            $error = intval($data[0]);
            $idsender = intval($data[1]);
            $idaddr = intval($data[2]);
            // exit(\Doctrine\Common\Util\Debug::dump('NO EXISTE EL REMITENTE: ' . $idsender));
            if ($error) {
                goto next;
            }
            $sender = $em->getRepository('NvCargaBundle:Customer')->find($idsender);
            $rebaddr = $em->getRepository('NvCargaBundle:Baddress')->find($idaddr);
            $entity->setShipper($sender);
            $sender->addShipped($entity);
            $entity->setReceiver($rebaddr);
            // $addr->addReceived($entity);
            $addr = $rebaddr->getCustomer();
            
            if ($addr->getPobox()) { // el destinatario tiene casillero.... ALERTAS DE PAQUETES DE RECIBIDOS
                $rtipo= "Casillero"; // se puede crear una alerta, si no exite para el paquete, y/o enviar email
            } else {
                $rtipo= "Pickup";
            }
            $highest_id = $em->getRepository('NvCargaBundle:Receipt')->createQueryBuilder('r')
                            ->select('count(r.id)')
                            ->where('r.maincompany =:thecompany')
                            ->setParameters(['thecompany'=>$maincompany])
                            ->getQuery()
                            ->getSingleScalarResult();
            $highest_id++;
            $countreceipt++;
            $typerec = $em->getRepository('NvCargaBundle:Receipttype')->findOneBy(array('name'=>$rtipo));
            $statusrec = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneBy(array('name'=>'Por procesar'));
            
            $comcod = str_pad($maincompany->getId(), 2, '0', STR_PAD_LEFT);
            $number = "PKG" . $comcod . $highest_id . 'P1';
            $track = $entity->getTracking();
            $carrier =  $entity->getCarrier();
            
            $alert = $thealert; //$em->getRepository('NvCargaBundle:Alert')->findOneBy(array('tracking'=>$track, 'carrier' => $carrier));
            if ($alert) {
                $alert->setReceipt($entity); // se podría validar que el destinatario sea el cliente del casillero
            }
            $entity->setType($typerec); 
            $entity->setStatus($statusrec); 
            $entity->setNumber($number);   
            $entity->setCreationdate(new \DateTime()); 	
            $entity->setArrivedate(new \DateTime($entity->getArrivedate()));
            $entity->setMaincompany($maincompany);
            if ($maincompany->getFirststatus()) {
                $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                $newstatus = new Statusreceipt();
                $newstatus->setReceipt($entity);
                $newstatus->setDate($entity->getCreationdate());
                $newstatus->setPlace($entity->getAgency()->getCity());
                $newstatus->setStep($step);
                $newstatus->setComment($translator->trans('Recibo') . ' creado');
                $entity->addListstatus($newstatus);
                $em->persist($newstatus);
            }
            $em->persist($entity);
        
            $mespack = $mespack . '<br><b>Paquete 1 </b><br>';
                if ($entity->getTracking() ) {
                    $mespack = $mespack . 'Tracking: ' .   $entity->getTracking() . '<br>';
                }
            $mespack = $mespack . 'Courrier: ' .   $entity->getCarrier() . '<br>';
            $mespack = $mespack . 'Descripcion: ' .   $entity->getDescription() . '<br>';
            $mespack = $mespack . 'Bultos: ' .   $entity->getQuantity() . '<br>';
            $mespack = $mespack . 'Dimensiones: ' .   $entity->getLength() . 'X'. $entity->getWidth() .  'X'. $entity->getHeight() .'<br>';
            $mespack = $mespack . 'Peso: ' .   $entity->getWeight() .'<br>';
            
            $maincompany->setCountreceipts($countreceipt);
            $em->flush();
            $email_sender = $form['email_sender']->getData();
            $email_addr = $form['email_addr']->getData();
            $noti_sender = $form['noti_sender']->getData();
            $noti_addr = $form['noti_addr']->getData();
            $sent_sender = false;
            
            if (($noti_sender) && ($email_sender)) {
                $this->sendemail($entity, $mespack, 1);
                $sent_sender = true;
            }
            
            $sent_addr = ((!$sent_sender) || ($email_sender != $email_addr));
            if (($noti_addr) && ($email_addr) && ($sent_addr)) {
                $this->sendemail($entity, $mespack, 2);
            }	
            return $this->redirect($this->generateUrl('receipt_show', array('id' => $entity->getId())));
        }  else {
            if  ($form['edition']->getData() == 1) {
                $edition = false;
            }
        }
        next:
            return array(
                'entity' => $entity,
                'form'   => $form->createView(),
                'edition' => $edition,
                'nameform' => 'Procesar Alerta', 
            );
    }
   /**
     * Creates a form to create a Receipt entity.
     *
     * @param Receipt $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createAlertForm(Receipt $entity)
    {
        $form = $this->createForm(new ReceiptType($this->getDoctrine()->getManager(), $this->getUser()), $entity, array());
	
        $form->remove('packages');
        $form->remove('tracking');
        $form->remove('carrier');
        $form->add('tracking','text', array('label' => 'Tracking ', 'required' => true, 'read_only'=> true));
        $form->add('carrier', 'entity', array('label'=>'Carrier', 'read_only'=> true, 'disabled' => true,
                            'required' => true, 'class'=>'NvCargaBundle:Carrier'));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    private function sendemail($entity, $mespack, $goto)
    {
        $body = '<p align="right">Ref: ' . $entity->getNumber() .  '</p><br><br>';
        $maincompany = $this->getUser()->getAgency()->getMainCompany();
        $body = $body . 'Le informamos que hemos recibido en nuestras bodegas los siguientes paquetes: <br><br>';
        $body = $body . '<b> REMITENTE:' .  $entity->getShipper()->getName() . ' '  . $entity->getShipper()->getLastname() . '</b><br>';
        $body = $body . '<b> DESTINATARIO:' .  $entity->getReceiver()->getName() . ' '  . $entity->getReceiver()->getLastname() . '</b><br>';
        $body = $body . $mespack;
        $body = $body . '<br>Por favor, ingresar a través de su <a href="http://' . $_SERVER['SERVER_NAME'] . '/login"> casillero personal</a>  para girar instrucciones acerca del despacho.<br><br>';
        $body = $body . 'Gracias por su confianza y ser parte de la familia <b>"' . $maincompany->getName() . '"</b><br><br>';
        $translator = $this->get('translator');
        $color = $translator->trans('tailcolor');
        $body = $body . '<p style="font-size:20px; color:' . $color . ';"><b>**ESTE ES UN CORREO NO MONITOREADO, POR FAVOR NO RESPONDA AL MISMO**</b></p>';
        // $maincompany = $this->getUser()->getMaincompany();
        $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
        $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');
        if ($goto == 1) {
            $sendto = $entity->getShipper()->getEmail();
        } else {
            $sendto = $entity->getReceiver()->getCustomer()->getEmail();
        }
        $message = \Swift_Message::newInstance()
		// ->setBcc($setfrom)
            //->setFrom(array($setfrom => $fromname))
       		->setContentType("text/html")
       		->setSubject('Paquete(s) recibidos en ' . $maincompany->getName())
            //->setTo($sendto)
    		// ->attach(\Swift_Attachment::fromPath($filename)->setFilename($file))
       		->setBody($body);
        $send = 0;
        try {
                $message->setTo($sendto);
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
            $send = $this->container->get('mailer')->send($message); // QUITAR COMENTARIO PARA ENVIAR EL EMAIL

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
                $msg->setSender($user);
                $msg->setReceiver($user);
                $msg->setSubject('Error enviando email (Paquete recibido)');
                $msg->setBody($head);
                $msg->setCreationdate(new \DateTime());
                $msg->setIsread(FALSE);
                $em->persist($msg);
        
                $em->flush();
            }
    
            return $send;
    }
    /**
     * Creates a new Receipt entity.
     *
     * @Route("/reempack", name="receipt_reempack")
     * @Template("NvCargaBundle:Receipt:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function reempackAction(Request $request)
    {
        $user=$this->getUser();
        $maincompany=$user->getMainCompany();
        $flashBag = $this->get('session')->getFlashBag();
        $typecus = $request->query->get('typecus');
        $list = $request->query->get('reclist');
        // exit(\Doctrine\Common\Util\Debug::dump($typecus . ' ' . $reclist)); 
        $em = $this->getDoctrine()->getManager();
        $reclist=json_decode($list);
        $total=count($reclist);
        $id = (int)$reclist[0];
        $receipt = $em->getRepository('NvCargaBundle:Receipt')->find($id);
        $translator = $this->get('translator');
        if (!$receipt) {
            throw $this->createNotFoundException($total . ' El ' . $translator->trans('Recibo') . ' ' . $id . ' no existe ');
        }
        if ($receipt->getGuide()) {
            throw $this->createNotFoundException(' El ' . $translator->trans('Recibo') . ' ' . $id . ' ya tiene ' . $translator->trans('Guía') . ': ('. $receipt->getGuide()->getNumber() . ').');
        }
        $weight =  $receipt->getWeight();
        $quantity = $receipt->getQuantity();
        $value = $receipt->getValue();
        $track = $receipt->getTracking();
        $descripcion = $receipt->getDescription();
        $note = 'Contiene los recibos: ' . $receipt->getNumber();
        $advr = false;
        $adva = false;
        $bsen = $receipt->getShipper();
        $badd = $receipt->getReceiver();
        $entity = new Receipt();
        $thistype = $em->getRepository('NvCargaBundle:Receipttype')->findOneByName('Reempaque');
        $unprocess = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Por procesar');
        $packed = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Reempacado');
        $carrier = $em->getRepository('NvCargaBundle:Carrier')->findOneByName('Reempaque en empresa');
        $entity->setType($thistype);
        $entity->setStatus($unprocess);
        $entity->setCarrier($carrier);
        $receipt->setStatus($packed);
        $receipt->setMaster($entity);
        $entity->addReceipt($receipt);
        
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $message1 = $translator->trans('Recibo') . ' 1: ' . $bsen->getName() . ' ' . $bsen->getLastName() . 
                    ' (' . $bsen->getAdrdefault()->getCity() . ', ' .  
                    $bsen->getAdrdefault()->getCity()->getState()->getCountry() . ")\n";
        $message2 = $translator->trans('Recibo') . '  1: ' .$badd->getName() . ' ' . $badd->getLastName() . 
                    ' (' . $badd->getCity() . ', ' .  
                    $badd->getCity()->getState()->getCountry() . ")\n";
        for ($i=1;$i<$total;$i++) {
            $id = (int)$reclist[$i];
            $receipt = $em->getRepository('NvCargaBundle:Receipt')->find($id);
            $translator = $this->get('translator');
            if (!$receipt) {
                throw $this->createNotFoundException($total . ' El ' . $translator->trans('Recibo') . ' ' . $id . ' no existe ');
            }
            if ($receipt->getGuide()) {
                throw $this->createNotFoundException(' El ' . $translator->trans('Recibo') . ' ' . $id . ' ya tiene ' . $translator->trans('Guía') . ': ('. $receipt->getGuide()->getNumber() . ').');
            }
            
            $note = $note . ', ' . $receipt->getNumber();
            $descripcion = $descripcion . '. ' . $receipt->getDescription();
            $track = $track . '. ' . $receipt->getTracking();
            $weight += $receipt->getWeight();
            $quantity += $receipt->getQuantity();
            $value += $receipt->getValue();
            $shipper = $receipt->getShipper();
            $receiver = $receipt->getReceiver();
            $receipt->setStatus($packed);
            $receipt->setMaster($entity);
            $entity->addReceipt($receipt);
            $val = $i + 1;
            $message1 = $message1 .  $translator->trans('Recibo') . ' ' . $val . ': ' . $shipper->getName() . ' ' . $shipper->getLastName() . 
                    ' (' . $shipper->getAdrdefault()->getCity() . ', ' .  
                    $shipper->getAdrdefault()->getCity()->getState()->getCountry() . ")\n";
            $message2 = $message2 .  $translator->trans('Recibo') . ' ' . $val . ': ' . $receiver->getName() . ' ' . $receiver->getLastName() . 
                    ' (' . $receiver->getCity() . ', ' .  
                    $receiver->getCity()->getState()->getCountry() . ")\n";
            if (($bsen != $shipper) && (!$advr)) {
                $advr = true;
            }
            if (($badd != $receiver) && (!$adva)) {
                $adva = true;
            }
        }
        if ($advr) {
            $flashBag->add('warning',
                    "Los " . $translator->trans('Recibos') . " tiene diferentes remitentes \n" . $message1);
            $hideshipper=false;
        }
        if ($adva) {
            $flashBag->add('warning',
                    "Los " . $translator->trans('Recibos') . " tienen diferentes destinatarios \n" . $message2);
            $hidereceiver=false;
        }	
        $shipper = $bsen;
        $receiver = $badd;
        $entity->setShipper($shipper);
        // $shipper->addShipped($entity);
        $entity->setReceiver($receiver);
        
        $entity->setNote($note);
        $entity->setTracking($track);
        $entity->setWeight($weight);
        $entity->setDescription($descripcion);
        $entity->setQuantity($quantity);
        $entity->setValue($value);
        $today= new \DateTime();
        $entity->setArrivedate($today->format('m/d/Y'));
        
        $agency = $receipt->getAgency();	
        
        // $entity = new Receipt();
        $form = $this->createReempackForm($entity);

        $form->handleRequest($request);
	
        // exit(\Doctrine\Common\Util\Debug::dump($packages));	
        $translator = $this->get('translator');
        if ($form->isValid()) {
            // $packages = $entity->getPackages();
            // exit(\Doctrine\Common\Util\Debug::dump($packages)); 
            $countreceipt =  $maincompany->getCountreceipts(); /*$em->getRepository('NvCargaBundle:Receipt')->createQueryBuilder('r')
                            ->select('count(r.id)')
                            ->where('r.maincompany =:thecompany')
                            ->setParameters(['thecompany'=>$maincompany])
                            ->getQuery()
                            ->getSingleScalarResult();*/
            $plan = $maincompany->getPlan();
            if (($plan->getReceipts()) && ($countreceipt >= $plan->getMaxreceipts())) {
                $message = 'Ha llegado al número máximo de ' . $translator->trans('Recibos')  .' permitidos. Para crear más debe actualizar su plan a uno superior.';
                if ($this->isGranted('ROLE_ADMIN')) {
                    $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                }
                $flashBag->add('notice',$message);
                goto next;
            }
            $countreceipt++;
            $maincompany->setCountreceipts($countreceipt);
            $entity->setReceiptdBy($user);
            $entity->setAgency($user->getAgency());
            $em = $this->getDoctrine()->getManager();
            $noti_sender = $form['noti_sender']->getData();
            $noti_addr = $form['noti_addr']->getData();
            
            $addcus = $this->forward('NvCargaBundle:Customer:addCustomer', array('form'=>$form, 'entity'=> $entity));
            // $error = $addcus['error'];
            $data = explode(':',$addcus->getContent());
            $error = intval($data[0]);
            $idsender = intval($data[1]);
            $idaddr = intval($data[2]);
            // exit(\Doctrine\Common\Util\Debug::dump('NO EXISTE EL REMITENTE: ' . $form['id_sender']->getData() . ' ' . $error . ' ' . $idsender));
            if ($error) {
                goto next;
            }
            $sender = $em->getRepository('NvCargaBundle:Customer')->find($idsender);
            $rebaddr = $em->getRepository('NvCargaBundle:Baddress')->find($idaddr);
            $entity->setShipper($sender);
            $sender->addShipped($entity);
            $entity->setReceiver($rebaddr);
            
            $highest_id = $em->getRepository('NvCargaBundle:Receipt')->createQueryBuilder('r')
                            ->select('MAX(r.id)')
                            ->where('r.maincompany =:thecompany')
                            ->setParameters(['thecompany'=>$maincompany])
                            ->getQuery()
                            ->getSingleScalarResult();
            $highest_id++;
            
            $comcod = str_pad($maincompany->getId(), 2, '0', STR_PAD_LEFT);
            $number = "PKG" . $comcod . $highest_id . 'R';
           
            $entity->setNumber($number);   
            $entity->setCreationdate(new \DateTime()); 	
            $entity->setArrivedate(new \DateTime($entity->getArrivedate()));
            $entity->setMaincompany($maincompany);
            $themaster = $em->getRepository('NvCargaBundle:Receipttype')->findOneByName('Master');
            $entity->setType($themaster);
            if ($maincompany->getFirststatus()) {
                $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                $newstatus = new Statusreceipt();
                $newstatus->setReceipt($entity);
                $newstatus->setDate($entity->getCreationdate());
                $newstatus->setPlace($entity->getAgency()->getCity());
                $newstatus->setStep($step);
                $newstatus->setComment($translator->trans('Recibo') . ' creado');
                $entity->addListstatus($newstatus);
                $em->persist($newstatus);
            }
            $em->persist($entity);
            $em->flush();
            foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
            }
            $flashBag->add('notice',
                    "Los " . $translator->trans('Recibos') . " fueron reempacados exitosamente." );
            return $this->redirect($this->generateUrl('receipt_search'));
        }  else {
            if ($request->isMethod('POST')) {
                $flashBag->set('notice', array());
                $translator = $this->get('translator');
                $message = 'Hay errores en la creación del ' . $translator->trans('Recibo') . ', por favor, revisar los mensajes de los campos. '; 
            
                if (!($form['id_sender']->getData())) {
                    $message = $message . 'Debe seleccionar el REMITENTE. '; 
                }
                if (!($form['id_addr']->getData())) {
                    $message = $message . 'Debe seleccionar el DESTINATARIO.'; 
                }
                $this->get('session')->getFlashBag()->add(
                                'notice',
                                $message);
                }
        }
        next:
            return array(
                'entity' => $entity,
                'form'   => $form->createView(),
                'nameform' => 'Reempaque de ' . $translator->trans('Recibos'),
                'edition' => true,
            );
    }
    public function getView($table, $type) {
        $option = null;
        $maincompany = $this->getUser()->getMaincompany();
        //exit(\Doctrine\Common\Util\Debug::dump($maincompany->getFormat()));
        if ($maincompany->getFormat()) {
            $option = $maincompany->getFormat()->getGprint();
        }
        $viewoption = 'NvCargaBundle:' . $table . ':' . $type . $option . '.html.twig';
        if (!$this->get('templating')->exists($viewoption) ) {
            $viewoption = 'NvCargaBundle:' . $table . ':' . $type . '.html.twig';
        }
        return $viewoption;
    }
    /**
     * Search all Receipt without guides.
     *
     * @Route("/fix", name="receipt_fix")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    public function fixAction() {
        $user=$this->getUser();
        $maincompany=$user->getMainCompany();
        $em = $this->getDoctrine()->getManager();
        $receipts = $em->getRepository('NvCargaBundle:Receipt')->findByMaincompany($maincompany);
        $first = $em->getRepository('NvCargaBundle:Stepstatus')->findOneBy(['maincompany'=>$maincompany, 'name'=>'Creado']);
        foreach ($receipts as $receipt) {
            $liststatus = $receipt->getListstatus();
            foreach ($liststatus as $status) {
                if (!$status->getStep()) {
                    $status->setStep($first);
                }
            }
        }
        $em->flush();
        return $this->redirect($this->generateUrl('homepage'));
    }
}
