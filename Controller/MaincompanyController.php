<?php

namespace NvCarga\Bundle\Controller;

use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Maincompany;
use NvCarga\Bundle\Entity\Agency;
use NvCarga\Bundle\Entity\User;
use NvCarga\Bundle\Entity\Warehouse;
use NvCarga\Bundle\Entity\Company;
use NvCarga\Bundle\Entity\Region;
use NvCarga\Bundle\Entity\City;
use NvCarga\Bundle\Entity\Profile;
use NvCarga\Bundle\Entity\Tariff;
use NvCarga\Bundle\Entity\Format;
use NvCarga\Bundle\Entity\Paidtype;
use NvCarga\Bundle\Entity\Carrier;
use NvCarga\Bundle\Entity\Office;
use NvCarga\Bundle\Entity\Labelconf;

use NvCarga\Bundle\Form\MaincompanyType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Agency controller.
 *
 * @Route("/maincompany")
 */
class MaincompanyController extends Controller
{
   /**
     * Lists all MAINCOMPANY entities.
     *
     * @Route("/index", name="maincompany")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user->getUsername() === 'trackingpremium') {
            $entities = $em->getRepository('NvCargaBundle:Maincompany')->findAll();
        } else {
           $entities = $em->getRepository('NvCargaBundle:Maincompany')->findById($user->getMaincompany()->getId());
        }

        return array(
            'entities' => $entities,
        );
    }
   /**
     * @Route("/{id}/show", name="maincompany_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $agency=$this->getUser()->getAgency();
        $maincompany = $this->getUser()->getMaincompany();

        if (($agency->getType() == "MASTER" ) && ($id == $maincompany->getId()) || ($this->isGranted('ROLE_ADMIN_MAINCOMPANY'))) {
            $entity = $em->getRepository('NvCargaBundle:Maincompany')->find($id);
        } else {
            throw $this->createNotFoundException('No tiene permisos para ver datos de la empresa');
        }

        if (!$entity) {
            throw $this->createNotFoundException('No se encuentran datos de la Empresa');
        }

        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            // 'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Maincompany entity.
    *
    * @param Maincompany $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditMainForm(Maincompany $entity)
    {
        $form = $this->createForm(new MaincompanyType(), $entity, array(
            'action' => $this->generateUrl('maincompany_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        $form->remove('homepage');
        //$form->add('file', 'file', array('label'=> 'Logo ', 'required'=>false));
        $form->add('submit', 'submit', array('label' => 'Actualizar'));
        $em = $this->getDoctrine()->getManager();
        /*
        $countries = $em->getRepository('NvCargaBundle:Country')->findAll();

        $choiceList = new ObjectChoiceList($countries, null, array(), null, 'id');

        $form->add('countries', 'choice', array('label' => 'Países donde trabaja ',
                     'multiple'=>true, 'expanded' => true,
                     'attr' => array('class' => 'icheck checkcountries'),
                    'choice_list'   => $choiceList,
                    'data'=> $entity->getCountries()->toarray()));
        */

        $form->add('countries', 'entity', array('label' => 'Países donde trabaja ', 'class'=> 'NvCargaBundle:Country',
                     'multiple'=>'true', 'expanded' => true, 'attr' => array('class' => 'icheck checkcountries')));

        $translator = $this->get('translator');
        /*
        if (($this->isGranted('ROLE_ADMIN_MAINCOMPANY')) && ($this->getUser()->getUsername() == 'trackingpremium')) {
            $form->add('maxguides', null, array('label'=>  $translator->trans('Guías')))
            ->add('maxreceipts', null, array('label'=>  $translator->trans('Recibos')))
            ->add('maxwhrecs', null, array('label'=>  $translator->trans('Warehouse')))
            ->add('maxconsolidates', null, array('label'=>  $translator->trans('Consolidados')))
            ->add('maxbills', null, array('label'=>  $translator->trans('Facturas')))
            ->add('maxcustomers', null, array('label'=>  $translator->trans('Clientes')))
            ->add('maxusers', null, array('label'=>  $translator->trans('Usuarios')))
            ->add('maxpoboxes', null, array('label'=>  $translator->trans('Casilleros')))
            ->add('maxagencies', null, array('label'=>  $translator->trans('Agencias')))
            ->add('maxbags', null, array('label'=>  $translator->trans('Bolsas')))
            ->add('maxaccounts', null, array('label'=>  $translator->trans('Cuentas Bancarias')))
            ->add('maxalerts', null, array('label'=>  $translator->trans('Alertas')))
            ->add('maxadservices', null, array('label'=>  $translator->trans('Servicios Adicionales')))
            ->add('maxcompanies', null, array('label'=>  $translator->trans('Sucursales')));
        }
        */

        $form->add('fileLogo', 'hidden', array('mapped'=>false));
        $form->add('fileLogomain', 'hidden', array('mapped'=>false));
        $form->add('fileBackground', 'hidden', array('mapped'=>false));

