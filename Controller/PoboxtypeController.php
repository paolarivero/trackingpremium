<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Poboxtype;
use NvCarga\Bundle\Form\PoboxtypeType;

/**
 * Poboxtype controller.
 *
 * @Route("/poboxtype")
 */
class PoboxtypeController extends Controller
{

    /**
     * Lists all Poboxtype entities.
     *
     * @Route("/index", name="poboxtype")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_POBOXTYPE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_POBOXTYPE')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Poboxtype')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Poboxtype entity.
     *
     * @Route("/create", name="poboxtype_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Poboxtype:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_POBOXTYPE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Poboxtype();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
	    $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('poboxtype_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Poboxtype entity.
     *
     * @param Poboxtype $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Poboxtype $entity)
    {
        $form = $this->createForm(new PoboxtypeType(), $entity, array(
            'action' => $this->generateUrl('poboxtype_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Poboxtype entity.
     *
     * @Route("/new", name="poboxtype_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_POBOXTYPE') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Poboxtype();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Poboxtype entity.
     *
     * @Route("/{id}/show", name="poboxtype_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_POBOXTYPE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_POBOXTYPE')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Poboxtype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Poboxtype entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Poboxtype entity.
     *
     * @Route("/{id}/edit", name="poboxtype_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_POBOXTYPE') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Poboxtype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Poboxtype entity.');
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
    * Creates a form to edit a Poboxtype entity.
    *
    * @param Poboxtype $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Poboxtype $entity)
    {
        $form = $this->createForm(new PoboxtypeType(), $entity, array(
            'action' => $this->generateUrl('poboxtype_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Poboxtype entity.
     *
     * @Route("/{id}/update", name="poboxtype_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Poboxtype:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_POBOXTYPE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Poboxtype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Poboxtype entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('poboxtype_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Poboxtype entity.
     *
     * @Route("/{id}/delete", name="poboxtype_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_POBOXTYPE') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Poboxtype')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Poboxtype entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('poboxtype'));
    }

    /**
     * Creates a form to delete a Poboxtype entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('poboxtype_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
