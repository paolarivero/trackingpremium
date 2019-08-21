<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Stepstatus;
use NvCarga\Bundle\Form\StepstatusType;

/**
 * Stepstatus controller.
 *
 * @Route("/stepstatus")
 */
class StepstatusController extends Controller
{
    /**
     * Creates a new Stepstatus entity.
     *
     * @Route("/create", name="stepstatus_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Stepstatus:new.html.twig")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entity = new Stepstatus(); 
        $form   = $this->createCreateForm($entity);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $form->handleRequest($request);

        if ($form->isValid()) {
            $step= $em->getRepository('NvCargaBundle:Stepstatus')->findOneBy(['maincompany'=>$maincompany, 'name'=>$entity->getName()]);
            if ($step) {
                $message = 'Ya tiene un paso (Movimiento) con el nombre '. $entity->getName() ;
                $flashBag->add('notice',$message);
                goto next;
            }
            $entity->setMaincompany($maincompany);
            $entity->setActive(true);
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('stepstatus'));
        }

        next:
        return array(
            'entity' => $entity,
            'form'  => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Stepstatus entity.
     *
     * @param Stepstatus $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Stepstatus $entity)
    {
        $form = $this->createForm(new StepstatusType(), $entity, array(
            'action' => $this->generateUrl('stepstatus_create'),
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Crear'));
        return $form;
    }

    /**
     * Displays a form to create a new Stepstatus entity.
     *
     * @Route("/new", name="stepstatus_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entity = new Stepstatus(); 
        $form   = $this->createCreateForm($entity);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        return array(
            'entity' => $entity,
            'form'  => $form->createView(),
        );
    }
    /**
     * Lists all stepstatus
     *
     * @Route("/", name="stepstatus")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
       
        $entities = $em->getRepository('NvCargaBundle:Stepstatus')->findByMaincompany($maincompany);
        
        return array(
            'entities' => $entities,
        );
    }

    /**
     * Displays a form to edit an existing Stepstatus entity.
     *
     * @Route("/{id}/edit", name="stepstatus_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Stepstatus')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);
        
        $translator = $this->get('translator');
        if (!$entity) {
            throw $this->createNotFoundException('No existe ese ' . $translator->trans('Paso'));
        }

        $form = $this->createEditForm($entity);

        return array(
            'entity'      => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
    * Creates a form to edit a Stepstatus entity.
    *
    * @param Stepstatus $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Stepstatus $entity)
    {
        $form = $this->createForm(new StepstatusType(), $entity, array(
            'action' => $this->generateUrl('stepstatus_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        
        $form->add('active', 'checkbox', array('label' => false, 'attr'=>array('class'=>'icheck')));
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Stepstatus entity.
     *
     * @Route("/{id}/update", name="stepstatus_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Stepstatus:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Stepstatus')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe ese ' . $translator->trans('Paso'));
        }

        // $deleteForm = $this->createDeleteForm($id);
        $oldname = $entity->getName();
        $form = $this->createEditForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $step = $em->getRepository('NvCargaBundle:Stepstatus')->findOneBy(['maincompany'=>$maincompany, 'name'=>$entity->getName()]);
            if (($step) && ($oldname != $entity->getName())) {
                throw $this->createNotFoundException('Ya existe otro ' . $translator->trans('Paso') . ' con el nombre ' . $entity->getName);
            }
            if ($entity->getName() == 'Creado') {
                $entity->setActive(true);
            }
            $em->flush();
            
            return $this->redirect($this->generateUrl('stepstatus'));
        }
        return array(
            'entity'      => $entity,
            'form'   => $form->createView(),
            // 'delete_form' => $deleteForm->createView(),
        );
    }
}
