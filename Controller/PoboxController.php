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
use NvCarga\Bundle\Entity\Pobox;
use NvCarga\Bundle\Entity\User;
use NvCarga\Bundle\Entity\City;
use NvCarga\Bundle\Entity\Message;
use NvCarga\Bundle\Entity\Customer;
use NvCarga\Bundle\Entity\Baddress;
use NvCarga\Bundle\Form\PoboxType;


// For login a user programatically 
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
/**
 * Pobox controller.
 *
 * @Route("/pobox")
 */
class PoboxController extends Controller
{

    /**
     * Lists all Pobox entities.
     *
     * @Route("/index", name="pobox")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_POBOX') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_POBOX')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $countryto = $user->getAgency()->getCity()->getstate()->getCountry();
        $all = $em->getRepository('NvCargaBundle:Pobox')->findByMaincompany($maincompany);

        if ($user->getAgency()->getType() != 'MASTER') {
            $entities = array();
            $agencies = null;
            foreach ($all as $pobox) {
                if ($pobox->getCustomer()->getAdrdefault()->getCity()->getState()->getCountry() == $countryto) {
                    $entities[] = $pobox;
                }
            }
        } else {
            $entities = $all;
            $agencies = $em->getRepository('NvCargaBundle:Agency')->findBy(['maincompany'=>$maincompany, 'poboxs'=>true]);
        }
        
        
        $result = array('entities' => $entities,
                'agencies' => $agencies,);
        return $result;
    }
    /**
     * Creates a new Pobox entity.
     *
     * @Route("/create", name="pobox_create")
     * @Template("NvCargaBundle:Pobox:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_POBOX') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Pobox();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $form   = $this->createCreateForm($entity);
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $thisuser = $this->getUser();
            $countpobox = $maincompany->getCountpoboxes();
            $plan = $maincompany->getPlan();
            if (($plan->getPoboxes()) && ($countpobox >= $plan->getMaxpoboxes())) {
                $message = 'Ha llegado al número máximo de CASILLEROS permitidos. Para crear más debe actualizar su plan a uno superior.';
                if ($this->isGranted('ROLE_ADMIN')) {
                    $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                }
                $flashBag->add('notice',$message);
                goto next;
                // throw $this->createNotFoundException('La empresa ha alcanzado el número MÁXIMO de CASILLEROS permitidos.' );
            }
            $countpobox++;
            $maincompany->setCountpoboxes($countpobox);
            
            $agency =  $form['agencypobox']->getData();
            $city = $agency->getCity();	    

            $em = $this->getDoctrine()->getManager();
            $warehouse = $agency->getWarehouse();
            $address=$warehouse->getAddress();

            $type = $em->getRepository('NvCargaBundle:Poboxtype')->findOneByName('NORMAL');   
            
            $status = $em->getRepository('NvCargaBundle:Poboxstatus')->findOneByName('ACTIVO');
            $cusid = $form['id_customer']->getData();
            $cityid = $form['cityid_customer']->getData();
            $city_customer = null;
            if ($cityid != 0) {
                $city_customer = $em->getRepository('NvCargaBundle:City')->find($cityid);
            }
            if (!$city_customer) {
                $city_customer = $em->getRepository('NvCargaBundle:City')->findOneBy(['name'=>$form['cityname_customer']->getData(), 'state' => $form['state_customer']->getData()]);
            }
            //exit(\Doctrine\Common\Util\Debug::dump($city_customer));

            if (!$city_customer) {
                $city_customer =  new City();
                $city_customer->setName($form['cityname_customer']->getData());
                $city_customer->setActive(false);
                $city_customer->setState($form['state_customer']->getData());
                $em->persist($city_customer);
            }
            $email = $form['email_customer']->getData();
            $user = $em->getRepository('NvCargaBundle:User')->findOneBy(['username'=>$email,'maincompany'=>$maincompany]);
            if ($user) {
                $message = 'Ya existe un casillero con el email: ' . $email;
                $this->get('session')->getFlashBag()->add(
                                'notice',
                            $message);
                goto next;
                //throw $this->createNotFoundException('Ya existe un casillero con el email: ' . $email );
            }
            $usermail = $em->getRepository('NvCargaBundle:User')->findOneBy(['email'=>$email,'maincompany'=>$maincompany]);
            if ($usermail) {
                $message = 'Ya existe un usuario con el email: ' . $email;
                $this->get('session')->getFlashBag()->add(
                                'notice',
                            $message);
                goto next;
                // throw $this->createNotFoundException('Ya existe un usuario con el email: ' . $email );
            }
            if ($cusid == 0) {  
                $customer = $em->getRepository('NvCargaBundle:Customer')->findOneBy(['email'=>$email,'maincompany'=>$maincompany]);
                if ($customer) {
                    $message = 'Ya existe un cliente con el email: ' . $email;
                    $this->get('session')->getFlashBag()->add(
                                'notice',
                            $message);
                    goto next;
                    //throw $this->createNotFoundException('Ya existe un cliente con el email: ' . $email );
                } else {// crear el cliente 
                    $countcustomer = $maincompany->getCountcustomers();
                    /* $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('r')
                                ->select('count(r.id)')
                                ->where('r.maincompany =:thecompany')
                                ->setParameters(['thecompany'=>$maincompany])
                                ->getQuery()
                                ->getSingleScalarResult();*/
                    $plan = $maincompany->getPlan();
                    if (($plan->getCustomers()) && ($countcustomer >= $plan->getMaxcustomers())) {
                        $translator = $this->get('translator');
                        $message = 'La empresa ha alcanzado el número MÁXIMO de CLIENTES permitidos.';
                        $this->get('session')->getFlashBag()->add(
                                    'notice',
                                $message);
                        goto next;
                        // throw $this->createNotFoundException('La empresa ha alcanzado el número MÁXIMO de CLIENTES permitidos.' );
                    }
                    $countcustomer++;
                    $maincompany->setCountcustomers($countcustomer);
                    $customer= new Customer();
                    $customer->setName($form['name_customer']->getData());
                    $customer->setLastname($form['lastname_customer']->getData());
                    $customer->setEmail($form['email_customer']->getData());
                    $customer->setCreationdate(new \DateTime());
                    // $ctype = $em->getRepository('NvCargaBundle:Customertype')->findOneBy(array('name' =>'NORMAL'));
                    $ctype = $form['type']->getData();
                    $cstatus = $em->getRepository('NvCargaBundle:Customerstatus')->findOneBy(array('name' =>'ACTIVO'));
                    $customer->setType($ctype);
                    $customer->setAgency($agency);
                    $customer->setStatus($cstatus);

                    $baddr = new Baddress();
                    //$baddr->setDocid($form['docid_customer']->getData());
                    $baddr->setAddress($form['address_customer']->getData());
                    $baddr->setCity($city_customer);
                    $baddr->setPhone($form['phone_customer']->getData());
                    $baddr->setMobile($form['mobile_customer']->getData());
                    // $baddr->setBarrio($form['barrio_customer']->getData());
                    $baddr->setZip($form['zip_customer']->getData());
                    $baddr->setName($form['name_customer']->getData());
                    $baddr->setLastname($form['lastname_customer']->getData());
                    $baddr->setCustomer($customer);
                    $customer->setAdrdefault($baddr);
                    $customer->setAdrmain($baddr);
                    $customer->addBaddress($baddr);

