<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Shippingtype;
use NvCarga\Bundle\Form\ShippingtypeType;

/**
 * Shippingtype controller.
 *
 * @Route("/shippingtype")
 */
class ShippingtypeController extends Controller
{

    /**
     * Lists all Shippingtype entities.
     *
     * @Route("/index", name="shippingtype")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_SHIPPINGTYPE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_SHIPPINGTYPE')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Shippingtype')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Shippingtype entity.
     *
     * @Route("/create", name="shippingtype_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Shippingtype:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_SHIPPINGTYPE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Shippingtype();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
	    $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('shippingtype_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Shippingtype entity.
     *
     * @param Shippingtype $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Shippingtype $entity)
    {
        $form = $this->createForm(new ShippingtypeType(), $entity, array(
            'action' => $this->generateUrl('shippingtype_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Shippingtype entity.
     *
     * @Route("/new", name="shippingtype_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_SHIPPINGTYPE') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Shippingtype();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Shippingtype entity.
     *
     * @Route("/{id}/show", name="shippingtype_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_SHIPPINGTYPE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_SHIPPINGTYPE')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Shippingtype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Shippingtype entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Shippingtype entity.
     *
     * @Route("/{id}/edit", name="shippingtype_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_SHIPPINGTYPE') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Shippingtype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Shippingtype entity.');
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
    * Creates a form to edit a Shippingtype entity.
    *
    * @param Shippingtype $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Shippingtype $entity)
    {
        $form = $this->createForm(new ShippingtypeType(), $entity, array(
            'action' => $this->generateUrl('shippingtype_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Shippingtype entity.
     *
     * @Route("/{id}/update", name="shippingtype_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Shippingtype:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_SHIPPINGTYPE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Shippingtype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Shippingtype entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('shippingtype_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Shippingtype entity.
     *
     * @Route("/{id}/delete", name="shippingtype_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_SHIPPINGTYPE') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Shippingtype')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Shippingtype entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('shippingtype'));
    }

    /**
     * Creates a form to delete a Shippingtype entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('shippingtype_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
