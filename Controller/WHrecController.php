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
use NvCarga\Bundle\Entity\WHrec;
use NvCarga\Bundle\Entity\Receipt;
use NvCarga\Bundle\Entity\Package;
use NvCarga\Bundle\Entity\Baddress;
use Nvcarga\Bundle\Entity\Statuswhrec;
use NvCarga\Bundle\Entity\City;
use NvCarga\Bundle\Entity\Statusreceipt;

use NvCarga\Bundle\Form\WHrecType;
use NvCarga\Bundle\Form\PackageType;


/**
 * WHrec controller.
 *
 * @Route("/whrec")
 */
class WHrecController extends Controller
{
    /**
     * Creates a new WHrec entity.
     *
     * @Route("/create", name="whrec_create")
     * @Method("POST")
     * @Template("NvCargaBundle:WHrec:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new WHrec();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        
        $form = $this->createCreateForm($entity);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $form->handleRequest($request);
        
        // exit(\Doctrine\Common\Util\Debug::dump($entity->getPackages()));	

        if ($form->isValid()) {
            $translator = $this->get('translator');
            $countwhrec =  $maincompany->getCountwhrecs();
            $plan = $maincompany->getPlan();
            if (($plan->getWhrecs()) && ($countwhrec >= $plan->getMaxwhrecs())) {
                $message = 'Ha llegado al número máximo de ' . $translator->trans('Warehouses')  .' permitidos. Para crear más debe actualizar su plan a uno superior.';
                if ($this->isGranted('ROLE_ADMIN')) {
                    $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                }
                $flashBag->add('notice',$message);
                goto next;
            }
	    
            $packages = $entity->getPackages();
            // exit(\Doctrine\Common\Util\Debug::dump($packages)); 
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
            // exit(\Doctrine\Common\Util\Debug::dump('NO EXISTE EL REMITENTE: ' . $idsender));
            if ($error) {
                goto next;
            }
            $sender = $em->getRepository('NvCargaBundle:Customer')->find($idsender);
            $rebaddr = $em->getRepository('NvCargaBundle:Baddress')->find($idaddr);
           
            $entity->setShipper($sender);
            // $sender->addShipped($entity);
            $entity->setReceiver($rebaddr);
            // $addr->addReceived($entity);	 
            $addr = $rebaddr->getCustomer();
            
            if ($addr->getPobox()) { // el destinatario tiene casillero.... ALERTAS DE PAQUETES DE RECIBIDOS
                $rtipo= "Casillero"; // se puede crear una alerta, si no exite para el paquete, y/o enviar email
            } else {
                $rtipo= "Pickup";
            }
            $highest_id = $em->getRepository('NvCargaBundle:WHrec')->createQueryBuilder('r')
                            ->select('MAX(r.id)')
                            ->where('r.maincompany =:thecompany')
                            ->setParameters(['thecompany'=>$maincompany])
                            ->getQuery()
                            ->getSingleScalarResult();
            $highest_id++;
            $typerec = $em->getRepository('NvCargaBundle:Receipttype')->findOneBy(array('name'=>$rtipo));
            $statusrec = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneBy(array('name'=>'Por procesar'));
            
            $comcod = str_pad($maincompany->getId(), 2, '0', STR_PAD_LEFT);
            $number = "WR" . $comcod . $highest_id;
            
            $entity->setStatus('RECIBIDO'); 
            $entity->setNumber($number); 
            $movdate = new \DateTime();
            $clock = $form['clock']->getData(); 
            $rdate = substr($movdate->format('m/d/Y'),0,10) . 'T' . $clock;
            $entity->setCreationdate(new \DateTime($rdate));
            
            $entity->setMaincompany($maincompany);

            $mespack = '';
            
            $count = 1;
            $countreceipts =  $maincompany->getCountreceipts(); 
            
            foreach ($packages as $package) {
                $thispack = $count;	
                $countreceipts++;
                $plan = $maincompany->getPlan();
                if (($plan->getReceipts()) && ($countreceipts >= $plan->getMaxreceipts())) {
                    $message = 'Ha llegado al número máximo de ' . $translator->trans('Recibos')  .' permitidos. Para crear más debe actualizar su plan a uno superior.';
                    if ($this->isGranted('ROLE_ADMIN')) {
                        $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                    }
                    $flashBag->add('notice',$message);
                    goto next;
                }
                $nrec = new Receipt();
                // $highest_id ++;
                $number = "PCK" . $comcod . $highest_id . 'P' . $count++;
                $nrec->setNumber($number);
                $nrec->setAgency($entity->getAgency());
                $nrec->setReceiptdBy($entity->getReceiptdBy());
                $nrec->setShipper($entity->getShipper());
                $nrec->setReceiver($entity->getReceiver());
                $nrec->setNote($entity->getNote());
                $nrec->setType($typerec);
                $nrec->setStatus($statusrec);
                // $addr->addReceived($nrec);
                $sender->addShipped($nrec);
                $nrec->setCarrier($package->getCarrier());
                $arrivedate = $package->getArrivedate();
                $rdate = substr($arrivedate,0,10) . 'T' . $clock;
                $nrec->setArrivedate(new \DateTime($rdate));
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
                    $alert=null;
                }
                if ($alert) {
                    $alert->setReceipt($nrec); // se podría validar que el destinatario sea el cliente del casillero
                }
                $nrec->setMaincompany($maincompany);
                $nrec->setWHrec($entity);
                $entity->addReceipt($nrec);
                
                if ($maincompany->getFirststatus()) {
                    $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                    $newstatus = new Statusreceipt();
                    $newstatus->setReceipt($nrec);
                    $newstatus->setDate($nrec->getCreationdate());
                    $newstatus->setPlace($nrec->getAgency()->getCity());
                    $newstatus->setStep($step);
                    $newstatus->setComment($translator->trans('Warehouse') . ' creado');
                    $nrec->addListstatus($newstatus);
                    $em->persist($newstatus);
                }
                $em->persist($nrec);
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
            $maincompany->setCountwhrecs($countwhrec + 1);
            $maincompany->setCountreceipts($countreceipts);
            if ($maincompany->getFirststatus()) {
                $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                $newstatus = new Statuswhrec();
                $newstatus->setWhrec($entity);
                $newstatus->setDate($entity->getCreationdate());
                $newstatus->setPlace($entity->getAgency()->getCity());
                $newstatus->setStep($step);
                $newstatus->setComment($translator->trans('Warehouse') . ' creado');
                $entity->addListstatus($newstatus);
                $em->persist($newstatus);
            }
            $em->persist($entity);
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
            return $this->redirect($this->generateUrl('whrec_search'));
        } else {
            // exit(\Doctrine\Common\Util\Debug::dump($form['address_sender']->getData())); 
            $flashBag = $this->get('session')->getFlashBag();
            foreach ($flashBag->keys() as $type) {
                        $flashBag->set($type, array());
            }
            $translator = $this->get('translator');
            $message = 'Hay errores en la creación del ' . $translator->trans('Warehouse') . ', por favor, revisar los mensajes de los campos';
            
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
                'nameform' => 'Crear ' . $translator->trans('Warehouse'), 
            );
    }

    /**
     * Creates a form to create a WHrec entity.
     *
     * @param WHrec $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(WHrec $entity)
    {
        $form = $this->createForm(new WHrecType($this->getDoctrine()->getManager(), $this->getUser()), $entity, array(
            'action' => $this->generateUrl('whrec_create'),
            'method' => 'POST',
        ));
	
        $form->add('submit', 'submit', array('label' => 'Crear'));
        $form->add('clock', 'hidden', array('mapped' => false));

        return $form;
    }

    /**
     * Displays a form to create a new WHrec entity.
     *
     * @Route("/new", name="whrec_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new WHrec();
        
        $form   = $this->createCreateForm($entity);
        $translator = $this->get('translator');
        
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'edition' => false,
            'nameform' => 'Crear ' . $translator->trans('Warehouse') ,
        );
    }
    /**
     * Search all Receipt without guides.
     *
     * @Route("/search", name="whrec_search")
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
        if ($agency->getType() == 'MASTER') {
            $entities = $em->getRepository('NvCargaBundle:WHrec')->findBy(array('status' => 'RECIBIDO', 'maincompany' => $maincompany));
            $agencies = $em->getRepository('NvCargaBundle:Agency')->findBy(array('maincompany' => $maincompany));
        } else {
            $entities = $em->getRepository('NvCargaBundle:WHrec')->findBy(array('status' => 'RECIBIDO', 'agency' => $agency));
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
     * Finds and displays a Guide entity.
     *
     * @Route("/{id}/show", name="whrec_show")
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

        $whrec = $em->getRepository('NvCargaBundle:WHrec')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$whrec) {
                throw $this->createNotFoundException('No existe el ' . $translator->trans('Warehouse') );
        }
        if ($user->getAgency()->getType() != 'MASTER') {
            $countryrec = $whrec->getReceiver()->getCity()->getState()->getCountry();
            if (($countryrec == $countryto) || ($whrec->getAgency() == $agency))  {
                $entity = $whrec;
            } else {
                throw $this->createNotFoundException('No tiene permiso para ver este ' . $translator->trans('Warehouse'));
            }
        } else {
            $entity = $whrec;
        }
        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            // 'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Displays a form to edit an existing WHrec entity.
     *
     * @Route("/{id}/edit", name="whrec_edit")
     * @Method("GET")
     * @Template("NvCargaBundle:WHrec:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:WHrec')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$entity) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Warehouse') );
        }
        if ($entity->getGuide()) {
            throw $this->createNotFoundException('No se puede editar un ' .  $translator->trans('Warehouse') . 'que ya tiene ' . $translator->trans('Guía'));
        }
        
        $editForm = $this->createEditForm($entity, true);
        //$deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'form'  => $editForm->createView(),
            'edition' => true,
            'nameform' => 'Editar ' . $translator->trans('Warehouse'), 
            // 'delete_form' => $deleteForm->createView(),
        );
    }
    /**
    * Creates a form to edit a WHrec entity.
    *
    * @param WHrec $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(WHrec $entity, $edition)
    {
        if ($edition) {
            $entity->newpackages();
            foreach ($entity->getReceipts() as $receipt) {
                $pack = new Package();
                $pack->setNumber($receipt->getNumber());
                $pack->setCarrier($receipt->getCarrier());
                $pack->setDescription($receipt->getDescription());
                $pack->setWeight($receipt->getWeight());
                $pack->setLength($receipt->getLength());
                $pack->setWidth($receipt->getWidth());
                $pack->setHeight($receipt->getHeight());
                $pack->setValue($receipt->getValue());
                $pack->setTracking($receipt->getTracking());
                $pack->setNpack($receipt->getNpack());
                $pack->setPacktype($receipt->getPacktype());
                $pack->setArrivedate($receipt->getArrivedate()->format('m/d/Y'));
                $entity->addPackage($pack);
            }
        } 
        $user = $this->getUser();
        
        $form = $this->createForm(new WHrecType($this->getDoctrine()->getManager(),$user), $entity, array(
                'action' => $this->generateUrl('whrec_update', array('id' => $entity->getId())),
                'method' => 'PUT', 
        ));
        $form->add('submit', 'submit', array('label' => 'Actualizar'));
        return $form;
        
    }
    /**
     * Edits an existing WHrec entity.
     *
     * @Route("/{id}/update", name="whrec_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:WHrec:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:WHrec')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$entity) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Warehouse') );
        }
        if ($entity->getGuide()) {
            throw $this->createNotFoundException('No se puede editar un ' .  $translator->trans('Warehouse') . 'que ya tiene ' . $translator->trans('Guía'));
        }
        
        $form = $this->createEditForm($entity, false);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
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
            $entity->setShipper($sender);
            $entity->setReceiver($rebaddr);
            $countreceipt = $maincompany->getCountreceipts();
            $plan = $maincompany->getPlan();
            if (($plan->getReceipts()) && ($countreceipt + count($entity->getPackages()) - count($entity->getReceipts()) >= $plan->getMaxreceipts())) {
                $translator = $this->get('translator');
                $message = 'Ha llegado al número máximo de ' . $translator->trans('Recibos')  .' permitidos. Para crear más debe actualizar su plan a uno superior.';
                if ($this->isGranted('ROLE_ADMIN')) {
                    $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                }
                $flashBag->add('notice',$message);
                goto next;
            }
            $count=1;
            $ncomplete = $entity->getReceipts()[0]->getNumber();
            $npos = strripos($ncomplete, 'P');
            $number = substr($ncomplete, 0, $npos);
            // exit(\Doctrine\Common\Util\Debug::dump($number));
            $lstatus = array();
            $lpos = [];
            $lcount = 0;
            foreach ($entity->getReceipts() as $receipt) {
                $entity->removeReceipt($receipt);
                $alert = $em->getRepository('NvCargaBundle:Alert')->findOneByReceipt($receipt);
                if ($alert) {
                    $alert->setReceipt(null);
                }
                $receipt->getShipper()->removeShipped($receipt);
                foreach ($receipt->getListstatus() as $thestatus) {
                    $thestatus->setReceipt(null);
                }
                $lstatus[] = $receipt->getListstatus();
                $lpos[] = $receipt->getNumber();
                $lcount++;
                $em->remove($receipt); 
                $countreceipt--;
            }
            $em->flush();
            $count= 1;
            $statusrec = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneBy(array('name'=>'Por procesar'));
            foreach ($entity->getPackages() as $key=>$package) { // NUEVOS
                $countreceipt++;
                $alert=null;
                if (!$package->getNumber()) {
                    $package->setNumber($number . 'P' . $count++);
                    $newstatus = true;
                } else {
                    $newstatus = false;
                }
                if ($package->getTracking()) {
                    $listalert = $em->getRepository('NvCargaBundle:Alert')->createQueryBuilder('a')
                            ->where('a.receipt IS NULL')
                            ->andWhere('a.tracking = :track')
                            ->andWhere('a.maincompany = :thecompany')
                            ->setParameters(array('thecompany'=>$maincompany,'track'=>$package->getTracking()))
                            ->getQuery()
                            ->getResult(); 				
                    if ($listalert) {
                        $alert = reset($listalert);
                    }
                } 
                /*
                else {
                    $package->setTracking(' ');
                }
                */
                if ($alert) {
                    $carrier = $alert->getCarrier();
                    $typerec = $em->getRepository('NvCargaBundle:Receipttype')->findOneBy(array('name'=>'Casillero'));
                } else {
                    $carrier = $em->getRepository('NvCargaBundle:Carrier')->findOneBy(array('name'=>'Currier'));
                    $typerec = $em->getRepository('NvCargaBundle:Receipttype')->findOneBy(array('name'=>'Clientes'));
                }
                
