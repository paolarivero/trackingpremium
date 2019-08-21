<?php

namespace NvCarga\Bundle\Controller;

use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Guide;
use NvCarga\Bundle\Entity\Customer;
use NvCarga\Bundle\Entity\Bill;
use NvCarga\Bundle\Entity\Alert;
use NvCarga\Bundle\Entity\Servicetype;
use NvCarga\Bundle\Entity\Tariff;
use NvCarga\Bundle\Entity\Receipttype;
use NvCarga\Bundle\Entity\Receipt;
use NvCarga\Bundle\Entity\Moveguides;
use NvCarga\Bundle\Entity\Bag;
use NvCarga\Bundle\Entity\Minipack;
use NvCarga\Bundle\Entity\ClassServ;
use NvCarga\Bundle\Entity\Servtoguide;
use NvCarga\Bundle\Entity\Baddress;
use NvCarga\Bundle\Entity\Message;
use NvCarga\Bundle\Entity\City;
use NvCarga\Bundle\Entity\Statusreceipt;
use NvCarga\Bundle\Entity\Statusguide;


use NvCarga\Bundle\Form\GuideType;
use NvCarga\Bundle\Form\MinipackType;
use NvCarga\Bundle\Form\ClassServType;

use NvCarga\Bundle\Utility\Changechar;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Doctrine\ORM\EntityRepository;
/**
 * Guide controller.
 *
 * @Route("/guide")
 */
class GuideController extends Controller
{

    /**
     * Lists all Guide entities.
     *
     * @Route("/index", name="guide")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();
        $theagen = null;
        $thecod = null;
        $thepaid = null;
        $theser = null;
        $date1 = null;
        $date2 = null;
        /* $filters['-- Seleccione el filtro --'] = "NOT";
        $filters['Tipo de pago'] = "COD";
        $filters['Forma de pago'] = "Paidtype";
        $filters['Servicio'] = "Shippingtype";
        $filters['Agencia'] = 'Agency'; */
        $today = new \DateTime();
        $today->modify('+59 minutes');
        $today->modify('+23 hours');

        $before = new \DateTime();
        $before->modify('-6 months');
        $guides = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                ->where('g.creationdate BETWEEN :dateini AND :dateend')
                ->andWhere('g.maincompany =:maincompany')
                ->setParameters(['dateini'=>$before, 'dateend'=>$today, 'maincompany'=>$maincompany])
                ->getQuery()
                ->getResult();
    
        //$guides = $em->getRepository('NvCargaBundle:Guide')->findByMaincompany($maincompany);

