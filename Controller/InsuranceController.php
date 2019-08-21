<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Insurance;
use NvCarga\Bundle\Form\InsuranceType;

/**
 * Insurance controller.
 *
 * @Route("/insurance")
 */
class InsuranceController extends Controller
{

    /**
     * Lists all Insurance entities.
     *
     * @Route("/index", name="insurance")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_INSURENCE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_INSURENCE')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Insurance')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Insurance entity.
     *
     * @Route("/create", name="insurance_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Insurance:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_INSURENCE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Insurance();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('insurance_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Insurance entity.
     *
     * @param Insurance $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Insurance $entity)
    {
        $form = $this->createForm(new InsuranceType(), $entity, array(
            'action' => $this->generateUrl('insurance_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Insurance entity.
     *
     * @Route("/new", name="insurance_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_INSURENCE') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Insurance();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Insurance entity.
     *
     * @Route("/{id}/show", name="insurance_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_INSURENCE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_INSURENCE')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Insurance')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Insurance entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Insurance entity.
     *
     * @Route("/{id}/edit", name="insurance_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_INSURENCE') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Insurance')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Insurance entity.');
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
    * Creates a form to edit a Insurance entity.
    *
    * @param Insurance $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Insurance $entity)
    {
        $form = $this->createForm(new InsuranceType(), $entity, array(
            'action' => $this->generateUrl('insurance_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Insurance entity.
     *
     * @Route("/{id}/update", name="insurance_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Insurance:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_INSURENCE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Insurance')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Insurance entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('insurance_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Insurance entity.
     *
     * @Route("/{id}/delete", name="insurance_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_INSURENCE') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Insurance')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Insurance entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('insurance'));
    }

    /**
     * Creates a form to delete a Insurance entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('insurance_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
