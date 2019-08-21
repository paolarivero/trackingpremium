<?php

namespace NvCarga\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

// For login a user programatically 
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Customer controller.
 *
 * @Route("/")
 */
class DefaultController extends Controller
{

    /**
     * Main menu system
     *
     * @Route("/system", name="system")
    */
    /*
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        
        $today= new \DateTime();
        $last = new \DateTime();
        $last->modify('-12 months');
        $cusmon = array();
        
        $result =  $em->getRepository('NvCargaBundle:Customer')->createQueryBuilder('c')
                        ->select('MONTH(c.creationdate) as month, COUNT(c.id) as numcus')
                        ->where('c.creationdate BETWEEN :dateini AND :dateend')
                        ->setParameters(['dateini'=>$last, 'dateend'=>$today])
                        ->groupBy('month')
                        ->orderBy('YEAR(c.creationdate)', 'ASC')
                        ->getQuery()
                        ->getResult();
        $first = date('m'); //$result[0]['month'];
        $pos = array();
        for ($i=0;$i<12;$i++) {
            $pos[$i] = $i;
        }
        for ($i=1;$i<13;$i++) {
            if ($first < 1) {
                $first = 12;
            }
            $cusmon[$i-1]['month']= $first;
            $cusmon[$i-1]['numcus']= 0;
            $pos[$first-1] = $i-1;
            $first--;
            
        }
        foreach ($result as $mon) {
            $pp = $mon['month'];
            $cusmon[$pos[$pp-1]]['month'] = $pp; 
            $cusmon[$pos[$pp-1]]['numcus'] = $mon['numcus']; 
        }
        return $this->render('NvCargaBundle:System:index.html.twig', array('cusmon' => $cusmon));
    }
    */
    /**
     * Main menu system with any user
     *
     * @Route("/switchuser/{id}", name="switchuser")
     * @Security("has_role('ROLE_ADMIN')")
    */
    public function switchuserAction($id)
    {
        $loguser = $this->getUser();
        if ($loguser->getUsername() != 'trackingpremium') {
            throw $this->createNotFoundException('No tiene permisos para ejecutar esta acción');
        }
        $em = $this->getDoctrine()->getManager();
        $repo  = $em->getRepository("NvCargaBundle:User"); //Entity Repository
        $user = $repo->findOneby(['id'=>$id,'maincompany'=>$loguser->getMaincompany()]);
        
        if (!$user) {
                throw new UsernameNotFoundException("El usuario " . $username . " no existe");
        } else {
                $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_secured_area', serialize($token));
        }
        
        return $this->redirect($this->generateUrl('homepage'));
    }
   /**
     * Test menu system
     *
     * @Route("/test", name="test")
    */
    public function testAction()
    {
        return $this->render('NvCargaBundle:System:test.html.twig');
    }
    
    /**
     * Finds and displays a  menu for Pobox entity.
     *
     * @Route("/menu", name="system_menu")
     * @Security("has_role('ROLE_USER')")
     */
    public function menuAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();

        $cancelstatus = $em->getRepository('NvCargaBundle:Receiptstatus')->findOneByName('Anulado');
        
        $receipts = $em->getRepository('NvCargaBundle:Receipt')->findBy(['maincompany'=> $maincompany, 'receiptd_by' => $user]);
        
        $receipts= $em->getRepository('NvCargaBundle:Receipt')->createQueryBuilder('r')
                    ->where('r.receiptd_by =:user')
                    ->andwhere('r.status !=:status')
                    ->setParameters(array('user'=>$user, 
                            'status' => $cancelstatus))
                    ->orderBy('r.arrivedate', 'DESC')
                    ->getQuery()
                    ->getResult();
        
        $warehouses= $em->getRepository('NvCargaBundle:WHrec')->createQueryBuilder('r')
                    ->where('r.receiptd_by =:user')
                    ->andwhere('r.status !=:status')
                    ->setParameters(array('user'=>$user, 
                            'status' => 'ANULADO'))
                    ->orderBy('r.creationdate', 'DESC')
                    ->getQuery()
                    ->getResult();
        
        $guides= $em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                    ->where('g.processedby =:user')
                    ->setParameter('user', $user)
                    ->orderBy('g.creationdate', 'DESC')
                    ->getQuery()
                    ->getResult();
                    
        
        $idbills = array();
        $idpays = array();
        foreach ($guides as $guide) {
            if ($guide->getBill()) {
                $id = $guide->getBill()->getId();
                if (!in_array($id, $idbills)) {
                    $idbills[] = $id;
                }
            }
        }
        
        $bills = $em->getRepository('NvCargaBundle:Bill')->findById($idbills); 
        $payments = $em->getRepository('NvCargaBundle:Payment')->findByGuide($guides); 
        // exit(\Doctrine\Common\Util\Debug::dump($bills));
        
        return $this->render('NvCargaBundle:System:menu.html.twig', array(
            'bills' => $bills,
            'guides' => $guides,
            'receipts' => $receipts,
            'payments' => $payments,
            'warehouses' => $warehouses,
        ));
    }
    /**
     * Finds and displays a  menu for Pobox entity.
     *
     * @Route("/inactive", name="system_inactive")
     * @Security("has_role('ROLE_USER')")
     */
    public function inactiveAction()
    {
        return $this->render('NvCargaBundle:System:inactive.html.twig', array());
    }
}
