<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Location;
use NvCarga\Bundle\Form\LocationType;

/**
 * Location controller.
 *
 * @Route("/location")
 */
class LocationController extends Controller
{

    /**
     * Lists all Location entities.
     *
     * @Route("/index", name="location")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_LOCATION') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_LOCATION')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Location')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Location entity.
     *
     * @Route("/create", name="location_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Location:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_LOCATION') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Location();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
	    $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
	    $position = $entity->getPosition();
	    $ware = $entity->getWarehouse();
	    $location = $em->getRepository('NvCargaBundle:Location')->findOneBy(array('position'=>$position, 'warehouse'=>$ware));
	    if ($location) {
		throw $this->createNotFoundException('Ya existe la ubicación ' .  $location . ' en la bodega ' . $ware);
	    }
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('location_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Location entity.
     *
     * @param Location $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Location $entity)
    {
        $form = $this->createForm(new LocationType(), $entity, array(
            'action' => $this->generateUrl('location_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Location entity.
     *
     * @Route("/new", name="location_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_LOCATION') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Location();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Location entity.
     *
     * @Route("/{id}/show", name="location_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_LOCATION') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_LOCATION')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Location')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Location entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Location entity.
     *
     * @Route("/{id}/edit", name="location_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_LOCATION') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Location')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Location entity.');
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
    * Creates a form to edit a Location entity.
    *
    * @param Location $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Location $entity)
    {
        $form = $this->createForm(new LocationType(), $entity, array(
            'action' => $this->generateUrl('location_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Location entity.
     *
     * @Route("/{id}/update", name="location_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Location:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_LOCATION') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Location')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Location entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('location_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Location entity.
     *
     * @Route("/{id}/delete", name="location_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_LOCATION') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Location')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Location entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('location'));
    }

    /**
     * Creates a form to delete a Location entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('location_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
    /**
     * @Route("/locations", name="select_locations")
     */
    public function ajaxAction(Request $request) {
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        // Get the warehouse ID
        $id = $request->query->get('warehouse_id');
        $result = array();
        // Return a list of locations
        $repo = $this->getDoctrine()->getManager()->getRepository('NvCargaBundle:Location');
        $locations = $repo->findByWarehouse($id, array('position' => 'asc'));
        foreach ($locations as $location) {
            $result[$location->getPosition()] = $location->getId();
        }
        return new JsonResponse($result);
    }
}