                    $em->persist($baddr);
                    $customer->setMaincompany($maincompany);
                    $em->persist($customer);
                }
            } else { // el cliente debería existir
                $customer = $em->getRepository('NvCargaBundle:Customer')->find($cusid);
                if (!$customer) { // No debería producirse este error
                    $message = 'El cliente no existe';
                        $this->get('session')->getFlashBag()->add(
                                    'notice',
                                $message);
                        goto next;
                    //throw $this->createNotFoundException('El cliente no existe');
                }
                $sebaddr = $customer->getAdrdefault();
                $change1 = ($sebaddr->getName() != $form['name_customer']->getData()) || ($sebaddr->getLastname() != $form['lastname_customer']->getData()) || ($sebaddr->getAddress() != $form['address_customer']->getData()) || ($sebaddr->getCity() != $city_customer) || ($sebaddr->getPhone() != $form['phone_customer']->getData()) || ($sebaddr->getMobile() != $form['mobile_customer']->getData())  ||  ($sebaddr->getZip() != $form['zip_customer']->getData()); 
                //|| ($sebaddr->getDocid() != $form['docid_customer']->getData());
                if ($change1) {
                    $sebaddr = new Baddress();
                    $sebaddr->setName($form['name_customer']->getData());
                    $sebaddr->setLastname($form['lastname_customer']->getData());
                    $sebaddr->setAddress($form['address_customer']->getData());
                    $sebaddr->setCity($city_customer);
                    $sebaddr->setPhone($form['phone_customer']->getData());
                    $sebaddr->setMobile($form['mobile_customer']->getData());
                    // $sebaddr->setBarrio($form['barrio_customer']->getData());
                    $sebaddr->setZip($form['zip_customer']->getData());
                    $sebaddr->setCustomer($customer);
                    $customer->setAdrdefault($sebaddr);
                    $customer->addBaddress($sebaddr);
                    $em->persist($sebaddr);
                }
            } 
            $thisagen = $agency;
            if ($thisagen->getParent()) {
                $ccsend = $thisagen->getParent()->getEmail();
            } else {
                $ccsend = $thisagen->getEmail();
            }
	    
            $numini= $maincompany->getIninum();
            $highest_id =  $em->getRepository('NvCargaBundle:Pobox')->createQueryBuilder('r')
                            ->select('MAX(r.id)')
                            ->where('r.maincompany =:thecompany')
                            ->setParameters(['thecompany'=>$maincompany])
                            ->getQuery()
                            ->getSingleScalarResult();
            if ($highest_id) {
                $poboxhigh =  $em->getRepository('NvCargaBundle:Pobox')->find($highest_id);
                $numberhigh = $poboxhigh->getNumber();
                $maxvalue = filter_var($numberhigh, FILTER_SANITIZE_NUMBER_INT);
                $maxvalue = str_replace('-', '', $maxvalue);
                $maxnumber = abs(intval($maxvalue));  
            } else {
                $maxnumber = 0;
            }
            $maxnumber++;
            if ($maxnumber < $numini) {
                $maxnumber = $numini;
            }
            
            $countpobox = $maincompany->getCountpoboxes();
            $countpobox++;
            $maincompany->setCountpoboxes($countpobox);
            
            //$number = $maincompany->getPrefixpobox()  . '-'. $maincompany->getAcronym() . '-' . $val;
            $number = $maincompany->getPrefixpobox()  . $maxnumber;
            
            $entity->setNumber($number);

            $entity->setCustomer($customer);
            $entity->setCreationdate(new \DateTime());
            $entity->setWarehouse($warehouse);
            $entity->setType($type);
            $entity->setStatus($status);
            $entity->setCreateby($thisuser);
            $entity->setMaincompany($maincompany);
    
            $countuser = $maincompany->getCountusers();
            $plan = $maincompany->getPlan();
            if (($plan->getUsers()) && ($countuser >= $plan->getMaxusers())) {
                $message = 'La empresa ha alcanzado el número MÁXIMO de USUARIOS permitidos.';
                $this->get('session')->getFlashBag()->add(
                                    'notice',
                            $message);
                goto next;
                //throw $this->createNotFoundException('La empresa ha alcanzado el número MÁXIMO de USUARIOS permitidos.' );
            }
            $countuser++;
            $maincompany->setCountusers($countuser);
            $user = new User();
            $user->setUsername($customer->getEmail());
            $user->setEmail($customer->getEmail());
            $user->setName($customer->getName());
            $user->setLastname($customer->getLastname());
            $user->setAgency($entity->getWarehouse()->getAgency());
            $thepassword = $entity->getPassword();
            $user->setPassword($entity->getPassword());
            $user->setMaincompany($maincompany);
            $user->setSalt(md5(time()));
     	    $encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', true, 10);
            $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
            $user->setPassword($password);
            $user->setCreationdate(new \DateTime());
            $nstatus = 'ACTIVO';
            $status = $em->getRepository('NvCargaBundle:Userstatus')->findOneByName($nstatus);
            if (!$status) {
                $message = 'El status de usuario' . $nstatus . 'no existe';
                $this->get('session')->getFlashBag()->add(
                                    'notice',
                            $message);
                goto next;
                // throw $this->createNotFoundException('El status de usuario' . $nstatus . 'no existe');
            }
            $user->setStatus($status);	
            $nrole = 'ROLE_USER';
            $role = $em->getRepository('NvCargaBundle:Role')->findOneBy(array('name' => $nrole));
            if (!$role) {
                $message = 'El rol' . $nrole . 'no existe';
                $this->get('session')->getFlashBag()->add(
                                    'notice',
                            $message);
                goto next;
                // throw $this->createNotFoundException('El rol' . $nrole . 'no existe');
            }
            $user->addUserRole($role);
            $customer->setPobox($entity);
            $entity->setUser($user);
            $user->setPobox($entity);
        
            $em->persist($entity);
            $em->persist($user);
            
            $pobox = $entity;
            $nameto =  ''; 
            if ($thisagen->getMaincompany()->getCompanyname()) {
                $nameto = $nameto . $thisagen->getMaincompany()->getName();
            }
            if ($thisagen->getMaincompany()->getCustomername()) {
                $nameto = $nameto . $pobox->getCustomer();
            }
            if ($thisagen->getMaincompany()->getNumbername()) {
                $nameto = $nameto . $pobox->getNumber();
            }
            if ($nameto == '') {
                $nameto = $pobox->getCustomer();
            }
            $body = '<p align="right">Ref: ' . $pobox->getNumber() .'</p><br>';
            $body = $body . "--Bienvenid@ a " . $thisagen->getMaincompany()->getName() . '<b> ' . $pobox->getCustomer() . '</b><br><br>';
            $body = $body .  $thisagen->getMaincompany()->getPoboxmsg() . '<br><br>';
            $body = $body .  '<b>*ENVÍA TUS COMPRAS, CON TU PROVEEDOR FAVORITO, SELECCIONANDO LA DIRECCIÓN*</b><br>';
            $body = $body .	'---------------------------------------------------------------------------------------------<br>';
            $body = $body . "Nombre-Name: " . $nameto . "<br>";
            $body = $body . "Dirección-Address: " . $pobox->getWarehouse()->getAddress(). "<br>";
            $body = $body . "Ciudad-City: " .  $pobox->getWarehouse()->getCity() . "<br>";
            $body = $body . "Estado-State: " .  $pobox->getWarehouse()->getCity()->getState() .  "<br>";
            $body = $body . "Country: " .  $pobox->getWarehouse()->getCity()->getState()->getCountry() . "<br>";
            $body = $body . "Código Postal-Zip Code: " . $warehouse->getZip(). "<br>";
            $body = $body . "Teléfono-Phone: " . $thisagen->getPhone() . "<br>";
            $body = $body .	'---------------------------------------------------------------------------------------------<br><br>';

            $body = $body . "<b>Para ingresar a nuestra aplicación WEB, entra en:</b><br>";
            $body = $body . '<a href="http://' . $_SERVER['SERVER_NAME'] . '/">' . $_SERVER['SERVER_NAME']. '</a><br>';
            $body = $body . "Usuario: " .  $customer->getEmail() .  "<br>";
            $body = $body . "Clave: " . $thepassword .  "<br><br>"; 
            $body = $body . 'Para información adicional contáctenos en nuestra web: <a href="http://' . $thisagen->getMaincompany()->getUrl() . '/"> http://'.  $thisagen->getMaincompany()->getUrl() . '</a> <br><br>';
            $translator = $this->get('translator');
            $color = $translator->trans('tailcolor');
            $body = $body . '<p style="font-size:20px; color:' . $color . ';"><b>**ESTE ES UN CORREO NO MONITOREADO, POR FAVOR NO RESPONDA AL MISMO**</b></p>';
           
           
            
            $msg = new Message();
            $msg->setSender($this->getUser());
            $msg->setReceiver($user);
            $msg->setSubject('Bienvenida');
            $msg->setBody($body);
            $msg->setCreationdate(new \DateTime());
            $msg->setIsread(FALSE);
            $em->persist($msg);
	    
            $em->flush();
            
            $message = \Swift_Message::newInstance()
                    // ->setBcc($setfrom)
                    ->setContentType("text/html")
                    //->setFrom(array($setfrom => $fromname))
                    ->setSubject('Nuevo casillero creado en '.  $thisagen->getMaincompany()->getName())
                    //->setTo($customer->getEmail())
                    ->setBody($body);
                    
            $send = 0;   
            // $mailparams = $maincompany->getMailparams();
            $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
            $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');
            
            //exit(\Doctrine\Common\Util\Debug::dump($setfrom . ' : ' . $fromname));
            
            try {
                $message->setTo($customer->getEmail());
            } catch (\Swift_RfcComplianceException $e) {
                $send = -1;
                goto out;
            }
            try {
                $message->setFrom(array($setfrom => $fromname));
            } catch (\Swift_RfcComplianceException $e) {
                $send = -2;
                goto out;
            }
            
            $send = $this->container->get('mailer')->send($message); // QUITAR COMENTARIO PARA ENVIAR EL EMAIL
            /*
            $mailparams = $maincompany->getMailparams();
            if ($mailparams) {
                $transport = \Swift_SmtpTransport::newInstance($mailparams->getHost(), $mailparams->getPort())
                    ->setUsername($mailparams->getUser())
                    ->setPassword($mailparams->getPassword())
                    ->setEncryption($mailparams->getEncryption());
                $mailer = \Swift_Mailer::newInstance($transport);
                $send = $mailer->send($message);
            } else {
                $send = $this->container->get('mailer')->send($message); // QUITAR COMENTARIO PARA ENVIAR EL EMAIL
            }
            */
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
                $msg->setSubject('Error enviando email (Creación de casillero)');
                $msg->setBody($head);
                $msg->setCreationdate(new \DateTime());
                $msg->setIsread(FALSE);
                $em->persist($msg);
        
                $em->flush();
            }
            if ($send > 0) {
                $message = 'Se ha creado el CASILLERO y se ha enviado el email al cliente: ' . $user;
            } else {
                $message = 'Se ha creado el CASILLERO; pero se produjo un error en el envío del email al cliente: ' . $user ;
            } 
            
            foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
            }
            $flashBag->add('notice',$message);
            // return $this->render('NvCargaBundle:Pobox:index.html.twig', array('message'=>$message, 'entities'=>$entities, 'agencies'=>$agencies));

            return $this->redirect($this->generateUrl('pobox'));
        }
        next:
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Creates a new Pobox entity.
     *
     * @Route("/create_public", name="pobox_create_public")
     * @Template("NvCargaBundle:Pobox:new_public.html.twig")
     */
    public function create_publicAction(Request $request)
    {
        $entity = new Pobox();
        $form = $this->createCreatePublicForm($entity);
        $em = $this->getDoctrine()->getManager();
        $form->handleRequest($request);
        $theclass = 'Pobox'; // get_class($entity);
        $thisuser = $this->getUser();
        $maincompany = $thisuser->getMaincompany();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        
        $plan=$maincompany->getPlan();
        $countpobox = $maincompany->getCountpoboxes();
        // exit(\Doctrine\Common\Util\Debug::dump($plan->getPoboxes() . ' ' . $countpobox . ' ' . $plan->getMaxpoboxes() )); 

        if (($plan->getPoboxes()) && ($countpobox >= $plan->getMaxpoboxes())) {
            $message = 'Ha llegado al número máximo de casilleros permitidos. Para crear más, su administrador debe actualizar su plan a uno superior.';
            $flashBag->add('notice',$message);
            return $this->redirect($this->generateUrl('login'));
        }
        
        $countuser = $maincompany->getCountusers();
        if (($plan->getUsers()) && ($countuser >= $plan->getMaxusers())) {
            $message = 'Ha llegado al número máximo de usuarios permitidos. Para crear más, su administrador debe actualizar su plan a uno superior.';
            $flashBag->add('notice',$message);
            return $this->redirect($this->generateUrl('login'));
        }
        
        $countcustomer = $maincompany->getCountcustomers();
        if (($plan->getCustomers()) && ($countcustomer >= $plan->getMaxcustomers())) {
            $message = 'Ha llegado al número máximo de clientes permitidos. Para crear más, su administrador debe actualizar su plan a uno superior.';
            $flashBag->add('notice',$message);
            return $this->redirect($this->generateUrl('login'));
        }
        
        $termcond = $em->getRepository("NvCargaBundle:Termcond")->findOneBy(array('maincompany'=>$maincompany, 'tableclass' => $theclass, 'active' => true));
        if ($termcond) {
            $content = $termcond->getMessage();
        } else {
            $content = null;
        }
        
        if ($form->isValid()) {
            if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
                //your site secret key
                $secret =  $this->container->getParameter('google_secret_key');
                //get verify response data
                $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
                $responseData = json_decode($verifyResponse);
                if (!$responseData->success) {
                    $message = 'La verificación de Robot ha fallado, vuelva a intentarlo';
                    $flashBag->add('notice',$message);
                    goto next;
                }
            } else {
                $message = 'Por favor, haga click en el check de reCAPTCHA';
                $flashBag->add('notice',$message);
                goto next;
            }
            $countpobox++;
            $maincompany->setCountpoboxes($countpobox);
            // $city = $form['citypobox']->getData();
            $atype = $em->getRepository('NvCargaBundle:Agencytype')->findOneByName('MASTER');
            $agency =  $em->getRepository("NvCargaBundle:Agency")->findOneBy(array('maincompany'=>$maincompany, 'type'=>$atype));//$form['agencypobox']->getData();
            $city = $agency->getCity();
            
            $warehouse = $agency->getWarehouse();
            $address=$warehouse->getAddress();

            $type = $em->getRepository('NvCargaBundle:Poboxtype')->findOneByName('PUBLICO');   
            $status = $em->getRepository('NvCargaBundle:Poboxstatus')->findOneByName('ACTIVO');
            $cusid = $form['id_customer']->getData();
            $cityid = $form['cityid_customer']->getData();
            $city_customer = null;
            if ($cityid != 0) {
                $city_customer = $em->getRepository('NvCargaBundle:City')->find($cityid);
            }
            if (!$city_customer) {
                $city_customer = $em->getRepository('NvCargaBundle:City')->findOneBy(['name'=>$form['cityname_customer']->getData(), 'state' => $form['state_customer']->getData()]);
            }
            if (!$city_customer) {
                $city_customer =  new City();
                $city_customer->setName($form['cityname_customer']->getData());
                $city_customer->setActive(false);
                $city_customer->setState($form['state_customer']->getData());
                $em->persist($city_customer);
            }
            $email = $form['email_customer']->getData();
            $user = $em->getRepository('NvCargaBundle:User')->findOneBy(['username'=>$email,'maincompany'=>$maincompany]);
            $customer = $em->getRepository('NvCargaBundle:Customer')->findOneBy(['email'=>$email,'maincompany'=>$maincompany]);
            if ($user) {
                throw $this->createNotFoundException('Ya existe un casillero con el email: ' . $email );
            }
            if ($cusid == 0 ) {  
                if ($customer) {
                    $message = 'Ya existe un cliente con el email: ' . $email . "\r\n" . 'Si desea crear un casillero con ese email, envíe un correo a '. $this->getUser()->getAgency()->getEmail();
                    throw $this->createNotFoundException($message);
                } else {// crear el cliente 
                    $countcustomer++;
                    $maincompany->setCountcustomers($countcustomer);
                    $customer= new Customer();
                    $customer->setName($form['name_customer']->getData());
                    $customer->setLastname($form['lastname_customer']->getData());
                    $customer->setEmail($form['email_customer']->getData());
                    $customer->setCreationdate(new \DateTime());
                    // $ctype = $em->getRepository('NvCargaBundle:Customertype')->findOneBy(array('name' =>'NORMAL'));
                    $ctype = $form['type']->getData();
                    $cstatus = $em->getRepository('NvCargaBundle:Customerstatus')->findOneBy(array('name' =>'ACTIVO'));
                    $customer->setType($ctype);
                    $customer->setStatus($cstatus);
                    $customer->setAgency($agency);

                    $baddr = new Baddress();
                    //$baddr->setDocid($form['docid_customer']->getData());
                    $baddr->setAddress($form['address_customer']->getData());
                    $baddr->setCity($city_customer);
                    $baddr->setPhone($form['phone_customer']->getData());
                    $baddr->setMobile($form['mobile_customer']->getData());
                    // $baddr->setBarrio($form['barrio_customer']->getData());
                    $baddr->setZip($form['zip_customer']->getData());
                    $baddr->setName($form['name_customer']->getData());
                    $baddr->setLastname($form['lastname_customer']->getData());
                    $baddr->setCustomer($customer);
                    $customer->setAdrdefault($baddr);
                    $customer->setAdrmain($baddr);
                    $customer->addBaddress($baddr);

                    $em->persist($baddr);
                    $customer->setMaincompany($maincompany);
                    $em->persist($customer);
                }
            } else { // el cliente debería existir
                $customer = $em->getRepository('NvCargaBundle:Customer')->findOneBy(['id'=>$cusid,'maincompany'=>$maincompany]);
                if (!$customer) { // No debería producirse este error
                    throw $this->createNotFoundException('El cliente no existe' );
                }
                /*
                $docid = $customer->getDocid();
                if (!$docid) {
                    $customer->setDocid($form['docid_customer']->getData());
                }
                */
            }
	    
            $thisagen = $warehouse->getAgency();
            if ($thisagen->getParent()) {
                $ccsend = $thisagen->getParent()->getEmail();
            } else {
                $ccsend = $thisagen->getEmail();
            }
            
            $numini= $maincompany->getIninum();
            $highest_id =  $em->getRepository('NvCargaBundle:Pobox')->createQueryBuilder('r')
                            ->select('MAX(r.id)')
                            ->where('r.maincompany =:thecompany')
                            ->setParameters(['thecompany'=>$maincompany])
                            ->getQuery()
                            ->getSingleScalarResult();
            if ($highest_id) {
                $poboxhigh =  $em->getRepository('NvCargaBundle:Pobox')->find($highest_id);
                $numberhigh = $poboxhigh->getNumber();
                $maxvalue = filter_var($numberhigh, FILTER_SANITIZE_NUMBER_INT);
                $maxvalue = str_replace('-', '', $maxvalue);
                $maxnumber = abs(intval($maxvalue));  
            } else {
                $maxnumber = 0;
            }
            $maxnumber++;
            if ($maxnumber < $numini) {
                $maxnumber = $numini;
            }
            
            $countpobox = $maincompany->getCountpoboxes();
            $countpobox++;
            $maincompany->setCountpoboxes($countpobox);
            
            $number = $maincompany->getPrefixpobox()  . $maxnumber;
            
            $entity->setNumber($number);    
            $entity->setNumber($number);
            $entity->setCustomer($customer);
            $entity->setCreationdate(new \DateTime());
            $entity->setWarehouse($warehouse);
            $entity->setType($type);
            $entity->setStatus($status);
            $entity->setCreateby($thisuser);
            
            $countuser++;
            $maincompany->setCountusers($countuser);
            $user = new User();
            $user->setUsername($customer->getEmail());
            $user->setEmail($customer->getEmail());
            $user->setName($customer->getName());
            $user->setLastname($customer->getLastname());
            $user->setAgency($entity->getWarehouse()->getAgency());
            $user->setPassword($entity->getPassword());
            $user->setSalt(md5(time()));
     	    $encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', true, 10);
            $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
            $user->setPassword($password);
            $user->setCreationdate(new \DateTime());
            $nstatus = 'ACTIVO';
            $status = $em->getRepository('NvCargaBundle:Userstatus')->findOneByName($nstatus);
            if (!$status) {
                throw $this->createNotFoundException('El status de usuario' . $nstatus . 'no existe');
            }
            $user->setStatus($status);	
            $nrole = 'ROLE_USER';
            $role = $em->getRepository('NvCargaBundle:Role')->findOneBy(array('name' => $nrole));
            if (!$role) {
                throw $this->createNotFoundException('El rol' . $nrole . 'no existe');
            }
            $user->addUserRole($role);
            $customer->setPobox($entity);
            $entity->setUser($user);
            $user->setPobox($entity);
			$entity->setMaincompany($maincompany);
			$user->setMaincompany($maincompany);
            $em->persist($entity);
            $em->persist($user);
	  
            $pobox = $entity;
            $nameto =  ''; 
            if ($thisagen->getMaincompany()->getCompanyname()) {
                $nameto = $nameto . $thisagen->getMaincompany()->getName();
            }
            if ($thisagen->getMaincompany()->getCustomername()) {
                $nameto = $nameto . $pobox->getCustomer();
            }
            if ($thisagen->getMaincompany()->getNumbername()) {
                $nameto = $nameto . $pobox->getNumber();
            }
            if ($nameto == '') {
                $nameto = $pobox->getCustomer();
            }
            $body = '<p align="right">Ref: ' . $pobox->getNumber() .'</p><br>';
            $body = $body . "--Bienvenid@ a " . $thisagen->getMaincompany()->getName() . '<b> ' . $pobox->getCustomer() . '</b><br><br>';
            $body = $body .  $thisagen->getMaincompany()->getPoboxmsg() . '<br><br>';
            $body = $body .  '<b>*ENVÍA TUS COMPRAS, CON TU PROVEEDOR FAVORITO, SELECCIONANDO LA DIRECCIÓN*</b><br>';
            $body = $body .	'---------------------------------------------------------------------------------------------<br>';
            $body = $body . "Nombre-Name: " . $nameto . "<br>";
	    	$body = $body . "Dirección-Address: " . $pobox->getWarehouse()->getAddress(). "<br>";
	    	$body = $body . "Ciudad-City: " .  $pobox->getWarehouse()->getCity() . "<br>";
            $body = $body . "Estado-State: " .  $pobox->getWarehouse()->getCity()->getState() .  "<br>";
	    	$body = $body . "Country: " .  $pobox->getWarehouse()->getCity()->getState()->getCountry() . "<br>";
            $body = $body . "Código Postal-Zip Code: " . $warehouse->getZip(). "<br>";
	    	$body = $body . "Teléfono-Phone: " . $thisagen->getPhone() . "<br>";
            $body = $body .	'---------------------------------------------------------------------------------------------<br><br>';

	    	$body = $body . "<b>Para ingresar a nuestra aplicación WEB, entra en:</b><br>";
            $body = $body . '<a href="http://' . $_SERVER['SERVER_NAME'] . '/">' . $_SERVER['SERVER_NAME']. '</a><br>';
	    	$body = $body . "Usuario: " .  $customer->getEmail() .  "<br>";
            $body = $body . "Clave: <b>*LA QUE USTED HAYA SELECCIONADO*</b> <br><br>";
	    	$body = $body . 'Para información adicional contáctenos en nuestra web: <a href="http://' . $thisagen->getMaincompany()->getUrl() . '/"> http://'.  $thisagen->getMaincompany()->getUrl() . '</a> <br><br>';
            $translator = $this->get('translator');
            $color = $translator->trans('tailcolor');
            $body = $body . '<p style="font-size:20px; color:' . $color . ';"><b>**ESTE ES UN CORREO NO MONITOREADO, POR FAVOR NO RESPONDA AL MISMO**</b></p>';
            
            $msg = new Message();
            $msg->setSender($user);
            $msg->setReceiver($user);
            $msg->setSubject('Bienvenid@ al uso de Casilleros');
            $msg->setBody($body);
            $msg->setCreationdate(new \DateTime());
            $msg->setIsread(FALSE);
            $em->persist($msg);
            
            $em->flush();
            
            $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
            $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');
            
            
            $message = \Swift_Message::newInstance()
                    // ->setBcc($setfrom)
                    ->setContentType("text/html")
                    //->setFrom(array($setfrom => $fromname))
                    ->setSubject('Nuevo casillero creado')
                    //->setTo($customer->getEmail())
                    ->setBody($body);
            $send=0;
            try {
                $message->setTo($customer->getEmail());
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
                $msg->setSubject('Error enviando email (Creación de casillero)');
                $msg->setBody($head);
                $msg->setCreationdate(new \DateTime());
                $msg->setIsread(FALSE);
                $em->persist($msg);
        
                $em->flush();
            }
    
            $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_secured_area', serialize($token));  
    
            return $this->redirect($this->generateUrl('pobox_menu', array('id' => $entity->getId())));
        }
        
        next:
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'content' => $content,
            'fondo' => $maincompany->getBackground(),
            'logomain' => $maincompany->getLogomain(),
            'header' => $maincompany->getPoboxheader(),
        );
    }
    /**
     * Creates a form to create a Pobox entity.
     *
     * @param Pobox $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Pobox $entity)
    {
        $form = $this->createForm(new PoboxType($this->getDoctrine()->getManager(), $this->getUser()->getMaincompany()), $entity, array(
            'action' => $this->generateUrl('pobox_create'),
            'method' => 'POST',
        ));
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        
        $agencies = $em->getRepository('NvCargaBundle:Agency')->findBy(array('poboxs'=>true,'maincompany'=>$maincompany));

        $choiceList = new ObjectChoiceList($agencies, null, array(), null, 'id'); 

        $form->add('agencypobox', 'choice', array('label' => 'Agencia del casillero ',
                    'choice_list'   => $choiceList,
                    'multiple'  => false,
                    'mapped' => false, ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }
    /**
     * Creates a form to create a Pobox entity.
     *
     * @param Pobox $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreatePublicForm(Pobox $entity)
    {
        $form = $this->createForm(new PoboxType($this->getDoctrine()->getManager(), $this->getUser()->getMaincompany()), $entity, array(
            'action' => $this->generateUrl('pobox_create_public'),
            'method' => 'POST',
        ));
        $em = $this->getDoctrine()->getManager();
        
        $agencies = $em->getRepository('NvCargaBundle:Agency')->findBy(['maincompany'=>$this->getUser()->getMaincompany(), 'poboxs'=>true]);

        $choiceList = new ObjectChoiceList($agencies, null, array(), null, 'id'); 

        /*
        $form->add('agencypobox', 'choice', array('label' => 'Agencia del casillero ',
                    'choice_list'   => $choiceList,
                    'multiple'  => false,
                    'mapped' => false, ));
        */
        $form->add('submit', 'submit', array('label' => 'Crear Cuenta'));

        return $form;
    }
    /**
     * Displays a form to create a new Pobox entity.
     *
     * @Route("/new", name="pobox_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_POBOX') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Pobox();
        $user=$this->getUser();
        $maincompany = $user->getMaincompany();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $plan = $maincompany->getPlan(); 
        /*
        if ($plan->getPoboxes()) {
            throw $this->createNotFoundException('La empresa NO TIENE MANEJO de CASILLEROS.');
        }
        */
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $countpobox = $maincompany->getCountpoboxes();
        $plan = $maincompany->getPlan();
        if (($plan->getPoboxes()) && ($countpobox >= $plan->getMaxpoboxes())) {
            $message = 'Ha llegado al número máximo de CASILLEROS permitidos. Para crear más debe actualizar su plan a uno superior.';
            if ($this->isGranted('ROLE_ADMIN')) {
                $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
            }
            $flashBag->add('notice',$message);
            // throw $this->createNotFoundException('La empresa ha alcanzado el número MÁXIMO de CASILLEROS permitidos.' );
        }
        $form   = $this->createCreateForm($entity);
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * Displays a form to create a new Pobox entity.
     *
     * @Route("/new_public", name="pobox_new_public")
     * @Method("GET")
     * @Template()
     */
    public function new_publicAction()
    {
        $entity = new Pobox();
        $em = $this->getDoctrine();
        $repo  = $em->getRepository("NvCargaBundle:User"); //Entity Repository
        $server = $_SERVER['SERVER_NAME'];
        //exit(\Doctrine\Common\Util\Debug::dump($server)); 
        $theurl = explode(".", $server);
        $name = $theurl[0];
        // $maincompany = $em->getRepository("NvCargaBundle:maincompany")->findOneByHomepage($server);
        $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->createQueryBuilder('m')
            ->where('m.homepage =:server OR m.homepage_aux =:server')
            ->setParameter('server', $server )
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
            
        if (!$maincompany) {
            throw $this->createNotFoundException('Error en el acceso por favor contacte al administrador ' );
        }
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $plan=$maincompany->getPlan();
        $countpobox = $maincompany->getCountpoboxes();
        // exit(\Doctrine\Common\Util\Debug::dump($plan->getPoboxes() . ' ' . $countpobox . ' ' . $plan->getMaxpoboxes() )); 

        if (($plan->getPoboxes()) && ($countpobox >= $plan->getMaxpoboxes())) {
            $message = 'Ha llegado al número máximo de casilleros permitidos. Para crear más, su administrador debe actualizar su plan a uno superior.';
            $flashBag->add('notice',$message);
            return $this->redirect($this->generateUrl('login'));
        }
        
        $countuser = $maincompany->getCountusers();
        if (($plan->getUsers()) && ($countuser >= $plan->getMaxusers())) {
            $message = 'Ha llegado al número máximo de usuarios permitidos. Para crear más, su administrador debe actualizar su plan a uno superior.';
            $flashBag->add('notice',$message);
            return $this->redirect($this->generateUrl('login'));
        }
        
        $countcustomer = $maincompany->getCountcustomers();
        if (($plan->getCustomers()) && ($countcustomer >= $plan->getMaxcustomers())) {
            $message = 'Ha llegado al número máximo de clientes permitidos. Para crear más, su administrador debe actualizar su plan a uno superior.';
            $flashBag->add('notice',$message);
            return $this->redirect($this->generateUrl('login'));
        }
        
        $username = "webuser";
        $user = $repo->findOneBy(['username'=>$username,'maincompany'=>$maincompany]);
        if (!$user) {
                throw new UsernameNotFoundException("El usuario " . $username . " no existe");
        } else {
                $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_secured_area', serialize($token));
        }
        //exit(\Doctrine\Common\Util\Debug::dump($user)); 
        $theclass = 'Pobox'; // get_class($entity);
        $termcond = $em->getRepository("NvCargaBundle:Termcond")->
                findOneBy(array('maincompany'=>$maincompany, 'tableclass' => $theclass, 'active' => true));
        if ($termcond) {
            $content = $termcond->getMessage();
        } else {
            $content = null;
        }
        $plan = $maincompany->getPlan();
        if ($plan->getMaxpoboxes() == 0) {
            throw $this->createNotFoundException('La empresa NO TIENE MANEJO de CASILLEROS.');
        }
        $form   = $this->createCreatePublicForm($entity);
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'content' => $content,
            'fondo' => $maincompany->getBackground(),
            'logomain' => $maincompany->getLogomain(),
            'header' => $maincompany->getPoboxheader(),
        );
    }
    /**
     * Finds and displays a  menu for Pobox entity.
     *
     * @Route("/{id}/menu", name="pobox_menu")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function menuAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Pobox')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe ese casillero.');
        }
        $this->denyAccessUnlessGranted('show', $entity, 'Acceso no autorizado');

        $customer = $em->getRepository('NvCargaBundle:Customer')->find($entity->getCustomer()->getId());
        if (!$customer) {
            throw $this->createNotFoundException('El casillero no tiene cliente asignado');
        }

        $cancelstatus = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Anulado');
        
        $receipts= $em->getRepository('NvCargaBundle:Receipt')->createQueryBuilder('r')
                    ->where('r.receiver IN (:receiver) OR r.shipper =:customer')
                    ->andwhere('r.status != :status')
                    ->setParameters(array('customer'=>$customer, 'receiver'=> $customer->getBaddress(), 
                            'status' => $cancelstatus))
                    ->orderBy('r.arrivedate', 'DESC')
                    ->setMaxResults(10)
                    ->setFirstResult(0)
                    ->getQuery()
                    ->getResult();
                    
        $guides= $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                    ->where('g.addressee IN (:addressee) OR g.sender =:customer')
                    ->setParameters(array('customer'=>$customer, 'addressee'=> $customer->getBaddress()))
                    ->orderBy('g.creationdate', 'DESC')
                    ->setMaxResults(10)
                    ->setFirstResult(0)
                    ->getQuery()
                    ->getResult();
                    
        $bills = $em->getRepository('NvCargaBundle:Bill')->createQueryBuilder('b')
                    ->where('b.customer =:customer')
                    ->andWhere('b.status IN (:status)')
                    ->orderBy('b.creationdate', 'DESC')
                    ->setParameters(array('customer'=>$customer, 'status'=>['PENDIENTE','COBRADA']))
                    ->setMaxResults(10)
                    ->setFirstResult(0)
                    ->getQuery()
                    ->getResult();
                    
        $payments = $em->getRepository('NvCargaBundle:Billpay')->createQueryBuilder('p')
                    ->where('p.bill IN (:bills)')
                    ->setParameters(array('bills'=>$bills))
                    ->orderBy('p.paydate', 'DESC')
                    ->setMaxResults(10)
                    ->setFirstResult(0)
                    ->getQuery()
                    ->getResult();     
                    
        return array(
            'customer' => $customer,
            'entity'      => $entity,
            'bills' => $bills,
            'guides' => $guides,
            'receipts' => $receipts,
            'payments' => $payments,
        );
    }

    /**
     * Finds and displays a Pobox entity.
     *
     * @Route("/{id}/show", name="pobox_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Pobox')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe ese casillero.');
        }

        $deleteForm = $this->createDeleteForm($id);
	
		
        $this->denyAccessUnlessGranted('show', $entity, 'Acceso no autorizado');
	
        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Pobox entity.
     *
     * @Route("/{id}/delete", name="pobox_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_POBOX') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Pobox')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Pobox entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('pobox'));
    }

    /**
     * Creates a form to delete a Pobox entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('pobox_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
    /**
     * show a list of POBOX 
     *
     * @Route("/list", name="pobox_list")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_POBOX') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_POBOX')")
     */
    public function listAction(Request $request)
    {
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $countryto = $user->getAgency()->getCity()->getstate()->getCountry();
        if (! $request->isXmlHttpRequest()) {
                throw new NotFoundHttpException();
        }
        $pobox = $request->query->get('pobox_number');
        $em = $this->getDoctrine()->getManager();
        if ($pobox)  {
            $all = $em->getRepository('NvCargaBundle:Pobox')->createQueryBuilder('o')
                ->where('o.number LIKE :number')
                ->andWhere('o.maincompany = :thecompany')
                ->setParameters(['number'=>'%'.$pobox.'%', 'thecompany'=>$maincompany])
                ->getQuery()
                ->getResult();
        }
        if ($user->getAgency()->getType() != 'MASTER') {
            $entities = array();
            foreach ($all as $thepobox) {
                if ($thepobox->getCustomer()->getAdrdefault()->getCity()->getState()->getCountry() == $countryto) {
                    $entities[] = $thepobox;
                }
            }
        } else {
            $entities = $all;
        }
        $result=array();
        $counter = 0;
        foreach ($entities as $entity) {
            $result[$counter]['poboxid'] = $entity->getId();
            $result[$counter]['number'] = $entity->getNumber();
            $result[$counter]['customer'] = strval($entity->getCustomer());
            $result[$counter]['warehouse'] = strval($entity->getWarehouse());	
            $counter++;
        }
        return new JsonResponse($result); 
    }
    /**
     * Creates a form to search Pobox
     *
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm()
    {
	$formBuilder = $this->get('form.factory')->createNamedBuilder('pobox_type', 'form',  array())
           
	    ->add('number', 'number', array('label' => 'Número de casillero', 'attr' => array('style' => 'width: 10em')))
	    ->add('namecustomer', 'text', array('label' => 'Nombre del cliente'))
	    ->add('lastnamecustomer', 'text', array('label' => 'Apellido del cliente'))
	    ->add('email','text', array('label' => 'Email del cliente'))
	    ->add('search', 'button', array('label' => 'Buscar'))
            ->getForm()
        ;
	return $formBuilder;
    }
    /**
     * Search POBOX.
     *
     * @Route("/search", name="pobox_search")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_POBOX') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_POBOX')")
     */
    public function searchAction()
    {
        $searchForm = $this->createSearchForm();

        return array(
	    'form' => $searchForm->createView(),
        );
    }
    /**
     * find a list of POBOX 
     *
     * @Route("/find", name="pobox_find")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_POBOX') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_POBOX')")
     */
    public function findAction(Request $request)
    {
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        $name = $request->query->get('name');
        $lastname = $request->query->get('lastname');
        $email = $request->query->get('email');
        $number = $request->query->get('number');

        $em = $this->getDoctrine()->getManager();
        $liscus=array();
        if ($name) {
            $customer = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('o')
                ->where('o.name LIKE :name')
                ->andWhere('o.maincompany = :thecompany')
                ->setParameters(['name'=>'%'.$name.'%', 'thecompany'=>$maincompany])
                ->getQuery()
                ->getResult();
            $c=0;
            $list1=array();
            foreach ($customer as $cus) {
                $list1[$c++]=$cus->getId();
            }
            $liscus = $list1;
        }
	
        if ($lastname) {
            $customer = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('o')
                ->where('o.lastname LIKE :lastname')
                ->andWhere('o.maincompany = :thecompany')
                ->setParameters(['lastname'=>'%'.$lastname.'%', 'thecompany'=>$maincompany])
                ->getQuery()
                ->getResult();
            $c=0;
            $list2=array();
            foreach ($customer as $cus) {
                $list2[$c++]=$cus->getId();
            }
            if ($name) {
                $liscus = array_intersect($liscus, $list2);
            } else {
                $liscus = $list2;
            }
        }
		
        if ($email) {
            $customer = $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('o')
                ->where('o.email LIKE :email')
                ->andWhere('o.maincompany = :thecompany')
                ->setParameters(['email'=>'%'.$email.'%', 'thecompany'=>$maincompany])
                ->getQuery()
                ->getResult();
            $c=0;
            $list3=array();
            foreach ($customer as $cus) {
                $list3[$c++]=$cus->getId();
            }
            if (($name) || ($lastname)) {
                $liscus = array_intersect($liscus, $list3);
            } else {
                $liscus = $list3;
            }
        }
        $entities1 = $em->getRepository('NvCargaBundle:Pobox')->findByCustomer($liscus);
        $c=0;
        $lispobox = array();
        foreach ($entities1 as $pox) {
            $lispobox[$c++]=$pox->getId();
        }
        $entities2= array();
        if ($number)  {
            $entities2 = $em->getRepository('NvCargaBundle:Pobox')->createQueryBuilder('o')
                ->where('o.number LIKE :number')
                ->andWhere('o.maincompany = :thecompany')
                ->setParameters(['number'=>'%'.$number.'%', 'thecompany'=>$maincompany])
                ->getQuery()
                ->getResult();
            $lispo1 = array();
            $c=0;
            foreach ($entities2 as $pox) {
                $lispo1[$c++]=$pox->getId();
            }
            if (($name) || ($lastname) || ($email)) {
                $lispobox = array_intersect($lispobox, $lispo1);
            } else {
                $lispobox=$lispo1;
            }
            
        }
        $user = $this->getUser();
        $countryto = $user->getAgency()->getCity()->getstate()->getCountry();
        $all = $em->getRepository('NvCargaBundle:Pobox')->findById($lispobox);
        if ($user->getAgency()->getType() != 'MASTER') {
                $entities = array();
            foreach ($all as $thepobox) {
                if ($thepobox->getCustomer()->getAdrdefault()->getCity()->getState()->getCountry() == $countryto) {
                    $entities[] = $thepobox;
                }
            }
        } else {
            $entities = $all;
        }
        $result=array();
        $counter = 0;
        foreach ($entities as $entity) {
            $result[$counter]['poboxid'] = $entity->getId();
            $result[$counter]['number'] = $entity->getNumber();
            $result[$counter]['customer'] = strval($entity->getCustomer());
            $result[$counter]['email'] = $entity->getCustomer()->getEmail();
            $result[$counter]['warehouse'] = strval($entity->getWarehouse());	
            $counter++;
        }
        return new JsonResponse($result); 
    }
    
    private function createSearchGuideForm($number)
    {
        $formBuilder = $this->get('form.factory')->createNamedBuilder('search_guide', 'form', array())
        ->add('number', 'text', array('label' => 'labels.guidenumber', 'required' => true, 'data' => $number))
        ->add('search', 'button', array('label' => 'Buscar'));
        $form = $formBuilder->getForm();
        return $form;
    }
    /**
     *
     * @Route("/tracking", name="guide_tracking")
     * @Method("GET")
     * @Template("NvCargaBundle:Tracking:tracking.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function trackingAction()
    {
        $number = null;
        $form   = $this->createSearchGuideForm($number);

        return array(
            'form'   => $form->createView(),
        );
    }
    /**
     * show a guide of specific customer 
     *
     * @Route("/poboxfind", name="guide_poboxfind")
     * @Template("NvCargaBundle:Tracking:find.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function poboxfindAction(Request $request)
    {
        $number = $request->query->get('guidenumber');
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['number'=>$number,'maincompany'=>$maincompany]); //AGREGAR el campo para no mostrar despues de un tiempo
        $status = $em->getRepository('NvCargaBundle:Guidestatus')->findAll();
        // exit(\Doctrine\Common\Util\Debug::dump($guide)); 
        $form   = $this->createSearchGuideForm($number);
	
        return array(
            'entity' => $entity,
            'status' => $status,
            'form'   => $form->createView(),
	    
        ); 
	
    }
    /**
     * Creates a form to create a Pobox entity.
     *
     * @param Pobox $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateDataForm(Pobox $entity)
    {
        $form = $this->createForm(new PoboxType($this->getDoctrine()->getManager(), $this->getUser()->getMaincompany()), $entity, array(
            'action' => $this->generateUrl('pobox_create_data', array('id' => $entity->getId())),
            'method' => 'POST',
        ));
        $em = $this->getDoctrine()->getManager();
        
        $agencies = $em->getRepository('NvCargaBundle:Agency')->findBy(array('poboxs'=>true,'maincompany'=>$this->getUser()->getMaincompany()));

        $choiceList = new ObjectChoiceList($agencies, null, array(), null, 'id'); 

        $form->add('agencypobox', 'choice', array('label' => 'Agencia del casillero ',
                    'choice_list'   => $choiceList,
                    'multiple'  => false,
                    'mapped' => false, 
                    'data'=> $entity->getWarehouse()->getAgency()));

        $form->add('submit', 'submit', array('label' => 'Guardar'));
        
        $form->remove('email_customer');
        $form->remove('password');
        $form->remove('id_customer');
        $form->remove('name_customer');
        $form->remove('lastname_customer');
        $form->remove('type');
        return $form;
    }
    /**
     * Displays a form to create a new Pobox entity.
     *
     * @Route("/data/{id}", name="pobox_data")
     * @Method("GET")
     * @Template("NvCargaBundle:Pobox:data.html.twig")
     */
    public function dataAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $pobox =  $em->getRepository("NvCargaBundle:Pobox")->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);
        if (!$pobox) {
            throw $this->createNotFoundException('No exite el Casillero.' );
        }
        if ($user != $pobox->getUser()) {
            throw $this->createNotFoundException('Esta acción solo es permitida para el cliente del Casillero' );
        }
        if ($pobox->getCustomer()->getAdrdefault()->getCity() != null) {
            return $this->redirect($this->generateUrl('pobox_menu', array('id' => $id)));
        }
        
        $form   = $this->createCreateDataForm($pobox);
        
        $theclass = 'Pobox'; // get_class($entity);
        $termcond = $em->getRepository("NvCargaBundle:Termcond")->
                findOneBy(array('maincompany'=>$maincompany, 'tableclass' => $theclass, 'active' => true));
                
        if ($termcond) {
            $content = $termcond->getMessage();
        } else {
            $content = null;
        }
        
        return array(
            'entity' => $pobox,
            'form'   => $form->createView(),
            'content' => $content,
        );
    }
       /**
     * Displays a form to create a new Pobox entity.
     *
     * @Route("/createdata/{id}", name="pobox_create_data")
     * @Template("NvCargaBundle:Pobox:data.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function create_dataAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $pobox =  $em->getRepository("NvCargaBundle:Pobox")->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);
        if (!$pobox) {
            throw $this->createNotFoundException('No exite el Casillero.' );
        }
        if ($user != $pobox->getUser()) {
            throw $this->createNotFoundException('Esta acción solo es permitida para el cliente del Casillero' );
        }
        // exit(\Doctrine\Common\Util\Debug::dump($pobox->getCustomer()->getAdrdefault()->getCity())); 
        if ($pobox->getCustomer()->getAdrdefault()->getCity()) {
            return $this->redirect($this->generateUrl('pobox_menu', array('id' => $id)));
        }
        
        $form   = $this->createCreateDataForm($pobox);
        
        $theclass = 'Pobox'; // get_class($entity);
        $termcond = $em->getRepository("NvCargaBundle:Termcond")->
                findOneBy(array('maincompany'=>$maincompany, 'tableclass' => $theclass, 'active' => true));
                
        if ($termcond) {
            $content = $termcond->getMessage();
        } else {
            $content = null;
        }
        $form->handleRequest($request);
        if ($form->isValid()) {
            $agency =  $form['agencypobox']->getData();
            $warehouse = $agency->getWarehouse();
            $address=$warehouse->getAddress();
            $city=$agency->getCity();
            $pobox->setWarehouse($warehouse);
            $cityid = $form['cityid_customer']->getData();
            $city_customer = $em->getRepository('NvCargaBundle:City')->find($cityid);
            $baddr = $pobox->getCustomer()->getAdrdefault();
            $baddr->setCity($city_customer);
            $baddr->setDocid($form['docid_customer']->getData());
            $baddr->setAddress($form['address_customer']->getData());
            $baddr->setCity($city_customer);
            $baddr->setPhone($form['phone_customer']->getData());
            $baddr->setMobile($form['mobile_customer']->getData());
            // $baddr->setBarrio($form['barrio_customer']->getData());
            $baddr->setZip($form['zip_customer']->getData());
            
            $nameto =  ''; 
            if ($maincompany->getCompanyname()) {
                $nameto = $nameto . $maincompany->getName();
            }
            if ($maincompany->getCustomername()) {
                $nameto = $nameto . $pobox->getCustomer();
            }
            if ($maincompany->getNumbername()) {
                $nameto = $nameto . $pobox->getNumber();
            }
            if ($nameto == '') {
                $nameto = $pobox->getCustomer();
            }
            $body = '<p align="right">Ref: ' . $pobox->getNumber() .'</p><br>';
            $body = $body . "--Bienvenid@ a " . $maincompany->getName() . '<b> ' . $pobox->getCustomer() . '</b><br><br>';
            $body = $body .  $maincompany->getPoboxmsg() . '<br><br>';
            $body = $body .  '<b>*ENVÍA TUS COMPRAS, CON TU PROVEEDOR FAVORITO, SELECCIONANDO LA DIRECCIÓN*</b><br>';
            $body = $body .	'---------------------------------------------------------------------------------------------<br>';
            $body = $body . "Nombre-Name: " . $nameto . "<br>";
	    	$body = $body . "Dirección-Address: " . $pobox->getWarehouse()->getAddress(). "<br>";
	    	$body = $body . "Ciudad-City: " .  $pobox->getWarehouse()->getCity() . "<br>";
            $body = $body . "Estado-State: " .  $pobox->getWarehouse()->getCity()->getState() .  "<br>";
	    	$body = $body . "Country: " .  $pobox->getWarehouse()->getCity()->getState()->getCountry() . "<br>";
            $body = $body . "Código Postal-Zip Code: " . $warehouse->getZip(). "<br>";
	    	$body = $body . "Teléfono-Phone: " . $agency->getPhone() . "<br>";
            $body = $body .	'---------------------------------------------------------------------------------------------<br><br>';

	    	$body = $body . "<b>Para ingresar a nuestra aplicación WEB, entra en:</b><br>";
            $body = $body . '<a href="http://' . $_SERVER['SERVER_NAME'] . '/">' . $_SERVER['SERVER_NAME']. '</a><br><br>';
	    	// $body = $body . "Usuario: " .  $customer->getEmail() .  "<br>";
            // $body = $body . "Clave: <b>*LA QUE USTED HAYA SELECCIONADO*</b> <br><br>";
	    	$body = $body . 'Para información adicional contáctenos en nuestra web: <a href="http://' . $maincompany->getUrl() . '/"> http://'.  $maincompany->getUrl() . '</a> <br><br>';
            $translator = $this->get('translator');
            $color = $translator->trans('tailcolor');
            $body = $body . '<p style="font-size:20px; color:' . $color . ';"><b>**ESTE ES UN CORREO NO MONITOREADO, POR FAVOR NO RESPONDA AL MISMO**</b></p>';
            
            $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
            $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');
            $message = \Swift_Message::newInstance()
                    // ->setBcc($setfrom)
                    ->setContentType("text/html")
                    //->setFrom(array($setfrom => $fromname))
                    ->setSubject('Nuevo casillero creado')
                    //->setTo($pobox->getCustomer()->getEmail())
                    ->setBody($body);
            $send = 0;
            try {
                $message->setTo($pobox->getCustomer()->getEmail());
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
                $msg->setSubject('Error enviando email (Creación de casillero)');
                $msg->setBody($head);
                $msg->setCreationdate(new \DateTime());
                $msg->setIsread(FALSE);
                $em->persist($msg);
            }

            $msg = new Message();
            $msg->setSender($user);
            $msg->setReceiver($user);
            $msg->setSubject('Bienvenid@ al uso de Casilleros');
            $msg->setBody($body);
            $msg->setCreationdate(new \DateTime());
            $msg->setIsread(FALSE);
            $em->persist($msg);
            
            $em->flush();

            return $this->redirect($this->generateUrl('pobox_menu', array('id' => $pobox->getId())));
	  
        } /*else {
            exit(\Doctrine\Common\Util\Debug::dump('La forma no es válida')); 
        }*/
        
        
        return array(
            'entity' => $pobox,
            'form'   => $form->createView(),
            'content' => $content,
        );
    }
}
