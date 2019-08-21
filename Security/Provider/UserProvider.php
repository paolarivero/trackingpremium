<?php 

namespace NvCarga\Bundle\Security\Provider;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Serializer\Exception\UnsupportedException;
use Doctrine\ORM\EntityManager;

use Symfony\Component\Routing\Exception\RouteNotFoundException;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\HttpFoundation\RedirectResponse;

use NvCarga\Bundle\Entity\User;
use NvCarga\Bundle\Entity\Customer;
use NvCarga\Bundle\Entity\Pobox;
use NvCarga\Bundle\Entity\Baddress;

class UserProvider implements OAuthAwareUserProviderInterface,UserProviderInterface
{
    protected $class;

    protected $userRepository;

    protected $mainCompanyrepository;
    
    protected $em;
    
    protected $container;

    public function __construct(EntityManager $theManager, ContainerInterface $container, $class, $classmain)
    {
        $this->class = $class;
        $this->em = $theManager;
        $this->container = $container;
        $this->userRepository = $theManager->getRepository($class);
        $this->mainCompanyrepository = $theManager->getRepository($classmain);
    }
 
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $sever = $_SERVER['SERVER_NAME'];
        
        // $maincompany = $this->mainCompanyrepository->findOneByHomepage($homepage);
        
        $maincompany = $this->mainCompanyrepository->createQueryBuilder('m')
            ->where('m.homepage =:server OR m.homepage_aux =:server')
            ->setParameter('server', $server )
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
            
        $typemaster = $this->em->getRepository('NvCargaBundle:Agencytype')->findOneByName('MASTER'); 
        // exit(\Doctrine\Common\Util\Debug::dump($maincompany));
        $agency = $this->em->getRepository('NvCargaBundle:Agency')->findOneBy(['type'=>$typemaster]); 
        
        $service = $response->getResourceOwner()->getName();
        $socialID = $response->getResponse()['id'];
        
        $user = $this->userRepository->findOneBy(['authId'=> $socialID, 'authProvider'=>$service, 'maincompany'=>$maincompany]);
        
        
        $data =  $response->getResponse();
        //exit(\Doctrine\Common\Util\Debug::dump($data));