        return $form;
    }

    /**
     * Creates a form to create a Guide entity.
     *
     * @param Maincompay $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Maincompany $entity)
    {

        $form = $this->createForm(new MaincompanyType(), $entity, array(
            'action' => $this->generateUrl('maincompany_create'),
            'method' => 'POST',
        ));
        $translator = $this->get('translator');
        /*
        $form->add('file', 'file', array('label'=> 'Logo ', 'required'=>true,
            'constraints' => array(new NotBlank(["message" => "Por favor, seleccione un archivo con el logo del la empresa (png/jpg)"]))));
        */
        $em = $this->getDoctrine()->getManager();
        /*
        $countries = $em->getRepository('NvCargaBundle:Country')->findAll();

        $choiceList = new ObjectChoiceList($countries, null, array(), null, 'id');

        $form->add('countries', 'choice', array('label' => 'Países donde trabaja ',
                     'multiple'=>true, 'expanded' => true,
                     'attr' => array('class' => 'icheck checkcountries'),
                    'choice_list'   => $choiceList,
                    'data'=> $entity->getCountries()->toarray())); */
        $form->add('countries', 'entity', array('label' => 'Países donde trabaja ', 'class'=> 'NvCargaBundle:Country',
                     'multiple'=>'true', 'expanded' => true, 'attr' => array('class' => 'icheck checkcountries')));
        /*
        $form->add('maxguides', null, array('label'=>  $translator->trans('Guías')))
            ->add('maxreceipts', null, array('label'=>  $translator->trans('Recibos')))
            ->add('maxwhrecs', null, array('label'=>  $translator->trans('Warehouse')))
            ->add('maxconsolidates', null, array('label'=>  $translator->trans('Consolidados')))
            ->add('maxbills', null, array('label'=>  $translator->trans('Facturas')))
            ->add('maxcustomers', null, array('label'=>  $translator->trans('Clientes')))
            ->add('maxusers', null, array('label'=>  $translator->trans('Usuarios')))
            ->add('maxpoboxes', null, array('label'=>  $translator->trans('Casilleros')))
            ->add('maxagencies', null, array('label'=>  $translator->trans('Agencias')))
            ->add('maxbags', null, array('label'=>  $translator->trans('Bolsas')))
            ->add('maxaccounts', null, array('label'=>  $translator->trans('Cuentas Bancarias')))
            ->add('maxalerts', null, array('label'=>  $translator->trans('Alertas')))
            ->add('maxadservices', null, array('label'=>  $translator->trans('Servicios Adicionales')))
            ->add('maxcompanies', null, array('label'=>  $translator->trans('Sucursales')));
        */

        //    ->add('maxcompanies', null, array('label'=> 'Cantidad máxima de ' .  $translator->trans('Guías')));

        $form->add('agencyname','text', array('label'=> 'Nombre ', 'mapped'=>false,
            'constraints' => array(new NotBlank(["message" => "Escoja un nombre para la agencia"]))))
            ->add('phone', 'text', array('label'=> 'Teléfono ', 'mapped'=>false,
                    'constraints' => array(new NotBlank(["message" => "Asigne el teléfono de la agencia"])),
                    ))
            ->add('fax', 'text', array('label'=> 'Fax ', 'required' => false, 'mapped'=>false))
            ->add('address', 'textarea', array('label'=> 'Dirección ', 'mapped'=>false,
              'constraints' => array(new NotBlank(["message" => "Asigne la dirección de la agencia"]),
                                new Length(
                                ["min" => 5, "max" => 150,
                                "minMessage" => "La dirección  debe tener al menos {{ limit }} caracteres",
                                "maxMessage" => "La dirección no puede tener mas de {{ limit }} caracteres"]) )))
            ->add('agencyemail', 'email', array('label'=> 'Email ', 'mapped'=>false))
            ->add('webmaster', 'text', array('label'=> 'Webmaster ', 'required' => false, 'mapped'=>false))
            ->add('zip','text', array('label'=> 'Zip ', 'required'=>true, 'mapped'=>false,
                    'constraints' => array(new NotBlank(["message" => "Asigne el ZIP de la agencia"]))))
            ->add('manager', 'text', array('label'=> 'Nombre del Manager', 'mapped'=>false,
                    'constraints' => array(new NotBlank(['message' => "El nombre del manager de la agencia no puede estar vacío"]),
                    new Length(
                        ['min' => 3, 'max' => 50,
                        'minMessage' => "El nombre del manager de la agencia debe tener al menos {{ limit }} caracteres",
                        'maxMessage' => "El nombre del manager de la agencia no puede tener mas de {{ limit }} caracteres"])
                )))
            ->add('guidecopies', 'checkbox', array('label'=> 'labels.emailagency', 'required' => false, 'mapped'=>false))
            ->add('poboxs', 'checkbox', array('label'=> 'Maneja Casilleros', 'required' => false, 'data'=>true, 'mapped'=>false))
            ->add('cityid', 'hidden', array('mapped'=>false, 'data'=>0))
           // ->add('cityname', 'text', array('label'=> 'Ciudad ', 'mapped'=>false,
			//		'constraints' => array(new NotBlank(["message" => "Escoja una ciudad"]))));
            ;
        $form->add('username', 'text', array('label' => 'Username ', 'mapped'=>false,
                'constraints' => array(new NotBlank(["message" => "Asigne el  USERNAME"]))))
            ->add('useremail','email', array('label' => 'Email ', 'mapped'=>false,
                    'constraints' => array(
							new NotBlank(["message" => "Debe asignar un email"]),
							new Email(["message" => "El email '{{ value }}' no es válido.",
                            "checkMX" => true, "checkHost" => true]
									))))
            ->add('firstname', 'text', array('label' => 'Nombre', 'mapped'=>false,
                    'constraints' => array(
							new NotBlank(["message" => "Debe asignar el nombre"]),)))
            ->add('lastname','text', array('label' => 'Apellido ', 'mapped'=>false,
                        'constraints' => array(
							new NotBlank(["message" => "Debe asignar el apellido"]),)))
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Los passwords deben ser iguales.',
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repita el Password'),
                'constraints' => array(new NotBlank(["message" => "Debe asignar el password"])),
                'mapped'=>false));
        $form->add('submit', 'submit', array('label' => 'Crear'));
        $form->add('plan', 'entity', array('label' => 'Plan ', 'mapped' => false,
                'required' => true,
                'data_class'=> 'NvCarga\Bundle\Entity\Plan', 'class'=> 'NvCargaBundle:Plan',
                'expanded' => false, 'multiple'=>false,));


        return $form;
    }
    /**
     * Creates a new Maincompany entity.
     *
     * @Route("/create", name="maincompany_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Maincompany:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_MAINCOMPANY')")
     */
    public function createAction(Request $request)
    {
        $entity = new Maincompany();
        $em = $this->getDoctrine()->getManager();
        $usa = $em->getRepository('NvCargaBundle:Country')->findOneByCode('US');
        $entity->addCountry($usa);

        $form   = $this->createCreateForm($entity);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $form->handleRequest($request);

        if ($form->isValid()) {
            $msg = $entity->getPoboxmsg();
            $msg = str_replace("<br>", "\r\n",  $msg);
            $entity->setPoboxmsg($msg);
            $em = $this->getDoctrine()->getManager();
            $error = false;
            if (count($entity->getCountries()) < 1) {
                $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Debe escoger por lo MENOS UN PAÍS');
                $error = true;
            }
            $mainemail = $em->getRepository('NvCargaBundle:Maincompany')->findOneByEmail($entity->getEmail());
            $mainname = $em->getRepository('NvCargaBundle:Maincompany')->findOneByName($entity->getName());
            $mainacron = $em->getRepository('NvCargaBundle:Maincompany')->findByAcronym($entity->getAcronym());
            $mainurl = $em->getRepository('NvCargaBundle:Maincompany')->findByUrl($entity->getUrl());
            $mainhome = $em->getRepository('NvCargaBundle:Maincompany')->findByHomepage($entity->getHomepage());
            if ($mainemail) {
                $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Ya existe una EMPRESA con ese EMAIL');
                $error = true;
            }
            if ($mainname) {
                $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Ya existe una EMPRESA con ese NOMBRE');
                $error = true;
            }
            if ($mainacron) {
                $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Ya existe una EMPRESA con ese ACRÓNIMO');
                $error = true;
            }
            if ($mainurl) {
                $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Ya existe una EMPRESA con ese URL');
                $error = true;
            }
            if ($mainhome) {
                $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Ya existe una EMPRESA con ese HOMEPAGE');
                $error = true;
            }
            $countries = count($entity->getCountries());
            $plan = $entity->getPlan();
            if ($plan != NULL) {
            if ($plan->getCountries()) {
                $max = $plan->getMaxcountries();
                /* ha alcanzado */
                if ($max == 1) {
                    $message = 'El plan seleccionado solo permite tener ' . $max  . ' país. Usted ha seleccionado ' . $countries ;
                } else {
                    $message = 'El plan seleccionado solo permite tener ' . $max  . ' países. Usted ha seleccionado ' . $countries;
                }
                if ($countries > $max) {
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        $message);
                    $error = true;
                }
            }
          }
            if ($error) {
                goto next;
            }
            $astatus = $em->getRepository('NvCargaBundle:Agencystatus')->findOneByName('ACTIVA');
            $atype = $em->getRepository('NvCargaBundle:Agencytype')->findOneByName('MASTER');
            //$cityname = $form['cityname']->getdata();
            //$state = $form['state']->getdata();
            $cid = $form['cityid']->getData();
            $city = $em->getRepository('NvCargaBundle:City')->find($cid);

            if (!$city) {
                $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Debe seleccionar una ciudad para la AGENCIA');
                goto next;
            }

            $agency = new Agency();
            $warehouse = new Warehouse();
            $agency->setName($form['agencyname']->getdata());
            $agency->setPhone($form['phone']->getdata());
            $agency->setFax($form['fax']->getdata());
            $agency->setAddress($form['address']->getdata());
            $agency->setEmail($form['agencyemail']->getdata());
            $agency->setWebmaster($form['webmaster']->getdata());
            $agency->setZip($form['zip']->getdata());
            $agency->setStatus($astatus);
            $agency->setType($atype);
            $agency->setWarehouse($warehouse);
            $agency->setCity($city);
            $agency->setManager($form['manager']->getdata());
            $agency->setGuidecopies($form['guidecopies']->getdata());
            $agency->setPoboxs($form['poboxs']->getdata());
            $agency->setCreationdate(new \DateTime());
            $agency->setLastupdate(new \DateTime());
            $agency->setEmail($form['agencyemail']->getdata());
            $agency->setMaincompany($entity);

            $warehouse->setName('Warehouse ' .  $agency->getName());
            $warehouse->setAddress($agency->getAddress());
            $warehouse->setDescription('Bodega de la agencia:' . $agency->getName());
            $warehouse->setZip($agency->getZip());
            $warehouse->setCreationdate(new \DateTime());
            $warehouse->setLastupdate(new \DateTime());
            $warehouse->setCity($agency->getCity());
            $warehouse->setMainCompany($entity);
            $warehouse->setAgency($agency);

            $roleadmin = $em->getRepository('NvCargaBundle:Role')->findOneByName('ROLE_ADMIN');
            $rolemain = $em->getRepository('NvCargaBundle:Role')->findOneByName('ROLE_ADMIN_MAINCOMPANY');
            $roleuser = $em->getRepository('NvCargaBundle:Role')->findOneByName('ROLE_USER');
            $rolepobox = $em->getRepository('NvCargaBundle:Role')->findOneByName('ROLE_CREATE_POBOX');
            $ustatus = $em->getRepository('NvCargaBundle:Userstatus')->findOneByName('ACTIVO');

            $user = new User();
            $user->setUsername($form['username']->getdata());
            $user->setEmail($form['useremail']->getdata());
            $user->setName($form['firstname']->getdata());
            $user->setLastname($form['lastname']->getdata());
            $user->setAgency($agency);
            $agency->addUser($user);
            $user->setCreationdate(new \DateTime());
            $user->setMaincompany($entity);
            $entity->setCountadmins(1);
            $user->addUserRole($roleadmin);
            $user->addUserRole($roleuser);
            $user->setStatus($ustatus);
            $user->setPassword($form['password']->getdata());
            $this->setSecurePassword($user);

            $utrack = new User();
            $utrack->setUsername('trackingpremium');
            $utrack->setEmail('trackingpremium@'. $entity->getName() . '.trackingpremium.com');
            $utrack->setName('Admin');
            $utrack->setLastname('Trackingpremium');
            $utrack->setAgency($agency);
            $agency->addUser($utrack);
            $utrack->setCreationdate(new \DateTime());
            $utrack->setMaincompany($entity);
            $utrack->addUserRole($roleadmin);
            $utrack->addUserRole($rolemain);
            $utrack->addUserRole($roleuser);
            $utrack->setStatus($ustatus);
            $utrack->setPassword('prueba');
            $this->setSecurePassword($utrack);

            $userweb = new User();
            $userweb->setUsername('webuser');
            $userweb->setEmail('webuser@' . $entity->getName() . '.trackingpremium.com');
            $userweb->setName('Usuario');
            $userweb->setLastname('Web');
            $userweb->setAgency($agency);
            $agency->addUser($userweb);
            $userweb->setCreationdate(new \DateTime());
            $userweb->setMaincompany($entity);
            $userweb->addUserRole($roleuser);
            $userweb->addUserRole($rolepobox);
            $userweb->setStatus($ustatus);
            $userweb->setPassword('webuser.prueba');
            $this->setSecurePassword($userweb);


            // CREACION DE LOS PERFILES BASICOS DEL SISTEMA
            // PERFIL DEL ADMINISTRADOR
            $padmin = new Profile();
            $padmin->setName('Administrador del Sistema');
            $padmin->setDescription('Puede ejecutar TODAS las acciones del sistema');
            $padmin->setMaincompany($entity);
            $padmin->addRole($roleadmin);
            $em->persist($padmin);

            // PERFIL PARA VER TODO
            $views = $em->getRepository('NvCargaBundle:Role')->createQueryBuilder('o')
                            ->where('o.name LIKE :name')
                            ->setParameters(['name'=>'ROLE_VIEW_%'])
                            ->getQuery()
                            ->getResult();
            $pview = new Profile();
            $pview->setName('Inspector del Sistema');
            $pview->setDescription('Puede VER TODAS las entidades del sistema');
            $pview->setMaincompany($entity);
            foreach ($views as $role) {
                $pview->addRole($role);
            }
            $em->persist($pview);

            // PERFIL PARA USAR TODAS LAS FUNCIONES DEL SISTEMA, EXCEPTO CREAR OTROS USUARIO y EDITAR UN PERFIL
            $roleouts = array();
            $createuser = $em->getRepository('NvCargaBundle:Role')->findOneByName('ROLE_ADMIN_USER');
            $roleouts[]=$createuser->getId();
            $roleouts[]=$roleadmin->getId();
            $admins = $em->getRepository('NvCargaBundle:Role')->createQueryBuilder('o')
                            ->where('o.name LIKE :name')
                            ->andWhere('o.id NOT IN (:roleouts)')
                            ->setParameters(['name'=>'ROLE_ADMIN_%', 'roleouts'=>$roleouts])
                            ->getQuery()
                            ->getResult();

            $pmanager = new Profile();
            $pmanager->setName('Manager del Sistema');
            $pmanager->setDescription('Puede usar TODAS las funciones del sistema, EXCEPTO crear otros usuario y editar un perfil');
            $pmanager->setMaincompany($entity);
            foreach ($admins as $role) {
                $pmanager->addRole($role);
            }
            $em->persist($pmanager);

            $viewconsol = $em->getRepository('NvCargaBundle:Role')->findOneByName('ROLE_VIEW_CONSOLIDATED');
            $preceptor = new Profile();
            $preceptor->setName('Receptor del Sistema');
            $preceptor->setDescription('Puede usar TODAS las funciones del sistema, EXCEPTO: crear otros usuario, crear consolidados y editar un perfil');
            $preceptor->setMaincompany($entity);
            $preceptor->addRole($viewconsol);
            foreach ($admins as $role) {
                if ($role->getName() != 'ROLE_ADMIN_CONSOLIDATED') {
                    $preceptor->addRole($role);
                }
            }
            $em->persist($preceptor);

            $lb = $em->getRepository('NvCargaBundle:Measure')->findOneByName('Lb');
            $cf = $em->getRepository('NvCargaBundle:Measure')->findOneByName('CF');
            $servAereo = $em->getRepository('NvCargaBundle:Shippingtype')->findOneByName('Aéreo');
            $servMar = $em->getRepository('NvCargaBundle:Shippingtype')->findOneByName('Marítimo');


            $patype1 = new Paidtype();
            $patype1->setName('TDC');
            $patype1->setCreationdate(new \DateTime());
            $patype1->setDescription('Pago con tarjeta de crédito');
            $patype1->setMaincompany($entity);
            $em->persist($patype1);

            $patype2 = new Paidtype();
            $patype2->setName('Débito');
            $patype2->setCreationdate(new \DateTime());
            $patype2->setDescription('Pago con tarjeta de débito');
            $patype2->setMaincompany($entity);
            $em->persist($patype2);

            $patype3 = new Paidtype();
            $patype3->setName('Efectivo');
            $patype3->setCreationdate(new \DateTime());
            $patype3->setDescription('Pago en efectivo');
            $patype3->setMaincompany($entity);
            $em->persist($patype3);

            $patype4 = new Paidtype();
            $patype4->setName('Cheque');
            $patype4->setCreationdate(new \DateTime());
            $patype4->setDescription('Pago con cheque');
            $patype4->setMaincompany($entity);
            $em->persist($patype4);

            $patype7 = new Paidtype();
            $patype7->setName('Transferencia ');
            $patype7->setCreationdate(new \DateTime());
            $patype7->setDescription('Pago por transferencia');
            $patype7->setMaincompany($entity);
            $em->persist($patype7);


            $labelconf = new Labelconf();
            $labelconf->setLastupdate(new \DateTime());
            $labelconf->setTableclass('Guide');
            $labelconf->setWidth(152);
            $labelconf->setHeight(102);
            $labelconf->setMaincompany($entity);
            $em->persist($labelconf);


            $carrier1 = new Carrier();
            $carrier1->setName('Amazon');
            $carrier1->setCreationdate(new \DateTime());
            $carrier1->setDescription('Carrier Amazon');
            $carrier1->setMaincompany($entity);
            $em->persist($carrier1);

            $carrier2 = new Carrier();
            $carrier2->setName('Walmart');
            $carrier2->setCreationdate(new \DateTime());
            $carrier2->setDescription('Carrier Walmart');
            $carrier2->setMaincompany($entity);
            $em->persist($carrier2);

            $carrier3 = new Carrier();
            $carrier3->setName('Currier');
            $carrier3->setCreationdate(new \DateTime());
            $carrier3->setDescription('Paquete entregado por el cliente');
            $carrier3->setMaincompany($entity);
            $em->persist($carrier3);

            $carrier0 = new Carrier();
            $carrier0->setName('No registrado');
            $carrier0->setCreationdate(new \DateTime());
            $carrier0->setDescription('El carrier no está registrado en el sistema');
            $carrier0->setMaincompany($entity);
            $em->persist($carrier0);

            $carrier4 = new Carrier();
            $carrier4->setName('Reempaque en empresa');
            $carrier4->setCreationdate(new \DateTime());
            $carrier4->setDescription('El paquete ha sido reempacado en la empresa');
            $carrier4->setMaincompany($entity);
            $em->persist($carrier4);


            $oficina = new Office();
            $oficina->setName('Master');
            $oficina->setComment('Oficina principal');
            $oficina->setCreationdate(new \DateTime());
            $oficina->setMaincompany($entity);
            $em->persist($oficina);


            foreach ($entity->getcountries() as $country) {
                $company = new Company();
                $company->setName($entity->getName() . ' en ' . $country->getName());
                $company->setCreationdate(new \DateTime());
                $company->setComment('Empresa Principal en ' . $country->getName() );
                $company->setCountry($country);
                $company->setMaincompany($entity);
                $em->persist($company);

                // if ($country->getName() != 'USA') {
                $states = $em->getRepository('NvCargaBundle:State')->findByCountry($country);
                $region = new Region();
                $region->setName('Todas las ciudades de '. $country->getName());
                $region->setCountry($country);
                $region->setMaincompany($entity);
                /*
                foreach ($states as $state) {
                    $cities = $em->getRepository('NvCargaBundle:City')->findByState($state);
                    foreach ($cities as $city) {
                        $region->addCity($city);
                    }
                }
                */
                    $em->persist($region);
                //}
                // AGREGAR DOS TARIFAS BASICAS

                $name = 'Aérea General ' . $country->getName();
                $tariff1 = new Tariff();
                $tariff1->setAgency($agency);
                $tariff1->setRegion($region);
                $tariff1->setLastupdate(new \DateTime());
                $tariff1->setShippingtype($servAereo);
                $tariff1->setName('Tarifa ' . $name);
                $tariff1->setMeasure($lb);
                $tariff1->setInsurance(false);
                $tariff1->setInsurancePer(0);
                $tariff1->setTax(false);
                $tariff1->setTaxPer(0);
                $tariff1->setDimentional(false);
                $tariff1->setCost(0);
                $tariff1->setBegin(0.01);
                $tariff1->setUntil(5000);
                $tariff1->setMinimun(0.01);
                $tariff1->setValueMeasure(1);
                $tariff1->setValueMin(1);
                $tariff1->setMinimunLimit('Total');
                $tariff1->setProfitAg(0);
                $tariff1->setVolumePrice(0);
                $tariff1->setProfitAgv(0);
                $tariff1->setAdditional(0);
                $tariff1->setLabelAdditional('');
                $tariff1->setActive(true);
                $em->persist($tariff1);

                $name = 'Marítima General ' . $country->getName();
                $tariff2 = new Tariff();
                $tariff2->setAgency($agency);
                $tariff2->setRegion($region);
                $tariff2->setLastupdate(new \DateTime());
                $tariff2->setShippingtype($servMar);
                $tariff2->setName('Tarifa ' . $name);
                $tariff2->setMeasure($cf);
                $tariff2->setInsurance(false);
                $tariff2->setInsurancePer(0);
                $tariff2->setTax(false);
                $tariff2->setTaxPer(0);
                $tariff2->setDimentional(false);
                $tariff2->setCost(0);
                $tariff2->setBegin(0.01);
                $tariff2->setUntil(5000);
                $tariff2->setMinimun(0.01);
                $tariff2->setValueMeasure(1);
                $tariff2->setValueMin(1);
                $tariff2->setMinimunLimit('Total');
                $tariff2->setProfitAg(0);
                $tariff2->setVolumePrice(0);
                $tariff2->setProfitAgv(0);
                $tariff2->setAdditional(0);
                $tariff2->setLabelAdditional('');
                $tariff2->setActive(true);
                $em->persist($tariff2);
            }

            $em->persist($user);
            $em->persist($utrack);
            $em->persist($userweb);
            $em->persist($warehouse);
            $em->persist($agency);
            $em->persist($entity);
            $path = $this->getParameter('companies_logos');
            $entity->setLogo('default.png');
            $entity->upload($path);
            $em->flush();
            return $this->redirect($this->generateUrl('maincompany'));

        } else {
            // exit(\Doctrine\Common\Util\Debug::dump($form));
            $translator = $this->get('translator');
            $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Hay errores en la creación de la EMPRESA, por favor, revisar los mensajes de los campos.');
        }
        next:
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Displays a form to create a new Maincompany entity.
     *
     * @Route("/new", name="maincompany_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_MAINCOMPANY')")
     */
    public function newAction()
    {
        $entity = new Maincompany();
        $em = $this->getDoctrine()->getManager();
        $usa = $em->getRepository('NvCargaBundle:Country')->findOneByCode('US');
        $entity->addCountry($usa);

        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Displays a form to edit an existing Maincompany entity.
     *
     * @Route("/{id}/edit", name="maincompany_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $agency=$this->getUser()->getAgency();
        $maincompany = $this->getUser()->getMaincompany();

        if (($agency->getType() == "MASTER" ) && ($id == $maincompany->getId()) || ($this->isGranted('ROLE_ADMIN_MAINCOMPANY'))) {
            $entity = $em->getRepository('NvCargaBundle:Maincompany')->find($id);
        } else {
            throw $this->createNotFoundException('No tiene permisos para editar datos de la empresa');
        }

        if (!$entity) {
            throw $this->createNotFoundException('No se encuentran datos de la Empresa');
        }
        // exit(\Doctrine\Common\Util\Debug::dump($entity->getPoboxmsg()));
        $msg = $entity->getPoboxmsg();
        $msg = str_replace(array("\r\n"), "<br>", $msg);
        $entity->setPoboxmsg($msg);

        $countries = array();
        foreach ($entity->getCountries() as $country) {
            $countries[] = $country->getName();
        }

        $editForm = $this->createEditMainForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'countries' =>$countries,
            //'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Edits an existing Maincompany entity.
     *
     * @Route("/{id}/update", name="maincompany_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Maincompany:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();
        $agency=$this->getUser()->getAgency();

        $maincompany = $this->getUser()->getMaincompany();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }

        if (($agency->getType() == "MASTER" ) && ($id == $maincompany->getId()) || ($this->isGranted('ROLE_ADMIN_MAINCOMPANY'))) {
            $entity = $em->getRepository('NvCargaBundle:Maincompany')->find($id);
        } else {
            throw $this->createNotFoundException('No tiene permisos para editar datos de la empresa');
        }

        if (!$entity) {
            throw $this->createNotFoundException('No se encuentran datos de la Empresa');
        }

        $thisemail = $entity->getEmail();
        $thisname = $entity->getName();
        $thisacron = $entity->getAcronym();
        $thisurl= $entity->getUrl();

        $originalCountries = $entity->getCountries();

        $countries = array();
        foreach ($entity->getCountries() as $country) {
            $countries[] = $country->getName();
        }

        $filename = $entity->getLogo();
        $editForm = $this->createEditMainForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            $msg = $entity->getPoboxmsg();
            $msg = str_replace("<br>", "\r\n",  $msg);
            $entity->setPoboxmsg($msg);
            $error = false;
            $mainemail = $em->getRepository('NvCargaBundle:Maincompany')->findOneByEmail($entity->getEmail());
            $mainname = $em->getRepository('NvCargaBundle:Maincompany')->findOneByName($entity->getName());
            $mainacron = $em->getRepository('NvCargaBundle:Maincompany')->findByAcronym($entity->getAcronym());
            $mainurl = $em->getRepository('NvCargaBundle:Maincompany')->findByUrl($entity->getUrl());
            // exit(\Doctrine\Common\Util\Debug::dump($thisemail . ' ' . $entity->getEmail()));
            if (($mainemail) && ($entity->getEmail() != $thisemail)) {
                $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Ya existe una EMPRESA con ese EMAIL');
                $error = true;
            }
            if (($mainname) && ($entity->getName() != $thisname)) {
                $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Ya existe una EMPRESA con ese NOMBRE');
                $error = true;
            }
            if (($mainacron) && ($entity->getAcronym() != $thisacron)) {
                $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Ya existe una EMPRESA con ese ACRÓNIMO');
                $error = true;
            }
            if (($mainurl) && ($entity->getUrl() != $thisurl)) {
                $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Ya existe una EMPRESA con ese URL');
                $error = true;
            }
            $countries = count($entity->getCountries());
            $plan = $entity->getPlan();
            if ($plan->getCountries()) {
                $max = $plan->getMaxcountries();
                if ($max == 1) {
                    $message = 'El plan seleccionado solo permite tener ' . $max  . ' país. Usted ha seleccionado ' . $countries ;
                } else {
                    $message = 'El plan seleccionado solo permite tener ' . $max  . ' países. Usted ha seleccionado ' . $countries;
                }
                if ($this->isGranted('ROLE_ADMIN')) {
                    $message = $message . 'Para usar  más debe actualizar su plan a uno superior. <a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                }
                if ($countries > $max) {
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        $message);
                    $error = true;
                }
            }
            if ($error) {
                goto next;
            }

            $lb = $em->getRepository('NvCargaBundle:Measure')->findOneByName('Lb');
            $cf = $em->getRepository('NvCargaBundle:Measure')->findOneByName('CF');
            $servAereo = $em->getRepository('NvCargaBundle:Shippingtype')->findOneByName('Aéreo');
            $servMar = $em->getRepository('NvCargaBundle:Shippingtype')->findOneByName('Marítimo');

            foreach ($entity->getcountries() as $country) {
                $compcountry = $em->getRepository('NvCargaBundle:Company')->findBy(['country'=>$country,'maincompany'=>$entity]);
                if (count($compcountry) == 0) {
                    $company = new Company();
                    $company->setName($entity->getName() . ' en ' . $country->getName());
                    $company->setCreationdate(new \DateTime());
                    $company->setComment('Empresa Principal en ' . $country->getName() );
                    $company->setCountry($country);
                    $company->setMaincompany($entity);
                    $em->persist($company);
                }
                $regionname =  'Todas las ciudades de '. $country->getName();
                $theregion = $em->getRepository('NvCargaBundle:Region')->findBy(['name'=>$regionname, 'country'=>$country,'maincompany'=>$entity]);
                if (!$theregion) {
                    $region = new Region();
                    $region->setName($regionname);
                    $region->setCountry($country);
                    $region->setMainCompany($entity);
                    $agencies = $em->getRepository('NvCargaBundle:Agency')->findBy(['maincompany'=>$entity]);
                    foreach ($agencies as $agency) {
                        // AGREGAR DOS TARIFAS BASICAS
                        $name = 'Aérea General ' . $country->getName();
                        $tariff1 = new Tariff();
                        $tariff1->setAgency($agency);
                        $tariff1->setRegion($region);
                        $tariff1->setLastupdate(new \DateTime());
                        $tariff1->setShippingtype($servAereo);
                        $tariff1->setName('Tarifa ' . $name);
                        $tariff1->setMeasure($lb);
                        $tariff1->setInsurance(false);
                        $tariff1->setInsurancePer(0);
                        $tariff1->setTax(false);
                        $tariff1->setTaxPer(0);
                        $tariff1->setDimentional(false);
                        $tariff1->setCost(0);
                        $tariff1->setBegin(0.01);
                        $tariff1->setUntil(5000);
                        $tariff1->setMinimun(0.01);
                        $tariff1->setValueMeasure(1);
                        $tariff1->setValueMin(1);
                        $tariff1->setMinimunLimit('Total');
                        $tariff1->setProfitAg(0);
                        $tariff1->setVolumePrice(0);
                        $tariff1->setProfitAgv(0);
                        $tariff1->setAdditional(0);
                        $tariff1->setLabelAdditional('');
                        $tariff1->setActive(true);
                        $em->persist($tariff1);

                        $name = 'Marítima General ' . $country->getName();
                        $tariff2 = new Tariff();
                        $tariff2->setAgency($agency);
                        $tariff2->setRegion($region);
                        $tariff2->setLastupdate(new \DateTime());
                        $tariff2->setShippingtype($servMar);
                        $tariff2->setName('Tarifa ' . $name);
                        $tariff2->setMeasure($cf);
                        $tariff2->setInsurance(false);
                        $tariff2->setInsurancePer(0);
                        $tariff2->setTax(false);
                        $tariff2->setTaxPer(0);
                        $tariff2->setDimentional(false);
                        $tariff2->setCost(0);
                        $tariff2->setBegin(0.01);
                        $tariff2->setUntil(5000);
                        $tariff2->setMinimun(0.01);
                        $tariff2->setValueMeasure(1);
                        $tariff2->setValueMin(1);
                        $tariff2->setMinimunLimit('Total');
                        $tariff2->setProfitAg(0);
                        $tariff2->setVolumePrice(0);
                        $tariff2->setProfitAgv(0);
                        $tariff2->setAdditional(0);
                        $tariff2->setLabelAdditional('');
                        $tariff2->setActive(true);
                        $em->persist($tariff2);
                    }
                    $em->persist($region);
                }
            }
          //$varTemporal= false; // variable para poder crear la empresa desde panel internacional
          //if($varTemporal == true){
            $logo = $editForm['fileLogo']->getData();
            $entity->upload($this->getUser()->getId(), $this->getParameter('companies_logos'), $logo, 'Logo');
            $logomain = $editForm['fileLogomain']->getData();
            $entity->upload($this->getUser()->getId(), $this->getParameter('companies_logosmain'), $logomain, 'Logomain');
            $background = $editForm['fileBackground']->getData();
            $entity->upload($this->getUser()->getId(), $this->getParameter('companies_backgrounds'), $background, 'Background');
            $em->flush();
            return $this->redirect($this->generateUrl('maincompany_show', array('id' => $entity->getId())));
        //}
      }
         else {
            //exit(\Doctrine\Common\Util\Debug::dump(filesize($entity->getLogo())));
            $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Hay errores en la FORMA');
        }


        next:
        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'countries' =>$countries,
            // 'delete_form' => $deleteForm->createView(),
        );
    }
    private function setSecurePassword(&$entity) {
        $entity->setSalt(md5(time()));
        $encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', true, 10);
        $password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
        $entity->setPassword($password);
    }
    /**
    * @Route("/loadlogo", name="loadlogo")
    */
    public function loadlogoAction(Request $request) {
        $output = array('uploaded' => false);
        // get the file from the request object
        $file = $request->files->get('file');
        // generate a new filename (safer, better approach)
        // To use original filename, $fileName = $this->file->getClientOriginalName();
        $fileName = $this->getUser()->getId() . '-' . md5(uniqid()). '.'.  $file->guessExtension();

        // set your uploads directory
        $uploadDir = $this->getParameter('companies_logos') . '/tmp';
        if (!file_exists($uploadDir) && !is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        if ($file->move($uploadDir, $fileName)) {
            $output['uploaded'] = true;
            $output['fileName'] = $fileName;
        }
        return new JsonResponse($output);
    }
    /**
    * @Route("/loadlogomain", name="loadlogomain")
    */
    public function loadlogomainAction(Request $request) {
        $output = array('uploaded' => false);
        // get the file from the request object
        $file = $request->files->get('file');
        // generate a new filename (safer, better approach)
        // To use original filename, $fileName = $this->file->getClientOriginalName();
        $fileName = $this->getUser()->getId() . '-' . md5(uniqid()). '.'.  $file->guessExtension();

        // set your uploads directory
        $uploadDir = $this->getParameter('companies_logosmain') . '/tmp';
        if (!file_exists($uploadDir) && !is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        if ($file->move($uploadDir, $fileName)) {
            $output['uploaded'] = true;
            $output['fileName'] = $fileName;
        }
        return new JsonResponse($output);
    }

    /**
    * @Route("/loadbackground", name="loadbackground")
    */
    public function loadbackgroundAction(Request $request) {
        $output = array('uploaded' => false);
        // get the file from the request object
        $file = $request->files->get('file');
        // generate a new filename (safer, better approach)
        // To use original filename, $fileName = $this->file->getClientOriginalName();
        $fileName = $this->getUser()->getId() . '-' . md5(uniqid()). '.'.  $file->guessExtension();

        // set your uploads directory
        $uploadDir = $this->getParameter('companies_backgrounds') . '/tmp';
        if (!file_exists($uploadDir) && !is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        if ($file->move($uploadDir, $fileName)) {
            $output['uploaded'] = true;
            $output['fileName'] = $fileName;
        }
        return new JsonResponse($output);
    }
    /**
     * Displays a form to change password.
     *
     * @Route("/{id}/updatelogo", name="updatelogo")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updatelogoAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Maincompany')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el usuario');
        }
        if ($maincompany != $entity) {
            throw $this->createNotFoundException('No tiene permiso para esta acción');
        }
        // Se verifica con el VOTER si se tiene el permiso para cambiar la clave
        $form = $this->get('form.factory')->createNamedBuilder('maincompany_type', 'form', array())
            ->add('fileLogo', 'hidden')
            ->getForm();
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $logo = $form['fileLogo']->getData();
                $entity->upload($this->getUser()->getId(), $this->getParameter('companies_logos'), $logo, 'Logo');
                $em->flush();
                return $this->redirect($this->generateUrl('maincompany_show', array('id' => $entity->getId())));
            } else {
                $flashBag = $this->get('session')->getFlashBag();
                foreach ($flashBag->keys() as $type) {
                        $flashBag->set($type, array());
                }
                $flashBag->add('notice', 'Hay errores.... DEBE revisar.');
            }
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Displays a form to change password.
     *
     * @Route("/{id}/updatelogomain", name="updatelogomain")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updatelogomainAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Maincompany')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el usuario');
        }
        if ($maincompany != $entity) {
            throw $this->createNotFoundException('No tiene permiso para esta acción');
        }
        // Se verifica con el VOTER si se tiene el permiso para cambiar la clave
        $form = $this->get('form.factory')->createNamedBuilder('maincompany_type', 'form', array())
            ->add('fileLogomain', 'hidden')
            ->getForm();
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $logomain = $form['fileLogomain']->getData();
                $entity->upload($this->getUser()->getId(), $this->getParameter('companies_logosmain'), $logomain, 'Logomain');
                $em->flush();
                return $this->redirect($this->generateUrl('maincompany_show', array('id' => $entity->getId())));
            } else {
                $flashBag = $this->get('session')->getFlashBag();
                foreach ($flashBag->keys() as $type) {
                        $flashBag->set($type, array());
                }
                $flashBag->add('notice', 'Hay errores.... DEBE revisar.');
            }
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Displays a form to change password.
     *
     * @Route("/{id}/updatebackground", name="updatebackground")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updatebackgroundAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Maincompany')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el usuario');
        }
        if ($maincompany != $entity) {
            throw $this->createNotFoundException('No tiene permiso para esta acción');
        }
        // Se verifica con el VOTER si se tiene el permiso para cambiar la clave
        $form = $this->get('form.factory')->createNamedBuilder('maincompany_type', 'form', array())
            ->add('fileBackground', 'hidden')
            ->getForm();
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $background = $form['fileBackground']->getData();
                $entity->upload($this->getUser()->getId(), $this->getParameter('companies_backgrounds'), $background, 'Background');
                $em->flush();
                return $this->redirect($this->generateUrl('maincompany_show', array('id' => $entity->getId())));
            } else {
                $flashBag = $this->get('session')->getFlashBag();
                foreach ($flashBag->keys() as $type) {
                        $flashBag->set($type, array());
                }
                $flashBag->add('notice', 'Hay errores.... DEBE revisar.');
            }
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Displays a form to change password.
     *
     * @Route("/active", name="maincompany_active")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function activeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $username=$user->getUsername();
        $id = $request->query->get('theid');
        $entity = $em->getRepository('NvCargaBundle:Maincompany')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la empresa');
        }
        if ($username != 'trackingpremium') {
            throw $this->createNotFoundException('No puede ejecutar esta acción');
        }
        $entity->setInactive(!$entity->getInactive());
        $em->flush();
        return $this->redirect($this->generateUrl('maincompany'));
    }
}