        if ($agency->getType() == 'MASTER') {
            $entities = $guides;
            $agencies =  $em->getRepository('NvCargaBundle:Agency')->findByMaincompany($maincompany);

        } else {
            $entities = array();
            foreach ($guides as $guide) {
                $countryrec = $guide->getAddressee()->getCity()->getState()->getCountry();
                if (($countryrec == $countryto) || ($guide->getAgency() == $agency))  {
                    $entities[] = $guide;

                }
            }
            $agencies = [];
        }
        $form = $this->createFilterForm($theagen, $thecod, $thepaid, $theser, $date1, $date2);
        $status = array();
        $oldstatus = $em->getRepository('NvCargaBundle:Guidestatus')->findAll();
        foreach ($oldstatus as $estado) {

            $status[] = $estado->getName();

        }
        $estados =  $em->getRepository('NvCargaBundle:Stepstatus')->findByMaincompany($maincompany);
        foreach ($estados as $estado) {
            if (!in_array($estado->getName(), $status)) {

                $status[] = $estado->getName();


            }
        }
        sort($status);
        return array(
                'entities' => $entities,
                'form'   => $form->createView(),
                'agencies' => $agencies,
                'status' => $status,
                // 'filters' => $filters,
            );
    }
   /**
     * Lists all Receipt entities in warehouse that aren't in transit
     *
     * @Route("/{id}/pbindex", name="guide_pbindex")
     * @Template()
     */
    public function pbindexAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $pobox = $em->getRepository('NvCargaBundle:Pobox')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        if (!$pobox) {
            throw $this->createNotFoundException('No existe el casillero');
        }
        $userpobox = $pobox->getUser();
        if ($userpobox != $user ) {
            throw $this->createNotFoundException('No tiene permiso para ver este casillero');
        }

        $entities= $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                    ->where('g.addressee in (:addressee) OR g.sender =:customer')
                    ->setParameters(array('addressee'=> $pobox->getCustomer()->getBaddress(), 'customer'=>$pobox->getCustomer()))
                    ->orderBy('g.number', 'ASC')
                    ->getQuery()
                    ->getResult();
        /*
        $transit = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName('En tránsito');
        foreach ($entities as $key => $guide) {
            $lastpos = $guide->getMoves()->last()->getStatus()->getPosition();
            if ($lastpos < $transit->getPosition())  {
                unset($entities[$key]);
            }
        }
        */
        return array(
	    'entity' => $pobox,
            'entities' => $entities,
        );
    }
    /**
     * Lists all Guide entities.
     *
     * @Route("/list", name="guide_list")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();
        $agencies = null;
        if ($agency->getType() == "MASTER") {
            $entities = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                    ->where('g.consol IS NULL')
                    ->andwhere('g.bag IS NULL')
                    ->andwhere('g.maincompany = :themaincompany')
                    ->setParameters(array('themaincompany'=>$maincompany))
                    ->orderBy('g.number', 'ASC')
                    ->getQuery()
                    ->getResult();
            $agencies = $em->getRepository('NvCargaBundle:Agency')->findByMaincompany($maincompany);
        } else {
            throw $this->createNotFoundException('Solamente la Agencia Principal (MASTER) puede consolidar guías');
        }
        return array(
                'entities' => $entities,
                'agencies' => $agencies,
        );
    }

    /**
     * Creates a new Guide entity.
     *
     * @Route("/create", name="guide_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Guide:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Guide();

        $entity->newservices();
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $maincompany = $user->getMaincompany();

        $services = $em->getRepository('NvCargaBundle:Adservice')->findBy(['isactive'=>true, 'maincompany'=>$maincompany]);
        // exit(\Doctrine\Common\Util\Debug::dump($services));
        foreach ($services as $service) {
            $serv = new ClassServ();
            $serv->setId($service->getId());
            $serv->setName($service->getName());
            $serv->setDescription($service->getDescription());
            $serv->setMeasure($service->getMeasure());
            $serv->setPrice($service->getPrice());
            $serv->setAmount(0);
            $serv->setTotal(0);
            $entity->addService($serv);
        }
        $translator = $this->get('translator');
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        //exit(\Doctrine\Common\Util\Debug::dump($entity->getFile()));

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $movdate = new \DateTime();
            $clock = $form['clock']->getData();
            $rdate = substr($movdate->format('m/d/Y'),0,10) . 'T' . $clock;
            $entity->setCreationdate(new \DateTime($rdate));

            $entity->upload();
            $tid = $form['tariffid']->getData();
            $tariff= $em->getRepository('NvCargaBundle:Tariff')->find($tid);
            $service=$tariff->getShippingtype();
            $entity->setConsol(null);
            $entity->setShippingtype($service);
            $entity->setTariff($tariff);
            $entity->setProcessedby($this->getUser());

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

            $entity->setSender($sender);
            $sender->addSguide($entity);
            $entity->setAddressee($rebaddr);

            $agcod = str_pad($entity->getAgency()->getId(), 3, '0', STR_PAD_LEFT);

            $packages = $entity->getPackages();

            $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneBy(array('name' =>'Procesado'));

            $countreceipt = $maincompany->getCountreceipts();
            $highest_id = $em->getRepository('NvCargaBundle:Receipt')->createQueryBuilder('r')
                            ->select('MAX(r.id)')
                            ->where('r.maincompany =:thecompany')
                            ->setParameters(['thecompany'=>$maincompany])
                            ->getQuery()
                            ->getSingleScalarResult();
            $highest_id++;
            $count = 1;
            foreach ($packages as $key=>$package) { // NUEVO
                $countreceipt++;
                $alert=null;
                if ($package->getTracking()) {
                    $listalert = $em->getRepository('NvCargaBundle:Alert')->createQueryBuilder('a')
                            ->where('a.receipt IS NULL')
                            ->andWhere('a.tracking = :track')
                            ->andWhere('a.maincompany =:thecompany')
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
                $plan = $maincompany->getPlan();
                if (($plan->getReceipts()) && ($countreceipt >= $plan->getMaxreceipts())) {
                    $translator = $this->get('translator');
                    $message = 'Ha llegado al número máximo de ' . $translator->trans('Recibos')  .' permitidos. Para crear más debe actualizar su plan a uno superior.';
                    if ($this->isGranted('ROLE_ADMIN')) {
                        $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                    }
                    $flashBag->add('notice',$message);
                    goto next;
                }
                $receipt = new Receipt();
                $receipt->setShipper($sender);
                $receipt->setReceiver($rebaddr);
                $receipt->setType($typerec);
                $receipt->setReceiptdBy($this->getUser());

                $comcod = str_pad($maincompany->getId(), 2, '0', STR_PAD_LEFT);
                $number = "PKG" . $comcod . $highest_id . 'P' . $count++;
                // $number = "PKG" . $maincompany->getAcronym() . '-' . $highest_id . '-P' . $count++;
                $receipt->setNumber($number);
                $receipt->setCreationdate($entity->getCreationdate());
                $receipt->setArrivedate($entity->getCreationdate());
                $receipt->setTracking($package->getTracking()); // OJO el TRACKING DEL RECIBO
                $translator = $this->get('translator');
                $receipt->setReference('Creado con ' . $translator->trans('Guía'));
                $receipt->setDescription($entity->getContain());
                $receipt->setStatus($status);
                $receipt->setCarrier($carrier);
                $receipt->setAgency($entity->getAgency());
                $receipt->setQuantity(1);
                $receipt->setWeight($package->getWeight());
                $receipt->setLength($package->getLength());
                $receipt->setWidth($package->getWidth());
                $receipt->setHeight($package->getHeight());
                $receipt->setValue($package->getValue());
                $receipt->setGuide($entity);
                if (!$package->getNpack())  {
                    $receipt->setNpack(1);
                } else {
                    $receipt->setNpack($package->getNpack());
                }
                $receipt->setPacktype($package->getPacktype());
                $receipt->setMaincompany($maincompany);
                if ($maincompany->getFirststatus()) {
                    $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                    $newstatus = new Statusreceipt();
                    $newstatus->setReceipt($receipt);
                    $newstatus->setDate($receipt->getCreationdate());
                    $newstatus->setPlace($receipt->getAgency()->getCity());
                    $newstatus->setStep($step);
                    $newstatus->setComment($translator->trans('Recibo') . ' creado con la ' . $translator->trans('Guía') );
                    $receipt->addListstatus($newstatus);
                    $em->persist($newstatus);
                }
                $em->persist($receipt);
                $entity->addReceipt($receipt);
                $sender->addShipped($receipt);
                // $addr->addReceived($receipt);
                if ($alert) {
                    $alert->setReceipt($receipt);
                }
            }// FIN DE AGREGAR RECIBO(S)
            $maincompany->setCountreceipts($countreceipt);
            // Asignando los paises origen y destino
            $entity->setCountryto($entity->getAddressee()->getCity()->getState()->getCountry());
            $entity->setCountryfrom($entity->getAgency()->getCity()->getState()->getCountry());

            // CREAR EL PRIMER MOVIMIENTO DE LA GUIA
            $maincompany = $this->getUser()->getMaincompany();
            $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
            $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');
            $translator = $this->get('translator');

            /*
            // ELIMINADA LA CREACION DEL PRIMER MOVIMIENTO PARA LA GUIA
            $moveguide = new Moveguides($this->get('translator'), $this->get('mailer'), $setfrom, $fromname, $em, $this->getUser());
            $moveguide->setMovdate(new \DateTime());
            $moveguide->setGuide($entity);
            $agentype = $entity->getAgency()->getType()->getName();
            $stamov = null;
            if ($agentype == 'MASTER' ) {
                    $stamov = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName('En Master');
            } else {
                    $stamov = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName('En Sucursal');
            }
            if (!$stamov) {
                $stamov = $em->getRepository('NvCargaBundle:Guidestatus')->find(1);
            }
            $moveguide->setStatus($stamov);
            $company = $em->getRepository('NvCargaBundle:Localcompany')->findOneByName('Empresa');
            $moveguide->setCompany($company);
            $moveguide->setTrack($entity->getNumber());
            $moveguide->setPercentage(0);
            $moveguide->setComment('Registro de '. $translator->trans('Guía'). ', debe incluirse en '. $translator->trans('Consolidado'));
            $entity->addMove($moveguide);
            $em->persist($moveguide);
            */

            $adservs = $entity->getServices();
            foreach ($adservs as $adserv) {
                if ($adserv->getAmount() > 0) {
                    $thiserv = $em->getRepository('NvCargaBundle:Adservice')->find($adserv->getId());
                    $servtoguide = new Servtoguide();
                    $servtoguide->setServicedate(new \DateTime());
                    $servtoguide->setAmount($adserv->getAmount());
                    $servtoguide->setTotal($adserv->getTotal());
                    $servtoguide->setAdservice($thiserv);
                    $servtoguide->setGuide($entity);
                    $em->persist($servtoguide);
                }
            }
            $cod = $form['cod']->getData();
            $paidtype = $form['paidtype']->getData();

	    	$numini= $entity->getAgency()->getMaincompany()->getIniguide();

            $countguide = $maincompany->getCountguides();
            $plan = $maincompany->getPlan();
            if (($plan->getGuides()) && ($countguide >= $plan->getMaxguides())) {
                    $translator = $this->get('translator');
                    $message = 'Ha llegado al número máximo de ' . $translator->trans('Guías')  .' permitidas. Para crear más debe actualizar su plan a uno superior.';
                    if ($this->isGranted('ROLE_ADMIN')) {
                        $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                    }
                    $flashBag->add('notice',$message);
                    goto next;
            }
            $number = $this->genNumber($entity);
            $entity->setNumber($number);
            $countguide++;
            $maincompany->setCountguides($countguide);
            if ($entity->getTariff()->getMeasure()->getName() == 'Lb' ) {
                $entity->setRoundmeasure($entity->getAgency()->getMaincompany()->getRoundweight());
            } else {
                $entity->setRoundmeasure($entity->getAgency()->getMaincompany()->getRoundvol());
            }
            $entity->setMaincompany($maincompany);
            if ($maincompany->getFirststatus()) {
                $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                $newstatus = new Statusguide();
                $newstatus->setGuide($entity);
                $newstatus->setDate($entity->getCreationdate());
                $newstatus->setPlace($entity->getAgency()->getCity());
                $newstatus->setStep($step);
                $newstatus->setComment($translator->trans('Guía') . ' creada');
                $entity->addListstatus($newstatus);
                $em->persist($newstatus);
            }
            $em->persist($entity);
            $em->flush();
            if ($entity->getEmailnoti()) {
                $this->sendemail($entity);
            }
            return $this->redirect($this->generateUrl('guide_show', array('id' => $entity->getId())));
        } else {
            // exit(\Doctrine\Common\Util\Debug::dump($form));
            $message = 'Hay errores en la creación de la' . $translator->trans('Guía') . ', por favor, revisar los mensajes de los campos. ';

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
            'services' => $services,
            'tariffs'=> null,
            'image'=>null,
            'edition' => false,
            'nameform' => 'Crear ' . $translator->trans('Guía'),
        );
    }

    /**
     * Creates a form to create a Guide entity.
     *
     * @param Guide $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Guide $entity)
    {

        $form = $this->createForm(new GuideType($this->getDoctrine()->getManager(), $this->getUser()), $entity, array(
                'action' => $this->generateUrl('guide_create'),
                'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Crear'));
        $user = $this->getUser();
        //$roles = $user->getRoles();
        $entity->setAgency($user->getAgency());
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN_MULTIAGENCY')) {
        //if (!(in_array('ROLE_ADMIN_MULTIAGENCY', $roles))) {
            $form->remove('agency');
            $form->add('agency', 'entity', array('label' => 'Agencia ', 'class' => 'NvCargaBundle:Agency',
                            'data' => $user->getAgency(), 'read_only'  => true, 'disabled' => true)) ;
        }
        if (count($entity->getServices()) > 0) {
            $form->add('services', 'collection', array('mapped' => true,
                'required' => false, 'label' => false, 'type' => new ClassServType(),'allow_add' => false,
                'allow_delete' => false, 'by_reference' => false, 'prototype' => true, 'error_bubbling' => false,
                'attr' => array('class' => 'list-services'),'options' => array('label' => false),
            ));
        }
        return $form;
    }

    /**
     * Displays a form to create a new Guide entity.
     *
     * @Route("/new", name="guide_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Guide();

        $entity->newservices();
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $maincompany = $user->getMaincompany();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }

        $services = $em->getRepository('NvCargaBundle:Adservice')->findBy(['isactive'=>true, 'maincompany'=>$maincompany]);
        // exit(\Doctrine\Common\Util\Debug::dump($services));
        foreach ($services as $service) {
            $serv = new ClassServ();
            $serv->setId($service->getId());
            $serv->setName($service->getName());
            $serv->setDescription($service->getDescription());
            $serv->setMeasure($service->getMeasure());
            $serv->setPrice($service->getPrice());
            $serv->setAmount(0);
            $serv->setTotal(0);
            $entity->addService($serv);
        }
        $form   = $this->createCreateForm($entity);
        $translator = $this->get('translator');
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'services' => $services,
            'tariffs'=> null,
            'image'=>null,
            'edition' => false,
            'nameform' => 'Crear ' . $translator->trans('Guía'),
        );
    }

    /**
     * Finds and displays a Guide entity.
     *
     * @Route("/{id}/show", name="guide_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();

        $maincompany = $user->getMaincompany();
        $guide = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$guide) {
            throw $this->createNotFoundException('No existe ' . $translator->trans('Guía'));
        }
        if ($user->getAgency()->getType() != 'MASTER') {
            $countryrec = $guide->getAddressee()->getCity()->getState()->getCountry();
            if (($countryrec == $countryto) || ($guide->getAgency() == $agency))  {
                $entity = $guide;
            } else {
                throw $this->createNotFoundException('No tiene permiso para ver esta ' . $translator->trans('Guía'));
            }
        } else {
            $entity = $guide;
        }


        $image = null;
        if ($entity->getImageSize() > 0) {
            $image = base64_encode(stream_get_contents($entity->getImageData()));
        }
        $services = $em->getRepository('NvCargaBundle:Servtoguide')->findByGuide($entity);
        $status = $em->getRepository('NvCargaBundle:Guidestatus')->findBy(array(), array('position' => 'ASC'));
        /*
        foreach ($entity->getMoves() as $move) {
            if ($move->getId() == 14090) {
                $entity->removeMove($move);
                $move->setGuide(null);
                $em->remove($move);
            }
        }
        $em->flush();
        */
        return array(
            'entity'      => $entity,
            'image' => $image,
            'services' => $services,
            'allstatus' => $status,
        );
    }

    /**
     * Displays a form to edit an existing Guide entity.
     *
     * @Route("/{id}/edit", name="guide_edit")
     * @Method("GET")
     * @Template("NvCargaBundle:Guide:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $user->getAgency();

        $maincompany = $user->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        $translator = $this->get('translator');

        if (!$entity) {
            throw $this->createNotFoundException('No existe ' . $translator->trans('Guía'));
        }
        if (($agency != $entity->getAgency()) && ($agency->getType() != "MASTER" )){
            throw $this->createNotFoundException('No puede editar ' .  $translator->trans('Guía') . ' de otra agencia');
        }

        $consol = $entity->getConsol();
        $bill = $entity->getBill();
        if ($bill) {
            throw $this->createNotFoundException('No se puede editar ' . $translator->trans('Guía') . ' con factura');
        }
        /*
        if ($consol) {
            throw $this->createNotFoundException('No se puede editar ' . $translator->trans('Guía') . ' que ya tiene '.  $translator->trans('Consolidado'));
        }
        */
        $entity->newservices();
        $services = $em->getRepository('NvCargaBundle:Adservice')->findBy(['isactive'=>true, 'maincompany'=>$maincompany]);
        // exit(\Doctrine\Common\Util\Debug::dump($services));
        foreach ($services as $service) {
            $servtoguide = $em->getRepository('NvCargaBundle:Servtoguide')->findOneBy(array('guide'=>$entity, 'adservice' => $service));
            $serv = new ClassServ();
            $serv->setId($service->getId());
            $serv->setName($service->getName());
            $serv->setDescription($service->getDescription());
            $serv->setMeasure($service->getMeasure());
            $serv->setPrice($service->getPrice());
            if ($servtoguide) {
                $serv->setPrice($servtoguide->getTotal()/$servtoguide->getAmount());
                $serv->setAmount($servtoguide->getAmount());
                $serv->setTotal($servtoguide->getTotal());
            } else {
                $serv->setAmount(0);
                $serv->setTotal(0);
            }
            $entity->addService($serv);
        }
        $image = null;
        if ($entity->getImageSize() > 0) {
            $image = base64_encode(stream_get_contents($entity->getImageData()));
        }
       	$editForm = $this->createEditForm($entity, true);
        return array(
        		'entity' => $entity,
                'form'   => $editForm->createView(),
                'image' => $image,
                'services' => $services,
                'tariffs' => null,
                'edition' => true,
                'nameform' => 'Editar ' . $translator->trans('Guía') . ' ' . $entity->getNumber(),
        );
    }

    /**
    * Creates a form to edit a Guide entity.
    *
    * @param Guide $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Guide $entity, $edition)
    {
        if ($edition) {
            $entity->newpackages();
            if ($entity->getMasterec()) {
                $receipt = $entity->getMasterec();
                $pack = new Minipack();
                $pack->setNumber($receipt->getNumber());
                $pack->setWeight($receipt->getWeight());
                $pack->setLength($receipt->getLength());
                $pack->setWidth($receipt->getWidth());
                $pack->setHeight($receipt->getHeight());
                $pack->setValue($receipt->getValue());
                $pack->setTracking($receipt->getTracking());
                $pack->setNpack($receipt->getNpack());
                $pack->setPacktype($receipt->getPacktype());
                $entity->addPackage($pack);
                $delpack = false;
                $addpack = false;
                $receipt->getShipper()->removeShipped($receipt);
            } else  {
                foreach ($entity->getReceipts() as $receipt) {
                    $pack = new Minipack();
                    $pack->setNumber($receipt->getNumber());
                    $pack->setWeight($receipt->getWeight());
                    $pack->setLength($receipt->getLength());
                    $pack->setWidth($receipt->getWidth());
                    $pack->setHeight($receipt->getHeight());
                    $pack->setValue($receipt->getValue());
                    $pack->setTracking($receipt->getTracking());
                    $pack->setNpack($receipt->getNpack());
                    $pack->setPacktype($receipt->getPacktype());
                    $entity->addPackage($pack);
                }
                $delpack = true;
                $addpack = true;
            }
        } else {
            if ($entity->getMasterec()) {
                $delpack = false;
                $addpack = false;
            } else  {
                $delpack = true;
                $addpack = true;
            }
        }
        $user = $this->getUser();

        $form = $this->createForm(new GuideType($this->getDoctrine()->getManager(),$user), $entity, array(
                'action' => $this->generateUrl('guide_update', array('id' => $entity->getId())),
                'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));
        $form->remove('agency');
        $form->remove('packages');
        $form->add('agency', 'entity', array('label' => 'Agencia ', 'class' => 'NvCargaBundle:Agency',
                            'data' => $user->getAgency(), 'read_only'  => true, 'disabled' => true)) ;
        $form->add('packages', 'collection', array('mapped'=>true, 'required' => false, 'label' => false,
                            'type' => new MinipackType(), 'allow_add' => $addpack, 'allow_delete' => $delpack,
                            'by_reference' => false, 'prototype' => true, 'error_bubbling' => false,
                            'attr' => array('class' => 'list-packages', 'TABINDEX' => 18), 'options' => array('label' => false)));
        if (count($entity->getServices()) > 0) {
            $form->add('services', 'collection', array('mapped' => true,
                    'required' => false, 'label' => false, 'type' => new ClassServType(),'allow_add' => false,
                'allow_delete' => false, 'by_reference' => false, 'prototype' => true, 'error_bubbling' => false,
                'attr' => array('class' => 'list-services'),'options' => array('label' => false),
                ));
        }
        return $form;

    }

    /**
     * Edits an existing Guide entity.
     *
     * @Route("/{id}/update", name="guide_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Guide:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $agency = $user->getAgency();
        $maincompany = $user->getMaincompany();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $entity = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);
        $translator = $this->get('translator');


        if (!$entity) {
            throw $this->createNotFoundException('No existe ' . $translator->trans('Guía'));
        }
        if (($agency != $entity->getAgency()) && ($agency->getType() != "MASTER" )){
            throw $this->createNotFoundException('No puede editar ' . $translator->trans('Guía') . ' de otra agencia');
        }

        $consol = $entity->getConsol();
        /*
        if ($consol) {
            throw $this->createNotFoundException('No se puede editar ' . $translator->trans('Guía') . ' que ya tiene ' . $translator->trans('Consolidado'));
        }
        */
        $bill = $entity->getBill();
        if ($bill) {
            throw $this->createNotFoundException('No se puede editar ' . $translator->trans('Guía') . ' con factura');
        }
        $image = null;
        if ($entity->getImageSize() > 0) {
            $image = base64_encode(stream_get_contents($entity->getImageData()));
        }
        $services = $em->getRepository('NvCargaBundle:Adservice')->findBy(['isactive'=>true,'maincompany'=>$maincompany]);
        // exit(\Doctrine\Common\Util\Debug::dump($services));
        foreach ($services as $service) {
            $servtoguide = $em->getRepository('NvCargaBundle:Servtoguide')->findOneBy(array('guide'=>$entity, 'adservice' => $service));
            $serv = new ClassServ();
            $serv->setId($service->getId());
            $serv->setName($service->getName());
            $serv->setDescription($service->getDescription());
            $serv->setMeasure($service->getMeasure());
            $serv->setPrice($service->getPrice());
            if ($servtoguide) {
                $serv->setPrice($servtoguide->getTotal()/$servtoguide->getAmount());
                $serv->setAmount($servtoguide->getAmount());
                $serv->setTotal($servtoguide->getTotal());
            } else {
                $serv->setAmount(0);
                $serv->setTotal(0);
            }
            $entity->addService($serv);
        }
        $editForm = $this->createEditForm($entity, false);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $form = $editForm;
            $entity->upload();
            $tid =  $editForm['tariffid']->getData();
            $tariff= $em->getRepository('NvCargaBundle:Tariff')->find($tid);
            $service=$tariff->getShippingtype();
            $entity->setConsol(null);
            $entity->setShippingtype($service);
            $entity->setTariff($tariff);
            $entity->setProcessedby($this->getUser());

            $addcus = $this->forward('NvCargaBundle:Customer:addCustomer', array('form'=>$editForm, 'entity'=> $entity));
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

            $entity->getSender()->removeSguide($entity);
            $entity->setSender($sender);
            $sender->addSguide($entity);

            // $entity->getAddressee()->removeRguide($entity);
            $entity->setAddressee($rebaddr);
            // $addr->addRguide($entity);

            $packages = $entity->getPackages();
            $countreceipt =  $maincompany->getCountreceipts();
            $diff = count($packages) - count($entity->getReceipts());
            $plan = $maincompany->getPlan();
            if (($plan->getReceipts()) && (($countreceipt + $diff) >= $plan->getMaxreceipts())) {
                $translator = $this->get('translator');
                $message = 'Ha llegado al número máximo de ' . $translator->trans('Recibos')  .' permitidos. Para crear más debe actualizar su plan a uno superior.';
                if ($this->isGranted('ROLE_ADMIN')) {
                    $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                }
                $flashBag->add('notice',$message);
                goto next;
            }
            $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneBy(array('name' =>'Procesado'));

            $count = count($entity->getReceipts()) + 1;
            $number = $entity->getReceipts()->first()->getNumber();
            $pos = strrpos($number, 'P');
            if ($pos !== false) {
                $number = substr($number, 0, $pos);
            }
            $lcount = 0;
            if ($entity->getMasterec()) {
                $package = $packages[0];
                $receipt = $entity->getMasterec();
                $receipt->setShipper($sender);
                $receipt->setReceiver($rebaddr);
                $receipt->setDescription($entity->getContain());
                $receipt->setWeight($package->getWeight());
                $receipt->setLength($package->getLength());
                $receipt->setWidth($package->getWidth());
                $receipt->setHeight($package->getHeight());
                $receipt->setValue($package->getValue());
                if (!$package->getNpack())  {
                    $receipt->setNpack(1);
                } else {
                    $receipt->setNpack($package->getNpack());
                }
                $receipt->setPacktype($package->getPacktype());
                $sender->addShipped($receipt);
            } else {
                $lstatus = array();
                $lpos = [];

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
                $count=1;
                $agcod = str_pad($entity->getAgency()->getId(), 3, '0', STR_PAD_LEFT);
                foreach ($packages as $key=>$package) { // NUEVOS
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
//                     $countreceipt =  $em->getRepository('NvCargaBundle:Receipt')->createQueryBuilder('r')
//                             ->select('count(r.id)')
//                             ->where('r.maincompany =:thecompany')
//                             ->setParameters(['thecompany'=>$maincompany])
//                             ->getQuery()
//                             ->getSingleScalarResult();
                    $plan = $maincompany->getPlan();
                    if (($plan->getReceipts()) && ($countreceipt >= $plan->getMaxreceipts())) {
                        $translator = $this->get('translator');
                        $message = 'Ha llegado al número máximo de ' . $translator->trans('Recibos')  .' permitidos. Para crear más debe actualizar su plan a uno superior.';
                        if ($this->isGranted('ROLE_ADMIN')) {
                            $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                        }
                        $flashBag->add('notice',$message);
                        goto next;
                    }
                    $receipt = new Receipt();
                    $receipt->setShipper($sender);
                    $receipt->setReceiver($rebaddr);
                    $receipt->setType($typerec);
                    $receipt->setReceiptdBy($this->getUser());
                    // $highest_id ++;
                    // $number = "PKG" . $agcod . $highest_id . '-P' . $count++;
                    $receipt->setNumber($package->getNumber());
                    $receipt->setCreationdate($entity->getCreationdate());
                    $receipt->setArrivedate($entity->getCreationdate());
                    $receipt->setTracking($package->getTracking()); // OJO el TRACKING DEL RECIBO
                    $translator = $this->get('translator');

                    $receipt->setReference('Creado con ' . $translator->trans('Guía'));
                    $receipt->setDescription($entity->getContain());
                    $receipt->setStatus($status);
                    $receipt->setCarrier($carrier);
                    $receipt->setAgency($entity->getAgency());
                    $receipt->setQuantity(1);
                    $receipt->setWeight($package->getWeight());
                    $receipt->setLength($package->getLength());
                    $receipt->setWidth($package->getWidth());
                    $receipt->setHeight($package->getHeight());
                    $receipt->setValue($package->getValue());
                    if (!$package->getNpack())  {
                        $receipt->setNpack(1);
                    } else {
                        $receipt->setNpack($package->getNpack());
                    }
                    $receipt->setPacktype($package->getPacktype());
                    $receipt->setGuide($entity);
                    $receipt->setMaincompany($maincompany);
                    if ($newstatus) {
                        if ($maincompany->getFirststatus()) {
                            $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                            $newstatus = new Statusreceipt();
                            $newstatus->setReceipt($receipt);
                            $newstatus->setDate($receipt->getCreationdate());
                            $newstatus->setPlace($receipt->getAgency()->getCity());
                            $newstatus->setStep($step);
                            $newstatus->setComment($translator->trans('Recibo') . ' creado');
                            $receipt->addListstatus($newstatus);
                            $em->persist($newstatus);
                         }
                    } else {
                        $pos=array_search($receipt->getNumber, $lpos);
                        if ($pos) {
                            foreach ($lstatus[$pos] as $thestatus) {
                                $thestatus->setReceipt($receipt);
                                $receipt->addListstatus($thestatus);
                            }
                            $lpos[$pos] = null;
                        }
                    }

                    $em->persist($receipt);
                    $entity->addReceipt($receipt);
                    $sender->addShipped($receipt);
                    // $addr->addReceived($receipt);
                    if ($alert) {
                        $alert->setReceipt($receipt);
                    }
                }
            } // FIN DE AGREGAR RECIBO(S)
            for ($ii=0;$ii<$lcount;$ii++) {
                if ($lpos[$ii]) {
                    foreach ($lstatus[$ii] as $thestatus) {
                        $em->remove($thestatus);
                    }
                }
            }

            // Asignando los paises origen y destino
            $entity->setCountryto($entity->getAddressee()->getCity()->getState()->getCountry());
            $entity->setCountryfrom($entity->getAgency()->getCity()->getState()->getCountry());
            /*
            if (($entity->getCod()->getName() == "C.O.D") && ($entity->getBill() != null)) {// eliminar la factura
                $bill=$entity->getBill();
                $bill->setGuide(null);
                $bill->removeGuide($entity);
                $entity->setBill(null);
                if ($bill->getGuides()->count() == 0) {
                    $em->remove($bill);
                }
	    	}
	    	*/

            $adservs = $entity->getServices();
            foreach ($adservs as $adserv) {
                $currentserv = $em->getRepository('NvCargaBundle:Servtoguide')->findOneBy(array('adservice' => $adserv->getId(), 'guide' => $entity->getId()));
                if ($currentserv) {
                    if ($adserv->getAmount() > 0) {
                        $currentserv->setAmount($adserv->getAmount());
                        $currentserv->setTotal($adserv->getTotal());
                    } else {
                        $em->remove($currentserv);
                    }
                } else {
                    if ($adserv->getAmount() > 0) {
                        $thiserv = $em->getRepository('NvCargaBundle:Adservice')->find($adserv->getId());
                        $servtoguide = new Servtoguide();
                        $servtoguide->setServicedate(new \DateTime());
                        $servtoguide->setAmount($adserv->getAmount());
                        $servtoguide->setTotal($adserv->getTotal());
                        $servtoguide->setAdservice($thiserv);
                        $servtoguide->setGuide($entity);
                        $em->persist($servtoguide);
                    }
                }
            }
            if ($entity->getTariff()->getMeasure()->getName() == 'Lb' ) {
                $entity->setRoundmeasure($entity->getAgency()->getMaincompany()->getRoundweight());
            } else {
                $entity->setRoundmeasure($entity->getAgency()->getMaincompany()->getRoundvol());
            }
            $maincompany->setCountreceipts($countreceipt);
            $em->flush();

            return $this->redirect($this->generateUrl('guide_show', array('id' => $entity->getId())));

        } else {
            $flashBag = $this->get('session')->getFlashBag();
            foreach ($flashBag->keys() as $type) {
                    $flashBag->set($type, array());
            }
            $message = 'Hay errores en la actualización de la ' . $translator->trans('Guía') . ', por favor, revisar los mensajes de los campos. ';

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
        $image = null;
        if ($entity->getImageSize() > 0) {
                $image = base64_encode(stream_get_contents($entity->getImageData()));
        }
        next:
            return array(
                'entity'      => $entity,
                'form'   => $editForm->createView(),
                'image' => $image,
                'services' => $services,
                'tariffs' => null,
                'edition' => false,
                'nameform' => 'Editar ' . $translator->trans('Guía') . ' ' . $entity->getNumber(),
            );
    }

    /**
     * Drop a guide entity.
     *
     * @Route("/{id}/drop", name="guide_drop")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function dropAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMainCompany();
        $entity = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        $translator = $this->get('translator');

        if (!$entity) {
            throw $this->createNotFoundException('No existe ' . $translator->trans('Guía'));
        }

        if ($entity->getConsol()) {
            throw $this->createNotFoundException('No se puede anular ' . $translator->trans('Guía') . ' que ya tiene ' . $translator->trans('Consolidado'));
        }

        if ($entity->getBill()) {
            throw $this->createNotFoundException('No se puede anular ' . $translator->trans('Guía') . ' con factura');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Guide entity.
     *
     * @Route("/{id}/delete", name="guide_delete")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
     public function deleteAction($id)
     {
        $user = $this->getUser();
        $maincompany = $user->getMainCompany();

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        $translator = $this->get('translator');

        if (!$entity) {
                throw $this->createNotFoundException('No existe ' . $translator->trans('Guía'));
        }

        if ($entity->getConsol()) {
            throw $this->createNotFoundException('No se puede anular ' . $translator->trans('Guía') . ' que ya tiene ' . $translator->trans('Consolidado'));
        }

        if ($entity->getBill()) {
            throw $this->createNotFoundException('No se puede anular ' . $translator->trans('Guía') . ' con factura');
        }
        $bag = $entity->getBag();
        if ($bag) {
            $bag->removeGuide($entity);
            $entity->setBag(null);
            if ($bag->getGuides()->count() == 0 ) {
                $em->remove($bag);
            }
        }
        $whrec = $entity->getWhrec();
        if ($whrec) {
            $whrec->setGuide(null);
            $whrec->setStatus('RECIBIDO');
            $entity->setWhrec(null);
        }
        $receipts = $entity->getReceipts();
        $deletes = array();
        $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Por procesar');
        foreach ($receipts as $receipt) {
            $receipt->setStatus($status);
            $receipt->setGuide(null);
            $entity->removeReceipt($receipt);
            if ($receipt->getType() == "Master") {
                $em->remove($receipt);
                $entity->setMasterec(null);
            }
            $deletes[]=$receipt->getId();
        }
        //exit(\Doctrine\Common\Util\Debug::dump($deletes));
        $services = $em->getRepository('NvCargaBundle:Servtoguide')->findByGuide($entity);
        foreach ($services as $service) {
            $em->remove($service);
        }
        $moves = $entity->getMoves();
        foreach ($moves as $move) {
            $entity->removeMove($move);
            $em->remove($move);
        }
        $lstatus = $entity->getListstatus();
        foreach ($lstatus as $status) {
            $entity->removeListstatus($status);
            $em->remove($status);
        }
        $entity->getSender()->removeSguide($entity);
        $countguide = $maincompany->getCountguides();
        $maincompany->setCountguides($countguide--);
        $em->flush();
        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('guide'));
    }
    /* public function deleteAction(Request $request, $id)
    {
        $user = $this->getUser();
        $maincompany = $user->getMainCompany();
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
        	$em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
            $translator = $this->get('translator');

        	if (!$entity) {
            		throw $this->createNotFoundException('No existe ' . $translator->trans('Guía'));
        	}

            if ($entity->getConsol()) {
                throw $this->createNotFoundException('No se puede anular ' . $translator->trans('Guía') . ' que ya tiene ' . $translator->trans('Consolidado'));
            }

            if ($entity->getBill()) {
                throw $this->createNotFoundException('No se puede anular ' . $translator->trans('Guía') . ' con factura');
            }
            $bag = $entity->getBag();
            if ($bag) {
                $bag->removeGuide($entity);
                $entity->setBag(null);
                if ($bag->getGuides()->count() == 0 ) {
                    $em->remove($bag);
                }
            }
            $receipts = $entity->getReceipts();
            $deletes = array();
            $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Por procesar');
            foreach ($receipts as $receipt) {
                $receipt->setStatus($status);
                $receipt->setGuide(null);
                $entity->removeReceipt($receipt);
                if ($receipt->getType() == "Master") {
                    $em->remove($receipt);
                    $entity->setMasterec(null);
                }
                $deletes[]=$receipt->getId();
            }
            //exit(\Doctrine\Common\Util\Debug::dump($deletes));
            $services = $em->getRepository('NvCargaBundle:Servtoguide')->findByGuide($entity);
            foreach ($services as $service) {
                $em->remove($service);
            }
            $moves = $entity->getMoves();
            foreach ($moves as $move) {
                $entity->removeMove($move);
                $em->remove($move);
            }
            $entity->getSender()->removeSguide($entity);
            $countguide = $maincompany->getCountguides();
            $maincompany->setCountguides($countguide--);
            $em->flush();
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('guide'));
    }
    */
     // MODIFICACIONES MULTIEMPRESA HASTA EL 22 DE SEPTIEMBRE 2017
    /**
     * Creates a form to delete a Guide entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('guide_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Confirmar Anulación'))
            ->getForm()
        ;
    }
    /**
     * Creates a form to create a Guide entity.
     *
     * @param Guide $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    //private function createRecForm(Guide $entity, $hideshipper, Customer $shipper, $hidereceiver,
	//			   Baddress $receiver, $agency, $pieces, $weight, $value)
    private function createRecForm(Guide $entity)
    {
        $form = $this->createForm(new GuideType($this->getDoctrine()->getManager(), $this->getUser()), $entity, array());
        $form->add('submit', 'submit', array('label' => 'Crear'));
        $form->remove('agency');
        $form->remove('packages');
        $form->add('agency', 'entity', array('label' => 'Agencia ', 'class' => 'NvCargaBundle:Agency',
                            'data' => $entity->getAgency(), 'read_only'  => true, 'disabled' => true)) ;
        $form->add('packages', 'collection', array('mapped'=>true, 'required' => false, 'label' => false,
                            'type' => new MinipackType(), 'allow_add' => false, 'allow_delete' => false,
                            'by_reference' => false, 'prototype' => true, 'error_bubbling' => false,
                            'attr' => array('class' => 'list-packages'), 'options' => array('label' => false)));
        if (count($entity->getServices()) > 0) {
            $form->add('services', 'collection', array('mapped' => true,
                    'required' => false, 'label' => false, 'type' => new ClassServType(),'allow_add' => false,
                'allow_delete' => false, 'by_reference' => false, 'prototype' => true, 'error_bubbling' => false,
                'attr' => array('class' => 'list-services'),'options' => array('label' => false),
                ));
        }
    /*

        $form = $this->get('form.factory')->createNamedBuilder('guide_type', 'form', $entity, array())
            //        ->setAction($this->generateUrl('guide_receipts'))
            //        ->setMethod('POST')
            // Para agregar paquetes a la guía
        ->add('packages', 'collection', array('required' => false, 'read_only' => true, 'label' => ' ',
                        'type' => new MinipackType(), 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false,
                        'prototype' => true, 'error_bubbling' => false, 'attr' => array('class' => 'list-packages',
                        'readonly' => true), 'options' => array('label' => false),))
        ->add('services', 'collection', array('mapped' => true, 'required' => false, 'label' => ' ',
                        'type' => new ClassServType(), 'allow_add' => false, 'allow_delete' => false,'by_reference' => false,
                        'prototype' => true, 'error_bubbling' => false, 'attr' => array('class' => 'list-services'),
                        'options' => array('label' => false), ))
        // NOTIFICACIONES DE LA GUIA
        ->add('emailnoti', 'checkbox', array('label' => 'Notificación Email ', 'data'=>true))
        ->add('mobilnoti', 'checkbox', array('label' => 'Notificación móvil '))
        ->add('contain', null , array('label' => 'Contenido ',  'attr' => array('style' => 'width: 15em')))
	    ->add('realweight', 'number', array('data'=>$weight, 'label' => 'Peso ', 'scale' => 2,
                        'attr' => array('style' => 'width: 7em')))
	    ->add('pieces', 'integer', array('data'=>$pieces,'label' => 'Piezas ', 'attr' => array('style' => 'width: 7em')))
        ->add('cod', 'entity', array('label' => 'Tipo COD ', 'class' => 'NvCargaBundle:COD',  )) // 'mapped' => false))
        ->add('paidtype', 'entity', array('label' => 'Tipo Pago ', 'class' => 'NvCargaBundle:Paidtype',
                        'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                            ->where('p.active = true')
                            ->andwhere('p.deleted = false')
            				->orderBy('p.name', 'ASC');
                        }, )) //'mapped' => false))
	    ->add('declared', 'number', array('data'=>$value, 'label' => 'Declarado ', 'attr' => array('style' => 'width: 7em')))
	    ->add('tax_paid', 'number', array('label' => ' ', 'data' => 0.0, 'read_only' => true,
                        'scale' => 2,'attr' => array('style' => 'width: 7em')))
	    ->add('tax_per', 'number', array('label' => '% impuesto ', 'data' => 0.0, 'scale' => 2,
                        'attr' => array('style' => 'width: 7em')))
	    ->add('file', null , array('label' => ' ', 'required' => false))
	    ->add('tariffid', 'hidden', array('mapped' => false))
	    ->add('tariffname', 'text',array('label' => 'Nombre de Tarifa ', 'mapped' => false, 'read_only'=>true,
                        'constraints' => array(new NotBlank(["message" => "Debe escoger una tarifa"])),
                        'attr' => array('style' => 'width: 15em')))
	    ->add('calculate', 'button', array('label' => 'Calcular'))
	    ->add('paidweight', 'number', array('scale' => 2, 'label' => 'Peso cobrado ', 'read_only' => true,
						'attr' => array('style' => 'width: 7em')))
	    ->add('downpayment', 'number', array('label' => 'Pago inicial', 'data' => 0.0, 'attr' => array('style' => 'width: 7em')))
        ->add('insurance_amount', 'number', array('label' => 'Asegurado', 'data' => 0.0, 'attr' => array('style' => 'width: 7em')))
	    ->add('insurance_per', 'number', array('label' => '% seguro ', 'data' => 0, 'attr' => array('style' => 'width: 7em')))
	    ->add('insurance_paid', 'number', array('label' => ' ', 'data' => 0.0, 'read_only' => true,
                        'attr' => array('style' => 'width: 7em')))
        ->add('discount', 'number', array('label' => ' ', 'scale' => 2, 'data' => 0.0, 'attr' => array('style' => 'width: 7em')))
        ->add('otherfees', 'number', array('label' => ' ', 'scale' => 2, 'data' => 0.0, 'attr' => array('style' => 'width: 7em')))
        ->add('freight', 'number', array('label' => ' ', 'read_only' => true, 'scale' => 2, 'attr' => array('style' => 'width: 7em')))
	    ->add('volfreight', 'number', array('label' => ' ', 'read_only' => true, 'scale' => 2,
                        'attr' => array('style' => 'width: 7em')))
        ->add('measurevalue', 'number', array('label' => 'Valor por medida ', 'scale' => 2,
                        'attr' => array('style' => 'width: 7em'))) // Viene de la tarifa
        ->add('totalpaid', 'number', array('label' => ' ', 'read_only' => true, 'scale' => 2,
						'rounding_mode' => 0 , 'attr' => array('style' => 'width: 7em'))) // Se calcula
	    // PARAMETROS OCULTOS PARA TOMAR INFORMACION DE TARIFA
        ->add('submit', 'submit', array('label' => 'Crear'))
        // Para buscar ciudad
        ->add('selcity', 'hidden', array('label' => ' ', 'mapped'=> false))
        // Para buscar cliente
        ->add('selcustomer', 'hidden', array('label' => ' ', 'mapped'=> false))
        ->getForm();

        $form->add('agency', 'entity', array('label' => 'Agencia ', 'class' => 'NvCargaBundle:Agency',
					 'data'  => $agency, 'read_only'=>true, 'disabled' => true));
        if ($hidereceiver) {
            $form->add('id_addr', 'hidden', array('read_only'=>true, 'mapped'=>false, 'data'=> $receiver->getId()));
            $form->add('name_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),'read_only'=>true,
                        'data'=>$receiver->getName(), 'label'=> 'Nombre ', 'mapped'=>false));
            $form->add('lastname_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'read_only'=>true,'data'=>$receiver->getLastname(),'label'=> 'Apellido ', 'mapped'=>false));
            $form->add('address_addr', 'text', array('read_only'=>true,'data'=>$receiver->getAddress(),
                        'label'=> 'Dirección ', 'mapped'=>false, 'attr' => array('style' => 'width: 90%')));
            $form->add('phone_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),'read_only'=>true,
                        'data'=>$receiver->getPhone(),'label'=> 'Teléfono', 'mapped'=>false, 'required' => false));
            $form->add('mobile_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),'read_only'=>true,
                        'data'=>$receiver->getMobile(),'label'=> 'Móvil ', 'mapped'=>false, 'required' => false));
            $form->add('barrio_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),'read_only'=>true,
                        'data'=>$receiver->getBarrio(),'label'=> 'Sector ', 'mapped'=>false, 'required' => false));
            $form->add('email_addr', 'email', array('read_only'=>true,'data'=>$receiver->getEmail(),
                        'label'=> 'Email ', 'mapped'=>false));
            $form->add('cityid_addr', 'hidden', array('read_only'=>true,'data'=>$receiver->getCity()->getId(),'mapped'=>false));
            $form->add('cityname_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),'read_only'=>true,
                        'data'=>$receiver->getCity(),'label'=> 'Ciudad ', 'mapped'=>false, 'read_only' => true));
            $form->add('state_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),'read_only'=>true,
                        'data'=>$receiver->getCity()->getState(),'label'=> 'Estado ', 'mapped'=>false, 'read_only' => true));
            $form->add('country_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),'read_only'=>true,
                        'data'=>$receiver->getCity()->getState()->getCountry(),'label'=> 'País ',
                        'mapped'=>false, 'read_only' => true));
            $form->add('zip_addr','text', array('attr' => array('class' => 'resizedTextbox'),'read_only'=>true,
                        'data'=>$receiver->getZip(),'label'=> 'Zip', 'mapped'=>false, 'required' => false));
            $form->add('typecus_addr', 'entity', array('label' => 'Tipo de Cliente ', 'mapped' => false,
                        'required' => false, 'read_only'=>true, 'disable'=>true, 'empty_value' => false,
                        'data'=>$receiver->getCustomer()->getType(), 'data_class'=> 'NvCarga\Bundle\Entity\Customertype',
                        'class'=> 'NvCargaBundle:Customertype', 'expanded' => true,));
        } else {
            $form->add('id_addr', 'hidden', array('mapped'=>false, 'data'=> $receiver->getId()));
            $form->add('name_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),'data'=>$receiver->getName(),
                        'label'=> 'Nombre ', 'mapped'=>false,
                        'constraints' => array(new NotBlank(["message" => "El nombre del remitente no puede estar vacío"]),
                        new Length(["min" => 2, "max" => 30,
                        "minMessage" => "El nombre del remitente debe tener al menos {{ limit }} caracteres",
                        "maxMessage" => "El nombre del remitente no puede tener mas de {{ limit }} caracteres"]))));
            $form->add('lastname_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$receiver->getLastname(),'label'=> 'Apellido ', 'mapped'=>false,
                        'constraints' => array(new Length(["max" => 30,
                        "maxMessage" => "El apellido del remitente no puede tener mas de {{ limit }} caracteres"]))));
            $form->add('address_addr', 'text', array('data'=>$receiver->getAddress(),'label'=> 'Dirección ', 'mapped'=>false,
                        'constraints' => array(new NotBlank(["message" => "La dirección del remitente no puede estar vacío"]),
                        new Length(["min" => 2, "max" => 120,
                        "minMessage" => "La dirección del remitente debe tener al menos {{ limit }} caracteres",
                        "maxMessage" => "La dirección del remitente no puede tener mas de {{ limit }} caracteres"])),
                        'attr' => array('style' => 'width: 90%')));
            $form->add('phone_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$receiver->getPhone(),'label'=> 'Teléfono', 'mapped'=>false, 'required' => false));
            $form->add('mobile_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$receiver->getMobile(),'label'=> 'Móvil ', 'mapped'=>false, 'required' => false));
            $form->add('barrio_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$receiver->getBarrio(),'label'=> 'Sector ', 'mapped'=>false, 'required' => false));
            $form->add('email_addr', 'email', array('data'=>$receiver->getCustomer()->getEmail(),'label'=> 'Email ',
                        'mapped'=>false, 'constraints' => array(
                        // new NotBlank(["message" => "Debe asignar un email del remitente"]),
                        new Email(["message" => "El email '{{ value }}' no es válido.",] // "checkMX" => true, "checkHost" => true]
                        ))));
            $form->add('cityid_addr', 'hidden', array('data'=>$receiver->getCity()->getId(),'mapped'=>false));
            $form->add('cityname_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$receiver->getCity(),'label'=> 'Ciudad ', 'mapped'=>false, 'read_only' => true));
            $form->add('state_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$receiver->getCity()->getState(),'label'=> 'Estado ', 'mapped'=>false, 'read_only' => true));
            $form->add('country_addr', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$receiver->getCity()->getState()->getCountry(),'label'=> 'País ',
                        'mapped'=>false, 'read_only' => true));
            $form->add('zip_addr','text', array('data'=>$receiver->getZip(),'label'=> 'Zip', 'mapped'=>false, 'required' => false));
            $form->add('typecus_addr', 'entity', array('label' => 'Tipo de Cliente ', 'mapped' => false,
                        'required' => false, 'empty_value' => false, 'data'=>$receiver->getCustomer()->getType(),
                        'data_class'=> 'NvCarga\Bundle\Entity\Customertype', 'class'=> 'NvCargaBundle:Customertype',
                        'expanded' => true,));
        }
        if ($hideshipper) {
            $form->add('id_sender', 'hidden', array('read_only'=>true, 'mapped'=>false, 'data'=> $shipper->getId()));
            $form->add('name_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),'read_only'=>true,
                        'data'=>$shipper->getName(), 'label'=> 'Nombre ', 'mapped'=>false,
                        'constraints' => array(new NotBlank(["message" => "El nombre del remitente no puede estar vacío"]),
                        new Length(["min" => 2, "max" => 30,
                        "minMessage" => "El nombre del remitente debe tener al menos {{ limit }} caracteres",
                        "maxMessage" => "El nombre del remitente no puede tener mas de {{ limit }} caracteres"]))));
            $form->add('lastname_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'read_only'=>true,'data'=>$shipper->getLastname(),'label'=> 'Apellido ', 'mapped'=>false,
                        'constraints' => array(new Length(["max" => 40,
                        "maxMessage" => "El apellido del remitente no puede tener mas de {{ limit }} caracteres"]))));
            $form->add('address_sender', 'text', array('read_only'=>true,'data'=>$shipper->getAdrdefault()->getAddress(),
                        'label'=> 'Dirección ', 'mapped'=>false, 'constraints' => array(
                        new NotBlank(["message" => "La dirección del remitente no puede estar vacío"]),
                        new Length(["min" => 2, "max" => 120,
                        "minMessage" => "La dirección del remitente debe tener al menos {{ limit }} caracteres",
                        "maxMessage" => "La dirección del remitente no puede tener mas de {{ limit }} caracteres"])),
                        'attr' => array('style' => 'width: 90%')));
            $form->add('phone_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),'read_only'=>true,
                        'data'=>$shipper->getAdrdefault()->getPhone(),'label'=> 'Teléfono', 'mapped'=>false, 'required' => false));
            $form->add('mobile_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),'read_only'=>true,
                        'data'=>$shipper->getAdrdefault()->getMobile(),'label'=> 'Móvil ', 'mapped'=>false, 'required' => false));
            $form->add('barrio_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),'read_only'=>true,
                        'data'=>$shipper->getAdrdefault()->getBarrio(),'label'=> 'Sector ', 'mapped'=>false, 'required' => false));
            $form->add('email_sender', 'email', array('read_only'=>true,'data'=>$shipper->getEmail(),
                        'label'=> 'Email ', 'mapped'=>false));
            $form->add('cityid_sender', 'hidden', array('read_only'=>true,
                        'data'=>$shipper->getAdrdefault()->getCity()->getId(),'mapped'=>false));
            $form->add('cityname_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'read_only'=>true,'data'=>$shipper->getAdrdefault()->getCity(),
                        'label'=> 'Ciudad ', 'mapped'=>false, 'read_only' => true));
            $form->add('state_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),'read_only'=>true,
                        'data'=>$shipper->getAdrdefault()->getCity()->getState(),'label'=> 'Estado ',
                        'mapped'=>false, 'read_only' => true));
            $form->add('country_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),'read_only'=>true,
                        'data'=>$shipper->getAdrdefault()->getCity()->getState()->getCountry(),'label'=> 'País ',
                        'mapped'=>false, 'read_only' => true));
            $form->add('zip_sender','text', array('attr' => array('class' => 'resizedTextbox'),'read_only'=>true,
                        'data'=>$shipper->getAdrdefault()->getZip(),'label'=> 'Zip', 'mapped'=>false, 'required' => false));
            $form->add('typecus_sender', 'entity', array('label' => 'Tipo de Cliente ', 'mapped' => false,
                        'required' => false, 'read_only'=>true, 'disable'=>true, 'empty_value' => false,
                        'data'=>$shipper->getType(), 'data_class'=> 'NvCarga\Bundle\Entity\Customertype',
                        'class'=> 'NvCargaBundle:Customertype', 'expanded' => true,));
        } else {
            $form->add('id_sender', 'hidden', array('mapped'=>false, 'data'=> $shipper->getId()));
            $form->add('name_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),'data'=>$shipper->getName(),
                        'label'=> 'Nombre ', 'mapped'=>false,
                        'constraints' => array(new NotBlank(["message" => "El nombre del remitente no puede estar vacío"]),
                        new Length(
                        ["min" => 2, "max" => 30,
                        "minMessage" => "El nombre del remitente debe tener al menos {{ limit }} caracteres",
                        "maxMessage" => "El nombre del remitente no puede tener mas de {{ limit }} caracteres"]))));
            $form->add('lastname_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$shipper->getLastname(),'label'=> 'Apellido ', 'mapped'=>false,
                        'constraints' => array(new Length(["max" => 30,
                        "maxMessage" => "El apellido del remitente no puede tener mas de {{ limit }} caracteres"]))));
            $form->add('address_sender', 'text', array('data'=>$shipper->getAdrdefault()->getAddress(),
                        'label'=> 'Dirección ', 'mapped'=>false,
                        'constraints' => array(
                        new NotBlank(["message" => "La dirección del remitente no puede estar vacío"]),
                        new Length(["min" => 2, "max" => 120,
                        "minMessage" => "La dirección del remitente debe tener al menos {{ limit }} caracteres",
                        "maxMessage" => "La dirección del remitente no puede tener mas de {{ limit }} caracteres"])),
                        'attr' => array('style' => 'width: 90%')));
            $form->add('phone_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$shipper->getAdrdefault()->getPhone(),'label'=> 'Teléfono', 'mapped'=>false, 'required' => false));
            $form->add('mobile_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$shipper->getAdrdefault()->getMobile(),'label'=> 'Móvil ', 'mapped'=>false, 'required' => false));
            $form->add('barrio_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$shipper->getAdrdefault()->getBarrio(),'label'=> 'Sector ', 'mapped'=>false, 'required' => false));
            $form->add('email_sender', 'email', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$shipper->getEmail(),'label'=> 'Email ', 'mapped'=>false,
                        'constraints' => array(
                        // new NotBlank(["message" => "Debe asignar un email del remitente"]),
                        new Email(["message" => "El email '{{ value }}' no es válido.", ] // "checkMX" => true, "checkHost" => true]
                        ))));
            $form->add('cityid_sender', 'hidden', array('data'=>$shipper->getAdrdefault()->getCity()->getId(),'mapped'=>false));
            $form->add('cityname_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$shipper->getAdrdefault()->getCity(),'label'=> 'Ciudad ', 'mapped'=>false, 'read_only' => true));
            $form->add('state_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$shipper->getAdrdefault()->getCity()->getState(),'label'=> 'Estado ',
                        'mapped'=>false, 'read_only' => true));
            $form->add('country_sender', 'text', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$shipper->getAdrdefault()->getCity()->getState()->getCountry(),'label'=> 'País ',
                        'mapped'=>false, 'read_only' => true));
            $form->add('zip_sender','text', array('attr' => array('class' => 'resizedTextbox'),
                        'data'=>$shipper->getAdrdefault()->getZip(),'label'=> 'Zip', 'mapped'=>false, 'required' => false));
            $form->add('typecus_sender', 'entity', array('label' => 'Tipo de Cliente ', 'mapped' => false, 'required' => false, 						'empty_value' => false, 'data'=>$shipper->getType(),
                        'data_class'=> 'NvCarga\Bundle\Entity\Customertype', 'class'=> 'NvCargaBundle:Customertype',
                        'expanded' => true,));
        }
        */
        return $form;
    }

    /**
     * Creates a new Guide entity with some receipts.
     * @Route("/guiderec", name="guide_receipts")
     * @Template("NvCargaBundle:Guide:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function guiderecAction(Request $request)
    {
        $typecus = $request->query->get('typecus');
        $list = $request->query->get('reclist');
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        // exit(\Doctrine\Common\Util\Debug::dump($typecus . ' ' . $reclist));
        $pobox=null;
        switch ($typecus) {
            case 1:
                $hideshipper=true;
                $hidereceiver=false;
            break;
            case 2:
                $hideshipper=false;
                $hidereceiver=true;
            break;
            case 3:
                $hideshipper=true;
                $hidereceiver=true;
            break;
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $reclist=json_decode($list);
        $total=count($reclist);
        $id = (int)$reclist[0];
        $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);
        $translator = $this->get('translator');
        if (!$receipt) {
            throw $this->createNotFoundException($total . ' El ' . $translator->trans('Recibo') . ' ' . $id . ' no existe ');
        }
        if ($receipt->getGuide()) {
            throw $this->createNotFoundException(' El ' . $translator->trans('Recibo') . ' ' . $id . ' ya tiene ' . $translator->trans('Guía') . ': ('. $receipt->getGuide()->getNumber() . ').');
        }
        $weight = $receipt->getWeight();
        $pieces = $receipt->getQuantity();
        $value = $receipt->getValue();
        $advr = false;
        $adva = false;
        $bsen = $receipt->getShipper();
        $badd = $receipt->getReceiver();
        $entity = new Guide();
        $entity->newpackages();

        $pack = new Minipack();
        $pack->setWeight($receipt->getWeight());
        $pack->setLength($receipt->getLength());
        $pack->setWidth($receipt->getWidth());
        $pack->setHeight($receipt->getHeight());
        $pack->setValue($receipt->getValue());
        $pack->setTracking($receipt->getTracking());
        $pack->setNpack($receipt->getNpack());
        $pack->setPacktype($receipt->getPacktype());
        $entity->addPackage($pack);
        $translator = $this->get('translator');
        $contain = $translator->trans('Recibos'). ': ' . $receipt->getNumber();
        $flashBag = $this->get('session')->getFlashBag();
        $hideshipper=false;
        $hidereceiver=false;
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $message1 =  $translator->trans('Recibo'). ' 1: ' . $bsen->getName() . ' ' .
                        $bsen->getLastName() . ' (' . $bsen->getAdrdefault()->getCity() . ', ' .
                        $bsen->getAdrdefault()->getCity()->getState()->getCountry() . ")\n";
        $message2 =  $translator->trans('Recibo'). ' 1: ' .$badd->getName() . ' ' .
                        $badd->getLastName() . ' (' . $badd->getCity() . ', ' .
                        $badd->getCity()->getState()->getCountry() . ")\n";
        for ($i=1;$i<$total;$i++) {
            $id = (int)$reclist[$i];
            $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);
            $translator = $this->get('translator');
            if (!$receipt) {
                throw $this->createNotFoundException($total . ' El ' . $translator->trans('Recibo') . ' ' . $id . ' no existe ');
            }
            if ($receipt->getGuide()) {
                throw $this->createNotFoundException(' El ' . $translator->trans('Recibo') . ' ' . $id . ' ya tiene ' . $translator->trans('Guía') . ': ('. $receipt->getGuide()->getNumber() . ').');
            }
            $contain = $contain . ', ' . $receipt->getNumber();
            $weight += $receipt->getWeight();
            $pieces += $receipt->getQuantity();
            $value += $receipt->getValue();
            $shipper = $receipt->getShipper();
            $receiver = $receipt->getReceiver();
            $val = $i + 1;
            $message1 = $message1 .   $translator->trans('Recibo'). ' ' . $val . ': ' . $shipper->getName() . ' ' .
                        $shipper->getLastName() . ' (' . $shipper->getAdrdefault()->getCity() . ', ' .
                        $shipper->getAdrdefault()->getCity()->getState()->getCountry() . ")\n";
            $message2 = $message2 .   $translator->trans('Recibo'). ' ' . $val . ': ' . $receiver->getName() . ' ' .
                        $receiver->getLastName() . ' (' . $receiver->getCity() . ', ' .
                        $receiver->getCity()->getState()->getCountry() . ")\n";
            if (($bsen != $shipper) && (!$advr)) {
                $advr = true;
            }
            if (($badd != $receiver) && (!$adva)) {
                $adva = true;
            }
            $pack = new Minipack();
            $pack->setWeight($receipt->getWeight());
            $pack->setLength($receipt->getLength());
            $pack->setWidth($receipt->getWidth());
            $pack->setHeight($receipt->getHeight());
            $pack->setValue($receipt->getValue());
            $pack->setTracking($receipt->getTracking());
            $pack->setNpack($receipt->getNpack());
            $pack->setPacktype($receipt->getPacktype());
            $entity->addPackage($pack);
        }
        if ($advr) {
            $flashBag->add('notice', "Los " .  $translator->trans('Recibos'). " tienen diferentes remitentes \n" . $message1);
            $hideshipper=false;
        }
        if ($adva) {
            $flashBag->add('notice',  "Los " .  $translator->trans('Recibos'). " tienen diferentes destinatarios \n" . $message2);
            $hidereceiver=false;
        }
        $entity->setContain($contain);
        $shipper = $bsen;
        $receiver = $badd;
        $agency = $receipt->getAgency();
        $services = $em->getRepository('NvCargaBundle:Adservice')->findBy(['isactive'=>true,'maincompany'=>$maincompany]);
        // exit(\Doctrine\Common\Util\Debug::dump($services));
        foreach ($services as $service) {
            $serv = new ClassServ();
            $serv->setId($service->getId());
            $serv->setName($service->getName());
            $serv->setDescription($service->getDescription());
            $serv->setMeasure($service->getMeasure());
            $serv->setPrice($service->getPrice());
            $serv->setAmount(0);
            $serv->setTotal(0);
            $entity->addService($serv);
        }
        $entity->setSender($shipper);
        $entity->setAddressee($receiver);
        $entity->setAgency($agency);

        //$form = $this->createRecForm($entity, $hideshipper, $shipper, $hidereceiver, $receiver, $agency, $pieces, $weight, $value);
        $form = $this->createRecForm($entity);

        $form->handleRequest($request);
        $edition = true;
        if ($form->isValid()) {
            $movdate = new \DateTime();
            $clock = $form['clock']->getData();
            $rdate = substr($movdate->format('m/d/Y'),0,10) . 'T' . $clock;
            $entity->setCreationdate(new \DateTime($rdate));

            $entity->upload();
            $tid = $form['tariffid']->getData();
            $tariff= $em->getRepository('NvCargaBundle:Tariff')->find($tid);
            $service=$tariff->getShippingtype();
            $entity->setConsol(null);
            $entity->setShippingtype($service);
            $entity->setTariff($tariff);
            $entity->setProcessedby($this->getUser());
            $entity->setAgency($agency);

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

            $entity->setSender($sender);
            $sender->addSguide($entity);
            $entity->setAddressee($rebaddr);
            $hid = $em->createQueryBuilder()
                        ->select('MAX(e.id)')
                        ->from('NvCargaBundle:Guide', 'e')
                        ->getQuery()
                        ->getSingleScalarResult();
            $hid+=1;
            $agcod = str_pad($entity->getAgency()->getId(), 3, '0', STR_PAD_LEFT);

            // $addr->addRguide($entity);
            // Asignando los paises origen y destino
            $entity->setCountryto($entity->getAddressee()->getCity()->getState()->getCountry());
            $entity->setCountryfrom($entity->getAgency()->getCity()->getState()->getCountry());
            // MODIFICAR LOS RECIBOS SELECCIONADOS
            $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneBy(array('name' =>'Procesado'));
            for ($i=0;$i<$total;$i++) {
                $id = (int)$reclist[$i];
                $receipt = $em->getRepository('NvCargaBundle:Receipt')->find($id);
                $receipt->setStatus($status);
                $receipt->setGuide($entity);
                $entity->addReceipt($receipt);
            }

            // CREAR EL PRIMER MOVIMIENTO DE LA GUIA
            $maincompany = $this->getUser()->getMaincompany();
            $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
            $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');
            $translator = $this->get('translator');

            /*
            // ELIMINADA LA CREACION DEL PRIMER MOVIMIENTO PARA LA GUIA
            $moveguide = new Moveguides($this->get('translator'), $this->get('mailer'), $setfrom, $fromname, $em, $this->getUser());
            $moveguide->setMovdate(new \DateTime());
            $moveguide->setGuide($entity);
            $agentype = $entity->getAgency()->getType()->getName();

            $stamov = null;
            if ($agentype == 'MASTER' ) {
                    $stamov = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName('En Master');
            } else {
                    $stamov = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName('En Sucursal');
            }
            if (!$stamov) {
                $stamov = $em->getRepository('NvCargaBundle:Guidestatus')->find(1);
            }
            $moveguide->setStatus($stamov);
            $company = $em->getRepository('NvCargaBundle:Localcompany')->findOneByName('Empresa');
            $moveguide->setCompany($company);
            $moveguide->setTrack($entity->getNumber());
            $moveguide->setPercentage(0);
            $moveguide->setComment('Registro de '. $translator->trans('Guía'). ', debe incluirse en '. $translator->trans('Consolidado'));
            $entity->addMove($moveguide);
            $em->persist($moveguide);
            */

            $adservs = $entity->getServices();
            foreach ($adservs as $adserv) {
                if ($adserv->getAmount() > 0) {
                    $thiserv = $em->getRepository('NvCargaBundle:Adservice')->find($adserv->getId());
                    $servtoguide = new Servtoguide();
                    $servtoguide->setServicedate(new \DateTime());
                    $servtoguide->setAmount($adserv->getAmount());
                    $servtoguide->setTotal($adserv->getTotal());
                    $servtoguide->setAdservice($thiserv);
                    $servtoguide->setGuide($entity);
                    $em->persist($servtoguide);
                }
            }
            //$entity->setNumber(0);
            //$em->persist($entity);
            $cod = $form['cod']->getData();
            $paidtype = $form['paidtype']->getData();
            $numini= $maincompany->getIniguide();
