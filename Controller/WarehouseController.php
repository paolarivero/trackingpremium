<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Warehouse;
use NvCarga\Bundle\Form\WarehouseType;

/**
 * Warehouse controller.
 *
 * @Route("/warehouse")
 */
class WarehouseController extends Controller
{

    /**
     * Lists all Warehouse entities.
     *
     * @Route("/index", name="warehouse")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_WAREHOUSE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_WAREHOUSE')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Warehouse')->findByMaincompany($maincompany);

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Warehouse entity.
     *
     * @Route("/create", name="warehouse_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Warehouse:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_WAREHOUSE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Warehouse();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $maincompany = $this->getUser()->getMaincompany();
        
        if ($form->isValid()) {
            $entity->setCreationdate(new \DateTime());
            $entity->setLastupdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $agency = $em->getRepository('NvCargaBundle:Agency')->find($entity->getAgency());
            $entity->setCity($agency->getCity());
            $agency->setWarehouse($entity);
            $entity->setMaincompany($maincompany);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('warehouse_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Warehouse entity.
     *
     * @param Warehouse $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Warehouse $entity)
    {
        $form = $this->createForm(new WarehouseType(), $entity, array(
            'action' => $this->generateUrl('warehouse_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Warehouse entity.
     *
     * @Route("/new", name="warehouse_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_WAREHOUSE') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Warehouse();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Warehouse entity.
     *
     * @Route("/{id}/show", name="warehouse_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_WAREHOUSE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_WAREHOUSE')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Warehouse')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la BODEGA');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Warehouse entity.
     *
     * @Route("/{id}/edit", name="warehouse_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_WAREHOUSE') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Warehouse')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la BODEGA');
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
    * Creates a form to edit a Warehouse entity.
    *
    * @param Warehouse $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Warehouse $entity)
    {
        $form = $this->createForm(new WarehouseType(), $entity, array(
            'action' => $this->generateUrl('warehouse_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        $form->remove('agency');
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Warehouse entity.
     *
     * @Route("/{id}/update", name="warehouse_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Warehouse:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_WAREHOUSE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Warehouse')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la BODEGA');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
	    $entity->setLastupdate(new \DateTime());
            $em->flush();

            return $this->redirect($this->generateUrl('warehouse_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Warehouse entity.
     *
     * @Route("/{id}/delete", name="warehouse_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_WAREHOUSE') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $maincompany = $this->getUser()->getMaincompany();
            $entity = $em->getRepository('NvCargaBundle:Warehouse')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

            if (!$entity) {
                throw $this->createNotFoundException('No existe la BODEGA');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('warehouse'));
    }

    /**
     * Creates a form to delete a Warehouse entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('warehouse_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
