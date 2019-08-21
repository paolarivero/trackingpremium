<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Poboxstatus;
use NvCarga\Bundle\Form\PoboxstatusType;

/**
 * Poboxstatus controller.
 *
 * @Route("/poboxstatus")
 */
class PoboxstatusController extends Controller
{

    /**
     * Lists all Poboxstatus entities.
     *
     * @Route("/index", name="poboxstatus")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_POBOXSTATUS') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_POBOXSTATUS')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Poboxstatus')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Poboxstatus entity.
     *
     * @Route("/create", name="poboxstatus_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Poboxstatus:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_POBOXSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Poboxstatus();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
	    $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('poboxstatus_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Poboxstatus entity.
     *
     * @param Poboxstatus $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Poboxstatus $entity)
    {
        $form = $this->createForm(new PoboxstatusType(), $entity, array(
            'action' => $this->generateUrl('poboxstatus_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Poboxstatus entity.
     *
     * @Route("/new", name="poboxstatus_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_POBOXSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Poboxstatus();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Poboxstatus entity.
     *
     * @Route("/{id}/show", name="poboxstatus_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_POBOXSTATUS') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_POBOXSTATUS')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Poboxstatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Poboxstatus entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Poboxstatus entity.
     *
     * @Route("/{id}/edit", name="poboxstatus_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_POBOXSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Poboxstatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Poboxstatus entity.');
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
    * Creates a form to edit a Poboxstatus entity.
    *
    * @param Poboxstatus $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Poboxstatus $entity)
    {
        $form = $this->createForm(new PoboxstatusType(), $entity, array(
            'action' => $this->generateUrl('poboxstatus_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Poboxstatus entity.
     *
     * @Route("/{id}/update", name="poboxstatus_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Poboxstatus:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_POBOXSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Poboxstatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Poboxstatus entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('poboxstatus_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Poboxstatus entity.
     *
     * @Route("/{id}/delete", name="poboxstatus_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_POBOXSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Poboxstatus')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Poboxstatus entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('poboxstatus'));
    }

    /**
     * Creates a form to delete a Poboxstatus entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('poboxstatus_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
