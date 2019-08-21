<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Labelconf;
use NvCarga\Bundle\Form\LabelconfType;

/**
 * Labelconf controller.
 *
 * @Route("/labelconf")
 */
class LabelconfController extends Controller
{
    /**
     * Creates a new Labelconf entity.
     *
     * @Route("/create", name="labelconf_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Labelconf:new.html.twig")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Labelconf();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $form   = $this->createCreateForm($entity);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $step= $em->getRepository('NvCargaBundle:Labelconf')->findOneBy(['maincompany'=>$maincompany, 'tableclass'=>$entity->getTableclass()]);
            if ($step) {
                $labels = $this->labels();
                $translator = $this->get('translator');
                $message = 'Ya tiene defenida una etiqueta para el elemento '. $translator->trans($labels[$entity->getTableclass()]) ;
                $flashBag->add('notice',$message);
                goto next;
            }
            $entity->setMaincompany($maincompany);
            $width = round($entity->getWidth()*25.4);
            $height = round($entity->getHeight()*25.4);
            $entity->setWidth($width);
            $entity->setHeight($height);
            $dateup = new \DateTime();
            $clock = $form['clock']->getData(); 
            $rdate = substr($dateup->format('m/d/Y'),0,10) . 'T' . $clock;
            $entity->setLastupdate(new \DateTime($rdate));
            
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('labelconf'));
        }
        next:
        return array(
            'entity' => $entity,
            'form'  => $form->createView(),
        );
    }
    /**
     * Lists all Labelconf entities.
     *
     * @Route("/", name="labelconf")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Labelconf')->findByMaincompany($maincompany);

        return array(
            'entities' => $entities,
            'labels' => $this->labels(),
            'nameform' => 'Nueva etiqueta',
        );
    }
    /**
     * Creates a form to create a Labelconf entity.
     *
     * @param Labelconf $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Labelconf $entity)
    {
        $form = $this->createForm(new LabelconfType(), $entity, array(
            'action' => $this->generateUrl('labelconf_create'),
            'method' => 'POST',
        ));
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Labelconf')->findByMaincompany($maincompany);
        
        $form->add('tableclass', 'choice', array('label' => 'Elemento ',  
                    'empty_value' => '-- Escoja el elemento --', 'required'=>true,
					'choices' => array('labels.receipt' => 'Receipt',
							'labels.warehouse' => 'WHrec',
							'labels.guide' => 'Guide', 
							'labels.bag' => 'Bag',
							'labels.consolidated' => 'Consolidated',
							'Factura' => 'Bill'), 
					'choices_as_values' => true));
        
       
        $form->add('submit', 'submit', array('label' => 'Crear'));
        return $form;
    }
    /**
     * Lists all Labelconf entities.
     *
     * @Route("/new", name="labelconf_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $entity = new Labelconf();
        $form   = $this->createCreateForm($entity);
        
        $entities = $em->getRepository('NvCargaBundle:Labelconf')->findByMaincompany($maincompany);
        
        return array(
            'entity' => $entity,
            'form'  => $form->createView(),
            'nameform' => 'Nueva etiqueta',
        );
    }
    /**
     * Lists all Labelconf entities.
     *
     * @Route("/{id}/edit", name="labelconf_edit")
     * @Template("NvCargaBundle:Labelconf:new.html.twig")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Labelconf')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        

        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar la etiqueta');
        }
        $entity->setWidth(round($entity->getWidth()/25.4));
        $entity->setHeight(round($entity->getHeight()/25.4));
        $form = $this->createEditForm($entity);

        return array(
            'entity' => $entity,
            'form'  => $form->createView(),
            'nameform' => 'Actualización de etiqueta',
        );
    }
     /**
     * Edits an existing Termcond entity.
     *
     * @Route("/{id}/update", name="labelconf_update")
     * @Template("NvCargaBundle:Labelconf:new.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Labelconf')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);


        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar la etiqueta');
        }
        
        $form = $this->createEditForm($entity);
        // $deleteForm = $this->createDeleteForm($id);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $form->handleRequest($request);
	

        if ($form->isValid()) {
            $width = round($entity->getWidth()*25.4);
            $height = round($entity->getHeight()*25.4);
            $entity->setWidth($width);
            $entity->setHeight($height);
            $dateup = new \DateTime();
            $clock = $form['clock']->getData(); 
            $rdate = substr($dateup->format('m/d/Y'),0,10) . 'T' . $clock;
            $entity->setLastupdate(new \DateTime($rdate));
            $em->flush();
            return $this->redirect($this->generateUrl('labelconf'));
            
        }

        return array(
            'entity' => $entity,
            'form'  => $form->createView(),
            'nameform' => 'Actualización de etiqueta',
        );
    }
    /**
     * Creates a form to create a Labelconf entity.
     *
     * @param Labelconf $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Labelconf $entity)
    {
        $form = $this->createForm(new LabelconfType(), $entity, array(
            'action' => $this->generateUrl('labelconf_update',  array('id' => $entity->getId())),
            'method' => 'POST',
        ));
        
        $form->add('tableclass', 'choice', array('label' => 'Elemento ',  
                    'empty_value' => '-- Escoja el elemento --', 'required'=>true,
					'choices' => array('labels.receipt' => 'Receipt',
							'labels.warehouse' => 'WHrec',
							'labels.guide' => 'Guide', 
							'labels.bag' => 'Bag',
							'labels.consolidated' => 'Consolidated',
							'Factura' => 'Bill'), 
					'choices_as_values' => true, 'read_only'=>true, 'data'=>$entity->getTableclass()));
        
       
        $form->add('submit', 'submit', array('label' => 'Actualizar'));
        return $form;
    }
    private function labels()
    {
        $result = array();
        $result['Guide'] = 'Guía';
        $result['Bill'] = 'Factura';
        $result['Receipt'] = 'Recibo';
        $result['WHrec'] = 'Warehouse';
        $result['Bag'] = 'Bolsa';
        $result['Consolidated'] = 'Consolidado';
        return $result;
    }
}