                $nrec = new Receipt();
                // $highest_id ++;
                $nrec->setNumber($package->getNumber());
                $nrec->setAgency($entity->getAgency());
                $nrec->setReceiptdBy($entity->getReceiptdBy());
                $nrec->setShipper($entity->getShipper());
                $nrec->setReceiver($entity->getReceiver());
                $nrec->setNote($entity->getNote());
                $nrec->setType($typerec);
                $nrec->setStatus($statusrec);
                // $addr->addReceived($nrec);
                $sender->addShipped($nrec);
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
                if ($newstatus) {
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
                } else {
                    $pos=array_search($nrec->getNumber, $lpos);
                    if ($pos) {
                        foreach ($lstatus[$pos] as $thestatus) {
                            $thestatus->setReceipt($nrec);
                            $nrec->addListstatus($thestatus);
                        }
                        $lpos[$pos] = null;
                    }
                }
                $nrec->setWHrec($entity);
                $entity->addReceipt($nrec);
                
                $em->persist($nrec);
            }
            for ($ii=0;$ii<$lcount;$ii++) {
                if ($lpos[$ii]) {
                    foreach ($lstatus[$ii] as $thestatus) {
                        $em->remove($thestatus); 
                    }
                }
            }
            $em->flush();

            return $this->redirect($this->generateUrl('whrec_show', array('id' => $id)));
        } else {
            $flashBag = $this->get('session')->getFlashBag();
            foreach ($flashBag->keys() as $type) {
                    $flashBag->set($type, array());
            }
            $translator = $this->get('translator');
            
            $message = 'Hay errores en la actualización del ' . $translator->trans('Warehouse') . ', por favor, revisar los mensajes de los campos';
            
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
            'form'  => $form->createView(),
            'edition' => false,
            'nameform' => 'Editar ' . $translator->trans('Warehouse'), 
            // 'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Change status to Bag entity.
     *
     * @Route("/cancel", name="whrec_cancel")
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN')")
     */
    public function cancelAction(Request $request)
    {
        
        $whrec_id = $request->query->get('whrec_id');
        
        $em = $this->getDoctrine()->getManager();
        $agency = $this->getUser()->getAgency();
        $maincompany =  $this->getUser()->getMaincompany();
        $countwhrec = $maincompany->getCountwhrecs();
        $entity = $em->getRepository('NvCargaBundle:WHrec')->findOneBy(['id'=>$whrec_id, 'maincompany'=>$maincompany]);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Warehouse') );
        }
        if ($entity->getStatus() == "PROCESADO") {
            throw $this->createNotFoundException('El ' . $translator->trans('Warehouse') .' '. $entity->getNumber() . ' no se puede anular');
        }
        if ($agency != $entity->getAgency() ) {
            throw $this->createNotFoundException('Esta acción solo se puede hacer en la agencia que creó el ' . $translator->trans('Warehouse'));
        }
        $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Anulado');
        $receipts = $entity->getReceipts();
        foreach ($receipts as $receipt) {
            $receipt->setWhrec(null);
            $receipt->setStatus($status);
            $entity->setStatus('ANULADO');
            $entity->removeReceipt($receipt);
        }
        foreach ($entity->getListstatus() as $thestatus) {
            $thestatus->setWhrec(null);
            $entity->removeListstatus($thestatus);
            $em->remove($thestatus);
        }
        $maincompany->setCountbags($countwhrec--);
        $em->flush();
        return $this->redirect($this->generateUrl('whrec_search'));
    }
    /**
     * Generate a guide label
     * 
     * @Route("/{id}/label", name="whrec_label")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    public function labelAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();

        $whrec = $em->getRepository('NvCargaBundle:WHrec')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$whrec) {
            throw $this->createNotFoundException('No existe el ' . $trsanslator->trans('Warehouse') );
        }
        if ($user->getAgency()->getType() != 'MASTER') {
            $countryrec = $whrec->getReceiver()->getCity()->getState()->getCountry();
            if (($countryrec == $countryto) || ($whrec->getAgency() == $agency))  {
                $entity = $whrec;
            } else {
                throw $this->createNotFoundException('No tiene permiso para imprimir esta ' . $translator->trans('Warehouse'));
            }
        } else {
            $entity = $whrec;
        }
        
        $viewoption = $this->getView("WHrec", "label");
        
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
     * @Route("/{id}/labelpdf", name="whrec_labelpdf")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function labelpdfAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();

        $whrec = $em->getRepository('NvCargaBundle:WHrec')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$whrec) {
            throw $this->createNotFoundException('No existe el ' . $trsanslator->trans('Warehouse') );
        }
        if ($user->getAgency()->getType() != 'MASTER') {
            $countryrec = $whrec->getReceiver()->getCity()->getState()->getCountry();
            if (($countryrec == $countryto) || ($whrec->getAgency() == $agency))  {
                $entity = $whrec;
            } else {
                throw $this->createNotFoundException('No tiene permiso para imprimir esta ' . $translator->trans('Warehouse'));
            }
        } else {
            $entity = $whrec;
        }
        
        $viewoption = $this->getView("WHrec", "label");
        
        //return  $this->render($viewoption,array('entity'=> $entity,'services' => $services)); 
        
        $html = $this->renderView($viewoption, array('entity' => $entity));
        // exit(\Doctrine\Common\Util\Debug::dump($html));
        $number = $entity->getNumber(); 
        $filename = sprintf('labelwhrec-%s.pdf', transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $number));

        $labeldata = $em->getRepository('NvCargaBundle:Labelconf')->findOneBy(array('tableclass' => 'WHrec', 'maincompany'=>$maincompany));
	
        if ($labeldata) {
            $width = $labeldata->getWidth();
            $height = $labeldata->getHeight();
        } else {
            $width = 105;
            $height = 62;
        }
        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
                // 'orientation' => 'landscape', //'portrait',
                'margin-top'    => 4,
                'margin-right'  => 7,
                'margin-bottom' => 4,
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
     * Generate  a guide for printer
     * 
     * @Route("/{id}/print", name="whrec_print")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_RECEIPT') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_RECEIPT')")
     */
    public function printAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();

        $whrec = $em->getRepository('NvCargaBundle:WHrec')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$whrec) {
            throw $this->createNotFoundException('No existe el ' . $trsanslator->trans('Warehouse') );
        }
        if ($user->getAgency()->getType() != 'MASTER') {
            $countryrec = $whrec->getReceiver()->getCity()->getState()->getCountry();
            if (($countryrec == $countryto) || ($whrec->getAgency() == $agency))  {
                $entity = $whrec;
            } else {
                throw $this->createNotFoundException('No tiene permiso para imprimir esta ' . $translator->trans('Warehouse'));
            }
        } else {
            $entity = $whrec;
        }
        
        $viewoption = $this->getView("WHrec", "print");
        
        return  $this->render($viewoption,array('entity'=> $entity)); 
        /*
        return array(
                'entity'      => $entity,
                'services' => $services,);
        */
    }
    /**
     * Export to PDF
     * 
     * @Route("/{id}/printpdf", name="whrec_printpdf")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function printpdfAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();

        $whrec = $em->getRepository('NvCargaBundle:WHrec')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$whrec) {
            throw $this->createNotFoundException('No existe el ' . $trsanslator->trans('Warehouse') );
        }
        if ($user->getAgency()->getType() != 'MASTER') {
            $countryrec = $whrec->getReceiver()->getCity()->getState()->getCountry();
            if (($countryrec == $countryto) || ($whrec->getAgency() == $agency))  {
                $entity = $whrec;
            } else {
                throw $this->createNotFoundException('No tiene permiso para imprimir esta ' . $translator->trans('Warehouse'));
            }
        } else {
            $entity = $whrec;
        }
        
        $viewoption = $this->getView("WHrec", "print");
        
        //return  $this->render($viewoption,array('entity'=> $entity,'services' => $services)); 
        
        $html = $this->renderView($viewoption, array('entity' => $entity));
        // exit(\Doctrine\Common\Util\Debug::dump($html));
        $number = $entity->getNumber(); 
        $filename = sprintf('whrec-%s.pdf', transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $number));

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
    public function getView($table, $type) {
        $option = null;
        $maincompany = $this->getUser()->getMaincompany();
        //exit(\Doctrine\Common\Util\Debug::dump($maincompany->getFormat()));
        if ($maincompany->getFormat()) {
            $option = $maincompany->getFormat()->getWHlprint();
        }
        $viewoption = 'NvCargaBundle:' . $table . ':' . $type . $option . '.html.twig';
        if (!$this->get('templating')->exists($viewoption) ) {
            $viewoption = 'NvCargaBundle:' . $table . ':' . $type . '.html.twig';
        }
        return $viewoption;
    }
    /**
     * Lists all Receipt entities in warehouse that aren't in transit
     *
     * @Route("/{id}/pbindex", name="whrec_pbindex")
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
        
        $entities= $em->getRepository('NvCargaBundle:WHrec')->createQueryBuilder('r')
                    ->where('r.receiver IN (:receiver)')
                    ->andwhere('r.status != :status')
                    ->setParameters(array('receiver'=> $pobox->getCustomer()->getBaddress(), 
                            'status' => 'ANULADO'))
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
}
