<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Guide;


/**
 * Tracking controller.
 *
 * @Route("/tracking")
 */
class TrackingController extends Controller
{ 
    private function createSearchForm($number)
    {
        $formBuilder = $this->get('form.factory')->createNamedBuilder('search_guide', 'form', array())
            ->add('number', 'text', array('label' => 'labels.guidenumber', 'required' => true, 'data' => $number))
            ->add('search', 'button', array('label' => 'Buscar'));
        $form = $formBuilder->getForm();
        return $form;
    }
    /**
     * Lists all Guide entities.
     *
     * @Route("/tracking", name="tracking_guide")
     * @Method("GET")
     * @Template()
     */
    public function trackingAction()
    {
        $this->container->get('security.context')->setToken(null);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $number = null;
        $form   = $this->createSearchForm($number);

        return array(
            'form'   => $form->createView(),
        );
    }
    /**
     * show a guide of specific customer 
     *
     * @Route("/find", name="guide_find")
     * @Template()
     */
    public function findAction(Request $request)
    {
        $number = $request->query->get('guidenumber');
        $em = $this->getDoctrine()->getManager();
        $server = $_SERVER['SERVER_NAME'];
        // $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage);
        $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->createQueryBuilder('m')
            ->where('m.homepage =:server OR m.homepage_aux =:server')
            ->setParameter('server', $server )
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        
        $entity = $em->getRepository('NvCargaBundle:Guide')->findOneBy(['number'=>$number, 'maincompany'=>$maincompany]); //AGREGAR el campo para no mostrar despues de un tiempo
        $status = $em->getRepository('NvCargaBundle:Guidestatus')->findAll();
        // exit(\Doctrine\Common\Util\Debug::dump($guide)); 
        $form   = $this->createSearchForm($number);
	
        return array(
            'entity' => $entity,
            'status' => $status,
            'form'   => $form->createView(),
        ); 
	
    }
   private function createRecForm($number)
    {
        $formBuilder = $this->get('form.factory')->createNamedBuilder('search_rec', 'form', array())
            ->add('number', 'text', array('label' => 'labels.recnumber', 'required' => true, 'data' => $number))
            ->add('search', 'button', array('label' => 'Buscar'));
        $form = $formBuilder->getForm();
        return $form;
    }
    /**
     * Lists all Guide entities.
     *
     * @Route("/trackrec", name="tracking_receipt")
     * @Method("GET")
     * @Template()
     */
    public function trackrecAction()
    {
        $this->container->get('security.context')->setToken(null);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $number = null;
        $form   = $this->createRecForm($number);

        return array(
            'form'   => $form->createView(),
        );
    }
    /**
     *
     * @Route("/findrec", name="receipt_find")
     * @Template()
     */ 
    public function findrecAction(Request $request)
    {
        $number = $request->query->get('recnumber');
        $em = $this->getDoctrine()->getManager();
        $server = $_SERVER['SERVER_NAME'];
        // $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->findOneByHomepage($homepage);
        $maincompany = $em->getRepository('NvCargaBundle:Maincompany')->createQueryBuilder('m')
            ->where('m.homepage =:server OR m.homepage_aux =:server')
            ->setParameter('server', $server )
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        
        $receipt = $em->getRepository('NvCargaBundle:Receipt')->findOneBy(['number'=>$number, 'maincompany'=>$maincompany]); //AGREGAR el campo para no mostrar despues de un tiempo
        //exit(\Doctrine\Common\Util\Debug::dump($receipt)); 
        $status = $em->getRepository('NvCargaBundle:Guidestatus')->findAll();
        $entity = null;	
        if (($receipt) && ($receipt->getGuide())) {
            $entity = $receipt->getGuide();
        }
	
        // 
        $form   = $this->createRecForm($number);
	
        return array(
            'receipt' => $receipt,
            'entity' => $entity,
            'status' => $status,
            'form'   => $form->createView(),
        ); 
	
    }
}
?>
