<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Receiptstatus;
use NvCarga\Bundle\Form\ReceiptstatusType;

/**
 * Receiptstatus controller.
 *
 * @Route("/receiptstatus")
 */
class ReceiptstatusController extends Controller
{

    /**
     * Lists all Receiptstatus entities.
     *
     * @Route("/index", name="receiptstatus")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Receiptstatus')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Receiptstatus entity.
     *
     * @Route("/create", name="receiptstatus_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Receiptstatus:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_SPECIAL')")
     */
    public function createAction(Request $request)
    {
        $entity = new Receiptstatus();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
	    $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('receiptstatus_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Receiptstatus entity.
     *
     * @param Receiptstatus $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Receiptstatus $entity)
    {
        $form = $this->createForm(new ReceiptstatusType(), $entity, array(
            'action' => $this->generateUrl('receiptstatus_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Receiptstatus entity.
     *
     * @Route("/new", name="receiptstatus_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_SPECIAL')")
     */
    public function newAction()
    {
        $entity = new Receiptstatus();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Receiptstatus entity.
     *
     * @Route("/{id}/show", name="receiptstatus_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_SPECIAL')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Receiptstatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Receiptstatus entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Receiptstatus entity.
     *
     * @Route("/{id}/edit", name="receiptstatus_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_SPECIAL')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Receiptstatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Receiptstatus entity.');
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
    * Creates a form to edit a Receiptstatus entity.
    *
    * @param Receiptstatus $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Receiptstatus $entity)
    {
        $form = $this->createForm(new ReceiptstatusType(), $entity, array(
            'action' => $this->generateUrl('receiptstatus_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Receiptstatus entity.
     *
     * @Route("/{id}/update", name="receiptstatus_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Receiptstatus:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_SPECIAL')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Receiptstatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Receiptstatus entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('receiptstatus_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Receiptstatus entity.
     *
     * @Route("/{id}/delete", name="receiptstatus_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_SPECIAL')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Receiptstatus')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Receiptstatus entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('receiptstatus'));
    }

    /**
     * Creates a form to delete a Receiptstatus entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('receiptstatus_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
