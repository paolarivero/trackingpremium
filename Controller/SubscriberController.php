<?php

namespace NvCarga\Bundle\Controller;

use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

// For login a user programatically
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Form\FormError;

use NvCarga\Bundle\Entity\Subscriber;
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
use NvCarga\Bundle\Entity\Measure;

use NvCarga\Bundle\Form\MaincompanyType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Subscriber controller.
 *
 * @Route("/subscriber")
 */
class SubscriberController extends Controller
{
    /**
     * Finds and displays a  menu for STRIPE Customer entity.
     *
     * @Route("/menu", name="subscriber_menu")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function menuAction() {
        $user = $this->getUser();
        if (!$user->getStripeCustomer()) {
            throw $this->createNotFoundException('El cliente no está registrado como EMPRESA en nuestro sistema.' );
        }
    }
    /**
     * Finds and displays a  menu for STRIPE Customer entity.
     *
     * @Route("/updateplan", name="subscriber_updateplan")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function updateplanAction(Request $request) {
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }

        $em = $this->getDoctrine()->getManager();
        $subscriber = $user->getSubscriber();
        if (!$user->getStripeCustomer()) {
            throw $this->createNotFoundException('El cliente no está registrado como EMPRESA en nuestro sistema.' );
        }
        if (!$subscriber) {
            throw $this->createNotFoundException('El cliente no tiene un PLAN en nuestro sistema.' );
        }
        if (!$subscriber->getSubscription()) {
            throw $this->createNotFoundException('El cliente no tiene un PLAN subscrito. ' );
        }
        $oldplan = $subscriber->getPlan();

        $stripe_mode = $this->container->getParameter('stripe_mode');
        $stripe_public_key = $this->container->getParameter('stripe_' . $stripe_mode . '_public_key');
        $stripe_secret_key = $this->container->getParameter('stripe_' . $stripe_mode . '_secret_key');


        $form = $this->get('form.factory')
            ->createNamedBuilder('plan-form')
            ->add('submit', 'submit', ['label'=> 'Actualizar '])
            ->add('selectcard', 'hidden')
            ->add('plan', 'entity', array('label' => false, 'mapped' => false,
                'required' => true,
                'class'=> 'NvCargaBundle:Plan',
                'expanded' => false, 'multiple'=>false, 'data'=>$subscriber->getPlan(),))
            ->getForm();
        \Stripe\Stripe::setApiKey($stripe_secret_key);
        try {
            $stripecustomer = \Stripe\Customer::retrieve($user->getStripeCustomer());
            $stripecards = $stripecustomer->sources->all(array("object" => "card"));
        } catch (Exception $e) {
            throw $this->createNotFoundException('No es posible verificar sus DATOS en este momento. Por favor, contacta al adinistrador del sistema y reporte el probelma. ');
        }
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $plan =  $form['plan']->getData();
                $newcard = $form['selectcard']->getData();
                try {
                    $subscription = \Stripe\Subscription::retrieve($subscriber->getSubscription());
                    \Stripe\Subscription::update($subscriber->getSubscription(), [
                        'items' => [
                            [
                                'id' => $subscription->items->data[0]->id,
                                'plan' => $plan->getStripePlan(),
                            ],
                        ],
                    ]);
                    $stripecustomer->default_source=$newcard;
                    $stripecustomer->save();
                    $subscriber->setPlan($plan);
                    $maincompany->setPlan($plan);
                    $em->flush();
                    } catch(Exception $e) {
                        throw $this->createNotFoundException('No es posible verificar sus DATOS en este momento. Por favor, contacta al adinistrador del sistema y reporte el probelma. ');
                    }
                    $flashBag->add('info', 'Usted se ha cambiado del Plan "'. $oldplan . " al Plan ". $plan . '"');
                    return $this->redirect($this->generateUrl('subscriber_menu'));
            } else {
                $flashBag->add('notice', 'Hay errores en la FORMA.. DEBE revisar.');
            }
        }
        return array(
            'form'   => $form->createView(),
            'stripe_public_key' =>$stripe_public_key,
            'cards' => $stripecards->data,
        );
    }
    /**
     * Finds and displays a  menu for STRIPE Customer entity.
     *
     * @Route("/loginmain", name="loginmain")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function loginmainAction() {
        $user = $this->getUser();
        $mainserver = $this->container->getParameter('mainserver');
        $maincompany = $user->getMaincompany();
        $newuser = $maincompany->getCompanyuser();
        if ($newuser)  {
            $em = $this->getDoctrine()->getManager();
            $token = md5(uniqid());
            $homecompany = $em->getRepository('NvCargaBundle:Maincompany')->find(1);
            $homecompany->setToken($token);
            $mainpage = 'http://' . $mainserver  . '/menulogin/'. $token . '/' . $newuser->getId();
            $em->flush();
            return new RedirectResponse($mainpage);
        } else {
            throw $this->createNotFoundException('No es posible INGRESAR a la actualización del PLAN. Contacte al ADMINISTRADOR del Sistema.' );
        }
    }
     /**
     * Finds and displays a  menu for STRIPE Customer entity.
     *
     * @Route("/login", name="subscriber_login")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function loginAction() {
        $user = $this->getUser();
        if (!$user->getStripeCustomer()) {
            throw $this->createNotFoundException('El cliente no está registrado como EMPRESA en nuestro sistema.' );
        }
        $subscriber = $user->getSubscriber();
        if ($subscriber) {
            $maincompany = $subscriber->getMaincompany();
            if ($maincompany) {
                $em = $this->getDoctrine()->getManager();
                $active = $em->getRepository('NvCargaBundle:Userstatus')->findOneByName('ACTIVO');
                $admin = $em->getRepository('NvCargaBundle:User')->createQueryBuilder('u')
                    ->where('u.username != :tracking')
                    ->andwhere('u.type = :sistema')
                    ->andwhere('u.status = :activo')
                    ->andwhere('u.maincompany = :maincompany')
                    ->setParameters(array('tracking'=>'trackingpremium', 'sistema'=> 'SISTEMA', 'activo'=>$active, 'maincompany'=>$maincompany))
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();
                if ($admin) {
                    $token=md5(uniqid());
                    $admin->setAuthId($token);
                    $em->flush();
                    return new RedirectResponse('http://'. $user->getSubscriber()->getMaincompany()->getHomepage(). '/logincompany/'. $token);
                } else {
                   throw $this->createNotFoundException('No tiene un usuario ADMINISTRADOR para ingresar al sistema' );
                }
            } else {
                throw $this->createNotFoundException('Para ingresar debe tener una EMPRESA configurada' );
            }
        } else {
            throw $this->createNotFoundException('Para ingresar debe haberse subscrito a uno de nuestros planes' );
        }
    }
    /**
     * @Route("/selectplan", name="subscriber_selectplan")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function selectplanAction(Request $request) {

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        if (!$user->getStripeCustomer()) {
            throw $this->createNotFoundException('El cliente no está registrado como EMPRESA en nuestro sistema.' );
        }
        $stripe_mode = $this->container->getParameter('stripe_mode');
        $stripe_public_key = $this->container->getParameter('stripe_' . $stripe_mode . '_public_key');
        $stripe_secret_key = $this->container->getParameter('stripe_' . $stripe_mode . '_secret_key');

        $form = $this->get('form.factory')
            ->createNamedBuilder('payment-form')
            ->add('token', 'hidden', [
                'constraints' => [new NotBlank()],
            ])
            ->add('submit', 'submit', ['label'=> 'Aceptar '])
            ->add('plan', 'entity', array('label' => false, 'mapped' => false,
                'required' => true,
                'data_class'=> 'NvCarga\Bundle\Entity\Plan', 'class'=> 'NvCargaBundle:Plan',
                'expanded' => false, 'multiple'=>false,))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $plan =  $form['plan']->getData();
                // exit(\Doctrine\Common\Util\Debug::dump($plan));
                \Stripe\Stripe::setApiKey($stripe_secret_key);
                try {
                    $customer = \Stripe\Customer::retrieve($user->getStripeCustomer());
                    $token = $form['token']->getData();
                    // exit(\Doctrine\Common\Util\Debug::dump($token));
                    $card = $customer->sources->create(['source' => $token]);
                    $subscription = \Stripe\Subscription::create(array(
                        "customer" => $user->getStripeCustomer(),
                        "items" => array(
                            array(
                            "plan" => $plan->getStripePlan(),
                            ),
                        )
                    ));
                    $customer->save();
                    $subscriber = new Subscriber();
                    $plan->addSubscriber($subscriber);
                    $user->setSubscriber($subscriber);

                    $subscriber->setPlan($plan);
                    $subscriber->setUser($user);
                    $subscriber->setActive(true);
                    $subscriber->setSubscription($subscription->id);
                    $subscriber->setBalance($customer->account_balance); // OJO CUANTO ES LA PRIMERA VEZ
                    $em->persist($subscriber);
                    $em->flush();

                } catch(Exception $e) {
                    throw $this->createNotFoundException('No es posible verificar sus DATOS en este momento. Por favor, contacta al adinistrador del sistema y reporte el probelma. ');
                }
                return $this->redirect($this->generateUrl('subscriber_menu'));
            } else {
                $flashBag = $this->get('session')->getFlashBag();
                foreach ($flashBag->keys() as $type) {
                        $flashBag->set($type, array());
                }
                $flashBag->add('notice', 'Hay errores en la FORMA.. DEBE revisar.');
            }
        }
        return array(
            'form'   => $form->createView(),
            'stripe_public_key' =>$stripe_public_key,
        );
    }

    /**
     * Finds and displays a  menu for STRIPE Customer entity.
     *
     * @Route("/config", name="subscriber_config")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function configAction() {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        if (!$user->getStripeCustomer()) {
            throw $this->createNotFoundException('El cliente no está registrado como EMPRESA en nuestro sistema.' );
        }
        $subscriber = $user->getSubscriber();
        if (!$subscriber) {
            throw $this->createNotFoundException('Debe estar SUBSCRITO a uno de nuestros planes para poder configurar su empresa' );
        }

        if ($subscriber->getMaincompany()) {
            throw $this->createNotFoundException('Usted ya ha configurado la EMPRESA. ' );
        }

        $entity = new Maincompany();
        $usa = $em->getRepository('NvCargaBundle:Country')->findOneByCode('US');
        $entity->addCountry($usa);

        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'nocollapse' => false,
        );
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
            'action' => $this->generateUrl('config_create'),
            'method' => 'POST',
        ));
        $translator = $this->get('translator');
        //$form->add('file', 'file', array('label'=> 'Logo ', 'required'=>true,
        //    'constraints' => array(new NotBlank(["message" => "Por favor, seleccione un archivo con el logo de la empresa (png/jpg)"]))));
        $form->add('fileLogo', 'hidden', array('mapped'=>false));
        $form->add('fileLogomain', 'hidden', array('mapped'=>false));
        $form->add('fileBackground', 'hidden', array('mapped'=>false));

        $em = $this->getDoctrine()->getManager();

        $form->add('countries', 'entity', array('label' => 'Países donde trabaja ', 'class'=> 'NvCargaBundle:Country',
                     'multiple'=>'true', 'expanded' => true, 'attr' => array('class' => 'icheck checkcountries')));

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
            ->add('cityid', 'hidden', array('mapped'=>false, 'data'=>0));
        $form->add('username', 'text', array('label' => 'Username ', 'mapped'=>false,
                'constraints' => array(new NotBlank(["message" => "Asigne el  USERNAME"]))))
                ->add('useremail','email', array('label' => 'Email ', 'mapped'=>false,
                    'constraints' => array(
                        new NotBlank(["message" => "Debe asignar un email"]),
                        new Email(["message" => "El email '{{ value }}' no es válido.",
                        "checkMX" => true, "checkHost" => true]))))
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
        $form->remove('homepage');
        $form->add('submit', 'submit', array('label' => 'Aceptar'));

        return $form;
    }
    /**
     * Creates a new Maincompany entity.
     *
     * @Route("/create", name="config_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Subscriber:config.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function createAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        if (!$user->getStripeCustomer()) {
            throw $this->createNotFoundException('El cliente no está registrado como EMPRESA en nuestro sistema.' );
        }
        $subscriber = $user->getSubscriber();
        if (!$subscriber) {
            throw $this->createNotFoundException('Debe estar SUBSCRITO a uno de nuestros planes para poder configurar su empresa' );
        }


        if ($subscriber->getMaincompany()) {
            throw $this->createNotFoundException('Usted ya ha configurado la EMPRESA. ' );
        }
        $entity = new Maincompany();
        $usa = $em->getRepository('NvCargaBundle:Country')->findOneByCode('US');
        $entity->addCountry($usa);
        $entity->setHomepage('NUEVA EMPRESA');
        $entity->setPlan($subscriber->getPlan());

        $form   = $this->createCreateForm($entity);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $form->handleRequest($request);

        $nocollapse = true;

        if ($form->isValid()) {
            // CREACION DEL SUBDOMINIO EN GODADDY
            $acronym = $entity->getAcronym();
            $subdomain = $acronym . '.multitrack';
            $homepage = $subdomain . '.trackingpremium.com';

            $msg = $entity->getPoboxmsg();
            $msg = str_replace("<br>", "\r\n",  $msg);
            $entity->setPoboxmsg($msg);
            $error = false;
            if (count($entity->getCountries()) < 1) {
                $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Debe escoger por lo MENOS UN PAÍS');
                $error = true;
            }
            $mainemail = $em->getRepository('NvCargaBundle:Maincompany')->findOneByEmail($entity->getEmail());
            $mainname = $em->getRepository('NvCargaBundle:Maincompany')->findOneByName($entity->getName());
            $mainacron = $em->getRepository('NvCargaBundle:Maincompany')->findByAcronym($acronym);
            $mainurl = $em->getRepository('NvCargaBundle:Maincompany')->findByUrl($entity->getUrl());
            $entity->setHomepage($homepage);
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
                        'No podemos crear el HOMEPAGE, ya existe una EMPRESA con el HOMEPAGE: ' . $homepage);
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
                if ($countries > $max) {
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        $message);
                    $error = true;
                }
            }
            $cid = $form['cityid']->getData();
            $city = $em->getRepository('NvCargaBundle:City')->find($cid);

            if (!$city) {
                $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Debe seleccionar una ciudad para la AGENCIA');
                $error = true;
            }
            $godaddy_api_key = $this->container->getParameter('godaddy_api_key');
            $godaddy_api_secret = $this->container->getParameter('godaddy_api_secret');
            $godaddy_ip = $this->container->getParameter('godaddy_ip');
            $godaddy_url = $this->container->getParameter('godaddy_url');
            $headers = array(
                'Authorization: sso-key ' .  $godaddy_api_key . ':' . $godaddy_api_secret,
                'Accept: application/json',
                'Content-Type: application/json',
            );
            $data = '[{ "type":"A", "name":"' . $subdomain . '", "data": "' . $godaddy_ip . '","ttl":600 }]';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $godaddy_url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            if (!$response) {
                $this->get('session')->getFlashBag()->add(
                        'notice',
                        'No podemos configurar su EMPRESA, por favor contáctenos por EMAIL a info@trackingpremium.com');
                $error = true;
            }
            if ($error) {
                goto next;
            }
            $astatus = $em->getRepository('NvCargaBundle:Agencystatus')->findOneByName('ACTIVA');
            $atype = $em->getRepository('NvCargaBundle:Agencytype')->findOneByName('MASTER');
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
            $user->setType('SISTEMA');
            $user->setUsername($form['username']->getdata());
            $user->setEmail($form['useremail']->getdata());
            $user->setName($form['firstname']->getdata());
            $user->setLastname($form['lastname']->getdata());
            $user->setAgency($agency);
            $agency->addUser($user);
            $user->setCreationdate(new \DateTime());
            $user->setMaincompany($entity);
            $user->addUserRole($roleadmin);
            $entity->setCountadmins(1);
            $user->addUserRole($roleuser);
            $user->setStatus($ustatus);
            $user->setPassword($form['password']->getdata());
            $this->setSecurePassword($user);

            $utrack = new User();
            $utrack->setType('SISTEMA');
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
            $userweb->setType('SISTEMA');
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

            $lb = new Measure();
            $lb->setName('Lb');
            $lb->setLabel('Lb');
            $lb->setMaincompany($entity);
            $lb->setDescription('Medida en Libras');
            $em->persist($lb);

            $cf = new Measure();
            $cf->setName('CF');
            $cf->setLabel('CF');
            $cf->setMaincompany($entity);
            $cf->setDescription('Medida en Pies Cúbicos');
            $em->persist($cf);


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

            /*
            $plan = $subscriber->getPlan();
            $entity->setMaxguides($plan->getMaxguides());
            $entity->setMaxreceipts($plan->getMaxreceipts());
            $entity->setMaxwhrecs($plan->getMaxwhrecs());
            $entity->setMaxconsolidates($plan->getMaxconsolidates());
            $entity->setMaxbills($plan->getMaxbills());
            $entity->setMaxcustomers($plan->getMaxcustomers());
            $entity->setMaxusers($plan->getMaxusers());
            $entity->setMaxpoboxes($plan->getMaxpoboxes());
            $entity->setMaxagencies($plan->getMaxagencies());
            $entity->setMaxbags($plan->getMaxbags());
            $entity->setMaxaccounts($plan->getMaxaccounts());
            $entity->setMaxalerts($plan->getMaxalerts());
            $entity->setMaxadservices($plan->getMaxadservices());
            $entity->setMaxcompanies($plan->getMaxcompanies());
            */

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
            $subscriber->setMaincompany($entity);
            $entity->setSubscriber($subscriber);
            $entity->setPlan($subscriber->getPlan());
            $entity->setCompanyuser($this->getUser());
            $logo = $form['fileLogo']->getData();
            $entity->upload($this->getUser()->getId(), $this->getParameter('companies_logos'), $logo, 'Logo');
            $logomain = $form['fileLogomain']->getData();
            $entity->upload($this->getUser()->getId(), $this->getParameter('companies_logosmain'), $logomain, 'Logomain');
            $background = $form['fileBackground']->getData();
            $entity->upload($this->getUser()->getId(), $this->getParameter('companies_backgrounds'), $background, 'Background');
            $em->persist($entity);


