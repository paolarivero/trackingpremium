<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Agencytype;
use NvCarga\Bundle\Form\AgencytypeType;

/**
 * Agencytype controller.
 *
 * @Route("/agencytype")
 */
class AgencytypeController extends Controller
{

    /**
     * Lists all Agencytype entities.
     *
     * @Route("/index", name="agencytype")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_AGENCYTYPE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_AGENCYTYPE')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Agencytype')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Agencytype entity.
     *
     * @Route("/create", name="agencytype_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Agencytype:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_AGENCYTYPE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Agencytype();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
	    $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('agencytype_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Agencytype entity.
     *
     * @param Agencytype $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Agencytype $entity)
    {
        $form = $this->createForm(new AgencytypeType(), $entity, array(
            'action' => $this->generateUrl('agencytype_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Agencytype entity.
     *
     * @Route("/new", name="agencytype_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_AGENCYTYPE') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Agencytype();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Agencytype entity.
     *
     * @Route("/{id}/show", name="agencytype_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_AGENCYTYPE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_AGENCYTYPE')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Agencytype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Agencytype entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Agencytype entity.
     *
     * @Route("/{id}/edit", name="agencytype_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_AGENCYTYPE') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Agencytype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Agencytype entity.');
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
    * Creates a form to edit a Agencytype entity.
    *
    * @param Agencytype $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Agencytype $entity)
    {
        $form = $this->createForm(new AgencytypeType(), $entity, array(
            'action' => $this->generateUrl('agencytype_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Agencytype entity.
     *
     * @Route("/{id}/update", name="agencytype_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Agencytype:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_AGENCYTYPE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Agencytype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Agencytype entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('agencytype_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Agencytype entity.
     *
     * @Route("/{id}/delete", name="agencytype_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_AGENCYTYPE') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Agencytype')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Agencytype entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('agencytype'));
    }

    /**
     * Creates a form to delete a Agencytype entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('agencytype_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
