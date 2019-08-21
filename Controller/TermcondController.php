<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Termcond;
use NvCarga\Bundle\Form\TermcondType;

/**
 * Termcond controller.
 *
 * @Route("/termcond")
 */
class TermcondController extends Controller
{

    /**
     * Lists all Termcond entities.
     *
     * @Route("/", name="termcond")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        
        $entities = $em->getRepository('NvCargaBundle:Termcond')->findByMaincompany($maincompany);

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Termcond entity.
     *
     * @Route("/", name="termcond_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Termcond:new.html.twig")
     * @Security("has_role('ROLE_ADMIN')")
     */

    public function createAction(Request $request)
    {
        $entity = new Termcond();
        $form = $this->createCreateForm($entity);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $form->handleRequest($request);
        $maincompany = $this->getUser()->getMaincompany();

        if ($form->isValid()) {
           $msg = $entity->getMessage();
            $msg = str_replace("<br>", "\r\n",  $msg);  
            $entity->setMessage($msg);
            $em = $this->getDoctrine()->getManager();
            $exist = $em->getRepository('NvCargaBundle:Termcond')->findOneBy(['tableclass'=>$entity->getTableclass(), 'maincompany'=>$maincompany]);
            if ($exist) {
                $this->get('session')->getFlashBag()->add(
                                'notice',
                                'Ya existe un documento de Términos y Condiciones asociado a esa entidad');
                goto next;
                //throw $this->createNotFoundException('Ya existe un documento de Términos y Condiciones asociado a esa entidad');
            }
            $entity->setLastupdate(new \DateTime());
            $entity->setMaincompany($maincompany);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('termcond_show', array('id' => $entity->getId())));
        } else {
            $this->get('session')->getFlashBag()->add(
                                'notice',
                                'Hay errores en los datos');
        }
        next:
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Termcond entity.
     *
     * @param Termcond $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */

    private function createCreateForm(Termcond $entity)
    {
        $form = $this->createForm(new TermcondType(), $entity, array(
            'action' => $this->generateUrl('termcond_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Termcond entity.
     *
     * @Route("/new", name="termcond_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */

    public function newAction()
    {
        $entity = new Termcond();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Termcond entity.
     *
     * @Route("/{id}", name="termcond_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Termcond')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar el documento de términos y condiciones');
        }

        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            // 'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Termcond entity.
     *
     * @Route("/{id}/edit", name="termcond_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Termcond')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);


        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar el documento de términos y condiciones');
        }
        $msg = $entity->getMessage();
        $msg = str_replace(array("\r\n"), "<br>", $msg);  
        $entity->setMessage($msg);
        $editForm = $this->createEditForm($entity);
        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            // 'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Termcond entity.
    *
    * @param Termcond $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Termcond $entity)
    {
        $form = $this->createForm(new TermcondType(), $entity, array(
            'action' => $this->generateUrl('termcond_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        $form->remove('tableclass');
        $form->add('tableclass', 'choice', array('label' => 'Asociado a ', 
                    'choices' => array('Casillero' => 'Pobox', 
                    'labels.guide' => 'Guide', 
                    'Factura' => 'Bill', 
                    'labels.receipt' => 'Receipt', 
                    'Servicio Adicional' => 'Adservice', 
                    'Alerta' => 'Alert', 
                    'labels.consolidated' => 'Consolidated'), 
                    'choices_as_values' => true, 'disabled'=>true,  'data'=>$entity->getTableclass()));
       
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Termcond entity.
     *
     * @Route("/{id}", name="termcond_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Termcond:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Termcond')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);
        $tableclass = $entity->getTableclass();
        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar el documento de términos y condiciones');
        }

        // $deleteForm = $this->createDeleteForm($id);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
	

        if ($editForm->isValid()) {
            $msg = $entity->getMessage();
            $msg = str_replace("<br>", "\r\n",  $msg);  
            $entity->setMessage($msg);
            /*
            $exist = $em->getRepository('NvCargaBundle:Termcond')->findOneBy(['tableclass'=>$entity->getTableclass(), 'maincompany'=>$maincompany]);
            if ($exist && ($tableclass != $entity->getTableclass())) {
                throw $this->createNotFoundException('Ya existe un documento de Términos y Condiciones asociado a esa entidad');
            }
            */
            $entity->setLastupdate(new \DateTime());
            $em->flush();

            return $this->redirect($this->generateUrl('termcond'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            // 'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Termcond entity.
     *
     * @Route("/{id}", name="termcond_delete")
     * @Method("DELETE")
     */
/*
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Termcond')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Termcond entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('termcond'));
    }
*/
    /**
     * Creates a form to delete a Termcond entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
/*
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('termcond_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
*/
}
