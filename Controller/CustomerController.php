<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Customer;
use NvCarga\Bundle\Entity\Baddress;
use NvCarga\Bundle\Entity\City;
use NvCarga\Bundle\Form\CustomerType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;



/**
 * Customer controller.
 *
 * @Route("/customer")
 */
class CustomerController extends Controller
{

    /**
     * Lists all Customer entities.
     *
     * @Route("/index", name="customer")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CUSTOMER') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_CUSTOMER')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $countryto = $user->getAgency()->getCity()->getstate()->getCountry();
        $maincompany = $user->getMaincompany();
        
       
        $shareagencies = $em->getRepository('NvCargaBundle:Agency')->findBy(['maincompany'=>$maincompany, 'sharecustomer'=>true]);
        if ($user->getAgency()->getType() != 'MASTER') {
            $entities = $em->getRepository('NvCargaBundle:Customer')->findBy(['agency'=>$shareagencies,'active'=>true]);
            $agencies = $shareagencies;
        } else {
            $entities= $em->getRepository('NvCargaBundle:Customer')->findBy(['maincompany'=>$maincompany,'active'=>true]);
            $agencies = $em->getRepository('NvCargaBundle:Agency')->findByMaincompany($maincompany);
        }

        return array(
            'entities' => $entities,
            'agencies' => $agencies,
        );
    }
    /**
     * Lists all Inactive Customer entities.
     *
     * @Route("/indexoff", name="customeroff")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CUSTOMER') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_CUSTOMER')")
     */
    public function indexoffAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $countryto = $user->getAgency()->getCity()->getstate()->getCountry();
        $maincompany = $user->getMaincompany();
        
       
        $shareagencies = $em->getRepository('NvCargaBundle:Agency')->findBy(['maincompany'=>$maincompany, 'sharecustomer'=>true]);
        if ($user->getAgency()->getType() != 'MASTER') {
            $entities = $em->getRepository('NvCargaBundle:Customer')->findBy(['agency'=>$shareagencies,'active'=>false]);
            $agencies = $shareagencies;
        } else {
            $entities= $em->getRepository('NvCargaBundle:Customer')->findBy(['maincompany'=>$maincompany,'active'=>false]);
            $agencies = $em->getRepository('NvCargaBundle:Agency')->findByMaincompany($maincompany);
        }

