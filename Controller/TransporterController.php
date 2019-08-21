<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Transporter;
use NvCarga\Bundle\Form\TransporterType;

/**
 * Transporter controller.
 *
 * @Route("/transporter")
 */
class TransporterController extends Controller
{

    /**
     * Lists all Transporter entities.
     *
     * @Route("/index", name="transporter")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_TRANSPORTER') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_TRANSPORTER')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Transporter')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Transporter entity.
     *
     * @Route("/create", name="transporter_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Transporter:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_TRANSPORTER') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Transporter();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
	    $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('transporter_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Transporter entity.
     *
     * @param Transporter $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Transporter $entity)
    {
        $form = $this->createForm(new TransporterType(), $entity, array(
            'action' => $this->generateUrl('transporter_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Transporter entity.
     *
     * @Route("/new", name="transporter_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_TRANSPORTER') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Transporter();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Transporter entity.
     *
     * @Route("/{id}/show", name="transporter_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_TRANSPORTER') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_TRANSPORTER')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Transporter')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transporter entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Transporter entity.
     *
     * @Route("/{id}/edit", name="transporter_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_TRANSPORTER') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Transporter')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transporter entity.');
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
    * Creates a form to edit a Transporter entity.
    *
    * @param Transporter $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Transporter $entity)
    {
        $form = $this->createForm(new TransporterType(), $entity, array(
            'action' => $this->generateUrl('transporter_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Transporter entity.
     *
     * @Route("/{id}/update", name="transporter_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Transporter:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_TRANSPORTER') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Transporter')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transporter entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('transporter_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Transporter entity.
     *
     * @Route("/{id}/update", name="transporter_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_TRANSPORTER') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Transporter')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Transporter entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('transporter'));
    }

    /**
     * Creates a form to delete a Transporter entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('transporter_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