//             $highest_id = $em->createQueryBuilder()
//                         ->select('MAX(e.id)')
//                         ->from('NvCargaBundle:Guide', 'e')
//                         ->getQuery()
//                         ->getSingleScalarResult();
//             if ($numini >= $highest_id) {
//                 $val=$numini + $highest_id + 1;
//             } else {
//                 $val=$highest_id+1;
//             }
            $countguide = $maincompany->getCountguides();
//             $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('r')
//                             ->select('count(r.id)')
//                             ->where('r.maincompany =:thecompany')
//                             ->setParameters(['thecompany'=>$maincompany])
//                             ->getQuery()
//                             ->getSingleScalarResult();
            $plan = $maincompany->getPlan();
            if (($plan->getGuides()) && ($countguide >= $plan->getMaxguides())) {
                    $translator = $this->get('translator');
                    $message = 'Ha llegado al número máximo de ' . $translator->trans('Guías')  .' permitidas. Para crear más debe actualizar su plan a uno superior.';
                    if ($this->isGranted('ROLE_ADMIN')) {
                        $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                    }
                    $flashBag->add('notice',$message);
                    goto next;
            }

            $number = $this->genNumber($entity);
            $entity->setNumber($number);
            $countguide++;
            $maincompany->setCountguides($countguide);
            if ($entity->getTariff()->getMeasure()->getName() == 'Lb' ) {
                $entity->setRoundmeasure($entity->getAgency()->getMaincompany()->getRoundweight());
            } else {
                $entity->setRoundmeasure($entity->getAgency()->getMaincompany()->getRoundvol());
            }
            $entity->setMaincompany($maincompany);
            if ($maincompany->getFirststatus()) {
                $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                $newstatus = new Statusguide();
                $newstatus->setGuide($entity);
                $newstatus->setDate($entity->getCreationdate());
                $newstatus->setPlace($entity->getAgency()->getCity());
                $newstatus->setStep($step);
                $newstatus->setComment($translator->trans('Guía') . ' creada');
                $entity->addListstatus($newstatus);
                $em->persist($newstatus);
            }
            $em->persist($entity);
            $em->flush();
            if ($entity->getEmailnoti()) {
                $this->sendemail($entity);
            }

            return $this->redirect($this->generateUrl('guide_show', array('id' => $entity->getId())));
        } else {
            if ($form['edition']->getData()) {
                $edition = false;
            }
        }
        next:
            return array(
                'entity' => $entity,
                'form'   => $form->createView(),
                'tariffs' => null,
                'services' => $services,
                'edition' => $edition,
                'nameform' => $translator->trans('Guía') . ' para ' . $translator->trans('Recibos'),
                );

    }
     /**
     * Generate  a guide for printer
     *
     * @Route("/{id}/print", name="guide_print")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function printAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();

        $guide = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$guide) {
            throw $this->createNotFoundException('No existe el ' . $trsanslator->trans('Guía') );
        }
        if ($user->getAgency()->getType() != 'MASTER') {
            $countryrec = $guide->getAddressee()->getCity()->getState()->getCountry();
            if (($countryrec == $countryto) || ($guide->getAgency() == $agency))  {
                $entity = $guide;
            } else {
                throw $this->createNotFoundException('No tiene permiso para imprimir esta ' . $translator->trans('Guía'));
            }
        } else {
            $entity = $guide;
        }
        $services = $em->getRepository('NvCargaBundle:Servtoguide')->findByGuide($entity);

        $viewoption = $this->getView('Guide', 'print');

        return  $this->render($viewoption,array('entity'=> $entity,'services' => $services));
        /*
        return array(
                'entity'      => $entity,
                'services' => $services,);
        */
    }
    /**
     * Generate a guide label
     *
     * @Route("/{id}/label", name="guide_label")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function labelAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();

        $guide = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$guide) {
            throw $this->createNotFoundException('No existe el ' . $trsanslator->trans('Guía') );
        }
        if ($user->getAgency()->getType() != 'MASTER') {
            $countryrec = $guide->getAddressee()->getCity()->getState()->getCountry();
            if (($countryrec == $countryto) || ($guide->getAgency() == $agency))  {
                $entity = $guide;
            } else {
                throw $this->createNotFoundException('No tiene permiso para imprimir esta ' . $translator->trans('Guía'));
            }
        } else {
            $entity = $guide;
        }

        $viewoption = $this->getView('Guide', 'label');

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
     * @Route("/{id}/labelpdf", name="guide_labelpdf")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function labelpdfAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();

        $guide = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$guide) {
            throw $this->createNotFoundException('No existe el ' . $trsanslator->trans('Guía') );
        }
        if ($user->getAgency()->getType() != 'MASTER') {
            $countryrec = $guide->getAddressee()->getCity()->getState()->getCountry();
            if (($countryrec == $countryto) || ($guide->getAgency() == $agency))  {
                $entity = $guide;
            } else {
                throw $this->createNotFoundException('No tiene permiso para imprimir la etiqueta de ' . $translator->trans('Guía'));
            }
        } else {
            $entity = $guide;
        }
        $viewoption = $this->getView('Guide', 'label');

        //return  $this->render($viewoption,array('entity'=> $entity,'services' => $services));

        $html = $this->renderView($viewoption, array('entity' => $entity));
        // exit(\Doctrine\Common\Util\Debug::dump($html));
        $number = $entity->getNumber();
        $filename = sprintf('labelguide-%s.pdf', transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $number));

        $labeldata = $em->getRepository('NvCargaBundle:Labelconf')->findOneBy(array('tableclass' => 'Guide', 'maincompany'=>$maincompany));

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
     * Export to PDF
     *
     * @Route("/{id}/printpdf", name="guide_printpdf")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function printpdfAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();
        $countryto = $agency->getCity()->getstate()->getCountry();

        $entity = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        $translator = $this->get('translator');
        if (!$entity) {
            throw $this->createNotFoundException('No existe el ' . $translator->trans('Guía') );
        }
        $admin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ADMIN_GUIDE');
        $countryrec = $entity->getAddressee()->getCity()->getState()->getCountry();
        $roleview =  (($countryrec == $countryto) || ($entity->getAgency() == $agency)) && ($this->isGranted('ROLE_VIEW_GUIDE'));
        $admin = $admin || $roleview;
        $services = $em->getRepository('NvCargaBundle:Servtoguide')->findByGuide($entity);

        // exit(\Doctrine\Common\Util\Debug::dump($user->getUsername()));
        if ($user->getPobox()) {
            $customer = $entity->getSender() == $user->getPobox()->getCustomer();
            $badd = $user->getPobox()->getCustomer()->getBaddress();
            $recto = $entity->getAddressee()->getId();
            $receiver = false;
            foreach($badd as $struct) {
                    if ($recto == $struct->getId()) {
                        $receiver = true;
                        break;
                    }
            }
        } else {
            $customer = false;
            $receiver = false;
        }
        /* if ((!$admin) && (!$customer) && (!$receiver)) {
            throw $this->createAccessDeniedException('');
        }
        */
        $viewoption = $this->getView('Guide', 'print');

        //return  $this->render($viewoption,array('entity'=> $entity,'services' => $services));

        $html = $this->renderView($viewoption, array('entity' => $entity, 'services' => $services));
        // exit(\Doctrine\Common\Util\Debug::dump($html));
        $number = $entity->getNumber();
        $filename = sprintf('guide-%s.pdf', transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $number));

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
     * Displays a form to edit an existing Guide entity.
     *
     * @Route("/{id}/group", name="guide_group")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function groupAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();

        $entity = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);


        $translator = $this->get('translator');
        if (!$entity) {
            throw $this->createNotFoundException('No existe ' . $translator->trans('Guía'));
        }
        if ($agency != $entity->getAgency()) {
            throw $this->createNotFoundException('Esta acción no es permitida en su agencia');
        }
        $stype = $entity->getShippingtype();
        $countryfrom = $entity->getCountryfrom();
        $countryto = $entity->getCountryto();

        $moves=$entity->getMoves();
        // exit(\Doctrine\Common\Util\Debug::dump($moves));
        $lastmove=$moves->last();
        $status=strtoupper($lastmove->getStatus());
        if ($entity->getBag() != null) {
            throw $this->createNotFoundException('Ya está en una bolsa');
        }
        // if (($status != 'EN MASTER') && ($status != 'EN SUCURSAL')) {
        if ($entity->getConsol() != null) {
            throw $this->createNotFoundException('Ya está en un ' . $translator->trans('Guía') . ', no se puede agrupar');
        }
        $entities = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                    ->where('g.id != :id')
                    ->andwhere('g.agency = :ag')
                    ->andwhere('g.countryto = :ct')
                    ->andwhere('g.countryfrom = :cf')
                    ->andwhere('g.shippingtype = :st')
                    ->andwhere('g.consol IS NULL')
                    ->andwhere('g.bag IS NULL')
                    ->setParameters(array('id'=> $entity->getId(), 'ag' => $agency,
                            'ct' => $countryto, 'cf' => $countryfrom, 'st'=>$stype ))
                    ->orderBy('g.number', 'ASC')
                    ->getQuery()
                    ->getResult();

        foreach ($entities as $key => $guide ) {
            $thisstatus= strtoupper($guide->getMoves()->last()->getStatus());
            if ($thisstatus != $status) {
                unset($entities[$key]);
            }
        }
        return array(
                'entities' => $entities,
                'guide' => $entity,
            );
    }
    /**
     * @Route("/tobag", name="guide_tobag")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function tobagAction(Request $request)
    {
        $list = $request->query->get('guidelist');
        $guidelist=json_decode($list);
        $user = $this->getUser();
        $agency = $user->getAgency();
        $total=count($guidelist);
        $em = $this->getDoctrine()->getManager();
        $maincompany =  $this->getUser()->getMaincompany();

        $countbag =  $maincompany->getCountbags();
        /*$em->getRepository('NvCargaBundle:Bag')->createQueryBuilder('r')
                            ->select('count(r.id)')
                            ->where('r.maincompany =:thecompany')
                            ->setParameters(['thecompany'=>$maincompany])
                            ->getQuery()
                            ->getSingleScalarResult();*/
        $plan = $maincompany->getPlan();
        if (($plan-getBags()) && ($countbag >= $plan->getMaxbags())) {
            $message = 'Ha llegado al número máximo de BOLSAS permitidas. Para crear más debe actualizar su plan a uno superior.';
            if ($this->isGranted('ROLE_ADMIN')) {
                $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
            }
            throw $this->createNotFoundException($message);
        }
        $bag = new Bag();
        $bag->setAgency($agency);
        $bag->setMaincompany($maincompany);
        $bag->setCreationdate(new \DateTime());
        /*
        if ($agency->getType()->getName() == 'MASTER' ) {
            $bag->setStatus('ENTREGADA');
        } else {
            $bag->setStatus('LISTA');
        }
        */
        $bag->setStatus('LISTA');
        $hid = $em->createQueryBuilder()
                    ->select('MAX(e.id)')
                    ->from('NvCargaBundle:Bag', 'e')
                    ->getQuery()
                    ->getSingleScalarResult();
