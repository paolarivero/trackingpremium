<?php
// src/NvCarga/Bundle/Controller/SecurityController.php
namespace NvCarga\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

use Symfony\Component\Security\Core\Util\SecureRandom;
use NvCarga\Bundle\Entity\User;

// For login a user programatically 
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Security controller.
 *
 * @Route("/")
 */
class SecurityController extends Controller
{
    /**
     * Definimos las rutas para el register_company:
     * @Route("/logincompany/{tokenid}", name="logincompany")
     * @Template()
     */
    public function logincompanyAction($tokenid)
    {
        $server = $_SERVER['SERVER_NAME'];
        $em = $this->getDoctrine()->getManager();
        // $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage);
        $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->createQueryBuilder('m')
            ->where('m.homepage =:server OR m.homepage_aux =:server')
            ->setParameter('server', $server )
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        
        $admin = $em->getRepository('NvCargaBundle:User')->findOneBy(['authId'=>$tokenid, 'maincompany'=>$maincompany]);
        // exit(\Doctrine\Common\Util\Debug::dump($admin));
        if ($admin) {
            $admin->setAuthId(null);
            $em->flush();
            $token = new UsernamePasswordToken($admin, null, 'secured_area', $admin->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_secured_area', serialize($token));
        }
        return $this->redirect($this->generateUrl('homepage'));
    }
    /**
     * @Route("/menulogin/{tokenid}/{uid}", name="menulogin")
     * @Template()
     */
    public function menuloginAction($tokenid, $uid)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->find(1);
        $user = $em->getRepository('NvCargaBundle:User')->find($uid);
        // exit(\Doctrine\Common\Util\Debug::dump($user->getId()));
        if (($user)  && ($tokenid == $maincompany->getToken())) {
            $maincompany->setToken('');
            $em->flush();
            $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_secured_area', serialize($token));
        }
        return $this->redirect($this->generateUrl('subscriber_updateplan'));
    }
    
