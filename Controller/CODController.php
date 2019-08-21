<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\COD;
use NvCarga\Bundle\Form\CODType;

/**
 * COD controller.
 *
 * @Route("/cod")
 */
class CODController extends Controller
{

    /**
     * Lists all COD entities.
     *
     * @Route("/index", name="cod")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_COD') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_COD')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
       
        $entities = $em->getRepository('NvCargaBundle:COD')->findAll(); 

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new COD entity.
     *
     * @Route("/create", name="cod_create")
     * @Method("POST")
     * @Template("NvCargaBundle:COD:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_COD') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new COD();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
	    $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('cod_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a COD entity.
     *
     * @param COD $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(COD $entity)
    {
        $form = $this->createForm(new CODType(), $entity, array(
            'action' => $this->generateUrl('cod_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new COD entity.
     *
     * @Route("/new", name="cod_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_COD') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new COD();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a COD entity.
     *
     * @Route("/{id}/show", name="cod_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_COD') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_COD')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NvCargaBundle:COD')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el COD.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing COD entity.
     *
     * @Route("/{id}/edit", name="cod_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_COD') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:COD')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el  COD.');
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
    * Creates a form to edit a COD entity.
    *
    * @param COD $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(COD $entity)
    {
        $form = $this->createForm(new CODType(), $entity, array(
            'action' => $this->generateUrl('cod_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing COD entity.
     *
     * @Route("/{id}/update", name="cod_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:COD:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_COD') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:COD')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el COD.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('cod_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a COD entity.
     *
     * @Route("/{id}/delete", name="cod_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_COD') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $entity = $em->getRepository('NvCargaBundle:COD')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('No existe el COD.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('cod'));
    }

    /**
     * Creates a form to delete a COD entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('cod_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