//        $hid = $countbag;
        $hid++;
        $countbag++;
        $maincompany->setCountbags($countbag);
        //$agcod = str_pad($agency->getId(), 3, '0', STR_PAD_LEFT);
        $number = 'BAG-' . $maincompany->getAcronym() . '-' . $hid;
        $bag->setNumber($number);
        $guides = $em->getRepository('NvCargaBundle:Guide')->findBy(['id'=>$guidelist, 'maincompany'=>$maincompany]);
        foreach ($guides as $guide) {
            if (($guide->getAgency() != $agency) || ($guide->getBag() != null) || ($guide->getConsol() != null)) {
                throw $this->createNotFoundException('No se puede crear una bolsa con la selección hecha');
            }
            $guide->setBag($bag);
            $bag->addGuide($guide);
        }
        $em->persist($bag);
        $em->flush();
        return $this->redirect($this->generateUrl('bag_show', array('id' => $bag->getId())));
        //$em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('NvCargaBundle:Receipt')->findById($reclist);
        // exit(\Doctrine\Common\Util\Debug::dump($entities));
        /* $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Anulado');
            foreach ($entities as $entity) {
            $entity->setStatus($status);

        }
        $em->flush();
        return $this->redirect($this->generateUrl('receipt_wg')); */
    }
    /**
     * Finds and displays a  Consolidated to include the Guide
     *
     * @Route("/{id}/toconsol", name="guide_toconsol")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function toconsolAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        // $agency = $user->getAgency();

        $entity = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        $translator = $this->get('translator');

        if (!$entity) {
            throw $this->createNotFoundException('No existe ' . $translator->trans('Guía'));
        }
        if ($entity->getConsol()) {
            throw $this->createNotFoundException('Ya está en un ' . $translator->trans('Consolidado'));
        }
        /*
        if ($entity->getBag()) {
            throw $this->createNotFoundException('Está en un bolsa, se debe incluir por grupo');
        }
        */
        $agency = $entity->getAgency();
        $countryto = $entity->getCountryto();
        $shiptype = $entity->getShippingtype();

        $listconsol = $em->getRepository('NvCargaBundle:Consolidated')->createQueryBuilder('c')
                ->where('c.agency = :ag')
                ->andwhere('c.countryto = :ct')
                ->andwhere('c.shippingtype = :st')
                ->andwhere('c.isopen = 1')
                ->andwhere('c.maincompany =:thecompany')
                ->setParameters(array('ag' => $agency,
                        'ct' => $countryto, 'st'=>$shiptype, 'thecompany'=>$maincompany ))
                ->orderBy('c.id', 'ASC')
                ->getQuery()
                ->getResult();
        $translator = $this->get('translator');

        if (count($listconsol) == 0) {
            throw $this->createNotFoundException('No existen ' . $translator->trans('Consolidados') . ' en la agencia donde se pueda incluir');
        }
        return array(
                'entity' => $entity,
                'listconsol' => $listconsol,
            );
    }

    private function createSearchForm($number,$namesender,$lastnamesender,$emailsender,$phonesender,$mobilesender,$nameaddr,$lastnameaddr,$emailaddr,$phoneaddr,$mobileaddr)
    {
        $formBuilder = $this->get('form.factory')->createNamedBuilder('guide_search', 'form', array())
            ->add('namesender', 'text', array('label' => 'Nombre ', 'required' => false,'data'=>$namesender))
            ->add('lastnamesender', 'text', array('label' => 'Apellido ', 'required' => false, 'data'=>$lastnamesender))
            ->add('emailsender', 'text', array('label' => 'Email ', 'required' => false, 'data'=>$emailsender))
            ->add('phonesender', 'text', array('label' => 'Teléfono ', 'required' => false, 'data'=>$phonesender))
            ->add('mobilesender', 'text', array('label' => 'Móvil ', 'required' => false,'data'=>$mobilesender))
            ->add('nameaddr', 'text', array('label' => 'Nombre ', 'required' => false, 'data'=>$nameaddr))
            ->add('lastnameaddr', 'text', array('label' => 'Apellido ', 'required' => false,'data'=>$lastnameaddr))
            ->add('emailaddr', 'text', array('label' => 'Email ', 'required' => false,'data'=>$emailaddr))
            ->add('phoneaddr', 'text', array('label' => 'Teléfono ', 'required' => false,'data'=>$phoneaddr))
            ->add('mobileaddr', 'text', array('label' => 'Móvil ', 'required' => false,'data'=>$mobileaddr))
            ->add('number', 'text', array('label' => 'Número', 'required' => false,'data'=>$number))
            ->add('search', 'button', array('label' => 'Buscar'))
        ;
        $form = $formBuilder->getForm();
        return $form;
    }
    /**
     * Finds and displays a Guide entity.
     *
     * @Route("/search", name="guide_search")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function searchAction()
    {
        $form = $this->createSearchForm('','','','','','','','','','','');
        return array(
            'form'   => $form->createView(),
            'entities' => null,
            'searching'=> 1,
        );
    }
    /**
     * Finds and displays a Guide entity.
     *
     * @Route("/listing", name="guide_listing")
     * @Template("NvCargaBundle:Guide:search.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function listingAction(Request $request)
    {
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();

        $number = $request->query->get('number');
        $namesender = $request->query->get('namesender');
        $lastnamesender = $request->query->get('lastnamesender');
        $emailsender = $request->query->get('emailsender');
        $phonesender = $request->query->get('phonesender');
        $mobilesender = $request->query->get('mobilesender');
        $nameaddr = $request->query->get('nameaddr');
        $lastnameaddr = $request->query->get('lastnameaddr');
        $emailaddr = $request->query->get('emailaddr');
        $phoneaddr = $request->query->get('phoneaddr');
        $mobileaddr = $request->query->get('mobileaddr');
        $em = $this->getDoctrine()->getManager();

        $sender = null;
        $makequery = false;
        $query='';
        $param = array();
        if ($namesender) {
            $query = $query . 'c.name LIKE :name';
            $param['name'] = '%'.$namesender.'%';
            $makequery = true;
        }
        if ($lastnamesender) {
            if ($makequery) {
                $query = $query . ' AND ';
            }
            $makequery = true;
            $query = $query . 'c.lastname LIKE :lastname';
            $param['lastname'] = '%'.$lastnamesender.'%';

        }
        if ($emailsender) {
            if ($makequery) {
                $query = $query . ' AND ';
            }
            $makequery = true;
            $query = $query . 'c.email LIKE :email';
            $param['email'] = '%'.$emailsender.'%';

        }
        if ($phonesender) {
            if ($makequery) {
                $query = $query . ' AND ';
            }
            $makequery = true;
            $query = $query . 'c.phone LIKE :phone';
            $param['phone'] = '%'.$phonesender.'%';

        }
        if ($mobilesender) {
            if ($makequery) {
                $query = $query . ' AND ';
            }
            $makequery = true;
            $query = $query . 'c.mobile LIKE :mobile';
            $param['mobile'] = '%'.$mobilesender.'%';

        }
        $param['thecompany'] = $maincompany;
        if ($makequery) {
            $sender = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('c')
                ->select('c.id')
                ->where($query)
                ->andWhere('c.maincompany =:thecompany')
                ->setParameters($param)
                ->getQuery()
                ->getResult();
        }

        $addr = null;
        $makequery = false;
        $query='';
        $param = array();
        if ($nameaddr) {
            $query = $query . 'c.name LIKE :name';
            $param['name'] = '%'.$nameaddr.'%';
            $makequery = true;
        }
        if ($lastnameaddr) {
            if ($makequery) {
                $query = $query . ' AND ';
            }
            $makequery = true;
            $query = $query . 'c.lastname LIKE :lastname';
            $param['lastname'] = '%'.$lastnameaddr.'%';

        }
        $liscustomer = null;
        if ($emailaddr) {
            $listcustomer = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('c')
                ->where('c.email LIKE :email')
                ->andWhere('c.maincompany =:thecompany')
                ->setParameters(array('email' => '%'.$emailaddr.'%', 'thecompany'=> $maincompany))
                ->getQuery()
                ->getResult();
            if ($makequery) {
                $query = $query . ' AND ';
            }
            $makequery = true;
            $query = $query . 'c.customer IN (:listcustomer)';
            $param['listcustomer'] = $listcustomer;
        }
        if ($phoneaddr) {
            if ($makequery) {
                $query = $query . ' AND ';
            }
            $makequery = true;
            $query = $query . 'c.phone LIKE :phone';
            $param['phone'] = '%'.$phoneaddr.'%';

        }
        if ($mobileaddr) {
            if ($makequery) {
                $query = $query . ' AND ';
            }
            $makequery = true;
            $query = $query . 'c.mobile LIKE :mobile';
            $param['mobile'] = '%'.$mobileaddr.'%';

        }

        if ($makequery) {
            $addr = $em->getRepository('NvCargaBundle:Baddress')->createQueryBuilder('c')
                ->select('c.id')
                ->where($query)
                ->setParameters($param)
                ->getQuery()
                ->getResult();
        }

        $form = $this->createSearchForm($number,$namesender,$lastnamesender,$emailsender,$phonesender,$mobilesender,$nameaddr,$lastnameaddr,$emailaddr,$phoneaddr,$mobileaddr);
        $entities = null;
        $query='';
        $param=array();
        $makequery = false;
        if ($number) {
            $query = $query . 'g.number LIKE :number';
            $param['number'] = '%'.$number.'%';
            $makequery = true;
        }
        if ($sender) {
            if ($makequery) {
                $query = $query . ' AND ';
            }
            $makequery = true;
            $query = $query . 'g.sender IN (:sender)';
            $param['sender'] = $sender;

        }
        if ($addr) {
            if ($makequery) {
                $query = $query . ' AND ';
            }
            $makequery = true;
            $query = $query . 'g.addressee IN (:addr)';
            $param['addr'] = $addr;
        }
        if ($agency->getType() != 'MASTER') {
            if ($makequery) {
                $query = $query . ' AND ';
            }
            $query = $query . 'g.agency =:agency';
            $param['agency'] = $agency;
        }

        $param['thecompany'] = $maincompany;
        if ($makequery) {
            // exit(\Doctrine\Common\Util\Debug::dump($param));
            $entities = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                ->where($query)
                ->andWhere('g.maincompany =:thecompany')
                ->setParameters($param)
                ->getQuery()
                ->getResult();
        }

        return array(
            'form'   => $form->createView(),
            'entities' => $entities,
            'searching'=> 0,
        );
    }
    private function createFilterForm($agency, $cod, $paidtype, $service, $date1, $date2)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        if ($agency) {
            $listar=$em->getRepository('NvCargaBundle:Agency')->findByMaincompany($maincompany);
            $agdefault = array();
            foreach ($listar as $val) {
                $agdefault[$val->getId()]= $val->getName();
            }
            $agchecked = true;
            $listagency = $agency;
        } else {
            $agdefault= null;
            $agchecked = false;
            $listagency = array();
        }
        if ($cod) {
            $listar=$em->getRepository('NvCargaBundle:COD')->findAll();
            $coddefault = array();
            foreach ($listar as $val) {
                $coddefault[$val->getId()]= $val->getName();
            }
            $codchecked = true;
            $listcod = $cod;
        } else {
            $coddefault=null;
            $codchecked = false;
            $listcod = array();
        }
        if ($paidtype) {
            $listar=$em->getRepository('NvCargaBundle:Paidtype')->findBy(array('active'=>true, 'deleted'=>false));
            $paiddefault = array();
            foreach ($listar as $val) {
                $paiddefault[$val->getId()]= $val->getName();
            }
            $paidchecked = true;
            $listpaidtype = $paidtype;
        } else {
            $paiddefault=null;
            $paidchecked = false;
            $listpaidtype = array();
        }
        if ($service) {
            $listar=$em->getRepository('NvCargaBundle:Shippingtype')->findAll();
            $serdefault = array();
            foreach ($listar as $val) {
                $serdefault[$val->getId()]= $val->getName();
            }

            $serchecked = true;
            $listservice = $service;
        } else {
            $serdefault= null;
            $serchecked = false;
            $listservice = array();
        }
        if (($date1) && ($date2)) {
            $d1 = new \DateTime($date1);
            $d2 = new \DateTime($date2);
            $d1 = $d1->format('m/d/Y');
            $d2 = $d2->format('m/d/Y');
            $datechecked = true;
        } else {
            $d1 = '';
            $d2 = '';
            $datechecked = false;
        }
        $formBuilder = $this->get('form.factory')->createNamedBuilder('guide_filter', 'form', array())
        ->add('agency', 'checkbox', array('label' => 'Agencia', 'required'=>false, 'data' => $agchecked))
        ->add('agvalue', 'choice', array('label' => ' ', 'placeholder' => '-- Escoja la agencia --',
                    'choices' => $agdefault, 'multiple'  => false, 'data' => $agency ))
        ->add('cod', 'checkbox', array('label' => 'Tipo de pago', 'required'=>false, 'data' => $codchecked))
        ->add('codvalue', 'choice', array('label' => ' ', 'placeholder' => '-- Escoja el tipo de pago --',
                    'choices' => $coddefault,'multiple'  => false, 'data' => $cod ))
        ->add('paidtype', 'checkbox', array('label' => 'Forma de pago', 'required'=>false, 'data' => $paidchecked))
        ->add('paidvalue', 'choice', array('label' => ' ', 'placeholder' => '-- Escoja la forma de pago --',
                    'choices' => $paiddefault,'multiple'  => false, 'data' => $paidtype ))
        ->add('service', 'checkbox', array('label' => 'Servicio', 'required'=>false, 'data' => $serchecked))
        ->add('servalue', 'choice', array('label' => ' ','placeholder' => '-- Escoja el tipo de servicio --',
                    'choices' => $serdefault,'multiple'  => false, 'data' => $service ))
        ->add('daterange', 'checkbox', array('label' => 'Rango de fecha', 'required'=>false, 'data' => $datechecked))
        ->add('dateini','text', array('label' => 'Desde ', 'data' => $d1))
        ->add('dateend','text', array('label' => 'Hasta ', 'data' => $d2))
        ->add('filter', 'button', array('label' => 'Filtrar'))
        ;
        $form = $formBuilder->getForm();
        return $form;
    }

    /**
     * @Route("/filter", name="guide_filter")
     */
    public function filterAction(Request $request) {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        $namefilter = $request->query->get('filtername');
        if (!$namefilter) {
            throw $this->createNotFoundException('Debe escoger un filtro');
        }
        $maincompany = $this->getUser()->getMaincompany();
        $repo = 'NvCargaBundle:'.$namefilter;
        $result = array();
        if ($namfilter == 'Agency') {
            $values = $this->getDoctrine()->getManager()->getRepository($repo)->findByMaincompany($maincompany);
        } else {
            $values = $this->getDoctrine()->getManager()->getRepository($repo)->findAll();
        }
        foreach ($values as $value) {
            $result[$value->getName()] = $value->getId();
        }
        return new JsonResponse($result);
    }
    /**
     * Lists all Guide entities.
     *
     * @Route("/lisfilter", name="guide_listfilter")
     * @Template("NvCargaBundle:Guide:index.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function listfilterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $agency = $user->getAgency();
        $maincompany = $user->getMaincompany();

        $theagen = $request->query->get('theagen');
        $thecod = $request->query->get('thecod');
        $thepaid = $request->query->get('thepaid');
        $theser = $request->query->get('theser');

        /* $filternames=json_decode($thefilters);
        $filtervalues=json_decode($thevalues);
        $filters = array();
        for ($i=0;$i<count($filternames);$i++) {
            $filters[$filternames[$i]] = $filtervalues[$i];
        }
        $field = $request->query->get('field');
        $value = $request->query->get('value'); */
        $dateini = $request->query->get('dateini');
        $dateend = $request->query->get('dateend');

        $param=array();
        /* $query = 'g.id IN (:guidelist)';
        $param['guidelist'] = $guidelist; */
        $query = null;

        if ($agency->getType() != 'MASTER') {
            $query = 'g.agency = :agency';
            $param['agency'] = $agency;
        } else {
            if ($theagen) {
                $query = 'g.agency = :theagen';
                $param['theagen'] = $theagen;
            }
        }
        if ($thecod) {
            if ($query) {
                $query = $query . ' AND g.cod = :thecod';
            } else {
                $query = 'g.cod = :thecod';
            }
                $param['thecod'] = $thecod;
        }
        if ($thepaid) {
            if ($query) {
                $query = $query . ' AND g.paidtype = :thepaid';
            } else {
                $query = 'g.paidtype = :thepaid';
            }
                $param['thepaid'] = $thepaid;
        }
        if ($theser) {
            if ($query) {
                $query = $query . ' AND g.shippingtype = :theser';
            } else {
                $query = 'g.shippingtype = :theser';
            }
                $param['theser'] = $theser;
        }
        if ($dateini && $dateend && ($dateini <= $dateend)) {
            $dateend2 = new \DateTime($dateend);
            $dateend2->modify('+59 minutes');
            $dateend2->modify('+23 hours');

            if ($query) {
                $query = $query . ' AND g.creationdate BETWEEN :dateini AND :dateend';
            } else {
                $query = 'g.creationdate BETWEEN :dateini AND :dateend';
            }
            $param['dateini'] = new \DateTime($dateini);
            $param['dateend'] = $dateend2;
        }
        $query = $query . 'g.maincompany = :thecompany';
        $param['thecompany'] = $maincompany;
        $form = $this->createFilterForm($theagen, $thecod, $thepaid, $theser, $dateini, $dateend);
        // exit(\Doctrine\Common\Util\Debug::dump($query));
        $entities = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                ->where($query)
                ->setParameters($param)
                ->getQuery()
                ->getResult();
        return array(
            'entities' => $entities,
            'form'   => $form->createView(),
            // 'filters' => $filters,
        );
    }
    /**
     * Lists all guide for a pobox without bill.
     *
     * @Route("/pobox", name="guide_pobox")
     * @Template()
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function poboxAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $pobox = $user->getPobox();
        $customer = null;

        if ($pobox) {
            $customer = $pobox->getCustomer();
        }
        if (!$customer) {
            throw $this->createNotFoundException('No está registrado como cliente');
        }
        $entities = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                ->where('g.addressee in (:addressee) OR g.sender =:sender')
                ->andwhere('g.bill IS NULL')
                ->andWhere('g.maincompany = :thecompany')
                ->setParameters(array('addressee'=> $customer->getBaddress(), 'thecompany'=>$maincompany, 'sender' => $customer))
                ->getQuery()
                ->getResult();

        return array(
            'customer' => $customer,
            'entities' => $entities,
        );
    }
   /**
     * Creates a form to create a Guide entity.
     *
     * @param Guide $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createAlertForm(Guide $entity)
    {
        if ($entity->getImageSize() > 0) {
            $hasimage=true;
        } else {
            $hasimage=false;
        }

        $user = $this->getUser();

        $form = $this->createForm(new GuideType($this->getDoctrine()->getManager(),$user), $entity, array());

        $form->add('submit', 'submit', array('label' => 'Crear '));
        $form->remove('agency');
        $form->remove('packages');
        $form->add('agency', 'entity', array('label' => 'Agencia ', 'class' => 'NvCargaBundle:Agency',
                            'data' => $user->getAgency(), 'read_only'  => true, 'disabled' => true)) ;
        $form->add('packages', 'collection', array('mapped'=>true, 'required' => false, 'label' => false,
                            'type' => new MinipackType(), 'allow_add' => false, 'allow_delete' => false,
                            'by_reference' => false, 'prototype' => true, 'error_bubbling' => false,
                            'attr' => array('class' => 'list-packages', 'TABINDEX' => 18), 'options' => array('label' => false)));
        if (count($entity->getServices()) > 0) {
            $form->add('services', 'collection', array('mapped' => true,
                    'required' => false, 'label' => false, 'type' => new ClassServType(),'allow_add' => false,
                'allow_delete' => false, 'by_reference' => false, 'prototype' => true, 'error_bubbling' => false,
                'attr' => array('class' => 'list-services'),'options' => array('label' => false),
                ));
        }

        $form->remove('file');

        $form->add('file', null , array('label' => ' ', 'required' => false, 'read_only'=> $hasimage, 'disabled' => $hasimage));


        return $form;
    }
   /**
     * Creates a new Guide entity with one alert.
     * @Route("/alert", name="guide_alert")
     * @Template("NvCargaBundle:Guide:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function alertAction(Request $request)
    {
        $alert = $request->query->get('alert');
        if (!$alert) {
            throw $this->createNotFoundException('Debe suministar un número de alerta');
        }
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $user->getAgency();
        $maincompany = $user->getMaincompany();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $thealert = $em->getRepository('NvCargaBundle:Alert')->findOneBy(['id'=>$alert,'maincompany'=>$maincompany]);
        if (!$thealert) {
            throw $this->createNotFoundException('No existe la alerta');
        }

        $entity = new Guide();
        $pack = new Minipack();
        $pack->setWeight($thealert->getWeight());
        $pack->setValue($thealert->getValue());
        $pack->setTracking($thealert->getTracking());
        $entity->addPackage($pack);

        $sender= $thealert->getPobox()->getCustomer();
        $addr = $thealert->getBaddress();
        $city = $addr->getCity();
        $regs = $city->getRegions();
        $regions = array();
        $ware =  $thealert->getPobox()->getWarehouse();
        $agency = $this->getUser()->getAgency();
        if (($agency->getType() != "MASTER") && ($agency->getWarehouse() !== $ware)) {
            throw $this->createNotFoundException('No tiene permiso para esta acción');
        }
        foreach ($regs as $reg) {
            $regions[] = $reg->getId();
        }
        $image = null;
        if ($thealert->getImageSize() > 0) {
            $image = base64_encode(stream_get_contents($thealert->getImageData()));
            $entity->setImageSize($thealert->getImageSize());
            $entity->setImageType($thealert->getImageType());
            $imagedata = $thealert->getImageData();
            $entity->setImageData(base64_decode($image));
        }
        //exit(\Doctrine\Common\Util\Debug::dump($imagedata));

        $sertypes = $em->getRepository('NvCargaBundle:Servicetype')->findByShippingtype($thealert->getShippingtype());
        $tariffs = $em->getRepository('NvCargaBundle:Tariff')->findBy(array('region' => $regions, 'agency' => $agency, 'service' => $sertypes));
        $services = $em->getRepository('NvCargaBundle:Adservice')->findBy(['isactive'=>true, 'maincompany'=>$maincompany]);
        // exit(\Doctrine\Common\Util\Debug::dump($services));
        foreach ($services as $service) {
            $serv = new ClassServ();
            $serv->setId($service->getId());
            $serv->setName($service->getName());
            $serv->setDescription($service->getDescription());
            $serv->setMeasure($service->getMeasure());
            $serv->setPrice($service->getPrice());
            $serv->setAmount(0);
            $serv->setTotal(0);
            $entity->addService($serv);
        }
        $entity->setSender($thealert->getPobox()->getCustomer());
        $entity->setAddressee($thealert->getBaddress());

        if ($thealert->getInsurance()) {
            $insvalue=$thealert->getValue();
        } else {
            $insvalue=0.0;
        }
        $entity->setInsuranceAmount($insvalue);
        $entity->setAddressee($thealert->getBaddress());
        $entity->setSender($thealert->getPobox()->getCustomer());
        $entity->setcontain($thealert->getDescription());
        $entity->setRealweight($thealert->getWeight());
        $entity->setPieces($thealert->getPieces());
        $entity->setDeclared($thealert->getValue());
        $form=$this->createAlertForm($entity);
        $form->handleRequest($request);
        $edition = true;
        if ($form->isValid()) {
            $movdate = new \DateTime();
            $clock = $form['clock']->getData();
            $rdate = substr($movdate->format('m/d/Y'),0,10) . 'T' . $clock;
            $entity->setCreationdate(new \DateTime($rdate));
            if ($thealert->getImageSize() <= 0 ) {
                $entity->upload();
            }
            $tid = $form['tariffid']->getData();
            $tariff= $em->getRepository('NvCargaBundle:Tariff')->find($tid);
            $service=$tariff->getShippingtype();
            $entity->setConsol(null);
	    	$entity->setShippingtype($service);
            $entity->setTariff($tariff);
            $entity->setProcessedby($this->getUser());
            $entity->setAgency($agency);
            $entity->setSender($sender);
            $sender->addSguide($entity);
            $entity->setAddressee($addr);
            // $addr->addRguide($entity);
            $hid = $em->createQueryBuilder()
                        ->select('MAX(e.id)')
                        ->from('NvCargaBundle:Guide', 'e')
                        ->getQuery()
                        ->getSingleScalarResult();
            $hid+=1;
            $agcod = str_pad($entity->getAgency()->getId(), 3, '0', STR_PAD_LEFT);

            // Asignando los paises origen y destino
            $entity->setCountryto($entity->getAddressee()->getCity()->getState()->getCountry());
            $entity->setCountryfrom($agency->getCity()->getState()->getCountry());
            // CREAR EL RECIBO PARA ESTA GUIA NUEVO
            $countreceipt = $maincompany->getCountreceipts();

            $plan = $maincompany->getPlan();
            if (($plan->getReceipts()) && ($countreceipt >= $plan->getMaxreceipts())) {
                $translator = $this->get('translator');
                $message = 'Ha llegado al número máximo de ' . $translator->trans('Recibos')  .' permitidos. Para crear más debe actualizar su plan a uno superior.';
                if ($this->isGranted('ROLE_ADMIN')) {
                    $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                }
                $flashBag->add('notice',$message);
                goto next;
            }
            $countreceipt++;
            $maincompany->setCountreceipts($countreceipt);
            $receipt = new Receipt();
            $typerec = $em->getRepository('NvCargaBundle:Receipttype')->findOneBy(array('name'=>'Casillero'));
            $carrier = $thealert->getCarrier();

            $highest_id = $em->getRepository('NvCargaBundle:Receipt')->createQueryBuilder('r')
                            ->select('MAX(r.id)')
                            ->where('r.maincompany =:thecompany')
                            ->setParameters(['thecompany'=>$maincompany])
                            ->getQuery()
                            ->getSingleScalarResult();
            $highest_id++;

            $receipt->setShipper($sender);
            $receipt->setReceiver($addr);
            $sender->addShipped($receipt);

            $receipt->setType($typerec);
            $receipt->setReceiptdBy($this->getUser());
            $packages = $entity->getPackages();
            $comcod = str_pad($maincompany->getId(), 2, '0', STR_PAD_LEFT);
            $number = "PKG" . $comcod . $highest_id . 'P1';
            // $number = "WR-" . $maincompany->getAcronym() . '-' .  $highest_id . '-P1';
            $receipt->setNumber($number);
            $receipt->setCreationdate($entity->getCreationdate());
            $receipt->setArrivedate($entity->getCreationdate());
            $receipt->setTracking($packages[0]->getTracking()); // OJO el TRACKING DEL RECIBO
            $translator = $this->get('translator');

            $receipt->setReference('Creado con ' . $translator->trans('Guía'));
            $receipt->setDescription($thealert->getDescription());
            $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneBy(array('name' =>'Procesado'));
            $receipt->setStatus($status);
            $receipt->setCarrier($carrier);
            $receipt->setAgency($entity->getAgency());
            $receipt->setQuantity($entity->getPieces());
            $receipt->setWeight($entity->getRealweight());
            $receipt->setLength($packages[0]->getLength());
            $receipt->setWidth($packages[0]->getWidth());
            $receipt->setHeight($packages[0]->getHeight());

            $receipt->setValue($entity->getDeclared());
            $receipt->setGuide($entity);
            $receipt->setNote($thealert->getInstructions());
            $receipt->setMaincompany($maincompany);
            if (!$packages[0]->getNpack())  {
                    $receipt->setNpack(1);
                } else {
                    $receipt->setNpack($packages[0]->getNpack());
                }
                $receipt->setPacktype($packages[0]->getPacktype());
            if ($maincompany->getFirststatus()) {
                $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                $newstatus = new Statusreceipt();
                $newstatus->setReceipt($receipt);
                $newstatus->setDate($receipt->getCreationdate());
                $newstatus->setPlace($receipt->getAgency()->getCity());
                $newstatus->setStep($step);
                $newstatus->setComment($translator->trans('Guía') . ' creada');
                $receipt->addListstatus($newstatus);
                $em->persist($newstatus);
            }
            $em->persist($receipt);
            $entity->addReceipt($receipt);
            // $thealert->setReceipt($receipt); // PRUEBA.......

            // CREAR EL PRIMER MOVIMIENTO DE LA GUIA
            $maincompany = $this->getUser()->getMaincompany();
            $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
            $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');
            $translator = $this->get('translator');

            /*
            // ELIMINADA LA CREACION DEL PRIMER MOVIMIENTO PARA LA GUIA
            $moveguide = new Moveguides($this->get('translator'), $this->get('mailer'), $setfrom, $fromname, $em, $this->getUser());
            $moveguide->setMovdate(new \DateTime());
            $moveguide->setGuide($entity);
            $agentype = $entity->getAgency()->getType()->getName();

            $stamov = null;
            if ($agentype == 'MASTER' ) {
                    $stamov = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName('En Master');
            } else {
                    $stamov = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName('En Sucursal');
            }
            if (!$stamov) {
                $stamov = $em->getRepository('NvCargaBundle:Guidestatus')->find(1);
            }

            $moveguide->setStatus($stamov);
            $company = $em->getRepository('NvCargaBundle:Localcompany')->findOneByName('Empresa');
            $moveguide->setCompany($company);
            $moveguide->setTrack($entity->getNumber());
            $moveguide->setPercentage(0);
            $moveguide->setComment('Registro de '. $translator->trans('Guía'). ', debe incluirse en '. $translator->trans('Consolidado'));
            $entity->addMove($moveguide);

            $em->persist($moveguide);
            */
            $adservs = $entity->getServices();
            foreach ($adservs as $adserv) {
                if ($adserv->getAmount() > 0) {
                    $thiserv = $em->getRepository('NvCargaBundle:Adservice')->find($adserv->getId());
                    $servtoguide = new Servtoguide();
                    $servtoguide->setServicedate(new \DateTime());
                    $servtoguide->setAmount($adserv->getAmount());
                    $servtoguide->setTotal($adserv->getTotal());
                    $servtoguide->setAdservice($thiserv);
                    $servtoguide->setGuide($entity);
                    $em->persist($servtoguide);
                }
            }
            $numini= $entity->getAgency()->getMaincompany()->getIniguide();