    /**
     * Creates a form to create a email entity.
     *
     * @param $data The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
   private function createRegisterForm()
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('register_company_check'))
            ->setMethod('POST')
            ->add('email', 'email', array('label'=> false, 'mapped'=>false, 
                        'attr' =>array('placeholder'=>'Correo electrónico'),
						'constraints' => array(
							new NotBlank(["message" => "Debe escribir su email"]),
							new Email(["message" => "El email '{{ value }}' no es válido.", 
								"checkMX" => true, "checkHost" => true]))))
            ->add('submit', 'submit', array('label' => 'Crear cuenta',))
            ->add('name', 'text', array('label'=> false, 'mapped'=>false, 
                        'attr' =>array('placeholder'=>'Nombre'),
						'constraints' => array(
							new NotBlank(["message" => "Debe escribir su nombre"]),
							new Length(["min" => 2, "max" => 30,
                            "minMessage" => "El nombre debe tener al menos {{ limit }} caracteres",
                            "maxMessage" => "El nombre no puede tener mas de {{ limit }} caracteres"]))))
            ->add('lastname', 'text', array('label'=> false, 'mapped'=>false, 
                        'attr' =>array('placeholder'=>'Apellido'),
						'constraints' => array(
							new NotBlank(["message" => "Debe escribir su Apellido"]),
							new Length(["min" => 2, "max" => 30,
                            "minMessage" => "El Apellido debe tener al menos {{ limit }} caracteres",
                            "maxMessage" => "El Apellido no puede tener mas de {{ limit }} caracteres"]))))
            ->add('password', 'repeated', array('type' => 'password', 
                    'constraints' => array(new NotBlank(["message" => "Debe colocar un password"])),
                    'first_options'  => array('attr' =>array('placeholder'=>'Password')),
                    'second_options' => array('attr' =>array('placeholder'=>'Confirme password')), 
                    'invalid_message' => 'Los password deben ser iguales'))
            ->add('submit', 'submit', array('label' => 'Crear cuenta', 'attr' => array('class' => 'btn btn-block btn-primary btn-rad btn-lg')))
            ->getForm();

        return $form;
    }
    /**
     * Definimos las rutas para el register_company:
     * @Route("/register_company", name="register_company")
     * @Template()
     */
    public function register_companyAction(Request $request)
    {
        //$request = $this->getRequest();
        //$session = $request->getSession();
        $server = $_SERVER['SERVER_NAME'];
        $em = $this->getDoctrine()->getManager();
        // $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage);
        $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->createQueryBuilder('m')
            ->where('m.homepage =:server OR m.homepage_aux =:server')
            ->setParameter('server', $server )
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        // exit(\Doctrine\Common\Util\Debug::dump($maincompany->getName()));
        $form = $this->createRegisterForm();

        $termcond = $em->getRepository("NvCargaBundle:Termcond")->findOneBy(array('maincompany'=>$maincompany, 'tableclass' => 'Subscriber', 'active' => true));
        if ($termcond) {
            $content = $termcond->getMessage();
        } else {
            $content = null;
        }
        return array(
            'form'   => $form->createView(),
            'fondo' => $maincompany->getBackground(),
            'logomain' => $maincompany->getLogomain(),
            'content' =>$content,
        );
    }
    /**
     * Definimos las rutas para el register_company:
     * @Route("/register_company_check", name="register_company_check")
     * @Template("NvCargaBundle:Security:register_company.html.twig")
     */
    public function register_company_checkAction(Request $request)
    {
        //$request = $this->getRequest();
        //$session = $request->getSession();
        $form = $this->createRegisterForm();
        $server = $_SERVER['SERVER_NAME'];
        $em = $this->getDoctrine()->getManager();
        // $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage);
        $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->createQueryBuilder('m')
            ->where('m.homepage =:server OR m.homepage_aux =:server')
            ->setParameter('server', $server )
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        $form->handleRequest($request);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        
        if ($form->isSubmitted() && $form->isValid()) {
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
            // data is an array with "name", "email", and "message" keys
            // $data = $form->getData();
            $email = $form['email']->getData();
            $em = $this->getDoctrine()->getManager();
            $usermail = $em->getRepository('NvCargaBundle:User')->findOneBy(['maincompany'=>$maincompany, 'username'=>$email, 'type'=>'EMPRESA']);
            $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->find(1);
            $master = $em->getRepository('NvCargaBundle:Agencytype')->findOneByName('Master');
            $agency = $em->getRepository('NvCargaBundle:Agency')->findOneBy(['maincompany'=>$maincompany,'type'=>$master]);
            if ($usermail) {
                $this->get('session')->getFlashBag()->add(
                            'notice',
                            'Ya tenemos un cliente registro con el EMAIL: ' . $email);
                    goto next;
            }
            $user = new User();
            $user->setType('EMPRESA');
            $user->setMaincompany($maincompany);
            $user->setAgency($agency);
            $user->setEmail($email);
            $user->setUsername($email);
            $user->setPassword($form['password']->getData());
            $user->setName($form['name']->getData());
            $user->setLastname($form['lastname']->getData());
            $this->setSecurePassword($user);
            $user->setCreationdate(new \DateTime());
            $nstatus = 'ACTIVO';
            $status = $em->getRepository('NvCargaBundle:Userstatus')->findOneByName($nstatus);
            if (!$status) {
                $message = 'El status de usuario' . $nstatus . 'no existe';
                $this->get('session')->getFlashBag()->add(
                                    'notice',
                            $message);
                goto next;
                
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
                
            }
            $user->addUserRole($role);
            // PRUEBAS INCIALES CON STRIPE
            
            $stripe_mode = $this->container->getParameter('stripe_mode');
            $stripe_public_key = $this->container->getParameter('stripe_' . $stripe_mode . '_public_key');
            $stripe_secret_key = $this->container->getParameter('stripe_' . $stripe_mode . '_secret_key');
            
            \Stripe\Stripe::setApiKey($stripe_secret_key);
            
            try {
                $customer = \Stripe\Customer::create(array(
                    'description' => $user->getName() . ' ' . $user->getLastname(), 
                    'email' => $user->getEmail(),
                ));
            } catch(Exception $e) {
                $message = 'No ha sido posible agregar el cliente ' . $user->getEmail() . ' en STRIPE. ' .  'Error:' . $e->getMessage();
                $this->get('session')->getFlashBag()->add(
                                    'notice',
                            $message);
                goto next;
            }
            
            // FIN DE PRUEBAS
            // exit(\Doctrine\Common\Util\Debug::dump($customer->id));
            $user->setStripeCustomer($customer->id);
            $em->persist($user);
            
            $em->flush();
            // exit(\Doctrine\Common\Util\Debug::dump($form['name']->getData()));
            
            $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_secured_area', serialize($token));  
    
            return $this->redirect($this->generateUrl('subscriber_menu'));
        }
        
        
        next:
        $termcond = $em->getRepository("NvCargaBundle:Termcond")->findOneBy(array('maincompany'=>$maincompany, 'tableclass' => 'Subscriber', 'active' => true));
        if ($termcond) {
            $content = $termcond->getMessage();
        } else {
            $content = null;
        }
        return array(
            'form'   => $form->createView(),
            'fondo' => $maincompany->getBackground(),
            'logomain' => $maincompany->getLogomain(),
            'content' =>$content,
        );
        // return $this->render('NvCargaBundle:Security:register_company.html.twig', array());
    }
    /**
     * Definimos las rutas para el login:
     * @Route("/login", name="login")
     * @Route("/login_check", name="login_check")
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $server = $_SERVER['SERVER_NAME'];
        $em = $this->getDoctrine()->getManager();
        // $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage);
        $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->createQueryBuilder('m')
            ->where('m.homepage =:server OR m.homepage_aux =:server')
            ->setParameter('server', $server )
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // obtiene el error de inicio de sesión si lo hay
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        //exit(\Doctrine\Common\Util\Debug::dump($maincompany->getBackground()));
        return $this->render('NvCargaBundle:Security:login.html.twig', array(
            // el último nombre de usuario ingresado por el usuario
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
            'fondo' => $maincompany->getBackground(),
            'logomain' => $maincompany->getLogomain(),
        ));
    }
    /**
     * Definimos las rutas para el timeout:
     * @Route("/timeout", name="timeout")
    */
    public function timeoutAction()
    {
        return $this->render('NvCargaBundle:Security:timeout.html.twig'); /* , array(
            // el último nombre de usuario ingresado por el usuario
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        )); */
    }
   /**
     * Creates a form to create a email entity.
     *
     * @param $data The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
   private function createFormEmail($data)
    {
        $form = $this->createFormBuilder($data)
            ->setAction($this->generateUrl('remember_email'))
            ->setMethod('POST')
            ->add('email', 'email', array('label'=> ' ', 'mapped'=>false, 
						'constraints' => array(
							new NotBlank(["message" => "Debe escribir su email"]),
							new Email(["message" => "El email '{{ value }}' no es válido.", 
								"checkMX" => true, "checkHost" => true]))))
            ->add('submit', 'submit', array('label' => 'Enviar', 'attr' => array('class' => 'btn btn-block btn-primary btn-rad btn-lg')))
            ->getForm();

        return $form;
    }

    /**
     * Definimos las rutas para el remember:
     * @Route("/remember", name="remember")
     * @Method("GET")
     * @Template()
    */
    public function rememberAction()
    {
        $data = array();
    	$form = $this->createFormEmail($data);
    	$server = $_SERVER['SERVER_NAME'];
        $em = $this->getDoctrine()->getManager();
        // $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage);
        $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->createQueryBuilder('m')
            ->where('m.homepage =:server OR m.homepage_aux =:server')
            ->setParameter('server', $server )
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    	
        return array(
            'form'   => $form->createView(),
            'fondo' => $maincompany->getBackground(),
            'logomain' => $maincompany->getLogomain(),
        ); 
    }