            $em->flush();

            return $this->redirect($this->generateUrl('subscriber_menu'));

        } else {
            $nocollapse = true;
            // exit(\Doctrine\Common\Util\Debug::dump($form));
            $translator = $this->get('translator');
            $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Hay errores en la configuración de su EMPRESA, por favor, revisar los mensajes de los campos.');
            $message = new FormError('Vuelva a asignar el password en ambos campos');
            $passError = strlen($form['password']->getErrorsAsString());
            if ($passError == 0) {
               $form['password']['first']->addError($message);
            }
            $message2 = new FormError('Por favor, seleccione NUEVAMENTE un archivo con el logo de la empresa (png/jpg)');
            $fileError = strlen($form['file']->getErrorsAsString());
            if ($fileError == 0) {
               $form['file']->addError($message2);
            }
        }
        next:
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'nocollapse' => $nocollapse,
        );
    }
    private function setSecurePassword(&$entity) {
        $entity->setSalt(md5(time()));
        $encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', true, 10);
        $password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
        $entity->setPassword($password);
    }
    /**
     * Add Card to Customer
     *
     * @Route("/addcard", name="addcard")
     * @Security("has_role('ROLE_USER')")
     */
    public function addcardAction(Request $request)
    {
        $user = $this->getUser();
        $token = $request->query->get('token');
        if (!$user->getStripeCustomer()) {
            $result = null;
            goto out;
        }
        $stripe_mode = $this->container->getParameter('stripe_mode');
        $stripe_public_key = $this->container->getParameter('stripe_' . $stripe_mode . '_public_key');
        $stripe_secret_key = $this->container->getParameter('stripe_' . $stripe_mode . '_secret_key');

        \Stripe\Stripe::setApiKey($stripe_secret_key);
        try {
            $customer = \Stripe\Customer::retrieve($user->getStripeCustomer());
            // exit(\Doctrine\Common\Util\Debug::dump($token));
            $card = $customer->sources->create(['source' => $token]);
            $customer->save();
            $result = array();
            $result['id'] = $card->id;
            $result['brand'] = $card->brand;
            $result['funding'] = $card->funding;
            $result['exp_month'] = $card->exp_month;
            $result['exp_year'] = $card->exp_year;
            $result['last4'] = $card->last4;
        } catch(Exception $e) {
            $result = null;
            goto out;
        }

        out:
        return new JsonResponse($result);
    }
    /**
     * Add Card to Customer
     *
     * @Route("/getcard", name="getcard")
     * @Security("has_role('ROLE_USER')")
     */
    public function getcardAction(Request $request)
    {
        $user = $this->getUser();
        $thecard = $request->query->get('card');
        if (!$user->getStripeCustomer()) {
            $result = null;
            goto out;
        }
        $stripe_mode = $this->container->getParameter('stripe_mode');
        $stripe_public_key = $this->container->getParameter('stripe_' . $stripe_mode . '_public_key');
        $stripe_secret_key = $this->container->getParameter('stripe_' . $stripe_mode . '_secret_key');

        \Stripe\Stripe::setApiKey($stripe_secret_key);
        try {
            $customer = \Stripe\Customer::retrieve($user->getStripeCustomer());
            // exit(\Doctrine\Common\Util\Debug::dump($token));
            $card = $customer->sources->retrieve($thecard);
            //$customer->save();
            $result = array();
            $result['id'] = $card->id;
            $result['brand'] = $card->brand;
            $result['funding'] = $card->funding;
            $result['exp_month'] = $card->exp_month;
            $result['exp_year'] = $card->exp_year;
            $result['last4'] = $card->last4;
        } catch(Exception $e) {
            $result = null;
            goto out;
        }

        out:
        return new JsonResponse($result);
    }
}
?>