//             $highest_id = $em->createQueryBuilder()
//                 ->select('MAX(e.id)')
//                 ->from('NvCargaBundle:Guide', 'e')
//                 ->getQuery()
//                 ->getSingleScalarResult();
//             if ($numini >= $highest_id) {
//                 $val=$numini + $highest_id + 1;
//             } else {
//                 $val=$highest_id+1;
//             }
            $countguide = $maincompany->getCountguides();

            $plan = $maincompany->getPlan();
            if (($plan->getGuides()) && ($countguide >= $plan->getMaxguides())) {
                    $translator = $this->get('translator');
                    $message = 'Ha llegado al número máximo de ' . $translator->trans('Guías')  .' permitidas. Para crear más debe actualizar su plan a uno superior.';
                    if ($this->isGranted('ROLE_ADMIN')) {
                        $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                    }
                    $flashBag->add('notice',$message);
                    goto next;
            }
            $number = $this->genNumber($entity);
            $entity->setNumber($number);
            $countguide++;
            $maincompany->setCountguides($countguide);
            if ($entity->getTariff()->getMeasure()->getName() == 'Lb' ) {
                $entity->setRoundmeasure($entity->getAgency()->getMaincompany()->getRoundweight());
            } else {
                $entity->setRoundmeasure($entity->getAgency()->getMaincompany()->getRoundvol());
            }
            $entity->setMaincompany($maincompany);
            if ($maincompany->getFirststatus()) {
                $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                $newstatus = new Statusguide();
                $newstatus->setGuide($entity);
                $newstatus->setDate($entity->getCreationdate());
                $newstatus->setPlace($entity->getAgency()->getCity());
                $newstatus->setStep($step);
                $newstatus->setComment($translator->trans('Guía') . ' creada');
                $entity->addListstatus($newstatus);
                $em->persist($newstatus);
            }
            $em->persist($entity);
            $em->flush();
            if ($entity->getEmailnoti()) {
                $this->sendemail($entity);
            }

            return $this->redirect($this->generateUrl('guide_show', array('id' => $entity->getId())));
        } else {
            if  ($form['edition']->getData()) {
                $edition = false;
            }
        }
        $translator = $this->get('translator');
        next:
        return array(
                'entity' => $entity,
                'form'   => $form->createView(),
                'tariffs' => $tariffs,
                'image' => $image,
                'services' => $services,
                'readonly' => true,
                'edition' => $edition,
                'nameform' => $translator->trans('Guía') . ' para Alerta',
            );
    }
   /**
     * @Route("/select_guides", name="select_guides")
     */
    public function ajaxAction(Request $request) {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        $user=$this->getUser();
        $maincompany = $user->getMaincompany();
        $email = $request->query->get('customer_email');
        if (!$email) {
            throw $this->createNotFoundException('Debe suministrar el email del usuario');
        }
        $em = $this->getDoctrine()->getManager();
        $customer = $em->getRepository('NvCargaBundle:Customer')->findOneBy(['email'=>$email,'maincompany'=>$maincompany]);
        /* if (!$customer) {
            throw $this->createNotFoundException('No existe el usuario');
        } */
        $result = array();
        // Return a list of states
        $guides= $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                ->where('g.addressee in (:addressee) OR g.sender = :sender')
                ->andwhere('g.bill IS NULL')
                ->setParameters(array('addressee'=> $customer->getBaddress(), 'sender'=> $customer))
                ->orderBy('g.number', 'ASC')
                ->getQuery()
                ->getResult();

        $payments = $em->getRepository('NvCargaBundle:Payment')->findAll();
        $guidespay = array();
        foreach ($payments as $pay) {
            $guidespay[] = $pay->getGuide()->getId();
        }
        foreach ($guides as $guide) {
            if (!in_array($guide->getId(),$guidespay)) {
                $result[$guide->getNumber()] = $guide->getId();
            }
        }
        return new JsonResponse($result);
    }
   /**
     * @Route("/totailpaid_guide", name="totalpaid_guide")
     */
    public function ajax2Action(Request $request) {
        $user=$this->getUser();
        $maincompany = $user->getMaincompany();
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        $guideid = $request->query->get('guide_id');
        if (!$guideid) {
            throw $this->createNotFoundException('Debe suministrar el número');
        }
        $em = $this->getDoctrine()->getManager();
        $guide = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$guideid,'maincompany'=>$maincompany]);
        if (!$guide) {
            throw $this->createNotFoundException('No existe');
        }
        //$result = array();
        // Return a list of states
        /* $guides= $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
			->where('g.addressee = :addressee OR g.sender = :sender')
			->andwhere('g.bill IS NULL')
    			->setParameters(array('addressee'=> $customer, 'sender'=> $customer))
    			->orderBy('g.number', 'ASC')
    			->getQuery()
			->getResult();
        foreach ($guides as $guide) {
            $result[$guide->getNumber()] = $guide->getId();
        } */
        return new JsonResponse($guide->getTotalpaid());
    }

    private function createGraphYForm()
    {
        $translator = $this->get('translator');
        $label = 'Cantidad de ' . $translator->trans('Guías');
        $formBuilder = $this->get('form.factory')->createNamedBuilder('guide_filter', 'form', array())
            ->add('type', 'choice', array('label' => 'Tipo de Gráfico','choices'=>array('1' => $label, '2' => 'Monto vendido'),
                    'required'  => true,'multiple' => false,'data' => 1,
                ))
            ->add('year', 'choice', array('label' => 'Año',
                    'choices' => range(date('Y') - 5, date('Y')),  //SOLO 5 AÑOS HACIA ATRAS....
                    'required'  => true, 'multiple' => false,
                    'data' => sizeof(range(date('Y') - 5, date('Y'))) -1,  //SOLO 5 AÑOS HACIA ATRAS....
                ))
            ->add('filter', 'button', array('label' => 'Procesar'));

        $form = $formBuilder->getForm();
        return $form;
    }
   /**
     *
     * @Route("/graphmonths", name="guide_graphmonths")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function graphmonthsAction()
    {
        $form = $this->createGraphYForm();

        return array(
            'entities' => null,
            'form'   => $form->createView(),
            // 'filters' => $filters,
        );
    }

   /**
     * @Route("/graphics", name="guide_graphics")
     * @Template("NvCargaBundle:Guide:graphmonths.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
   public function graphicsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $user->getAgency();
        $maincompany = $user->getMaincompany();
        $entities= array();
        $count = 0;

        $type = $request->query->get('type');
        $year = $request->query->get('year');

        if (($type != 1) and ($type != 2)) {
            throw $this->createNotFoundException('Debe escoger el tipo de gráfico apropiado');
        }
        if ($type == 1) {
            if ($agency->getType() == "MASTER" ) {
                $agencies = $em->getRepository("NvCargaBundle:Agency")->findByMaincompany($maincompany);
                foreach ($agencies as $theagen) {
                    $result = $em->getRepository("NvCargaBundle:Guide")
                                ->createQueryBuilder('g')
                                ->select('MONTH(g.creationdate) as month, COUNT(g.id) as numg')
                                ->where('YEAR(g.creationdate) = :year')
                                ->andwhere('g.agency = :ag')
                                ->setParameters(array('ag' => $theagen, 'year' => $year))
                                ->groupBy('month')
                                ->getQuery()
                                ->getResult();
                    $entities[$theagen->getName()]= $result;
                }
            } else {
                $result = $em->getRepository("NvCargaBundle:Guide")
                        ->createQueryBuilder('g')
                        ->select('MONTH(g.creationdate) as month, COUNT(g.id) as numg')
                        ->where('YEAR(g.creationdate) = :year')
                        ->andwhere('g.agency = :ag')
                        ->setParameters(array('ag' => $agency, 'year' => $year))
                        ->groupBy('month')
                        ->getQuery()
                        ->getResult();
                $entities[$agency->getName()]= $result;
            }
        } else {
            if ($agency->getType() == "MASTER" ) {
                $agencies = $em->getRepository("NvCargaBundle:Agency")->findByMaincompany($maincompany);
                foreach ($agencies as $theagen) {
                    $result = $em->getRepository("NvCargaBundle:Guide")
                            ->createQueryBuilder('g')
                            ->select('MONTH(g.creationdate) as month, sum(g.totalpaid) as numg')
                            ->where('YEAR(g.creationdate) = :year')
                            ->andwhere('g.agency = :ag')
                            ->setParameters(array('ag' => $theagen, 'year' => $year))
                            ->groupBy('month')
                            ->getQuery()
                            ->getResult();
                    $entities[$theagen->getName()]= $result;
                }
            } else {
                $result = $em->getRepository("NvCargaBundle:Guide")
                        ->createQueryBuilder('g')
                        ->select('MONTH(g.creationdate) as month, sum(g.totalpaid) as numg')
                        ->where('YEAR(g.creationdate) = :year')
                        ->andwhere('g.agency = :ag')
                        ->setParameters(array('ag' => $agency, 'year' => $year))
                        ->groupBy('month')
                        ->getQuery()
                        ->getResult();
                $entities[$agency->getName()]= $result;
            }
        }
        //exit(\Doctrine\Common\Util\Debug::dump($entities));
        $form = $this->createGraphYForm();
        return array(
                'form'   => $form->createView(),
                'entities' => $entities,
            );
    }
    private function createGraphForm()
    {
        $formBuilder = $this->get('form.factory')->createNamedBuilder('guide_filter', 'form', array())
        ->add('month', 'choice', array('label' => 'Mes', 'choices'   => array(	'1' => 'Enero', '2' =>
                    'Febrero', '3' => 'Marzo', '4' => 'Abril', '5' => 'Mayo', '6' => 'Junio', '7' => 'Julio',
                    '8' => 'Agosto', '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' =>'Diciembre'),
                    'required'  => true, 'multiple' => false, 'data' => date('n'),))
        ->add('year', 'choice', array('label' => 'Año', 'choices'   => range(date('Y') - 5, date('Y')),  //SOLO 5 AÑOS HACIA ATRAS....
                    'required'  => true,  'multiple' => false,
                    'data' => sizeof(range(date('Y') - 5, date('Y'))) -1,  //SOLO 5 AÑOS HACIA ATRAS....
                    ))
        ->add('filter', 'button', array('label' => 'Procesar'));

        $form = $formBuilder->getForm();
        return $form;
    }
   /**
     *
     * @Route("/graphdays", name="guide_graphdays")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function graphdaysAction()
    {
        $form = $this->createGraphForm();

        return array(
            'entities' => null,
            'form'   => $form->createView(),
            //'filters' => $filters,
        );
    }
    /**
     *
     * @Route("/graphics2", name="guide_graphics2")
     * @Template("NvCargaBundle:Guide:graphdays.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */
    public function graphics2Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $agency = $user->getAgency();
        $entities= array();
        $count = 0;

        $month = $request->query->get('month');
        $year = $request->query->get('year');
        if ((!$month) or (!$year)) {
            throw $this->createNotFoundException('No se puede ralizar el gráfico');
        }

        if ($agency->getType() == "MASTER" ) {
            $agencies = $em->getRepository("NvCargaBundle:Agency")->findByMaincompany($maincompany);
            foreach ($agencies as $theagen) {
                $result = $em->getRepository("NvCargaBundle:Guide")
                        ->createQueryBuilder('g')
                        ->select('DAYOFMONTH(g.creationdate) as day, COUNT(g.id) as numg')
                        ->where('MONTH(g.creationdate) = :month')
                        ->andwhere('YEAR(g.creationdate) = :year')
                        ->andwhere('g.agency = :ag')
                        ->setParameters(array('ag' => $theagen, 'month' => $month, 'year' => $year))
                        ->groupBy('day')
                        ->getQuery()
                        ->getResult();
                $entities[$theagen->getName()]= $result;
            }
        } else {
            $result = $em->getRepository("NvCargaBundle:Guide")
                    ->createQueryBuilder('g')
                    ->select('DAYOFMONTH(g.creationdate) as day, COUNT(g.id) as numg')
                    ->where('MONTH(g.creationdate) = :month')
                    ->andwhere('YEAR(g.creationdate) = :year')
                    ->andwhere('g.agency = :ag')
                    ->setParameters(array('ag' => $agency, 'month' => $month, 'year' => $year))
                    ->groupBy('day')
                    ->getQuery()
                    ->getResult();
            $entities[$agency->getName()]= $result;
        }

        // exit(\Doctrine\Common\Util\Debug::dump($entities[$theagen->getName()]));
        // exit(\Doctrine\Common\Util\Debug::dump($entities));
        $form = $this->createGraphForm();
        return array(
                'entities' => $entities,
                'form'   => $form->createView(),
            );
    }
   /**
     * @Route("/findguide", name="findguide")
    */
    public function findguideAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMainCompany();
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        $translator = $this->get('translator');
        $guidenumber = $request->query->get('guidenumber');
        $consolid = $request->query->get('consolid');
        if (!$consolid) {
            throw $this->createNotFoundException('Debe suministrar el número del ' . $translator->trans('Consolidado'));
        }
        // exit(\Doctrine\Common\Util\Debug::dump($guidenumber));
        if (!$guidenumber) {
            throw $this->createNotFoundException('Debe suministrar el número de ' . $translator->trans('Guía'));
        }
        $consol = $em->getRepository('NvCargaBundle:Consolidated')->findOneBy(['id'=>$consolid,'maincompany'=>$maincompany]);

        if (!$consol) {
            throw $this->createNotFoundException('NO EXISTE el ' . $translator->trans('Consolidado'));
        }
        $agency = $consol->getAgency();

        $guides = $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                    //->where('g.agency = :ag')
                    ->where('g.number = :number')
                    ->andwhere('g.consol IS NULL')
                    ->andwhere('g.bag IS NULL')
                    ->andwhere('g.countryto = :ct')
                    ->andwhere('g.shippingtype = :st')
                    ->andwhere('g.maincompany = :thecompany')
                    // ->andwhere('g.agency = :ag')
                    ->setParameters(array(// 'ag' => $agency,
                            'ct' => $consol->getCountryto(),
                            'st'=> $consol->getShippingtype(),
                            'number' => $guidenumber, 'thecompany' => $maincompany))
                    //->setParameters(array('ag' => $agency, 'number' => $guidenumber))
                    ->getQuery()
                    ->getResult();
        $result = [];
        if ($guides) {
            foreach ($guides as $guide) {
                $result['id']=$guide->getId();
                $result['agency']=$guide->getAgency()->getName();
                $result['number']=$guide->getNumber();
                $result['sender']=$guide->getSender()->getName() . ' ' .  $guide->getSender()->getLastname();
                $result['addressee']=$guide->getAddressee()->getName() . ' ' .  $guide->getAddressee()->getLastname();
                $result['creationdate']=$guide->getCreationdate()->format('m/d/Y');
                $result['realweight']=$guide->getRealweight();
                $result['volumen']= '100'; // $guide->getlength() . 'X' .  $guide->getWidth() . 'X' . $guide->getHeight(); //NUEVO
            }
        }  else {
            $result['id']='';
        }
        return new JsonResponse($result);
    }
    /**
     * Creates a new Guide entity with some receipts.
     * @Route("/guidereempack", name="guide_reempack")
     * @Template("NvCargaBundle:Guide:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function guidereempackAction(Request $request)
    {
        $typecus = $request->query->get('typecus');
        $list = $request->query->get('reclist');

        // exit(\Doctrine\Common\Util\Debug::dump($typecus . ' ' . $reclist));

        $pobox=null;

        $em = $this->getDoctrine()->getManager();
        $reclist=json_decode($list);
        $total=count($reclist);
        $id = (int)$reclist[0];
        $maincompany = $this->getUser()->getMainCompany();
        $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);
        $translator = $this->get('translator');
        if (!$receipt) {
            throw $this->createNotFoundException($total . ' El ' . $translator->trans('Recibo') . ' ' . $id . ' no existe ');
        }
        if ($receipt->getGuide()) {
            throw $this->createNotFoundException(' El ' . $translator->trans('Recibo') . ' ' . $id . ' ya tiene ' . $translator->trans('Guía') . ': ('. $receipt->getGuide()->getNumber() . ').');
        }
        $weight = $receipt->getWeight();
        $pieces = $receipt->getQuantity();
        $value = $receipt->getValue();
        $advr = false;
        $adva = false;
        $bsen = $receipt->getShipper();
        $badd = $receipt->getReceiver();
        $entity = new Guide();
        $entity->newpackages();
        $contain = $translator->trans('Recibos') . ': ' . $receipt->getNumber();
        $hideshipper=false;
        $hidereceiver=false;
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $message1 = $translator->trans('Recibo') . ' 1: ' . $bsen->getName() . ' ' . $bsen->getLastName() .
                    ' (' . $bsen->getAdrdefault()->getCity() . ', ' .
                    $bsen->getAdrdefault()->getCity()->getState()->getCountry() . ")\n";
        $message2 = $translator->trans('Recibo') . '  1: ' .$badd->getName() . ' ' . $badd->getLastName() .
                    ' (' . $badd->getCity() . ', ' .  $badd->getCity()->getState()->getCountry() . ")\n";
        for ($i=1;$i<$total;$i++) {
            $id = (int)$reclist[$i];
            $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);
            $translator = $this->get('translator');
            if (!$receipt) {
                throw $this->createNotFoundException($total . ' El ' . $translator->trans('Recibo') . ' ' . $id . ' no existe ');
            }
            if ($receipt->getGuide()) {
                throw $this->createNotFoundException(' El ' . $translator->trans('Recibo') . ' ' . $id . ' ya tiene ' . $translator->trans('Guía') . ': ('. $receipt->getGuide()->getNumber() . ').');
            }
            $contain = $contain . ', ' . $receipt->getNumber();
            $weight += $receipt->getWeight();
            $pieces += $receipt->getQuantity();
            $value += $receipt->getValue();
            $shipper = $receipt->getShipper();
            $receiver = $receipt->getReceiver();
            $val = $i + 1;
            $message1 = $message1 .  $translator->trans('Recibo') . ' ' . $val . ': ' . $shipper->getName() . ' ' . $shipper->getLastName() .  ' (' . $shipper->getAdrdefault()->getCity() . ', ' .
                    $shipper->getAdrdefault()->getCity()->getState()->getCountry() . ")\n";
            $message2 = $message2 .  $translator->trans('Recibo') . ' ' . $val . ': ' . $receiver->getName() . ' ' . $receiver->getLastName() . ' (' . $receiver->getCity() . ', ' .  $receiver->getCity()->getState()->getCountry() . ")\n";
            if (($bsen != $shipper) && (!$advr)) {
                $advr = true;
            }
            if (($badd != $receiver) && (!$adva)) {
                $adva = true;
            }
        }
        $pack = new Minipack();
        $pack->setWeight($weight);
        /*
        $pack->setLength(0.00);
        $pack->setWidth(0.00);
        $pack->setHeight(0.00);
        */
        $pack->setValue($value);
        // $pack->setTracking(' ');
        $entity->addPackage($pack);
        if ($advr) {
            $flashBag->add('notice',
                    "Los " . $translator->trans('Recibos') . " tienen diferentes remitentes \n" . $message1);
            $hideshipper=false;
        }
        if ($adva) {
            $flashBag->add('notice',
                    "Los " . $translator->trans('Recibos') . " tienen diferentes destinatarios \n" . $message2);
            $hidereceiver=false;
        }
        $entity->setContain($contain);

        $shipper = $bsen;
        $receiver = $badd;
        //$agency = $receipt->getAgency();
        $agency = $this->getUser()->getAgency();

        $services = $em->getRepository('NvCargaBundle:Adservice')->findBy(['isactive'=>true, 'maincompany'=>$maincompany]);
        // exit(\Doctrine\Common\Util\Debug::dump($services));
        foreach ($services as $service) {
            $serv = new ClassServ();
            $serv->setId($service->getId());
            $serv->setName($service->getName());
            $serv->setDescription($service->getDescription());
            $serv->setMeasure($service->getMeasure());
            $serv->setPrice($service->getPrice());
            $serv->setAmount(0);
            $serv->setTotal(0);
            $entity->addService($serv);
        }

        $entity->setPieces($pieces);
        $entity->setRealweight($weight);
        $entity->setDeclared($value);
        $entity->setAgency($agency);
        $entity->setSender($shipper);
        $entity->setAddressee($receiver);
        $form = $this->createReempackForm($entity);
        $form->handleRequest($request);
        $edition = true;


        if ($form->isValid()) {
            $movdate = new \DateTime();
            $clock = $form['clock']->getData();
            $rdate = substr($movdate->format('m/d/Y'),0,10) . 'T' . $clock;
            $entity->setCreationdate(new \DateTime($rdate));

            $entity->upload();
            $tid = $form['tariffid']->getData();
            $tariff= $em->getRepository('NvCargaBundle:Tariff')->find($tid);
            $service=$tariff->getShippingtype();
            $entity->setConsol(null);
            $entity->setShippingtype($service);
            $entity->setTariff($tariff);
            $entity->setProcessedby($this->getUser());
            $entity->setAgency($agency);

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
            $entity->setSender($sender);
            $sender->addSguide($entity);
            $entity->setAddressee($rebaddr);

            $hid = $em->createQueryBuilder()
                        ->select('MAX(e.id)')
                        ->from('NvCargaBundle:Guide', 'e')
                        ->getQuery()
                        ->getSingleScalarResult();
            $hid+=1;
            $agcod = str_pad($entity->getAgency()->getId(), 3, '0', STR_PAD_LEFT);


            // $addr->addRguide($entity);
            // Asignando los paises origen y destino
            $entity->setCountryto($entity->getAddressee()->getCity()->getState()->getCountry());
            $entity->setCountryfrom($entity->getAgency()->getCity()->getState()->getCountry());
                // MODIFICAR LOS RECIBOS SELECCIONADOS
            $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneBy(array('name' =>'Procesado'));
            for ($i=0;$i<$total;$i++) {
                $id = (int)$reclist[$i];
                $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
                $receipt->setStatus($status);
                $receipt->setGuide($entity);
                $entity->addReceipt($receipt);
            }
            // CREAR NUEVO RECIBO ESPECIAL
            $packages = $entity->getPackages();
            $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneBy(array('name' =>'Procesado'));
            $highest_id = $em->getRepository('NvCargaBundle:Receipt')->createQueryBuilder('r')
                            ->select('MAX(r.id)')
                            ->where('r.maincompany =:thecompany')
                            ->setParameters(['thecompany'=>$maincompany])
                            ->getQuery()
                            ->getSingleScalarResult();
            $countreceipt = $maincompany->getCountreceipts();
            $countreceipt = $highest_id;
            $highest_id++;

            $typerec = $em->getRepository('NvCargaBundle:Receipttype')->findOneBy(array('name' =>'Master'));
            foreach ($packages as $key=>$package) { // NUEVO
                $countreceipt++;
                //$package->setTracking(' ');
                $plan = $maincompany->getPlan();
                if (($plan->getReceipts()) && ($countreceipt >= $plan->getMaxreceipts())) {
                    $translator = $this->get('translator');
                    $message = 'Ha llegado al número máximo de ' . $translator->trans('Recibos')  .' permitidos. Para crear más debe actualizar su plan a uno superior.';
                    if ($this->isGranted('ROLE_ADMIN')) {
                        $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                    }
                    $flashBag->add('notice',$message);
                    goto next;
                }
                $receipt = new Receipt();
                $receipt->setShipper($sender);
                $receipt->setReceiver($rebaddr);
                $receipt->setType($typerec);
                $receipt->setReceiptdBy($this->getUser());
                $comcod = str_pad($maincompany->getId(), 2, '0', STR_PAD_LEFT);
                $number = "PKG" . $comcod . $highest_id . 'M1';
                // $number = "WR-" . $maincompany->getAcronym() . '-'. $highest_id . '-M1';
                $receipt->setNumber($number);
                $receipt->setCreationdate($entity->getCreationdate());
                $receipt->setType($typerec);
                $receipt->setArrivedate($entity->getCreationdate());
                $receipt->setTracking($package->getTracking()); // OJO el TRACKING DEL RECIBO
                $translator = $this->get('translator');

                $receipt->setReference('Creado con ' . $translator->trans('Guía'));
                $receipt->setDescription($entity->getContain());
                $receipt->setStatus($status);
                // $receipt->setCarrier($carrier);
                $receipt->setAgency($entity->getAgency());
                $receipt->setQuantity(1);
                $receipt->setWeight($package->getWeight());
                $receipt->setLength($package->getLength());
                $receipt->setWidth($package->getWidth());
                $receipt->setHeight($package->getHeight());
                $receipt->setValue($package->getValue());
                $receipt->setGuide($entity);
                $receipt->setMaincompany($maincompany);
                if (!$package->getNpack())  {
                    $receipt->setNpack(1);
                } else {
                    $receipt->setNpack($package->getNpack());
                }
                $receipt->setPacktype($package->getPacktype());
                if ($maincompany->getFirststatus()) {
                    $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                    $newstatus = new Statusreceipt();
                    $newstatus->setReceipt($receipt);
                    $newstatus->setDate($receipt->getCreationdate());
                    $newstatus->setPlace($receipt->getAgency()->getCity());
                    $newstatus->setStep($step);
                    $newstatus->setComment($translator->trans('Guía') . ' creada');
                    $receipt->addListstatus($newstatus);
                    $em->persist($newstatus);
                }
                $em->persist($receipt);
                // $entity->addReceipt($receipt);
                // $sender->addShipped($receipt);
                // $addr->addReceived($receipt);
                $entity->setMasterec($receipt);
            }

            // CREAR EL PRIMER MOVIMIENTO DE LA GUIA
            $maincompany = $this->getUser()->getMaincompany();
            $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
            $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');

            $translator = $this->get('translator');
            /*
            // ELIMINADA LA CREACION DEL PRIMER MOVIMIENTO PARA LA GUIA
            $moveguide = new Moveguides($this->get('translator'), $this->get('mailer'), $setfrom, $fromname, $em, $this->getUser());
            $moveguide->setMovdate(new \DateTime());
            $moveguide->setGuide($entity);
            $agentype = $entity->getAgency()->getType()->getName();
            $stamov = null;
            if ($agentype == 'MASTER' ) {
                    $stamov = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName('En Master');
            } else {
                    $stamov = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName('En Sucursal');
            }
            if (!$stamov) {
                $stamov = $em->getRepository('NvCargaBundle:Guidestatus')->find(1);
            }
            $moveguide->setStatus($stamov);
            $company = $em->getRepository('NvCargaBundle:Localcompany')->findOneByName('Empresa');
            $moveguide->setCompany($company);
            $moveguide->setTrack($entity->getNumber());
            $moveguide->setPercentage(0);


            $moveguide->setComment('Registro de '. $translator->trans('Guía'). ', debe incluirse en '. $translator->trans('Consolidado'));
            $entity->addMove($moveguide);

            $em->persist($moveguide);
            */

            $adservs = $entity->getServices();
            foreach ($adservs as $adserv) {
                if ($adserv->getAmount() > 0) {
                    $thiserv = $em->getRepository('NvCargaBundle:Adservice')->find($adserv->getId());
                    $servtoguide = new Servtoguide();
                    $servtoguide->setServicedate(new \DateTime());
                    $servtoguide->setAmount($adserv->getAmount());
                    $servtoguide->setTotal($adserv->getTotal());
                    $servtoguide->setAdservice($thiserv);
                    $servtoguide->setGuide($entity);
                    $em->persist($servtoguide);
                }
            }
            //$entity->setNumber(0);
            //$em->persist($entity);
            $cod = $form['cod']->getData();
            $paidtype = $form['paidtype']->getData();
            $numini= $entity->getAgency()->getMaincompany()->getIniguide();
            $countguide =  $maincompany->getCountguides();
            $plan = $maincompany->getPlan();
            if (($plan->getGuides()) && ($countguide >= $plan->getMaxguides())) {
                    $translator = $this->get('translator');
                    $message = 'Ha llegado al número máximo de ' . $translator->trans('Guías')  .' permitidas. Para crear más debe actualizar su plan a uno superior.';
                    if ($this->isGranted('ROLE_ADMIN')) {
                        $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                    }
                    $flashBag->add('notice',$message);
                    goto next;
            }
            $number = $this->genNumber($entity);
            $entity->setNumber($number);
            $countguide++;
            $maincompany->setCountguides($countguide);
            if ($entity->getTariff()->getMeasure()->getName() == 'Lb' ) {
                $entity->setRoundmeasure($entity->getAgency()->getMaincompany()->getRoundweight());
            } else {
                $entity->setRoundmeasure($entity->getAgency()->getMaincompany()->getRoundvol());
            }
            $entity->setMaincompany($maincompany);

            $maincompany->setCountreceipts($countreceipt);
            if ($maincompany->getFirststatus()) {
                $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                $newstatus = new Statusguide();
                $newstatus->setGuide($entity);
                $newstatus->setDate($entity->getCreationdate());
                $newstatus->setPlace($entity->getAgency()->getCity());
                $newstatus->setStep($step);
                $newstatus->setComment($translator->trans('Guía') . ' creada');
                $entity->addListstatus($newstatus);
                $em->persist($newstatus);
            }
            $em->persist($entity);
            $em->flush();
            if ($entity->getEmailnoti()) {
                $this->sendemail($entity);
            }

            return $this->redirect($this->generateUrl('guide_show', array('id' => $entity->getId())));
        }  else {
            if ($form['edition']->getData()) {
                $edition = false;
            }
        }
        next:
            return array(
                'entity' => $entity,
                'form'   => $form->createView(),
                'services' => $services,
                'tariffs' => null,
                'edition' => $edition,
                'nameform' => 'Reempaque en ' . $translator->trans('Guía'),
            );

    }
   /**
     * Creates a form to create a Guide entity.
     *
     * @param Guide $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createReempackForm(Guide $entity)
    {
        $form = $this->createForm(new GuideType($this->getDoctrine()->getManager(),$this->getUser()), $entity, array());
        $form->add('submit', 'submit', array('label' => 'Crear'));
        $form->remove('agency');
        $form->remove('packages');
        $form->add('agency', 'entity', array('label' => 'Agencia ', 'class' => 'NvCargaBundle:Agency',
                            'data' => $entity->getAgency(), 'read_only'  => true, 'disabled' => true)) ;
        $form->add('packages', 'collection', array('mapped'=>true, 'required' => false, 'label' => false,
                            'type' => new MinipackType(), 'allow_add' => false, 'allow_delete' => false,
                            'by_reference' => false, 'prototype' => true, 'error_bubbling' => false,
                            'attr' => array('class' => 'list-packages', 'TABINDEX' => 18), 'options' => array('label' => false)));

        if (count($entity->getServices()) > 0) {
            $form->add('services', 'collection', array('mapped' => true,
                    'required' => false, 'label' => false, 'type' => new ClassServType(),'allow_add' => false,
                'allow_delete' => false, 'by_reference' => false, 'prototype' => true, 'error_bubbling' => false,
                'attr' => array('class' => 'list-services'),'options' => array('label' => false),
                ));
        }

        return $form;
    }

    private function sendemail($entity)
    {
        $number = $entity->getNumber();
        $file = sprintf('guide-%s.pdf', transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $number));
        // exit(\Doctrine\Common\Util\Debug::dump($file));
        $dir = '/tmp';
            $filename = sprintf('%s/%s', $dir, $file);
        $filename = $filename . '';

        $em = $this->getDoctrine()->getManager();
        $services = $em->getRepository('NvCargaBundle:Servtoguide')->findByGuide($entity);

        $viewoption = $this->getView('Guide', 'print');

        $this->get('knp_snappy.pdf')->generateFromHtml($this->renderView($viewoption,
                array('entity' => $entity, 'services' => $services)), $filename,  array(
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
            ), true);

        $body = '<p align="right">Ref:' . $entity->getNumber() . '</p><br>';
        $body = $body . '<b>' .  $entity->getSender()->getName() . ' '  . $entity->getSender()->getLastname() . '</b><br><br>';
        $maincompany = $this->getUser()->getAgency()->getMainCompany();
        $body = $body . 'Su envío se encuentra <b>RECIBIDO</b> por <b>' . $maincompany->getName() . '</b>, adjunto encontrará la Guía que ha sido creada como constancia del mismo.<br><br>';
        $body = $body . 'Fecha: ' .  $entity->getCreationdate()->format('m/d/Y') . '<br>';
        $body = $body . 'Número: ' .  $entity->getNumber() . '<br>';
        $body = $body . 'Remitente: ' .   $entity->getSender()->getName() . ' '  . $entity->getSender()->getLastname() . '<br>';
        $body = $body . 'Destinatario: ' .   $entity->getAddressee()->getName() . ' '  . $entity->getAddressee()->getLastname() . '<br>';
        $body = $body . 'Bultos: ' .  $entity->getPieces() . '<br>';
        $body = $body . 'Peso: ' .  $entity->getRealweight() . '<br><br>';
        $body = $body . 'Si desea consultar el estatus de la misma, sírvase ingresar en nuestro ';
        $body = $body . '<a href="http://' . $_SERVER['SERVER_NAME'] . '/tracking/tracking"> ENLACE de Seguimiento de Guías</a>, usando el número de Guía suministrado; o a través de su casillero personal para mayor detalle. <br><br>';
        $body = $body . 'Gracias por su confianza y ser parte de la familia <b>"' . $maincompany->getName() . '"</b><br><br>';
        $translator = $this->get('translator');
        $color = $translator->trans('tailcolor');
        $body = $body . '<p style="font-size:20px; color:' . $color . ';"><b>**ESTE ES UN CORREO NO MONITOREADO, POR FAVOR NO RESPONDA AL MISMO**</b></p>';
        $maincompany = $this->getUser()->getMaincompany();
        $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
        $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');

        $message = \Swift_Message::newInstance()
            //->setBcc($setfrom)
            //->setFrom(array($setfrom => $fromname))
            ->setContentType("text/html")
            ->setSubject('Guía '. $entity->getNumber() . ' creada en ' . $maincompany->getName())
            // ->setTo($entity->getAddressee()->getCustomer()->getEmail())
            //->setTo($entity->getSender()->getEmail())
            ->attach(\Swift_Attachment::fromPath($filename)->setFilename($file))
            ->setBody($body);

        $send = 0;

        try {
            $message->setTo($entity->getSender()->getEmail());
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
            $msg->setSubject('Error enviando email (Movimiento de guía)');
            $msg->setBody($head);
            $msg->setCreationdate(new \DateTime());
            $msg->setIsread(FALSE);
            $em->persist($msg);

            $em->flush();
        }
        return $send;
    }
    /**
     *
     * @Route("/{id}/sendemail", name="guide_sendemail")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDE')")
     */

    public function sendemailAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        $translator = $this->get('translator');

        if (!$entity) {
            throw $this->createNotFoundException('No existe ' . $translator->trans('Guía'));
        }
        $this->sendemail($entity);

        return $this->redirect($this->generateUrl('guide_show', array('id' => $entity->getId())));
    }

    public function genNumber($entity) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $entity->getAgency();
        $maincompany = $user->getMaincompany();
        $highest_id =  $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('r')
                            ->select('MAX(r.id)')
                            ->where('r.maincompany =:thecompany')
                            ->setParameters(['thecompany'=>$maincompany])
                            ->getQuery()
                            ->getSingleScalarResult();

        $numini= $maincompany->getIniguide();
        if ($maincompany->getName() != 'Globalenvía') {
            if ($highest_id) {
                $guidehigh =  $em->getRepository('NvCargaBundle:Guide')->find($highest_id);
                $numberhigh = $guidehigh->getNumber();
                $maxvalue = filter_var($numberhigh, FILTER_SANITIZE_NUMBER_INT);
                $maxvalue = str_replace('-', '', $maxvalue);
                $maxnumber = abs(intval($maxvalue));
                // exit(\Doctrine\Common\Util\Debug::dump([$maxnumber, $maxvalue, $numberhigh]));
            } else {
                $maxnumber = 0;
            }
            if ($numini > $maxnumber) {
                $val = $numini + 1;
            } else {
                $val = $maxnumber + 1;
            }
            //$number = $maincompany->getPrefixguide()  . '-'. $maincompany->getAcronym() . '-' . $val;
            $number = $maincompany->getPrefixguide() . $val;
            //exit(\Doctrine\Common\Util\Debug::dump($val . '=>' .  $numberhigh));

        } else {
            if ($highest_id) {
                $guidehigh =  $em->getRepository('NvCargaBundle:Guide')->find($highest_id);
                $numberhigh = $guidehigh->getNumber();
                $maxvalue = filter_var($numberhigh, FILTER_SANITIZE_NUMBER_INT);
                $maxvalue = str_replace('-', '', $maxvalue);
                $maxnumber = abs(intval($maxvalue));
            } else {
                $maxnumber = 0;
            }
            if ($numini > $maxnumber) {
                $val = $numini + 1;
            } else {
                $val = $maxnumber + 1;
            }
            $theval = str_pad($val, 5, '0', STR_PAD_LEFT);
            $number = $agency->getName()[0] . $user->getName()[0] . $entity->getCountryto()->getCode() . $entity->getShippingtype()->getName()[0] . $theval;
        }
        return strtoupper($number);
    }
    public function getView($table, $type) {
        $option = null;
        $maincompany = $this->getUser()->getMaincompany();
        //exit(\Doctrine\Common\Util\Debug::dump($maincompany->getFormat()));
        if ($maincompany->getFormat()) {
            if ($type == 'print') {
                $option = $maincompany->getFormat()->getGprint();
            } else {
                $option = $maincompany->getFormat()->getGlprint();
            }
        }
        $viewoption = 'NvCargaBundle:' . $table . ':' . $type . $option . '.html.twig';
        if (!$this->get('templating')->exists($viewoption) ) {
            $viewoption = 'NvCargaBundle:' . $table . ':' . $type . '.html.twig';
        }
        return $viewoption;
    }
    /**
     * Creates a new Guide entity with some receipts.
     * @Route("/guidewhrec", name="guide_whrec")
     * @Template("NvCargaBundle:Guide:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDE') or has_role('ROLE_ADMIN')")
     */
    public function guidewhrecAction(Request $request)
    {

        $whrec_id = $request->query->get('whrec_id');

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();

        $whrec = $em->getRepository('NvCargaBundle:WHrec')->findOneBy(['id'=>$whrec_id, 'maincompany'=>$maincompany]);
        //exit(\Doctrine\Common\Util\Debug::dump($whrec->getNumber()));

        $translator = $this->get('translator');
        if (!$whrec) {
            throw $this->createNotFoundException(' El ' . $translator->trans('Warehouse') . ' ' . $whrec_id . ' no existe ');
        }
        if ($whrec->getGuide()) {
            throw $this->createNotFoundException(' El ' . $translator->trans('Warehouse') . ' ' . $whrec->getNumber() . ' ya tiene ' . $translator->trans('Guía') . ': ('. $whrec->getGuide()->getNumber() . ').');
        }
        $weight = 0;
        $pieces = 0;
        $value = 0;
        $bsen = $whrec->getShipper();
        $badd = $whrec->getReceiver();
        $entity = new Guide();
        $entity->newpackages();


        $translator = $this->get('translator');
        $contain = $translator->trans('Warehouse'). ': ' . $whrec->getNumber();
        $flashBag = $this->get('session')->getFlashBag();
        $hideshipper=false;
        $hidereceiver=false;
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $agency = $whrec->getAgency();
        foreach ($whrec->getReceipts() as $receipt) {
            if (!$receipt) {
                throw $this->createNotFoundException($total . ' El ' . $translator->trans('Recibo') . ' ' . $id . ' no existe ');
            }
            if ($receipt->getGuide()) {
                throw $this->createNotFoundException(' El ' . $translator->trans('Recibo') . ' ' . $id . ' ya tiene ' . $translator->trans('Guía') . ': ('. $receipt->getGuide()->getNumber() . ').');
            }
            $weight += $receipt->getWeight();
            $pieces += $receipt->getQuantity();
            $value += $receipt->getValue();

            $pack = new Minipack();
            $pack->setWeight($receipt->getWeight());
            $pack->setLength($receipt->getLength());
            $pack->setWidth($receipt->getWidth());
            $pack->setHeight($receipt->getHeight());
            $pack->setValue($receipt->getValue());
            $pack->setTracking($receipt->getTracking());
            $pack->setNpack($receipt->getNpack());
            $pack->setPacktype($receipt->getPacktype());
            $entity->addPackage($pack);
        }
        $entity->setContain($contain);
        $shipper = $bsen;
        $receiver = $badd;

        $services = $em->getRepository('NvCargaBundle:Adservice')->findBy(['isactive'=>true,'maincompany'=>$maincompany]);
        // exit(\Doctrine\Common\Util\Debug::dump($services));
        foreach ($services as $service) {
            $serv = new ClassServ();
            $serv->setId($service->getId());
            $serv->setName($service->getName());
            $serv->setDescription($service->getDescription());
            $serv->setMeasure($service->getMeasure());
            $serv->setPrice($service->getPrice());
            $serv->setAmount(0);
            $serv->setTotal(0);
            $entity->addService($serv);
        }
        $entity->setSender($shipper);
        $entity->setAddressee($receiver);
        $entity->setAgency($agency);

        //$form = $this->createRecForm($entity, $hideshipper, $shipper, $hidereceiver, $receiver, $agency, $pieces, $weight, $value);
        $form = $this->createRecForm($entity);

        $form->handleRequest($request);
        $edition = true;
        if ($form->isValid()) {
            $movdate = new \DateTime();
            $clock = $form['clock']->getData();
            $rdate = substr($movdate->format('m/d/Y'),0,10) . 'T' . $clock;
            $entity->setCreationdate(new \DateTime($rdate));

            $entity->upload();
            $tid = $form['tariffid']->getData();
            $tariff= $em->getRepository('NvCargaBundle:Tariff')->find($tid);
            $service=$tariff->getShippingtype();
            $entity->setConsol(null);
	    	$entity->setShippingtype($service);
            $entity->setTariff($tariff);
            $entity->setProcessedby($this->getUser());
            $entity->setAgency($agency);

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
            $entity->setSender($sender);
            // $whrec->setShipper($sender);
            $sender->addSguide($entity);
            $entity->setAddressee($rebaddr);

            //$whrec->setReceiver($sender);
            $hid = $em->createQueryBuilder()
                        ->select('MAX(e.id)')
                        ->from('NvCargaBundle:Guide', 'e')
                        ->getQuery()
                        ->getSingleScalarResult();
            $hid+=1;
            $agcod = str_pad($entity->getAgency()->getId(), 3, '0', STR_PAD_LEFT);

            // $addr->addRguide($entity);
            // Asignando los paises origen y destino
            $entity->setCountryto($entity->getAddressee()->getCity()->getState()->getCountry());
            $entity->setCountryfrom($entity->getAgency()->getCity()->getState()->getCountry());
            // MODIFICAR LOS RECIBOS SELECCIONADOS
            $status = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneBy(array('name' =>'Procesado'));
            $packages = $entity->getPackages();
            $count = 0;
            foreach ($whrec->getReceipts() as $receipt) {
                $package = $packages[$count];
                $count++;
                $receipt->setStatus($status);
                $receipt->setGuide($entity);
                $entity->addReceipt($receipt);
                $receipt->setWeight($package->getWeight());
                $receipt->setLength($package->getLength());
                $receipt->setWidth($package->getWidth());
                $receipt->setHeight($package->getHeight());
                $receipt->setValue($package->getValue());
                $receipt->setTracking($package->getTracking());
                $receipt->setNpack($package->getNpack());
                $receipt->setPacktype($package->getPacktype());
            }
            $whrec->setGuide($entity);
            $entity->setWHrec($whrec);
            // CREAR EL PRIMER MOVIMIENTO DE LA GUIA
            $maincompany = $this->getUser()->getMaincompany();
            $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
            $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');
            $translator = $this->get('translator');

            /*
            // ELIMINADA LA CREACION DEL PRIMER MOVIMIENTO PARA LA GUIA
            $moveguide = new Moveguides($this->get('translator'), $this->get('mailer'), $setfrom, $fromname, $em, $this->getUser());
            $moveguide->setMovdate(new \DateTime());
            $moveguide->setGuide($entity);
            $agentype = $entity->getAgency()->getType()->getName();

            $stamov = null;
            if ($agentype == 'MASTER' ) {
                    $stamov = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName('En Master');
            } else {
                    $stamov = $em->getRepository('NvCargaBundle:Guidestatus')->findOneByName('En Sucursal');
            }
            if (!$stamov) {
                $stamov = $em->getRepository('NvCargaBundle:Guidestatus')->find(1);
            }
            $moveguide->setStatus($stamov);
            $company = $em->getRepository('NvCargaBundle:Localcompany')->findOneByName('Empresa');
            $moveguide->setCompany($company);
            $moveguide->setTrack($entity->getNumber());
            $moveguide->setPercentage(0);


            $moveguide->setComment('Registro de '. $translator->trans('Guía'). ', debe incluirse en '. $translator->trans('Consolidado'));
            $entity->addMove($moveguide);
            $em->persist($moveguide);
            */
            $adservs = $entity->getServices();
            foreach ($adservs as $adserv) {
                if ($adserv->getAmount() > 0) {
                    $thiserv = $em->getRepository('NvCargaBundle:Adservice')->find($adserv->getId());
                    $servtoguide = new Servtoguide();
                    $servtoguide->setServicedate(new \DateTime());
                    $servtoguide->setAmount($adserv->getAmount());
                    $servtoguide->setTotal($adserv->getTotal());
                    $servtoguide->setAdservice($thiserv);
                    $servtoguide->setGuide($entity);
                    $em->persist($servtoguide);
                }
            }
            //$entity->setNumber(0);
            //$em->persist($entity);
            $cod = $form['cod']->getData();
            $paidtype = $form['paidtype']->getData();
            $numini= $maincompany->getIniguide();
//             $highest_id = $em->createQueryBuilder()
//                         ->select('MAX(e.id)')
//                         ->from('NvCargaBundle:Guide', 'e')
//                         ->getQuery()
//                         ->getSingleScalarResult();
//             if ($numini >= $highest_id) {
//                 $val=$numini + $highest_id + 1;
//             } else {
//                 $val=$highest_id+1;
//             }
            $countguide = $maincompany->getCountguides();
//             $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('r')
//                             ->select('count(r.id)')
//                             ->where('r.maincompany =:thecompany')
//                             ->setParameters(['thecompany'=>$maincompany])
//                             ->getQuery()
//                             ->getSingleScalarResult();
            $plan = $maincompany->getPlan();
            if (($plan->getGuides()) && ($countguide >= $plan->getMaxguides())) {
                    $translator = $this->get('translator');
                    $message = 'Ha llegado al número máximo de ' . $translator->trans('Guías')  .' permitidas. Para crear más debe actualizar su plan a uno superior.';
                    if ($this->isGranted('ROLE_ADMIN')) {
                        $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                    }
                    $flashBag->add('notice',$message);
                    goto next;
            }

            $number = $this->genNumber($entity);
            $entity->setNumber($number);
            $countguide++;
            $maincompany->setCountguides($countguide);
            if ($entity->getTariff()->getMeasure()->getName() == 'Lb' ) {
                $entity->setRoundmeasure($entity->getAgency()->getMaincompany()->getRoundweight());
            } else {
                $entity->setRoundmeasure($entity->getAgency()->getMaincompany()->getRoundvol());
            }
            $entity->setMaincompany($maincompany);
            if ($maincompany->getFirststatus()) {
                $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneByName('Creado');
                $newstatus = new Statusguide();
                $newstatus->setGuide($entity);
                $newstatus->setDate($entity->getCreationdate());
                $newstatus->setPlace($entity->getAgency()->getCity());
                $newstatus->setStep($step);
                $newstatus->setComment($translator->trans('Guía') . ' creada');
                $entity->addListstatus($newstatus);
                $em->persist($newstatus);
            }
            $em->persist($entity);
            $em->flush();
            if ($entity->getEmailnoti()) {
                $this->sendemail($entity);
            }

            return $this->redirect($this->generateUrl('guide_show', array('id' => $entity->getId())));
        } else {
            if ($form['edition']->getData()) {
                $edition = false;
            }
        }
        next:
            return array(
                'entity' => $entity,
                'form'   => $form->createView(),
                'tariffs' => null,
                'services' => $services,
                'edition' => $edition,
                'nameform' => $translator->trans('Guía') . ' para ' . $translator->trans('Warehouse'),
                );

    }

}
