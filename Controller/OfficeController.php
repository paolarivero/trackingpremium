<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Office;
use NvCarga\Bundle\Form\OfficeType;

/**
 * Office controller.
 *
 * @Route("/office")
 */
class OfficeController extends Controller
{

    /**
     * Lists all Office entities.
     *
     * @Route("/index", name="office")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_OFFICE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_OFFICE')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany= $this->getUser()->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Office')->findByMaincompany($maincompany);

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Office entity.
     *
     * @Route("/create", name="office_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Office:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_OFFICE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Office();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $maincompany= $this->getUser()->getMaincompany();
        
        if ($form->isValid()) {
	    $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $entity->setMaincompany($maincompany);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('office_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Office entity.
     *
     * @param Office $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Office $entity)
    {
        $form = $this->createForm(new OfficeType(), $entity, array(
            'action' => $this->generateUrl('office_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Office entity.
     *
     * @Route("/new", name="office_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_OFFICE') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Office();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Office entity.
     *
     * @Route("/{id}/show", name="office_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_OFFICE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_OFFICE')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany= $this->getUser()->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Office')->findByMaincompany($maincompany);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Office entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Office entity.
     *
     * @Route("/{id}/edit", name="office_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_OFFICE') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany= $this->getUser()->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Office')->findByMaincompany($maincompany);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Office entity.');
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
    * Creates a form to edit a Office entity.
    *
    * @param Office $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Office $entity)
    {
        $form = $this->createForm(new OfficeType(), $entity, array(
            'action' => $this->generateUrl('office_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Office entity.
     *
     * @Route("/{id}/update", name="office_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Office:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_OFFICE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany= $this->getUser()->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Office')->findByMaincompany($maincompany);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Office entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('office_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Office entity.
     *
     * @Route("/{id}/delete", name="office_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_OFFICE') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $maincompany= $this->getUser()->getMaincompany();
            $entities = $em->getRepository('NvCargaBundle:Office')->findByMaincompany($maincompany);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Office entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('office'));
    }

    /**
     * Creates a form to delete a Office entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('office_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
