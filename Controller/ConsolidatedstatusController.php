<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Consolidatedstatus;
use NvCarga\Bundle\Form\ConsolidatedstatusType;

/**
 * Consolidatedstatus controller.
 *
 * @Route("/consolidatedstatus")
 */
class ConsolidatedstatusController extends Controller
{

    /**
     * Lists all Consolidatedstatus entities.
     *
     * @Route("/index", name="consolidatedstatus")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CONSOLIDATEDSTATUS') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_CONSOLIDATEDSTATUS')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Consolidatedstatus')->findBy([], ['position' => 'ASC']);

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Consolidatedstatus entity.
     *
     * @Route("/create", name="consolidatedstatus_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Consolidatedstatus:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_CONSOLIDATEDSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Consolidatedstatus();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
	$em = $this->getDoctrine()->getManager();
	
	$status = $em->getRepository('NvCargaBundle:Consolidatedstatus')->createQueryBuilder('c')
			->addOrderBy('c.position', 'ASC')
    			->getQuery()
			->getResult(); 

        if ($form->isValid()) {
	    $position = $form['thepos']->getData();
            $rstatus = array_reverse($status);
	    foreach ($rstatus as $st) {
		if ($st->getPosition() >= $position) {
			$pos = $st->getPosition();
			$st->setPosition($pos+1);
			$em->flush();
		}
	    }
	    $entity->setPosition($position);
	    $entity->setCreationdate(new \DateTime());
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('consolidatedstatus'));
        }

        return array(
	    'status' => $status,
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Consolidatedstatus entity.
     *
     * @param Consolidatedstatus $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Consolidatedstatus $entity)
    {
        $form = $this->createForm(new ConsolidatedstatusType(), $entity, array(
            'action' => $this->generateUrl('consolidatedstatus_create'),
            'method' => 'POST',
        ));

	$form->add('thepos', 'hidden', array('mapped'=>false));
        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Consolidatedstatus entity.
     *
     * @Route("/new", name="consolidatedstatus_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CONSOLIDATEDSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Consolidatedstatus();
        $form   = $this->createCreateForm($entity);
	$em = $this->getDoctrine()->getManager();
	
	$status = $em->getRepository('NvCargaBundle:Consolidatedstatus')->createQueryBuilder('c')
			->orderBy('c.position', 'ASC')
    			->getQuery()
			->getResult(); 
        return array(
	    'status' => $status,
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Consolidatedstatus entity.
     *
     * @Route("/{id}/show", name="consolidatedstatus_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CONSOLIDATEDSTATUS') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_CONSOLIDATEDSTATUS')")
     */
   /* public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Consolidatedstatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Consolidatedstatus entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }*/

    /**
     * Displays a form to edit an existing Consolidatedstatus entity.
     *
     * @Route("/{id}/edit", name="consolidatedstatus_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CONSOLIDATEDSTATUS') or has_role('ROLE_ADMIN')")
     */
    /* public function editAction($id)
    {
	
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Consolidatedstatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Consolidatedstatus entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
	
    } */

    /**
    * Creates a form to edit a Consolidatedstatus entity.
    *
    * @param Consolidatedstatus $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    /* private function createEditForm(Consolidatedstatus $entity)
    {
	
        $form = $this->createForm(new ConsolidatedstatusType(), $entity, array(
            'action' => $this->generateUrl('consolidatedstatus_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
	$form->remove('name');
	$form->add('name',  null , array('label' => 'Nombre', 'read_only'=>true));
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
	
    } */
    /**
     * Edits an existing Consolidatedstatus entity.
     *
     * @Route("/{id}/update", name="consolidatedstatus_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Consolidatedstatus:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_CONSOLIDATEDSTATUS') or has_role('ROLE_ADMIN')")
     */
    /* public function updateAction(Request $request, $id)
    {
	
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Consolidatedstatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Consolidatedstatus entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('consolidatedstatus'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
	
    } */
    /**
     * Deletes a Consolidatedstatus entity.
     *
     * @Route("/{id}/delete", name="consolidatedstatus_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_CONSOLIDATEDSTATUS') or has_role('ROLE_ADMIN')")
     */
    /* public function deleteAction(Request $request, $id)
    {
	/*
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Consolidatedstatus')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Consolidatedstatus entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('consolidatedstatus'));
	
    } */

    /**
     * Creates a form to delete a Consolidatedstatus entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    /* private function createDeleteForm($id)
    {
	
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('consolidatedstatus_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
	
    } */
}
