<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Tax;
use NvCarga\Bundle\Form\TaxType;

/**
 * Tax controller.
 *
 * @Route("/tax")
 */
class TaxController extends Controller
{

    /**
     * Lists all Tax entities.
     *
     * @Route("/index", name="tax")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_TAX') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_TAX')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Tax')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Tax entity.
     *
     * @Route("/create", name="tax_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Tax:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_TAX') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Tax();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('tax_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Tax entity.
     *
     * @param Tax $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Tax $entity)
    {
        $form = $this->createForm(new TaxType(), $entity, array(
            'action' => $this->generateUrl('tax_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Tax entity.
     *
     * @Route("/new", name="tax_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_TAX') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Tax();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Tax entity.
     *
     * @Route("/{id}/show", name="tax_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_TAX') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_TAX')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Tax')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tax entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Tax entity.
     *
     * @Route("/{id}/edit", name="tax_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_TAX') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Tax')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tax entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Tax entity.
    *
    * @param Tax $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Tax $entity)
    {
        $form = $this->createForm(new TaxType(), $entity, array(
            'action' => $this->generateUrl('tax_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Eliminar'));

        return $form;
    }
    /**
     * Edits an existing Tax entity.
     *
     * @Route("/{id}/update", name="tax_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Tax:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_TAX') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Tax')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tax entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('tax_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Tax entity.
     *
     * @Route("/{id}/delete", name="tax_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_TAX') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Tax')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Tax entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('tax'));
    }

    /**
     * Creates a form to delete a Tax entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('tax_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
