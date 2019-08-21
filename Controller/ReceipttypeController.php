<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Receipttype;
use NvCarga\Bundle\Form\ReceipttypeType;

/**
 * Receipttype controller.
 *
 * @Route("/receipttype")
 */
class ReceipttypeController extends Controller
{

    /**
     * Lists all Receipttype entities.
     *
     * @Route("/index", name="receipttype")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Receipttype')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Receipttype entity.
     *
     * @Route("/create", name="receipttype_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Receipttype:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_SPECIAL')")
     */
    public function createAction(Request $request)
    {
        $entity = new Receipttype();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
	    $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('receipttype_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Receipttype entity.
     *
     * @param Receipttype $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Receipttype $entity)
    {
        $form = $this->createForm(new ReceipttypeType(), $entity, array(
            'action' => $this->generateUrl('receipttype_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Receipttype entity.
     *
     * @Route("/new", name="receipttype_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_SPECIAL')")
     */
    public function newAction()
    {
        $entity = new Receipttype();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Receipttype entity.
     *
     * @Route("/{id}/show", name="receipttype_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_SPECIAL')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Receipttype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Receipttype entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Receipttype entity.
     *
     * @Route("/{id}/edit", name="receipttype_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_SPECIAL')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Receipttype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Receipttype entity.');
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
    * Creates a form to edit a Receipttype entity.
    *
    * @param Receipttype $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Receipttype $entity)
    {
        $form = $this->createForm(new ReceipttypeType(), $entity, array(
            'action' => $this->generateUrl('receipttype_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Receipttype entity.
     *
     * @Route("/{id}/update", name="receipttype_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Receipttype:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_SPECIAL')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Receipttype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Receipttype entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('receipttype_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Receipttype entity.
     *
     * @Route("/{id}/delete", name="receipttype_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_SPECIAL')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Receipttype')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Receipttype entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('receipttype'));
    }

    /**
     * Creates a form to delete a Receipttype entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('receipttype_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
