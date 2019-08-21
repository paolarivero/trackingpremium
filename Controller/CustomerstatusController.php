<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Customerstatus;
use NvCarga\Bundle\Form\CustomerstatusType;

/**
 * Customerstatus controller.
 *
 * @Route("/customerstatus")
 */
class CustomerstatusController extends Controller
{

    /**
     * Lists all Customerstatus entities.
     *
     * @Route("/index", name="customerstatus")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CUSTOMERSTATUS') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_CUSTOMERSTATUS')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Customerstatus')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Customerstatus entity.
     *
     * @Route("/create", name="customerstatus_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Customerstatus:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_CUSTOMERSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Customerstatus();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
	    $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('customerstatus_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Customerstatus entity.
     *
     * @param Customerstatus $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Customerstatus $entity)
    {
        $form = $this->createForm(new CustomerstatusType(), $entity, array(
            'action' => $this->generateUrl('customerstatus_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Customerstatus entity.
     *
     * @Route("/new", name="customerstatus_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CUSTOMERSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Customerstatus();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Customerstatus entity.
     *
     * @Route("/{id}/show", name="customerstatus_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CUSTOMERSTATUS') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_CUSTOMERSTATUS')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Customerstatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Customerstatus entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Customerstatus entity.
     *
     * @Route("/{id}/edit", name="customerstatus_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CUSTOMERSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Customerstatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Customerstatus entity.');
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
    * Creates a form to edit a Customerstatus entity.
    *
    * @param Customerstatus $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Customerstatus $entity)
    {
        $form = $this->createForm(new CustomerstatusType(), $entity, array(
            'action' => $this->generateUrl('customerstatus_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Customerstatus entity.
     *
     * @Route("/{id}/update", name="customerstatus_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Customerstatus:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_CUSTOMERSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Customerstatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Customerstatus entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('customerstatus_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Customerstatus entity.
     *
     * @Route("/{id}/delete", name="customerstatus_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_CUSTOMERSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Customerstatus')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Customerstatus entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('customerstatus'));
    }

    /**
     * Creates a form to delete a Customerstatus entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('customerstatus_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