    private function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);
        return $min + $rnd;
    }

private function getToken($length)
{
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "0123456789";
    $codeAlphabet.= "._/#()-";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    
    $max = strlen($codeAlphabet); // edited

    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max-1)];
    }

    return $token;
}
   private function setSecurePassword(&$entity) {
     $entity->setSalt(md5(time()));
     $encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', true, 10);
     $password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
     $entity->setPassword($password);
    }
   /**
     * Definimos las rutas para el remember_email: 
     * @Route("/remember_email", name="remember_email")
     * @Template("NvCargaBundle:Security:remember.html.twig")
    */
    public function remember_emailAction(Request $request)
    {
        $flashBag = $this->get('session')->getFlashBag();
        $hideshipper=false;
        $hidereceiver=false;
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $data = array();
        $form = $this->createFormEmail($data);
        $server = $_SERVER['SERVER_NAME'];
        $em = $this->getDoctrine()->getManager();
        // $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage);
        $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->createQueryBuilder('m')
            ->where('m.homepage =:server OR m.homepage_aux =:server')
            ->setParameter('server', $server )
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
            
        $form->handleRequest($request);
            if ($form->isValid()) {
            $email = $form['email']->getData();
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('NvCargaBundle:User')->findOneBy(['email'=>$email, 'maincompany'=>$maincompany]);
            if (!$user) {
                $flashBag->add('notice', 'Usted no está registrado en el sistema con el Email: ' . $email);
                //throw $this->createNotFoundException('Usted no está registrado en el sistema con el Email: ' . $email );
                goto next;
            }
            $maincompany = $user->getMaincompany();
            //$password= $user->getPassword();
            //$generator = new SecureRandom();
            // $newpass = $this->$generator->nextBytes(10);
            $newpass = $this->getToken(15);
            $user->setPassword($newpass);
            $this->setSecurePassword($user);
            // throw $this->createNotFoundException('Su Password es' . $password . ' ' . $newpass);
            $body ="";
            $body.= "Se ha asigando una nueva contraseña para ingresar a:\n";
            $body.= $user->getAgency()->getMaincompany()->getUrl() . "/login\n";
            $body.= "Usuario: ". $user->getUsername(). "\n";
            $body.= "Contraseña: ". $newpass . "\n";
            $setfrom =  $maincompany->getEmail(); //$this->container->getParameter('mailer_user');
            $fromname = 'Notificación de ' . $maincompany->getName(); //$this->container->getParameter('mailer_username');
            $message = \Swift_Message::newInstance()
                    ->setFrom(array($setfrom => $fromname))
                        ->setSubject('Nueva clave en ' .  $maincompany->getHomepage())
                        //->setTo($email)
                        ->setBody($body);
            $send = 0;
            try {
                $message->setTo($email);
            } catch (\Swift_RfcComplianceException $e) {
                //exit(\Doctrine\Common\Util\Debug::dump('Dirección MALA'));
                $send = -1;
            }
            try {
                $message->setFrom(array($setfrom => $fromname));
            } catch (\Swift_RfcComplianceException $e) {
                //exit(\Doctrine\Common\Util\Debug::dump('Dirección MALA'));
                $send = -2;
            }
            
            if ($send == 0) {
                $send = $this->get('mailer')->send($message);
            }
            
            if ($send > 0) {
                $em->flush();
                return $this->render('NvCargaBundle:Security:remember_sent.html.twig' , array('email' => $email));
                // throw $this->createNotFoundException('Se ha enviado un correo electrónico a la dirección ' . $email . ' con una nueva clave' );
            } else {
                $flashBag->add('notice', 'No se pudo enviar el correo electrónico a la dirección ' . $email .  '\n Intente luego; si el problema persiste comuniquese con: '.  $user->getAgency()->getMaincompany()->getEmail() );
                //throw $this->createNotFoundException('Usted no está registrado en el sistema con el Email: ' . $email );
                goto next;
                // throw $this->createNotFoundException('No se pudo enviar el correo electrónico a la dirección ' . $email .  '\n Intente luego; si el problema persiste comuniquese con: '.  $user->getAgency()->getMaincompany()->getEmail() );
            }
        }
        next:
        return array(
            'form'   => $form->createView(),
        ); 
    }

    /**
     * Definimos las rutas para el exit:
     * @Route("/exit", name="exit")
    */
    public function exitAction()
    {
        $this->container->get('security.context')->setToken(null);
        $message = 'Debe volver ingresar al sistema'; 
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $flashBag->set('info', $message);
        return $this->redirect($this->generateUrl('timeout'));
    }
   /**
     * Definimos las rutas para el timeout:
     * @Route("/config", name="config")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
    */
    public function configAction()
    {
        return $this->render('NvCargaBundle:Security:config.html.twig'); 
    }
}
?>
