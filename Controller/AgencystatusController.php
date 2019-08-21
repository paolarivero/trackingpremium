<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Agencystatus;
use NvCarga\Bundle\Form\AgencystatusType;

/**
 * Agencystatus controller.
 *
 * @Route("/agencystatus")
 */
class AgencystatusController extends Controller
{

    /**
     * Lists all Agencystatus entities.
     *
     * @Route("/index", name="agencystatus")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_AGENCYSTATUS') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_AGENCYSTATUS')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Agencystatus')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Agencystatus entity.
     *
     * @Route("/create", name="agencystatus_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Agencystatus:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_AGENCYSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Agencystatus();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
	    $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('agencystatus_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Agencystatus entity.
     *
     * @param Agencystatus $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Agencystatus $entity)
    {
        $form = $this->createForm(new AgencystatusType(), $entity, array(
            'action' => $this->generateUrl('agencystatus_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Agencystatus entity.
     *
     * @Route("/new", name="agencystatus_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_AGENCYSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Agencystatus();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Agencystatus entity.
     *
     * @Route("/{id}/show", name="agencystatus_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_AGENCYSTATUS') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_AGENCYSTATUS')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Agencystatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Agencystatus entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Agencystatus entity.
     *
     * @Route("/{id}/edit", name="agencystatus_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_AGENCYSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Agencystatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Agencystatus entity.');
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
    * Creates a form to edit a Agencystatus entity.
    *
    * @param Agencystatus $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Agencystatus $entity)
    {
        $form = $this->createForm(new AgencystatusType(), $entity, array(
            'action' => $this->generateUrl('agencystatus_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Agencystatus entity.
     *
     * @Route("/{id}/update", name="agencystatus_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Agencystatus:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_AGENCYSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Agencystatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Agencystatus entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('agencystatus_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Agencystatus entity.
     *
     * @Route("/{id}/delete", name="agencystatus_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_AGENCYSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Agencystatus')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Agencystatus entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('agencystatus'));
    }

    /**
     * Creates a form to delete a Agencystatus entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('agencystatus_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