        $email = $data['email'];
        $name = $data['first_name'];
        $lastname = $data['last_name'];
        if ($user === null) {
            $user = $this->userRepository->findOneBy(['username'=>$email, 'maincompany'=>$maincompany]);
            if ($user === null) {
                /*
                $customer = $this->em->getRepository('NvCargaBundle:Customer')->findOneBy(['email'=>$email,'maincompany'=>$maincompany]);
                if ($customer === null) {
                    $countcustomer = $maincompany->getCountcustomers();
                    if ($countcustomer >= $maincompany->getMaxcustomers() ){
                        throw new RouteNotFoundException('La empresa ha alcanzado el número MÁXIMO de CLIENTES permitidos.  Usted no se puede registrar' );
                    }
                    $countcustomer++;
                    $maincompany->setCountcustomers($countcustomer);
                    
                    $customer= new Customer();
                    $customer->setName($name);
                    $customer->setLastname($lastname);
                    $customer->setEmail($email);
                    $customer->setCreationdate(new \DateTime());
                    $ctype = $this->em->getRepository('NvCargaBundle:Customertype')->findOneBy(array('name' =>'NORMAL'));
                    $cstatus = $this->em->getRepository('NvCargaBundle:Customerstatus')->findOneBy(array('name' =>'ACTIVO'));
                    $customer->setType($ctype);
                    $customer->setStatus($cstatus);

                    $baddr = new Baddress();
                    $baddr->setDocid('');
                    $baddr->setAddress('');
                    $baddr->setCity(null);
                    $baddr->setPhone('');
                    $baddr->setMobile('');
                    $baddr->setBarrio('');
                    $baddr->setZip('');
                    $baddr->setName($name);   
                    $baddr->setLastname($lastname);  
                    $baddr->setCustomer($customer);
                    $customer->setAdrdefault($baddr);
                    $customer->addBaddress($baddr);

                    $this->em->persist($baddr);
                    $customer->setMaincompany($maincompany);
                    $this->em->persist($customer);
                } 
                $countpobox = $maincompany->getCountpoboxes();
                if ($countpobox >= $maincompany->getMaxpoboxes() ){
                    throw new RouteNotFoundException('La empresa ha alcanzado el número MÁXIMO de CASILLEROS permitidos. Usted no se puede registrar' );
                }
                $countpobox++;
                $type = $this->em->getRepository('NvCargaBundle:Poboxtype')->findOneByName('PUBLICO');   
                $status = $this->em->getRepository('NvCargaBundle:Poboxstatus')->findOneByName('ACTIVO');
                
                $pobox = new Pobox();
                $numini= $maincompany->getIninum();
                $highest_id = $maincompany->getCountpoboxes();
                $highest_id++;
                $maincompany->setCountpoboxes($highest_id);
                $val = $numini + $highest_id;
                
                // $number = $maincompany->getPrefixpobox()  . '-'. $maincompany->getAcronym() . '-' . $val;
                $number = $maincompany->getPrefixpobox()  .  $val;
                
                $agency = $this->em->getRepository('NvCargaBundle:Agency')->findOneByPoboxs(true);   
                
                $warehouse = $agency->getWarehouse();
                $thisuser = $this->userRepository->findOneBy(['username'=>'webuser', 'maincompany'=>$maincompany]);
                
                $pobox->setNumber($number);
                $pobox->setCustomer($customer);
                $pobox->setCreationdate(new \DateTime());
                $pobox->setWarehouse($warehouse);
                $pobox->setType($type);
                $pobox->setStatus($status);
                $pobox->setCreateby($thisuser);
                $pobox->setMaincompany($maincompany);
                
                $countuser = $maincompany->getCountusers();
                
                if ($countuser >= $maincompany->getMaxusers() ){
                    throw new RouteNotFoundException('La empresa ha alcanzado el número MÁXIMO de USUARIOS permitidos. Usted no se puede registrar' );
                }
                
                $countuser++;
                $maincompany->setCountusers($countuser);
                $user = new User();
                $user->setUsername($customer->getEmail());
                $user->setEmail($customer->getEmail());
                $user->setName($customer->getName());
                $user->setLastname($customer->getLastname());
                $user->setAgency($pobox->getWarehouse()->getAgency());
                $user->setPassword(md5(uniqid()));
                $user->setSalt(md5(time()));
                $encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', true, 10);
                $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                $user->setPassword($password);
                $user->setCreationdate(new \DateTime());
                
                $status = $this->em->getRepository('NvCargaBundle:Userstatus')->findOneByName('ACTIVO');
                $user->setStatus($status);	
                $role = $this->em->getRepository('NvCargaBundle:Role')->findOneByName('ROLE_USER');
                $user->addUserRole($role);
                $customer->setPobox($pobox);
                $pobox->setUser($user);
                $user->setPobox($pobox);
                
                $user->setMaincompany($maincompany);
                $user->setAuthProvider($service);
                $user->setAuthId($socialID);
                $this->em->persist($pobox);
                $this->em->persist($user);
                */
                $user = new User();
                $user->setType('EMPRESA');
                $user->setMaincompany($maincompany);
                $user->setAgency($agency);
                $user->setEmail($email);
                $user->setUsername($email);
                $user->setPassword(md5(uniqid()));
                $user->setSalt(md5(time()));
                $encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', true, 10);
                $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                $user->setPassword($password);
                $user->setName($name);
                $user->setLastname($lastname);
                $user->setCreationdate(new \DateTime());
                $nstatus = 'ACTIVO';
                $status = $this->em->getRepository('NvCargaBundle:Userstatus')->findOneByName($nstatus);
                $user->setStatus($status);	
                $nrole = 'ROLE_USER';
                $role = $this->em->getRepository('NvCargaBundle:Role')->findOneBy(array('name' => $nrole));
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
                }
                
                // FIN DE PRUEBAS
                // exit(\Doctrine\Common\Util\Debug::dump($customer->id));
                $user->setStripeCustomer($customer->id);
                $this->em->persist($user);
            } else {
                $user->setAuthProvider($service);
                $user->setAuthId($socialID);
            }
            $this->em->flush();
        }
        return $user;
    }
    
    public function loadUserByUsername($username)
    {
        $server = $_SERVER['SERVER_NAME'];
        
        // $theurl = explode(".", $server);
        // $name = $theurl[0];
        
        //$maincompany = $this->mainCompanyrepository->findOneByHomepage($homepage);
        //$maincompany = $this->mainCompanyrepository->find(2);
        //exit(\Doctrine\Common\Util\Debug::dump([$homepage, $maincompany]));
        $maincompany = $this->mainCompanyrepository->createQueryBuilder('m')
            ->where('m.homepage =:server OR m.homepage_aux =:server')
            ->setParameter('server', $server )
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        
        $active = $this->em->getRepository('NvCargaBundle:Userstatus')->findOneByName('ACTIVO');
        $user = $this->userRepository->findOneBy(array('username' => $username, 'maincompany' => $maincompany, 'status'=>$active));
        //$user = $this->userRepository->findOneBy(array('username' => $username));
        //$user = null;
        //exit(\Doctrine\Common\Util\Debug::dump($user->getMaincompany()));
        if (null === $user) {
            $message = sprintf(
                'No se puede encontrar un Usuario activo con el nombre "%s"',
                $username
            );
            throw new UsernameNotFoundException($message);
        }
        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (false == $this->supportsClass($class)) {
            throw new UnsupportedException(
                sprintf(
                    'Instances of "%s" are not supported',
                    $class
                )
            );
        }
        return $this->userRepository->find($user->getId());
    }

    public function supportsClass($class)
    {
        return $this->class === $class
            || is_subclass_of($class, $this->class);

    }
}