        return array(
            'entities' => $entities,
            'agencies' => $agencies,
        );
    }
    /**
     * Creates a new Customer entity.
     *
     * @Route("/create", name="customer_create")
     * @Template("NvCargaBundle:Customer:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_CUSTOMER') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Customer();
        $form   = $this->createCreateForm($entity);
        $maincompany = $this->getUser()->getMaincompany();
        
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $countcustomer =  $maincompany->getCountcustomers();
        $plan = $maincompany->getPlan();
        if (($plan->getCustomers()) && ($countcustomer >= $plan->getMaxcustomers())) {
            $message = 'Ha llegado al número máximo de CLIENTES permitidos. Para crear más debe actualizar su plan a uno superior.';
            if ($this->isGranted('ROLE_ADMIN')) {
                $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
            }
            $flashBag->add('notice',$message);
            goto next;
        }
        $countcustomer++;
        $maincompany->setCountcustomers($countcustomer);
        
        $form->handleRequest($request);
        
        if ($form->isValid()) { 
            $em = $this->getDoctrine()->getManager();
            if ($entity->getemail()) {
                $exist = $em->getRepository('NvCargaBundle:Customer')->findOneBy(['email'=>$entity->getEmail(),'maincompany' => $maincompany]);
            } else {
                $exist = null;
            }
            /*
            if (($exist)  && ($maincompany->getAcronym() != 'enviamoscarga')) {
                $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        'Ya existe un cliente con el email: ' . $entity->getEmail());
                goto next;
            }
            */
            $id = $form->get("cityid")->getData();
            $dir = $form->get("address")->getData();
            $phone = $form->get("phone")->getData();
            $docid = $form->get("docid")->getData();
            $mobile = $form->get("mobile")->getData();
            $zip = $form->get("zip")->getData();
            // $barrio = $form->get("barrio")->getData();
            $email = $form->get("email")->getData();
            if (!$id) {
                $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        'Debe seleccionar una ciudad');
                goto next;
                //throw $this->createNotFoundException('Debe seleccionar una ciudad');
            }
            /*
            if (!$email) {
                $mainemail = $maincompany->getEmail();
                $pos = strpos($mainemail, '@');
                $domain = substr($mainemail, $pos, strlen($mainemail));
                $pemail = 'cliente_' . strtolower($maincompany->getAcronym()) . $countcustomer . $domain;
                $entity->setEmail($pemail);    	
            } else {
                $entity->setEmail($email);
            }
            */
            $city = $em->getRepository('NvCargaBundle:City')->find($id);
            // $type = $em->getRepository('NvCargaBundle:Customertype')->findOneByName('NORMAL');
            $status = $em->getRepository('NvCargaBundle:Customerstatus')->findOneByName('ACTIVO');
            $entity->setCreationdate(new \DateTime());
                // $entity->setType($type);
            $entity->setStatus($status);
            
        
            $addr = new Baddress();
            $addr->setName($entity->getName());
            $addr->setlastname($entity->getLastname());
            $addr->setCustomer($entity);
            $addr->setAddress($dir);
            $addr->setPhone($phone);
            $addr->setdocid($docid);
            $addr->setmobile($mobile);
            $addr->setzip($zip);
            // $addr->setBarrio($barrio);
            $addr->setCity($city);
            $em->persist($addr);
        
            $entity->setAdrdefault($addr);
            $entity->setAdrmain($addr);
            $entity->addBaddress($addr);
            
            
            $entity->setMaincompany($maincompany);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('customer_show', array('id' => $entity->getId())));
        }
        next:
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Customer entity.
     *
     * @param Customer $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Customer $entity)
    {
        $user = $this->getUser();
        $agency = $user->getAgency();
        $em = $this->getDoctrine()->getManager();
        
        $form = $this->createForm(new CustomerType(), $entity, array(
            'action' => $this->generateUrl('customer_create'),
            'method' => 'POST',
        ));
        
        if ($agency->getType() == "MASTER") {
            $agencies = $em->getRepository('NvCargaBundle:Agency')->findByMaincompany($user->getMaincompany());
            $form->add('agency', 'entity', array('label' => false, 'class' => 'NvCargaBundle:Agency', 
                'placeholder' => '--Seleccione una agencia--', 
                'choices' => $agencies,
                'data'  => $agency));
        } else {
            $form->add('agency', 'entity', array('label' => false, 'class' => 'NvCargaBundle:Agency', 
                'data'  => $agency, 'readonly'=>true));
                
            $form->add('submit', 'submit', array('label' => 'Crear'));
        }
        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Customer entity.
     *
     * @Route("/new", name="customer_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CUSTOMER') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Customer();
        $maincompany = $this->getUser()->getMaincompany();
        $em = $this->getDoctrine()->getManager();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $countcustomer =  $maincompany->getCountcustomers();
        $plan = $maincompany->getPlan();
        if (($plan->getCustomers()) && ($countcustomer >= $plan->getMaxcustomers())) {
            $message = 'Ha llegado al número máximo de CLIENTES permitidos. Para crear más debe actualizar su plan a uno superior.';
            if ($this->isGranted('ROLE_ADMIN')) {
                $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
            }
            $flashBag->add('notice',$message);
        }
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Customer entity.
     *
     * @Route("/{id}/show", name="customer_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $entity = $em->getRepository('NvCargaBundle:Customer')->find($id);
        $pobox = $user->getPobox();
        $countryto = $user->getAgency()->getCity()->getState()->getCountry();
	
        $customer = null;
        if (!$entity) {
                throw $this->createNotFoundException('No existe el cliente');
        }
        if ($pobox) {
            $customer = $pobox->getCustomer();
        }
        $countrycus = $entity->getAdrdefault()->getCity()->getState()->getCountry();
        $admin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ADMIN_CUSTOMER') || $this->isGranted('ROLE_VIEW_CUSTOMER');
        $admin = ($admin) && (($countrycus == $countryto) || ($user->getAgency()->getType()->getName() == 'MASTER')) && ($entity->getMaincompany() == $this->getUser()->getMaincompany());
        
        if (($customer != $entity) && (!$admin)) {
            throw $this->createNotFoundException('No tiene permiso para ver datos del cliente');
        }
        // $deleteForm = $this->createDeleteForm($id);
        $billsc = $em->getRepository('NvCargaBundle:Bill')->findBy(['customer'=>$entity, 'status'=>'COBRADA']);
        $countc = 0;
        $balancec = 0;
        $totalc = 0;
        foreach ($billsc as $bill) {
            $totalc += $bill->getTotal();
            $balancec += $bill->getBalance();
            $countc++;
        }
        $billsp = $em->getRepository('NvCargaBundle:Bill')->findBy(['customer'=>$entity, 'status'=>'PENDIENTE']);
        $countp = 0;
        $balancep = 0;
        $totalp = 0;
        foreach ($billsp as $bill) {
            $totalp += $bill->getTotal();
            $balancep += $bill->getBalance();
            $countp++;
        }
        $bills = $em->getRepository('NvCargaBundle:Bill')->findBy(['customer'=>$entity, 'status'=>['PENDIENTE', 'COBRADA']]);
        return array(
            'entity' => $entity,
            'billsc' => ['count'=>$countc, 'balance'=>$balancec, 'total'=>$totalc],
            'billsp' => ['count'=>$countp, 'balance'=>$balancep, 'total'=>$totalp],
            'bills' => $bills,
            // 'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Customer entity.
     *
     * @Route("/{id}/edit", name="customer_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $entity = $em->getRepository('NvCargaBundle:Customer')->find($id);
        $pobox = $user->getPobox();
        $customer = null;
        $countryto = $user->getAgency()->getCity()->getState()->getCountry();
        if (!$entity) {
                throw $this->createNotFoundException('No existe el cliente');
            }
        if ($pobox) {
            $customer = $pobox->getCustomer();
        }
        if ($entity->getAdrdefault()) {
            $countrycus = $entity->getAdrdefault()->getCity()->getState()->getCountry();
        } else {
            $countrycus = $countryto;
        }
        $admin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ADMIN_CUSTOMER');
        $admin = ($admin) && (($countrycus == $countryto) || ($user->getAgency()->getType()->getName() == 'MASTER')) && ($entity->getMaincompany() == $this->getUser()->getMaincompany());
        if (($customer != $entity) && (!$admin)) {
            throw $this->createNotFoundException('No tiene permiso para editar datos del cliente');
        }

        $editForm = $this->createEditForm($entity, true);
//        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Customer entity (EMAIL).
     *
     * @Route("/{id}/email", name="customer_email")
     * @Method("GET")
     * @Template("NvCargaBundle:Customer:edit.html.twig")
     */
/*
    public function emailAction($id)
    {
        $em = $this->getDoctrine()->getManager();
	$user = $this->getUser();
	$admin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ADMIN_CUSTOMER');
        $entity = $em->getRepository('NvCargaBundle:Customer')->find($id);
	$pobox = $user->getPobox();
	$customer = null;
	if (!$entity) {
            throw $this->createNotFoundException('No existe el cliente');
        }
	
	if (!$admin) {
		throw $this->createNotFoundException('No tiene permiso para editar datos del cliente');
	}

        $editForm = $this->createEmailForm($entity);
        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        //   'delete_form' => $deleteForm->createView(),
        );
    }
*/
    /**
    * Creates a form to edit a Customer entity.
    *
    * @param Customer $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Customer $entity, $edition)
    {
        $form = $this->createForm(new CustomerType(), $entity, array(
            'action' => $this->generateUrl('customer_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        if ($entity->getAdrmain()) {
            $dir = $entity->getAdrmain();
            $city = $dir->getCity();
            $cityname = $city->getName() . ' ('. $city->getState()->getName() . ', ' . $city->getState()->getCountry()->getName() . ')';
        } else {
            $edition= false;
        }
         
        $form->remove('address');
        $form->remove('phone');
        $form->remove('docid');
        $form->remove('mobile');
        $form->remove('barrio');
        $form->remove('zip');
        $form->remove('cityid');
        $form->remove('cityname');
        $form->remove('selcity');
        
        if ($edition) {
            $form
            ->add('address', 'textarea', array('label'=> 'Dirección ', 'mapped'=>false, 'data'=>$dir->getAddress(),
                    'constraints' => array(
                        new NotBlank(["message" => "La dirección no puede estar vacía"]), 
                        new Length(
                        ["min" => 4, "max" => 120,
                        "minMessage" => "La dirección debe tener al menos {{ limit }} caracteres",
                        "maxMessage" => "La dirección no puede tener mas de {{ limit }} caracteres"]))))
            ->add('phone', 'text', array('mapped'=> false,'label'=> 'Teléfono', 'required' => false, 'data'=>$dir->getPhone()))
            ->add('docid', 'text', array('mapped'=> false,'label'=> 'Documento de identidad', 'required' => false, 'data'=>$dir->getDocid()))
            ->add('mobile', 'text', array('mapped'=> false,'label'=> 'Móvil ', 'required' => false, 'data'=>$dir->getMobile()))
            //->add('barrio', 'text', array('mapped'=> false,'label'=> 'Sector ', 'required' => false))
            ->add('zip','text', array('mapped'=> false,'label'=> 'Zip', 'required' => false, 'data'=>$dir->getZip()))
            ->add('cityid', 'hidden', array('mapped'=>false, 'data'=>$city->getId(),))
            ->add('cityname', 'hidden', array('label'=> 'Ciudad ', 'read_only' => true, 'mapped'=>false, 'data'=>$cityname,
                    'constraints' => array(new NotBlank(["message" => "Escoja una ciudad"]))));
        } else {
            $form
            ->add('address', 'textarea', array('label'=> 'Dirección ', 'mapped'=>false, 
                    'constraints' => array(
                        new NotBlank(["message" => "La dirección no puede estar vacía"]), 
                        new Length(
                        ["min" => 4, "max" => 120,
                        "minMessage" => "La dirección debe tener al menos {{ limit }} caracteres",
                        "maxMessage" => "La dirección no puede tener mas de {{ limit }} caracteres"]))))
            ->add('phone', 'text', array('mapped'=> false,'label'=> 'Teléfono', 'required' => false))
            ->add('docid', 'text', array('mapped'=> false,'label'=> 'Documento de identidad', 'required' => false))
            ->add('mobile', 'text', array('mapped'=> false,'label'=> 'Móvil ', 'required' => false))
            //->add('barrio', 'text', array('mapped'=> false,'label'=> 'Sector ', 'required' => false))
            ->add('zip','text', array('mapped'=> false,'label'=> 'Zip', 'required' => false))
            ->add('cityid', 'hidden', array('mapped'=>false))
            ->add('cityname', 'hidden', array('label'=> 'Ciudad ', 'read_only' => true, 'mapped'=>false, 
                    'constraints' => array(new NotBlank(["message" => "Escoja una ciudad"]))));
        }
        
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
    * Creates a form to edit a Customer entity.
    *
    * @param Customer $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
/*
    private function createEmailForm(Customer $entity)
    {
        $form = $this->createForm(new CustomerType(), $entity, array(
            'action' => $this->generateUrl('customer_updatemail', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
*/
    /**
     * Edits an existing Customer entity.
     *
     * @Route("/{id}/update", name="customer_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Customer:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Customer')->find($id);
        $pobox = $user->getPobox();
        $customer = null;
        $countryto = $user->getAgency()->getCity()->getState()->getCountry();
        if (!$entity) {
                throw $this->createNotFoundException('No existe el cliente');
            }
        if ($pobox) {
            $customer = $pobox->getCustomer();
        }
        if ($entity->getAdrdefault()) {
            $countrycus = $entity->getAdrdefault()->getCity()->getState()->getCountry();
        } else {
            $countrycus = $countryto;
        }
        $admin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ADMIN_CUSTOMER');
        $admin = ($admin) && (($countrycus == $countryto) || ($user->getAgency()->getType()->getName() == 'MASTER')) && ($entity->getMaincompany() == $this->getUser()->getMaincompany());
        if (($customer != $entity) && (!$admin)) {
            throw $this->createNotFoundException('No tiene permiso para actualizar datos del cliente');
        }
        $theemail = $entity->getEmail();

//        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity, false);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $newemail = $entity->getEmail();
            if ($theemail != $newemail) {
                $cusemail = $em->getRepository('NvCargaBundle:Customer')->findOneBy(['email'=>$newemail,'maincompany' => $maincompany]);
                /*
                if (($cusemail) && ($maincompany->getAcronym() != 'enviamoscarga'))  {
                    throw $this->createNotFoundException('No puede asignar el email: ' . $newemail . '. Ya existe un cliente registrado con ese email');
                } 
                */
            }
            $cityid = $editForm['cityid']->getData();
            $city = $em->getRepository('NvCargaBundle:City')->find($cityid);
            if ((!$entity->getAdrdefault()) || (!$this->sameDir($editForm, $entity->getAdrmain()))) {
                if ($entity->getAdrmain() == $entity->getAdrdefault()) {
                    $sebaddr = new Baddress();
                    $sebaddr->setName($editForm['name']->getData());
                    $sebaddr->setLastname($editForm['lastname']->getData());
                    $sebaddr->setAddress($editForm['address']->getData());
                    $sebaddr->setCity($city);
                    $sebaddr->setPhone($editForm['phone']->getData());
                    $sebaddr->setMobile($editForm['mobile']->getData());
                    $sebaddr->setZip($editForm['zip']->getData());
                    $sebaddr->setCustomer($entity);
                    $entity->setAdrmain($sebaddr);
                    if (!$entity->getAdrdefault()) {
                        $entity->setAdrdefault($sebaddr);
                    }
                    $entity->addBaddress($sebaddr);
                    $em->persist($sebaddr);
                } else {
                    $sebaddr = $entity->getAdrmain();
                    $sebaddr->setName($editForm['name']->getData());
                    $sebaddr->setLastname($editForm['lastname']->getData());
                    $sebaddr->setAddress($editForm['address']->getData());
                    $sebaddr->setCity($city);
                    $sebaddr->setPhone($editForm['phone']->getData());
                    $sebaddr->setMobile($editForm['mobile']->getData());
                    $sebaddr->setZip($editForm['zip']->getData());
                    $sebaddr->setCustomer($sender);
                }
            }
            $em->flush();

            return $this->redirect($this->generateUrl('customer_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
 //           'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Edits an existing Customer entity.
     *
     * @Route("/{id}/updatemail", name="customer_updatemail")
     * @Method("PUT")
     * @Template("NvCargaBundle:Customer:edit.html.twig")
     */
/*
    public function updatemailAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
	$user = $this->getUser();
	$admin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ADMIN_CUSTOMER');
        $entity = $em->getRepository('NvCargaBundle:Customer')->find($id);
	$pobox = $user->getPobox();
	$customer = null;
	if (!$entity) {
            throw $this->createNotFoundException('No existe el cliente');
        }
	if (!$admin) {
		throw $this->createNotFoundException('No tiene permiso para actualizar datos del cliente');
	}

        // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEmailForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
	    $oldcus = $em->getRepository('NvCargaBundle:Customer')->find($id);
	    $cityid = $editForm->get("cityid")->getData();
	    if (!$cityid) {
			throw $this->createNotFoundException('Debe seleccionar una ciudad');
	    }
	    if ($cityid != $entity->getCity()->getId()) {
		$city = $em->getRepository('NvCargaBundle:City')->find($cityid);
		$entity->setCity($city);
	    }
	    if ($oldcus->getEmail() !== $entity->getEmail()) {
		$lrec = $oldcus->getReceived();
		$lenv = $oldcus->getShipped();
		$grec = $oldcus->getRguides();
		$genv = $oldcus->getSguides();
		foreach ($genv as $guide) {
			$guide->setSender($entity);
			$entity->addSguides($guide);
		}
		foreach ($grec as $guide) {
			$guide->setAddressee($entity);
			$entity->addRguides($guide);
		}
		foreach ($lenv as $rec) {
			$rec->setShipper($entity);
			$entity->addShipped($rec);
		}
		foreach ($lrec as $rec) {
			$rec->setReceiver($entity);
			$entity->addReceived($rec);
		}
		$bills = $em->getRepository('NvCargaBundle:Bill')->findByCustomer($oldcus);
		foreach ($bills as $bill) {
			$bill->setCustomer($entity);
		}
		$pobox = $oldcus->getPobox();
		if ($pobox) {
			$entity->setPobox($pobox);
			$oldcus->setPobox(null);
			$pobox->setCustomer($entity);
		}
		$em->remove($oldcus);
		$em->persist($entity);
	    } 
	    	
            $em->flush();

            return $this->redirect($this->generateUrl('customer_show', array('id' => $entity->getId())));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
           // 'delete_form' => $deleteForm->createView(),
        );
    }
*/
    /**
     * Deletes a Customer entity.
     *
     * @Route("/{id}/delete", name="customer_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_CUSTOMER') or has_role('ROLE_ADMIN')")
     */
     /*
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Customer')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Customer entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('customer'));
    }
*/
    /**
     * Creates a form to delete a Customer entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
     /*
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('customer_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
    */
    private function createSearchForm()
    {
        $formBuilder = $this->get('form.factory')->createNamedBuilder('form_scustomer', 'form', array())
        ->add('name', 'text', array('label' => 'Nombre ', 'required' => false))
        ->add('lastname', 'text', array('label' => 'Apellido ', 'required' => false))
        ->add('search', 'button', array('label' => 'Buscar'))
        ;
        $form = $formBuilder->getForm();
        return $form;
    }

    
    /**
     * search a Customer o list of them.
     * @Route("/search", name="customer_search")
     * @Template("NvCargaBundle:Customer:search.html.twig")
     * @Security("has_role('ROLE_ADMIN_CUSTOMER') or has_role('ROLE_ADMIN')")
     */
    public function searchAction(Request $request)
    {
        $form = $this->createSearchForm();
        $form->handleRequest($request);

        return array(
            'form'   => $form->createView(),
        );
    }
    /**
     * show a list of Customer 
     *
     * @Route("/customer_byemail", name="customer_byemail")
     * @Security("has_role('ROLE_USER')")
     */
    public function byemailAction(Request $request)
    {
        $maincompany = $this->getUser()->getMaincompany();
        $email = $request->query->get('email');
        $emailConstraint = new Email();
        $errorList = $this->get('validator')->validateValue($email, $emailConstraint);
        $result = array();
        $result['error'] = 0; 
        if (count($errorList) != 0) {
           $result['error'] = -1;
           $result['id'] = -1;
           $result['name'] = '';
           goto next;
        }
        $em = $this->getDoctrine()->getManager();
        $customer = $em->getRepository('NvCargaBundle:Customer')->findoneBy(['maincompany'=>$maincompany,'email'=>$email]);
        
        if ($customer) {
            $result['id'] = $customer->getId();
            $result['name'] = $customer->getName() . ' ' . $customer->getLastname();
        } else {
            $result['id'] = 0;
            $result['name'] = '';
        }
        next:
        return new JsonResponse($result); 
    }
    /**
     * show a list of Customer 
     *
     * @Route("/customer_add", name="customer_add")
     * @Security("has_role('ROLE_ADMIN_CUSTOMER') or has_role('ROLE_ADMIN')")
     */
    public function customeraddAction(Request $request)
    {
        $result = array();
        $maincompany = $this->getUser()->getMaincompany();
        $em = $this->getDoctrine()->getManager();
        $countcustomer =  $maincompany->getCountcustomers();
        $plan = $maincompany->getPlan();
        if (($plan->getCustomers()) && ($countcustomer >= $plan->getMaxcustomers())) {
            $message = 'Ha llegado al número máximo de CLIENTES permitidos. Para crear más debe actualizar su plan a uno superior.';
            if ($this->isGranted('ROLE_ADMIN')) {
                $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
            }
            $result['error']=-1;
            $result['message'] = $message;
            goto next;
        } else {
            $result['error']=0;
        }
        // DATOS para crear el nuevo cliente
        $email = $request->query->get('email');
        $name = $request->query->get('name');
        $lastname = $request->query->get('lastname');
        $direccion = $request->query->get('direccion');
        $typecus = $request->query->get('typecus');
        $cityid = $request->query->get('cityid');
        $cityname = $request->query->get('cityname');
        $state = $request->query->get('state');
        $zip = $request->query->get('zip');
        $phone = $request->query->get('phone');
        $mobile = $request->query->get('mobile');
        
        $entity = new Customer();
        $entity->setName($name);
        $typecustomer = $em->getRepository('NvCargaBundle:Customertype')->find($typecus);
        $entity->setType($typecustomer);
        $entity->setCreationdate(new \DateTime());
        if ($email) {
            $entity->setEmail($email);
        }
        if ($lastname) {
            $entity->setLastname($lastname);
        }
        if ($cityid) {
            $thecity=$em->getRepository('NvCargaBundle:City')->find($cityid);
        } else {
            $thestate = $em->getRepository('NvCargaBundle:State')->find($state);
            $thecity = $em->getRepository('NvCargaBundle:City')->findOneBy(['name'=>$cityname,'state'=>$thestate]);
            if (!$thecity) {
                $thecity = new City();
                $thecity->setName($cityname);
                $thecity->setState($thestate);
                $thecity->setActive(false);
                $em->persist($thecity);
            }
        }
        
        
        $baddress = new Baddress();
        $baddress->setCustomer($entity);
        $entity->addBaddress($baddress);
        $entity->setAgency($this->getUser()->getAgency());
        $cstatus = $em->getRepository('NvCargaBundle:Customerstatus')->findOneBy(array('name' =>'ACTIVO'));
        $entity->setStatus($cstatus);
        $entity->setAdrdefault($baddress);
        $entity->setAdrmain($baddress);
        $baddress->setAddress($direccion);
        $baddress->setName($entity->getName());
        $baddress->setLastname($entity->getLastname());
        $baddress->setCity($thecity);
        if ($zip) {
            $baddress->setZip($zip);
        }
        if ($phone) {
            $baddress->setPhone($phone);
        }
        if ($mobile) {
            $baddress->setMobile($mobile);
        }
        $em->persist($baddress);
        $entity->setMaincompany($maincompany);
        $em->persist($entity);
        $countcustomer++;
        $maincompany->setCountcustomers($countcustomer);
        $em->flush();
        
        $result['customer'] = $entity->getId();
        $result['baddress'] = $baddress->getId();
        $result['cityid'] = $thecity->getId();
        
        next:
        return new JsonResponse($result); 
    }
    /**
     * show a list of Customer 
     *
     * @Route("/list", defaults={"name" = null}, name="customer_list")
     * @Template("NvCargaBundle:Customer:list.html.twig")
     * @Security("has_role('ROLE_ADMIN_CUSTOMER') or has_role('ROLE_ADMIN')")
     */
    public function listAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        $maincompany = $this->getUser()->getMaincompany();
        $name = $request->query->get('customer_name');
        $lastname = $request->query->get('customer_lastname');
        $em = $this->getDoctrine()->getManager();
        if (($name) && ($lastname)) {
            $entities = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('o')
                                        ->where('o.name LIKE :name')
                                        ->andWhere('o.lastname LIKE :lastname')
                                        ->andWhere('o.maincompany = :maincompany')
                                        ->andWhere('o.active = :active')
                                        ->setParameters(['name' =>'%'.$name.'%', 'lastname' => '%'.$lastname.'%', 'maincompany'=>$maincompany, 'active'=>true])
                                        ->getQuery()
                                        ->getResult();
        } else { 
            if ($name)  {
                $entities = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('o')
                                                    ->where('o.name LIKE :name')
                                                    ->andWhere('o.maincompany = :maincompany')
                                                    ->andWhere('o.active = :active')
                                                    ->setParameters(['name' =>'%'.$name.'%', 'maincompany'=>$maincompany, 'active'=>true])
                                                    ->getQuery()
                                                    ->getResult();
            } else {
                $entities = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('o')
                                            ->where('o.lastname LIKE :lastname')
                                            ->andWhere('o.maincompany = :maincompany')
                                            ->andWhere('o.active = :active')
                                            ->setParameters(['lastname' => '%'.$lastname.'%', 'maincompany'=>$maincompany, 'active'=>true])
                                            ->getQuery()
                                            ->getResult();
	      
            }
        }
        $result=array();
        $counter = 0;
        foreach ($entities as $entity) {
            $result[$counter]['name'] = $entity->getName();
            $result[$counter]['lastname'] = $entity->getLastname();
            $result[$counter]['docid'] = $entity->getDocid();
            $result[$counter]['address'] = $entity->getAdrdefault()->getAddress();
            $result[$counter]['customerid'] = $entity->getId();
            $city = $entity->getCity();
            $result[$counter]['cityid'] = $city->getId();
            $result[$counter]['cityname'] = $city->getName();
            $result[$counter]['state'] = $city->getState()->getName();
            $result[$counter]['country'] = $city->getState()->getCountry()->getName();
            $result[$counter]['phone'] = $entity->getAdrdefault()->getPhone();
            $result[$counter]['mobile'] = $entity->getAdrdefault()->getMobile();
            $result[$counter]['email'] = $entity->getEmail();
            $result[$counter]['barrio'] = $entity->getAdrdefault()->getBarrio();
            $result[$counter]['zip'] = $entity->getAdrdefault()->getZip();
            $counter++;
        }
        return new JsonResponse($result); 
    }
    /**
     * show a list of all Customer 
     *
     * @Route("/listall", name="customer_listall")
     * @Security("has_role('ROLE_ADMIN_CUSTOMER') or has_role('ROLE_ADMIN')")
     */
    public function listallAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
	
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Customer')->findBy(['maincompany'=>$maincompany,'active'=>true]);
        
        $result=array();
        $counter = 0;
        foreach ($entities as $entity) {
            foreach ($entity->getBaddress() as $dir ) {
                $city = $dir->getCity();
                if ($city) { 
                    $result[$counter]['name'] = $dir->getName();
                    $result[$counter]['lastname'] = $dir->getLastname();
                    if ($dir->getCustomer()->getPobox()) {
                        $result[$counter]['pobox'] = $dir->getCustomer()->getPobox()->getNumber();
                    } else {
                        $result[$counter]['pobox'] = '';
                    }
                    $result[$counter]['email'] = $dir->getCustomer()->getEmail();

                    $result[$counter]['docid'] = $dir->getDocid();
                    $result[$counter]['address'] = $dir->getAddress();
                    $result[$counter]['customerid'] = $entity->getId();
                    
                    $result[$counter]['cityid'] = $city->getId();
                    $result[$counter]['cityname'] = $city->getName();
                    $result[$counter]['state'] = $city->getState()->getName();
                    $result[$counter]['country'] = $city->getState()->getCountry()->getName();
                    $result[$counter]['phone'] = $dir->getPhone();
                    $result[$counter]['mobile'] = $dir->getMobile();
                
                    $result[$counter]['barrio'] = $dir->getBarrio();
                    $result[$counter]['zip'] = $dir->getZip();
                    if ($dir->getCustomer()->getType()) {
                        $result[$counter]['type'] = $dir->getCustomer()->getType()->getName();
                    } else {
                        $result[$counter]['type'] = 'Persona';
                    }
                    $counter++;
                }
            }
        }
//         foreach ($entities as $entity) {
//             $result[$counter]['customerid'] = $entity->getId();
//             $result[$counter]['name'] = $entity->getName();
//             $result[$counter]['lastname'] = $entity->getLastname();
//             if ($entity->getPobox()) {
//                 $result[$counter]['pobox'] = $entity->getPobox()->getNumber();
//             } else {
//                 $result[$counter]['pobox'] = '';
//             }
//             $result[$counter]['email'] = $entity->getEmail();
// 
//             $result[$counter]['docid'] = $entity->getAdrdefault()->getDocid();
//             $result[$counter]['address'] = $entity->getAdrdefault()->getAddress();
//             $city = $entity->getAdrdefault()->getCity();
//             $result[$counter]['cityid'] = $city->getId();
//             $result[$counter]['cityname'] = $city->getName();
//             $result[$counter]['state'] = $city->getState()->getName();
//             $result[$counter]['country'] = $city->getState()->getCountry()->getName();
//             $result[$counter]['phone'] = $entity->getAdrdefault()->getPhone();
//             $result[$counter]['mobile'] = $entity->getAdrdefault()->getMobile();
//             
//             $result[$counter]['barrio'] = $entity->getAdrdefault()->getBarrio();
//             $result[$counter]['zip'] = $entity->getAdrdefault()->getZip();
//             $result[$counter]['type'] = $entity->getType()->getName();
//             $counter++;
//         }
        return new JsonResponse($result); 
    }
    /**
     * show a list of all Customer 
     *
     * @Route("/listdir", name="customer_listdir")
     * @Security("has_role('ROLE_ADMIN_CUSTOMER') or has_role('ROLE_ADMIN')")
     */
    public function listdirAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
	
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Customer')->findBy(['maincompany'=>$maincompany,'active'=>true]);
        $result=array();
        $counter = 0;
        foreach ($entities as $entity) {
            foreach ($entity->getBaddress() as $dir ) {
                $city = $dir->getCity();
                if ($city) { 
                    $result[$counter]['name'] = $dir->getName();
                    $result[$counter]['lastname'] = $dir->getLastname();
                    if ($dir->getCustomer()->getPobox()) {
                        $result[$counter]['pobox'] = $dir->getCustomer()->getPobox()->getNumber();
                    } else {
                        $result[$counter]['pobox'] = '';
                    }
                    $result[$counter]['email'] = $dir->getCustomer()->getEmail();

                    $result[$counter]['docid'] = $dir->getDocid();
                    $result[$counter]['address'] = $dir->getAddress();
                    $result[$counter]['customerid'] = $dir->getId();
                
                    $result[$counter]['cityid'] = $city->getId();
                    $result[$counter]['cityname'] = $city->getName();
                    $result[$counter]['state'] = $city->getState()->getName();
                    $result[$counter]['country'] = $city->getState()->getCountry()->getName();
                    $result[$counter]['phone'] = $dir->getPhone();
                    $result[$counter]['mobile'] = $dir->getMobile();
                
                    $result[$counter]['barrio'] = $dir->getBarrio();
                    $result[$counter]['zip'] = $dir->getZip();
                    if ($dir->getCustomer()->getType()) {
                        $result[$counter]['type'] = $dir->getCustomer()->getType()->getName();
                    } else {
                        $result[$counter]['type'] = 'Persona';
                    }
                    $counter++;
                }
            }
        }
        return new JsonResponse($result); 
    }
    /**
     * show a list of all Customer 
     *
     * @Route("/defaultsender", name="defaultsender")
     * @Security("has_role('ROLE_ADMIN_CUSTOMER') or has_role('ROLE_ADMIN') or has_role('ROLE-ROLE_VIEW_CUSTOMER')")
     */
    public function defaultsenderAction(Request $request)
    {
        /*
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        */
        $em = $this->getDoctrine()->getManager();
        $sender = $request->query->get('sender');
        $customer = $em->getRepository('NvCargaBundle:Customer')->find($sender);
        $result = null;
        if ($customer) {
            $dir = $customer->getAdrdefault();
            $city = $dir->getCity();
            if ($city) { 
                $result['name'] = $dir->getName();
                $result['lastname'] = $dir->getLastname();
                if ($dir->getCustomer()->getPobox()) {
                    $result['pobox'] = $dir->getCustomer()->getPobox()->getNumber();
                } else {
                    $result['pobox'] = '';
                }
                $result['email'] = $dir->getCustomer()->getEmail();

                $result['docid'] = $dir->getDocid();
                $result['address'] = $dir->getAddress();
                $result['customerid'] = $dir->getId();
                $result['cityid'] = $city->getId();
                $result['cityname'] = $city->getName();
                $result['state'] = $city->getState()->getName();
                $result['country'] = $city->getState()->getCountry()->getName();
                $result['phone'] = $dir->getPhone();
                $result['mobile'] = $dir->getMobile();
            
                $result['barrio'] = $dir->getBarrio();
                $result['zip'] = $dir->getZip();
                if ($dir->getCustomer()->getType()) {
                    $result['type'] = $dir->getCustomer()->getType()->getName();
                } else {
                    $result['type'] = 'Persona';
                }
            }
        }
        return new JsonResponse($result); 
    }
    /**
     * show a list of all Customer 
     *
     * @Route("/listcustomer", name="listcustomer")
     * @Security("has_role('ROLE_ADMIN_CUSTOMER') or has_role('ROLE_ADMIN') or has_role('ROLE-ROLE_VIEW_CUSTOMER')")
     */
    public function listcustomerAction(Request $request)
    {
        /*
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        */
        
        $pattern = $request->query->get('pattern');
        $pattern = preg_replace('/\s+/', ' ', $pattern);
        $pos = strpos($pattern, ' ');
        if ($pos === false) {
            $pattern2 = null;
            $pattern1 = $pattern;
        } else {
            $pattern1 = substr($pattern, 0, $pos-1); 
            $pattern2 = substr($pattern, $pos+1); 
        }
        
        $typecus = $request->query->get('typecus');
        
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $shareagencies = $em->getRepository('NvCargaBundle:Agency')->findBy(['maincompany'=>$maincompany, 'sharecustomer'=>true]);
        $shareagencies[] = $this->getUser()->getAgency();
        
        if ($this->getUser()->getAgency()->getType() != 'MASTER') {
            $agencies = $shareagencies;
        } else {
            $agencies = $em->getRepository('NvCargaBundle:Agency')->findByMaincompany($maincompany);
        }
        $result=array();
        $counter = 0;
        if (!$pattern2) {
            $statuspobox = $em->getRepository('NvCargaBundle:Poboxstatus')->findOneByName('ACTIVO');
            $poboxs = $em->getRepository('NvCargaBundle:Pobox')->createQueryBuilder('p')
                ->where('p.number LIKE :number' )
                ->andWhere('p.maincompany = :maincompany')
                ->andWhere('p.status = :status')
                ->setParameters(['number'=>'%'. $pattern. '%','maincompany'=>$maincompany, 'status'=>$statuspobox])
                ->getQuery()
                ->getResult();
            switch ($typecus) {
                case 1:
                    $entities = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('o')
                        ->where('o.name LIKE :name OR o.lastname LIKE :lastname OR o.pobox IN (:poboxs)' )
                        //->setParameter('name', '%'.$name.'%')
                        ->andWhere('o.agency IN (:agencies)')
                        ->andWhere('o.active = :active')
                        ->setParameters(['name'=>$pattern.'%','lastname'=>$pattern.'%', 'poboxs'=>$poboxs, 'agencies'=>$agencies, 'active'=>true])
                        ->setFirstResult(0)
                        ->setMaxResults(25)
                        ->getQuery()
                        ->getResult();
                        
                    foreach ($entities as $entity) {
                        if ($entity->getAdrdefault()) {
                            $dir = $entity->getAdrdefault();
                            $city = $dir->getCity();
                            if ($city) {
                                $result[$counter]['name'] = $entity->getName();
                                $result[$counter]['lastname'] = $entity->getLastname();
                                if ($entity->getPobox()) {
                                    $result[$counter]['pobox'] = $entity->getPobox()->getNumber();
                                } else {
                                    $result[$counter]['pobox'] = '';
                                }
                                $result[$counter]['email'] = $entity->getEmail();
                                $result[$counter]['docid'] = $dir->getDocid();
                                $result[$counter]['address'] = $dir->getAddress();
                                $result[$counter]['customerid'] = $entity->getId();
                                
                                $result[$counter]['cityid'] = $city->getId();
                                $result[$counter]['cityname'] = $city->getName();
                                $result[$counter]['state'] = $city->getState()->getName();
                                $result[$counter]['country'] = $city->getState()->getCountry()->getName();
                                $result[$counter]['phone'] = $dir->getPhone();
                                $result[$counter]['mobile'] = $dir->getMobile();
                            
                                $result[$counter]['barrio'] = $dir->getBarrio();
                                $result[$counter]['zip'] = $dir->getZip();
                                if ($dir->getCustomer()->getType()) {
                                    $result[$counter]['type'] = $dir->getCustomer()->getType()->getName();
                                } else {
                                    $result[$counter]['type'] = 'Persona';
                                }
                                $counter++;
                            }
                        } else {
                            $result[$counter]['name'] = $entity->getName();
                            $result[$counter]['lastname'] = $entity->getLastname();
                            if ($entity->getPobox()) {
                                $result[$counter]['pobox'] = $entity->getPobox()->getNumber();
                            } else {
                                $result[$counter]['pobox'] = '';
                            }
                            $result[$counter]['email'] = $entity->getEmail();
                            $result[$counter]['docid'] = '';
                            $result[$counter]['address'] = '';
                            $result[$counter]['customerid'] = $entity->getId();
                            
                            $result[$counter]['cityid'] = 0;
                            $result[$counter]['cityname'] = '';
                            $result[$counter]['state'] = '';
                            $result[$counter]['country'] = ''; 
                            $result[$counter]['phone'] = '';
                            $result[$counter]['mobile'] = '';
                        
                            $result[$counter]['barrio'] = '';
                            $result[$counter]['zip'] = '';
                            if ($entity->getType()) {
                                $result[$counter]['type'] = $entity->getType()->getName();
                            } else {
                                $result[$counter]['type'] = 'Persona';
                            }
                            $counter++;
                        }
                    }
                    break;
                case 2: 
                    if ($poboxs) {
                        // exit(\Doctrine\Common\Util\Debug::dump($poboxs));
                        $customers = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('o')
                            ->where('o.pobox IN (:poboxs)' )
                            //->setParameter('name', '%'.$name.'%')
                            ->andWhere('o.agency IN (:agencies)')
                            ->andWhere('o.active = :active')
                            ->setParameters(['poboxs'=>$poboxs, 'agencies'=>$agencies,'active'=>true])
                            ->getQuery()
                            ->getResult();
                            
                        $entities = $em->getRepository('NvCargaBundle:Baddress')->createQueryBuilder('o')
                            ->where('o.name LIKE :name OR o.lastname LIKE :lastname OR o.customer IN (:customers)' )
                            ->setParameters(['name'=>$pattern.'%','lastname'=>$pattern.'%', 'customers'=>$customers])
                            ->setFirstResult(0)
                            ->setMaxResults(25)
                            ->getQuery()
                            ->getResult();
                    } else {
                        //$customers = $em->getRepository('NvCargaBundle:Customer')->findBy(['agency'=>$agencies]);
                        $customers = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('o')
                            ->where('o.agency IN (:agencies)')
                            ->andWhere('o.active = :active')
                            ->setParameters(['agencies'=>$agencies,'active'=>true])
                            ->getQuery()
                            ->getResult();
                        
                        $entities = $em->getRepository('NvCargaBundle:Baddress')->createQueryBuilder('o')
                            ->where('o.name LIKE :name OR o.lastname LIKE :lastname' )
                            ->andWhere('o.customer IN (:customers)')
                            ->setParameters(['name'=>$pattern.'%','lastname'=>$pattern.'%', 'customers'=>$customers])
                            ->setFirstResult(0)
                            ->setMaxResults(25)
                            ->getQuery()
                            ->getResult();
                    }
                    $diradd = array();
                    
                    foreach ($entities as $dir) {
                        $city = $dir->getCity();
                        if ($city) { 
                            $result[$counter]['name'] = $dir->getName();
                            $result[$counter]['lastname'] = $dir->getLastname();
                            $thisdir= $dir->getName() . $dir->getLastname();
                            if ($dir->getCustomer()->getPobox()) {
                                $result[$counter]['pobox'] = $dir->getCustomer()->getPobox()->getNumber();
                                $thisdir = $thisdir . $dir->getCustomer()->getPobox()->getNumber();
                                
                            } else {
                                $result[$counter]['pobox'] = '';
                            }
                            $diradd[] = $thisdir;
                            $result[$counter]['email'] = $dir->getCustomer()->getEmail();

                            $result[$counter]['docid'] = $dir->getDocid();
                            $result[$counter]['address'] = $dir->getAddress();
                            $result[$counter]['customerid'] = $dir->getId();
                            $result[$counter]['cityid'] = $city->getId();
                            $result[$counter]['cityname'] = $city->getName();
                            $result[$counter]['state'] = $city->getState()->getName();
                            $result[$counter]['country'] = $city->getState()->getCountry()->getName();
                            $result[$counter]['phone'] = $dir->getPhone();
                            $result[$counter]['mobile'] = $dir->getMobile();
                        
                            $result[$counter]['barrio'] = $dir->getBarrio();
                            $result[$counter]['zip'] = $dir->getZip();
                            if ($dir->getCustomer()->getType()) {
                                $result[$counter]['type'] = $dir->getCustomer()->getType()->getName();
                            } else {
                                $result[$counter]['type'] = 'Persona';
                            }
                            $counter++; 
                        }
                    }
                    $customers = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('o')
                            ->where('o.name LIKE :name OR o.lastname LIKE :lastname OR o.pobox IN (:poboxs)' )
                            ->andWhere('o.agency IN (:agencies)')
                            ->andWhere('o.active = :active')
                            ->setParameters(['name'=>$pattern.'%','lastname'=>$pattern.'%', 'poboxs'=>$poboxs, 'agencies'=>$agencies, 'active'=>true])
                            ->setFirstResult(0)
                            ->setMaxResults(25)
                            ->getQuery()
                            ->getResult();
                        
                    foreach ($customers as $entity) {
                        if ($entity->getPobox()) {
                            $thisdir = $entity->getName() . $entity->getLastname() .  $entity->getPobox()->getNumber();
                            if (!in_array($thisdir,$diradd)) {
                                $result[$counter]['name'] = $entity->getName();
                                $result[$counter]['lastname'] = $entity->getLastname();
                                $result[$counter]['pobox'] = $entity->getPobox()->getNumber();
                                $result[$counter]['email'] = $entity->getEmail();
                                $result[$counter]['docid'] = '';
                                $result[$counter]['address'] = '';
                                $result[$counter]['customerid'] = 0;
                                
                                $result[$counter]['cityid'] = 0;
                                $result[$counter]['cityname'] = '';
                                $result[$counter]['state'] = '';
                                $result[$counter]['country'] = ''; 
                                $result[$counter]['phone'] = '';
                                $result[$counter]['mobile'] = '';
                            
                                $result[$counter]['barrio'] = $entity->getId();
                                $result[$counter]['zip'] = '';
                                if ($entity->getType()) {
                                    $result[$counter]['type'] = $entity->getType()->getName();
                                } else {
                                    $result[$counter]['type'] = 'Persona';
                                }
                                $counter++;
                            }
                        }
                    }
                    break;
                case 3:
                    $customer = $em->getRepository('NvCargaBundle:Customer')->find($pattern);
                    if ($customer) {
                        foreach ($customer->getBaddress() as $dir) {
                            $city = $dir->getCity();
                            if ($city) { 
                                $result[$counter]['name'] = $dir->getName();
                                $result[$counter]['lastname'] = $dir->getLastname();
                                if ($dir->getCustomer()->getPobox()) {
                                    $result[$counter]['pobox'] = $dir->getCustomer()->getPobox()->getNumber();
                                } else {
                                    $result[$counter]['pobox'] = '';
                                }
                                $result[$counter]['email'] = $dir->getCustomer()->getEmail();

                                $result[$counter]['docid'] = $dir->getDocid();
                                $result[$counter]['address'] = $dir->getAddress();
                                $result[$counter]['customerid'] = $dir->getId();
                                $result[$counter]['cityid'] = $city->getId();
                                $result[$counter]['cityname'] = $city->getName();
                                $result[$counter]['state'] = $city->getState()->getName();
                                $result[$counter]['country'] = $city->getState()->getCountry()->getName();
                                $result[$counter]['phone'] = $dir->getPhone();
                                $result[$counter]['mobile'] = $dir->getMobile();
                            
                                $result[$counter]['barrio'] = $dir->getBarrio();
                                $result[$counter]['zip'] = $dir->getZip();
                                if ($dir->getCustomer()->getType()) {
                                    $result[$counter]['type'] = $dir->getCustomer()->getType()->getName();
                                } else {
                                    $result[$counter]['type'] = 'Persona';
                                }
                                $counter++;
                            }
                        }
                    }
                    break;
            }
        } else {
             switch ($typecus) {
                case 1:
                    $entities = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('o')
                        ->where('o.name LIKE :name' )
                        ->andWhere('o.lastname LIKE :lastname')
                        ->andWhere('o.agency IN (:agencies)')
                        ->andWhere('o.active = :active')
                        ->setParameters(['name'=>$pattern1.'%','lastname'=>$pattern2.'%', 'agencies'=>$agencies, 'active'=>true])
                        ->setFirstResult(0)
                        ->setMaxResults(25)
                        ->getQuery()
                        ->getResult();
                    foreach ($entities as $entity) {
                        if ($entity->getAdrdefault()) {
                            $dir = $entity->getAdrdefault();
                            $city = $dir->getCity();
                            if ($city) {
                                $result[$counter]['name'] = $entity->getName();
                                $result[$counter]['lastname'] = $entity->getLastname();
                                if ($entity->getPobox()) {
                                    $result[$counter]['pobox'] = $entity->getPobox()->getNumber();
                                } else {
                                    $result[$counter]['pobox'] = '';
                                }
                                $result[$counter]['email'] = $entity->getEmail();
                                $result[$counter]['docid'] = $dir->getDocid();
                                $result[$counter]['address'] = $dir->getAddress();
                                $result[$counter]['customerid'] = $entity->getId();
                                
                                $result[$counter]['cityid'] = $city->getId();
                                $result[$counter]['cityname'] = $city->getName();
                                $result[$counter]['state'] = $city->getState()->getName();
                                $result[$counter]['country'] = $city->getState()->getCountry()->getName();
                                $result[$counter]['phone'] = $dir->getPhone();
                                $result[$counter]['mobile'] = $dir->getMobile();
                            
                                $result[$counter]['barrio'] = $dir->getBarrio();
                                $result[$counter]['zip'] = $dir->getZip();
                                if ($dir->getCustomer()->getType()) {
                                    $result[$counter]['type'] = $dir->getCustomer()->getType()->getName();
                                } else {
                                    $result[$counter]['type'] = 'Persona';
                                }
                                $counter++;
                            }
                        } else {
                            $result[$counter]['name'] = $entity->getName();
                            $result[$counter]['lastname'] = $entity->getLastname();
                            if ($entity->getPobox()) {
                                $result[$counter]['pobox'] = $entity->getPobox()->getNumber();
                            } else {
                                $result[$counter]['pobox'] = '';
                            }
                            $result[$counter]['email'] = $entity->getEmail();
                            $result[$counter]['docid'] = '';
                            $result[$counter]['address'] = '';
                            $result[$counter]['customerid'] = $entity->getId();
                            
                            $result[$counter]['cityid'] = 0;
                            $result[$counter]['cityname'] = '';
                            $result[$counter]['state'] = '';
                            $result[$counter]['country'] = ''; 
                            $result[$counter]['phone'] = '';
                            $result[$counter]['mobile'] = '';
                        
                            $result[$counter]['barrio'] = '';
                            $result[$counter]['zip'] = '';
                            if ($entity->getType()) {
                                $result[$counter]['type'] = $entity->getType()->getName();
                            } else {
                                $result[$counter]['type'] = 'Persona';
                            }
                            $counter++;
                        }
                    }
                    break;
                case 2: 
                    $customers = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('o')
                            ->where('o.agency IN (:agencies)')
                            ->andWhere('o.active = :active')
                            ->setParameters(['agencies'=>$agencies,'active'=>true])
                            ->getQuery()
                            ->getResult();
                        
                    $entities = $em->getRepository('NvCargaBundle:Baddress')->createQueryBuilder('o')
                        ->where('o.name LIKE :name')
                        ->andWhere('o.lastname LIKE :lastname' )
                        ->andWhere('o.customer IN (:customers)')
                        ->setParameters(['name'=>$pattern1.'%','lastname'=>$pattern2.'%', 'customers'=>$customers])
                        ->setFirstResult(0)
                        ->setMaxResults(25)
                        ->getQuery()
                        ->getResult();
                        
                    $diradd = array();
                    
                    foreach ($entities as $dir) {
                        $city = $dir->getCity();
                        if ($city) { 
                            $result[$counter]['name'] = $dir->getName();
                            $result[$counter]['lastname'] = $dir->getLastname();
                            $thisdir= $dir->getName() . $dir->getLastname();
                            if ($dir->getCustomer()->getPobox()) {
                                $result[$counter]['pobox'] = $dir->getCustomer()->getPobox()->getNumber();
                                $thisdir = $thisdir . $dir->getCustomer()->getPobox()->getNumber();
                                
                            } else {
                                $result[$counter]['pobox'] = '';
                            }
                            $diradd[] = $thisdir;
                            $result[$counter]['email'] = $dir->getCustomer()->getEmail();

                            $result[$counter]['docid'] = $dir->getDocid();
                            $result[$counter]['address'] = $dir->getAddress();
                            $result[$counter]['customerid'] = $dir->getId();
                            $result[$counter]['cityid'] = $city->getId();
                            $result[$counter]['cityname'] = $city->getName();
                            $result[$counter]['state'] = $city->getState()->getName();
                            $result[$counter]['country'] = $city->getState()->getCountry()->getName();
                            $result[$counter]['phone'] = $dir->getPhone();
                            $result[$counter]['mobile'] = $dir->getMobile();
                        
                            $result[$counter]['barrio'] = $dir->getBarrio();
                            $result[$counter]['zip'] = $dir->getZip();
                            if ($dir->getCustomer()->getType()) {
                                $result[$counter]['type'] = $dir->getCustomer()->getType()->getName();
                            } else {
                                $result[$counter]['type'] = 'Persona';
                            }
                            $counter++; 
                        }
                    }
                    $customers = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('o')
                            ->where('o.name LIKE :name')
                            ->andWhere('o.lastname LIKE :lastname')
                            ->andWhere('o.agency IN (:agencies)')
                            ->andWhere('o.active = :active')
                            ->setParameters(['name'=>$pattern1.'%','lastname'=>$pattern2.'%', 'agencies'=>$agencies, 'active'=>true])
                            ->setFirstResult(0)
                            ->setMaxResults(25)
                            ->getQuery()
                            ->getResult();
                        
                    foreach ($customers as $entity) {
                        if ($entity->getPobox()) {
                            $thisdir = $entity->getName() . $entity->getLastname() .  $entity->getPobox()->getNumber();
                            if (!in_array($thisdir,$diradd)) {
                                $result[$counter]['name'] = $entity->getName();
                                $result[$counter]['lastname'] = $entity->getLastname();
                                $result[$counter]['pobox'] = $entity->getPobox()->getNumber();
                                $result[$counter]['email'] = $entity->getEmail();
                                $result[$counter]['docid'] = '';
                                $result[$counter]['address'] = '';
                                $result[$counter]['customerid'] = 0;
                                
                                $result[$counter]['cityid'] = 0;
                                $result[$counter]['cityname'] = '';
                                $result[$counter]['state'] = '';
                                $result[$counter]['country'] = ''; 
                                $result[$counter]['phone'] = '';
                                $result[$counter]['mobile'] = '';
                            
                                $result[$counter]['barrio'] = $entity->getId();
                                $result[$counter]['zip'] = '';
                                if ($entity->getType()) {
                                    $result[$counter]['type'] = $entity->getType()->getName();
                                } else {
                                    $result[$counter]['type'] = 'Persona';
                                }
                                $counter++;
                            }
                        }
                    }
                    break;
            }
        }
        return new JsonResponse($result); 
    }
    /**
     * show a list of all Customer 
     *
     * @Route("/listcuspobox", name="listcuspobox")
     * @Security("has_role('ROLE_ADMIN_CUSTOMER') or has_role('ROLE_ADMIN')")
     */
    public function listcuspoboxAction(Request $request)
    {
        /*
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        */
        $pattern = $request->query->get('pattern');
        
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        
        $result=array();
        $counter = 0;
        $entities = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('o')
            ->where('o.name LIKE :name OR o.lastname LIKE :lastname OR o.email LIKE :email' )
            //->setParameter('name', '%'.$name.'%')
            ->andWhere('o.maincompany = :maincompany')
            ->andWhere('o.active = :active')
            ->setParameters(['name'=>$pattern.'%','lastname'=>$pattern.'%', 'email'=>$pattern, 'maincompany'=>$maincompany, 'active'=> true])
            ->setFirstResult(0)
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
            
        foreach ($entities as $entity) {
            if ($entity->getAdrdefault()) {
            $dir = $entity->getAdrdefault();
            $city = $dir->getCity();
            if ($city) { 
                $result[$counter]['name'] = $entity->getName();
                $result[$counter]['lastname'] = $entity->getLastname();
                if ($entity->getPobox()) {
                    $result[$counter]['pobox'] = $entity->getPobox()->getNumber();
                } else {
                    $result[$counter]['pobox'] = '';
                }
                $result[$counter]['email'] = $entity->getEmail();

                $result[$counter]['docid'] = $dir->getDocid();
                $result[$counter]['address'] = $dir->getAddress();
                
                $result[$counter]['customerid'] = $entity->getId();
               
                $result[$counter]['cityid'] = $city->getId();
                $result[$counter]['cityname'] = $city->getName();
                $result[$counter]['state'] = $city->getState()->getName();
                $result[$counter]['country'] = $city->getState()->getCountry()->getName();
                $result[$counter]['phone'] = $dir->getPhone();
                $result[$counter]['mobile'] = $dir->getMobile();
            
                $result[$counter]['barrio'] = $dir->getBarrio();
                $result[$counter]['zip'] = $dir->getZip();
                if ($dir->getCustomer()->getType()) {
                    $result[$counter]['type'] = $dir->getCustomer()->getType()->getName();
                } else {
                    $result[$counter]['type'] = 'Persona';
                }
                $counter++;
            }
            }
        }
        return new JsonResponse($result); 
    }
    /**
     * Edits an existing Customer entity.
     *
     * @Route("/{id}/inactive", name="customer_inactive")
     * @Security("has_role('ROLE_ADMIN_CUSTOMER') or has_role('ROLE_ADMIN')")
     */
    public function inactiveAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Customer')->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('No existe el cliente');
        }
        $statuspobox = $em->getRepository('NvCargaBundle:Poboxstatus')->findOneByName('SUSPENDIDO');
        $statususer = $em->getRepository('NvCargaBundle:Userstatus')->findOneByName('SUSPENDIDO');
        $pobox = $entity->getPobox();
        
        if ($pobox) {
            $pobox->setStatus($statuspobox);
            $userp = $pobox->getUser();
            $userp->setStatus($statususer);
        }
        
        $entity->setActive(false);
        
        $em->flush();

        return $this->redirect($this->generateUrl('customer'));
    }
     /**
     * Edits an existing Customer entity.
     *
     * @Route("/{id}/reactive", name="customer_reactive")
     * @Security("has_role('ROLE_ADMIN_CUSTOMER') or has_role('ROLE_ADMIN')")
     */
    public function reactiveAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Customer')->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('No existe el cliente');
        }
        $statuspobox = $em->getRepository('NvCargaBundle:Poboxstatus')->findOneByName('ACTIVO');
        $statususer = $em->getRepository('NvCargaBundle:Userstatus')->findOneByName('ACTIVO');
        $pobox = $entity->getPobox();
        
        if ($pobox) {
            $pobox->setStatus($statuspobox);
            $userp = $pobox->getUser();
            $userp->setStatus($statususer);
        }
        
        $entity->setActive(true);
        
        $em->flush();

        return $this->redirect($this->generateUrl('customeroff'));
    }
    
    public function addCustomerAction($form, $entity) 
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $sender = null;
        $rebaddr = null;
        $error = 0;
        $doflush = false;
        
        // VERIFICA SI EXISTA EL DESTINATARIO y SI TIENE EMAIL
        $baddr_id = $form['id_addr']->getData();
        $rebaddr = $em->getRepository('NvCargaBundle:Baddress')->find($baddr_id);
        $email_addr = $form['email_addr']->getData();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        // exit(\Doctrine\Common\Util\Debug::dump($rebaddr));
        
        if ($rebaddr) {
            $addr = $rebaddr->getCustomer();
        } else {
            if ($form['barrio_addr']->getData() > 0) {
                $addr = $em->getRepository('NvCargaBundle:Customer')->find($form['barrio_addr']->getData());
            } else {
                $addr = null;
            }
        }
        
        if ($email_addr) {
            $addr_email=$em->getRepository('NvCargaBundle:Customer')->findOneby(array('maincompany'=>$maincompany, 'email'=>$email_addr));
        } else {
            $addr_email = null;
        }
        // VERIFICA SI LA CIUDAD DEL DESTINATARIO EXISTE
        $cid_addr = $form['cityid_addr']->getData();
        $city_addr = $em->getRepository('NvCargaBundle:City')->find($cid_addr);
        $cityname_addr = $form['cityname_addr']->getData();
        $state_addr = $form['state_addr']->getData();
        $change_addr = false;
        if ((!$city_addr) || ($city_addr->getName() != $cityname_addr) || ($city_addr->getState() != $state_addr))  {
            $change_addr = true;
            $city_addr = $em->getRepository('NvCargaBundle:City')->findOneBy(['state'=>$state_addr,'name'=>$cityname_addr]);
            if (!$city_addr)  {
                //exit(\Doctrine\Common\Util\Debug::dump('NO EXISTE LA CIUDAD...creado '));
                $city_addr = new City();
                $city_addr->setName($cityname_addr);
                $city_addr->setState($state_addr);
                $city_addr->setActive(false);
                $em->persist($city_addr);
                $doflush = true;
            }
        }
        
        // VERIFICA SI EXISTE EL REMITENTE y SI TEIEN EMAIl
        $sid = $form['id_sender']->getData();
        $sender= $em->getRepository('NvCargaBundle:Customer')->find($sid);
        $email_sender = $form['email_sender']->getData();
        if ($email_sender) {
            $sender_email=$em->getRepository('NvCargaBundle:Customer')->findOneby(array('maincompany'=>$maincompany, 'email'=>$email_sender));
        } else {
            $sender_email = null;
        }
        
        // VERIFICA SI LA CIUDAD DEL REMITENTE EXISTE
        $cid_sender = $form['cityid_sender']->getData();
        $city_sender = $em->getRepository('NvCargaBundle:City')->find($cid_sender);
        $cityname_sender = $form['cityname_sender']->getData();
        $state_sender = $form['state_sender']->getData();
        $change_sender = false;
        if ((!$city_sender) || ($city_sender->getName() != $cityname_sender) || ($city_sender->getState() != $state_sender))  {
            $change_sender = true;
            $city_sender = $em->getRepository('NvCargaBundle:City')->findOneBy(['state'=>$state_sender,'name'=>$cityname_sender]);
            if (!$city_sender) { 
                if (($cityname_addr == $cityname_sender) && ($state_addr == $state_sender)) {
                    $city_sender = $city_addr;
                    //exit(\Doctrine\Common\Util\Debug::dump('LA MISMA CIUDAD YA SE CREO '));
                } else {
                    $city_sender = new City();
                    $city_sender->setName($cityname_sender);
                    $city_sender->setState($state_sender);
                    $city_sender->setActive(false);
                    $em->persist($city_sender);
                    $doflush=true;
                }
            }
        }
        if (!$sender) {	//AGREGAR EL REMITENT
            //exit(\Doctrine\Common\Util\Debug::dump('NO EXISTE EL REMITENTE...fue creado '));
            $countcustomer =  $maincompany->getCountcustomers();
            $plan = $maincompany->getPlan();
            if (($plan->getCustomers()) && ($countcustomer >= $plan->getMaxcustomers())) {
                $message = 'Ha llegado al número máximo de CLIENTES permitidos. Para crear más debe actualizar su plan a uno superior.';
                if ($this->isGranted('ROLE_ADMIN')) {
                    $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                }
                $flashBag->add('notice',$message);
                $error = 1;
                goto next;
            }
            /*
            if (($sender_email) && ($maincompany->getAcronym() != 'enviamoscarga')) {
                $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Ya existe un cliente con el correo del remitente');
                $error = 1;
                goto next;
            }
            */
            // $ctype = $em->getRepository('NvCargaBundle:Customertype')->findOneBy(array('name' =>'NORMAL'));
            $ctype = $form['typecus_sender']->getData();
            $cstatus = $em->getRepository('NvCargaBundle:Customerstatus')->findOneBy(array('name' =>'ACTIVO'));
            $sender= new Customer();
            $sender->setAgency($entity->getAgency());
            $sender->setCreationdate(new \DateTime());
            $sender->setName($form['name_sender']->getData());
            $sender->setLastname($form['lastname_sender']->getData());
            $sender->setType($ctype);
            $sender->setStatus($cstatus);
            $maxcus = $maincompany->getCountcustomers();
            $maxcus++;
            $maincompany->setCountcustomers($maxcus);
            if ($email_sender) {
                $sender->setEmail($form['email_sender']->getData());
            }
            $sebaddr = new Baddress();
            $sebaddr->setName($form['name_sender']->getData());
            $sebaddr->setLastname($form['lastname_sender']->getData());
            $sebaddr->setAddress($form['direccion_sender']->getData());
            $sebaddr->setCity($city_sender);
            $sebaddr->setPhone($form['phone_sender']->getData());
            $sebaddr->setMobile($form['mobile_sender']->getData());
            // $sebaddr->setBarrio($form['barrio_sender']->getData());
            $sebaddr->setZip($form['zip_sender']->getData());
            $sebaddr->setCustomer($sender);
            $sender->setAdrdefault($sebaddr);
            $sender->setAdrmain($sebaddr);
            $sender->addBaddress($sebaddr);
            
            $em->persist($sebaddr);
            
            $sender->setMaincompany($maincompany);
            
            $em->persist($sender);
            $doflush=true;
            
        } else {
            // exit(\Doctrine\Common\Util\Debug::dump('EXISTE EL REMITENTE...NO fue creado '));
            $sebaddr = $sender->getAdrdefault();
            if ($sebaddr) {
                // VERIFICAR SI HUBO CAMBIOS EN ALGÚN DATO DE LA DIRECCION DEL REMITENTE	
                $change1 = ($sebaddr->getName() != $form['name_sender']->getData()) || ($sebaddr->getLastname() != $form['lastname_sender']->getData()) || ($sebaddr->getAddress() != $form['direccion_sender']->getData()) || ($sebaddr->getCity() != $city_sender) || ($sebaddr->getPhone() != $form['phone_sender']->getData()) || ($sebaddr->getMobile() != $form['mobile_sender']->getData())  ||  ($sebaddr->getZip() != $form['zip_sender']->getData());
            } else { 
                $change1 = true;
            }
            if (($change1) || ($change_sender)) {
                $sebaddr = new Baddress();
                $sebaddr->setName($form['name_sender']->getData());
                $sebaddr->setLastname($form['lastname_sender']->getData());
                $sebaddr->setAddress($form['direccion_sender']->getData());
                $sebaddr->setCity($city_sender);
                $sebaddr->setPhone($form['phone_sender']->getData());
                $sebaddr->setMobile($form['mobile_sender']->getData());
                // $sebaddr->setBarrio($form['barrio_sender']->getData());
                $sebaddr->setZip($form['zip_sender']->getData());
                $sebaddr->setCustomer($sender);
                $sender->setAdrdefault($sebaddr);
                $sender->addBaddress($sebaddr);
                $em->persist($sebaddr);
                if (!$sender->getAdrmain()) {
                    $newddr = new Baddress();
                    $newddr->setName($sender->getName());
                    $newddr->setLastname($sender->getLastname());
                    $newddr->setAddress($form['direccion_sender']->getData());
                    $newddr->setCity($city_sender);
                    $newddr->setPhone($form['phone_sender']->getData());
                    $newddr->setMobile($form['mobile_sender']->getData());
                    // $newddr->setBarrio($form['barrio_sender']->getData());
                    $newddr->setZip($form['zip_sender']->getData());
                    $newddr->setCustomer($sender);
                    $sender->setAdrmain($newddr);
                    $sender->addBaddress($newddr);
                    $em->persist($newddr);
                }
                $doflush=true;
            }
        }
        if (!$addr) { 
            if ($email_sender === $email_addr) {
                $addr=$sender;//se crea SOLO un cliente
                if ($this->sameAddress($form, $city_sender, $city_addr)) {
                    // exit(\Doctrine\Common\Util\Debug::dump('MISMO REMITENTE Y DESTINATARIO SERA .. '));
                    $rebaddr = $sebaddr;
                } else {
                    // CREATE NEW BADDRESS FOR THIS CUSTOMER
                    $rebaddr = new Baddress();
                    $rebaddr->setName($form['name_addr']->getData());
                    $rebaddr->setLastname($form['lastname_addr']->getData());
                    $rebaddr->setAddress($form['direccion_addr']->getData());
                    $rebaddr->setCity($city_addr);
                    $rebaddr->setPhone($form['phone_addr']->getData());
                    $rebaddr->setMobile($form['mobile_addr']->getData());
                    $rebaddr->setBarrio($form['barrio_addr']->getData());
                    $rebaddr->setZip($form['zip_addr']->getData());
                    $rebaddr->setCustomer($addr);
                    // $addr->setAdrdefault($rebaddr);
                    $addr->addBaddress($rebaddr);
                    $em->persist($rebaddr);
                    $doflush=true;
                }
            } else { //AGREGAR DESTINATARIO
                $countcustomer =  $maincompany->getCountcustomers();
                $plan = $maincompany->getPlan();
                if (($plan->getCustomers()) && ($countcustomer >= $plan->getMaxcustomers())) {
                    $message = 'Ha llegado al número máximo de CLIENTES permitidos. Para crear más debe actualizar su plan a uno superior.';
                    if ($this->isGranted('ROLE_ADMIN')) {
                        $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                    }
                    $flashBag->add('notice',$message);
                    $error = 1;
                    goto next;
                }
                /*
                if (($addr_email) && ($maincompany->getAcronym() != 'enviamoscarga')) {
                    $this->get('session')->getFlashBag()->add(
                                'notice',
                                'Ya existe un cliente con el correo del destinatario');
                    $error = 1;
                    goto next;
                    // throw $this->createNotFoundException('Ya existe un cliente con el correo del destinatario');
                }
                */
                // $ctype = $em->getRepository('NvCargaBundle:Customertype')->findOneBy(array('name' =>'NORMAL'));
                $ctype = $form['typecus_addr']->getData();
                $cstatus = $em->getRepository('NvCargaBundle:Customerstatus')->findOneBy(array('name' =>'ACTIVO'));
                $addr= new Customer();
                $addr->setAgency($entity->getAgency());
                $addr->setCreationdate(new \DateTime());
                $addr->setName($form['name_addr']->getData());
                $addr->setLastname($form['lastname_addr']->getData());
                $addr->setType($ctype);
                $addr->setStatus($cstatus);
                $maxcus = $maincompany->getCountcustomers();
                $maxcus++;
                $maincompany->setCountcustomers($maxcus);
                if ($email_addr) {
                    $addr->setEmail($form['email_addr']->getData());
                }
                $addr->setType($ctype);
                $addr->setStatus($cstatus);
                $rebaddr = new Baddress();
                $rebaddr->setName($form['name_addr']->getData());
                $rebaddr->setLastname($form['lastname_addr']->getData());
                $rebaddr->setAddress($form['direccion_addr']->getData());
                $rebaddr->setCity($city_addr);
                $rebaddr->setPhone($form['phone_addr']->getData());
                $rebaddr->setMobile($form['mobile_addr']->getData());
                // $rebaddr->setBarrio($form['barrio_addr']->getData());
                $rebaddr->setZip($form['zip_addr']->getData());
                $rebaddr->setCustomer($addr);
                $addr->setAdrdefault($rebaddr);
                $addr->setAdrmain($rebaddr);
                $addr->addBaddress($rebaddr);
                $em->persist($rebaddr);
                $addr->setMaincompany($maincompany);
                $em->persist($addr);
                $doflush=true;
            }
        } else {
            // VERIFICAR SI HUBO CAMBIOS EN ALGÚN DATO DE LA DIRECCION DEL REMITENTE
            if ($rebaddr) {
                $change1 = ($rebaddr->getName() != $form['name_addr']->getData()) || ($rebaddr->getLastname() != $form['lastname_addr']->getData()) || ($rebaddr->getAddress() != $form['direccion_addr']->getData()) || ($rebaddr->getCity() != $city_addr) || ($rebaddr->getPhone() != $form['phone_addr']->getData()) || ($rebaddr->getMobile() != $form['mobile_addr']->getData()) ||  ($rebaddr->getZip() != $form['zip_addr']->getData());
                // exit(\Doctrine\Common\Util\Debug::dump($change1));
            } else {
                if ($sender == $addr) {
                    $change1 = ($form['name_sender']->getData() != $form['name_addr']->getData()) || ($form['lastname_sender']->getData() != $form['lastname_addr']->getData()) || ($form['direccion_sender']->getData() != $form['direccion_addr']->getData()) || ($city_sender != $city_addr) || ($form['phone_sender']->getData() != $form['phone_addr']->getData()) || ($form['mobile_sender']->getData() != $form['mobile_addr']->getData()) ||  ($form['zip_sender']->getData() != $form['zip_addr']->getData());
                    if (!$change1) {
                        $rebaddr = $sebaddr;
                    }
                } else {
                    $change1 = true;
                }
            }
            if (($change1) || ($change_addr)) {
                $rebaddr = new Baddress();
                $rebaddr->setName($form['name_addr']->getData());
                $rebaddr->setLastname($form['lastname_addr']->getData());
                $rebaddr->setAddress($form['direccion_addr']->getData());
                $rebaddr->setCity($city_addr);
                $rebaddr->setPhone($form['phone_addr']->getData());
                $rebaddr->setMobile($form['mobile_addr']->getData());
                // $rebaddr->setBarrio($form['barrio_addr']->getData());
                $rebaddr->setZip($form['zip_addr']->getData());
                $rebaddr->setCustomer($addr);
                if (!$addr->getAdrdefault()) {
                    $addr->setAdrdefault($rebaddr);
                }
                $addr->addBaddress($rebaddr);
                $em->persist($rebaddr);
                if (!$addr->getAdrmain()) {
                    $newddr = new Baddress();
                    $newddr->setName($addr->getName());
                    $newddr->setLastname($addr->getLastname());
                    $newddr->setAddress($form['direccion_addr']->getData());
                    $newddr->setCity($city_addr);
                    $newddr->setPhone($form['phone_addr']->getData());
                    $newddr->setMobile($form['mobile_addr']->getData());
                    // $newddr->setBarrio($form['barrio_addr']->getData());
                    $newddr->setZip($form['zip_addr']->getData());
                    $newddr->setCustomer($addr);
                    $addr->setAdrmain($newddr);
                    $addr->addBaddress($newddr);
                    $em->persist($newddr);
                }
                $doflush=true;
            }
        }
        // exit(\Doctrine\Common\Util\Debug::dump('NO EXISTE EL REMITENTE...fue creado '. $sender->getId()));
        if ($doflush) {
            $em->flush();
        }
        
        next:
            // exit(\Doctrine\Common\Util\Debug::dump($sender));
            if ($sender) {
                $idsender = $sender->getId();
            } else {
                $idsender = 0;
            }
            if ($rebaddr) {
                $idaddr = $rebaddr->getId();
            } else {
                $idaddr = 0;
            }
            $result = $error .':' . $idsender . ':' . $idaddr;
        
        
        return new Response($result);
    }
    
    private function sameAddress($form, $city_sender, $city_addr) 
    { 
        $fields= array("name", "lastname", "direccion", "phone", "mobile", "zip");
        $nfields = count($fields);
        $result = ($city_sender == $city_addr);
        $pos = 0;
        
        while (($result) && ($pos < $nfields)) {
            $field = $fields[$pos];
            if ($form[$field . '_sender']->getData() != $form[$field . '_addr']->getData()) {
                $result = false;
            }
            $pos++;
        }
        // exit(\Doctrine\Common\Util\Debug::dump('MISMO REMITENTE Y DESTINATARIO SERA .. ' . $result));
        return $result;
    }
    private function sameDir($form, $baddr) 
    { 
        $fields= array("name", "lastname", "direccion", "docid", "phone", "mobile", "zip");
        $nfields = count($fields);
        
        $result = ($form['cityid']->getData() == $baddr->getCity()->getId());
        $pos = 0;
        while (($result) && ($pos < $nfields)) {
            $field = $fields[$pos];
            if ($field == 'direccion') {
                $field = 'address';
            }
            $function = 'get' . ucfirst($field);
            if ($form[$field]->getData() != $baddr->$function()) {
                $result = false;
            }
            $pos++;
        }
        return $result;
    }
}
