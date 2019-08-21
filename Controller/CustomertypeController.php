<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Customertype;
use NvCarga\Bundle\Form\CustomertypeType;

/**
 * Customertype controller.
 *
 * @Route("/customertype")
 */
class CustomertypeController extends Controller
{

    /**
     * Lists all Customertype entities.
     *
     * @Route("/index", name="customertype")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CUSTOMERTYPE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_CUSTOMERTYPE')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Customertype')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Customertype entity.
     *
     * @Route("/create", name="customertype_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Customertype:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_CUSTOMERTYPE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Customertype();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
	    $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('customertype_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Customertype entity.
     *
     * @param Customertype $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Customertype $entity)
    {
        $form = $this->createForm(new CustomertypeType(), $entity, array(
            'action' => $this->generateUrl('customertype_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Customertype entity.
     *
     * @Route("/new", name="customertype_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CUSTOMERTYPE') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Customertype();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Customertype entity.
     *
     * @Route("/{id}/show", name="customertype_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CUSTOMERTYPE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_CUSTOMERTYPE')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Customertype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Customertype entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Customertype entity.
     *
     * @Route("/{id}/edit", name="customertype_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CUSTOMERTYPE') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Customertype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Customertype entity.');
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
    * Creates a form to edit a Customertype entity.
    *
    * @param Customertype $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Customertype $entity)
    {
        $form = $this->createForm(new CustomertypeType(), $entity, array(
            'action' => $this->generateUrl('customertype_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Customertype entity.
     *
     * @Route("/{id}/update", name="customertype_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Customertype:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_CUSTOMERTYPE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Customertype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Customertype entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('customertype_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Customertype entity.
     *
     * @Route("/{id}/delete", name="customertype_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_CUSTOMERTYPE') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Customertype')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Customertype entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('customertype'));
    }

    /**
     * Creates a form to delete a Customertype entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('customertype_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
