<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Servicetype;
use NvCarga\Bundle\Form\ServicetypeType;

/**
 * Servicetype controller.
 *
 * @Route("/servicetype")
 */
class ServicetypeController extends Controller
{

    /**
     * Lists all Servicetype entities.
     *
     * @Route("/{idag}/index", name="servicetype")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_SERVICETYPE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_SERVICETYPE')")
     */
    public function indexAction($idag)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $user->getAgency();
        if (($agency->getType() != 'MASTER' ) && ($agency->getId() != $idag)) {
            throw $this->createNotFoundException('No tiene permisos para consultar los servicios de esta agencia');
        }
        $agency = $em->getRepository('NvCargaBundle:Agency')->find($idag);
        $entities = $em->getRepository('NvCargaBundle:Servicetype')->findByAgency($agency);
        //$entities = $em->getRepository('NvCargaBundle:Servicetype')->findByAgency(1);

        return array(
            'entities' => $entities,
            'agency' => $agency,
        );
    }
    /**
     * Creates a new Servicetype entity.
     *
     * @Route("/{idag}/create", name="servicetype_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Servicetype:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_SERVICETYPE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request, $idag)
    {
        $entity = new Servicetype();
        $form = $this->createCreateForm($entity,$idag);
        $form->handleRequest($request);
        $user = $this->getUser();
        $agency = $user->getAgency();

        if ($form->isValid()) {
            $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            if (($agency->getType() != 'MASTER' ) && ($agency->getId() != $idag)) {
                throw $this->createNotFoundException('No tiene permisos para crear servicios en esta agencia');
            }
            $agency = $em->getRepository('NvCargaBundle:Agency')->find($idag);
            $service = $em->getRepository('NvCargaBundle:Servicetype')->findOneBy(array('name' =>$entity->getName(), 
                                            'agency' => $agency, 
                                            'shippingtype' => $entity->getShippingtype()));
            if ($service) {
                throw $this->createNotFoundException('Ya existe un sevicio con ese nombre y ese tipo de envío en esa agencia');
            }
            $entity->setAgency($agency);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('servicetype', array('idag' => $idag)));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'agency' => $agency,
        );
    }

    /**
     * Creates a form to create a Servicetype entity.
     *
     * @param Servicetype $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Servicetype $entity, $idag)
    {
        $form = $this->createForm(new ServicetypeType(), $entity, array(
            'action' => $this->generateUrl('servicetype_create', array('idag' => $idag)),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Servicetype entity.
     *
     * @Route("/{idag}/new", name="servicetype_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_SERVICETYPE') or has_role('ROLE_ADMIN')")
     */
    public function newAction($idag)
    {
        $entity = new Servicetype();
        $form   = $this->createCreateForm($entity,$idag);
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $agency = $user->getAgency();
        if (($agency->getType() != 'MASTER' ) && ($agency->getId() != $idag)) {
            throw $this->createNotFoundException('No tiene permisos para crear servicios de esta agencia');
        }
        $agency = $em->getRepository('NvCargaBundle:Agency')->find($idag);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'agency' => $agency,
        );
    }

    /**
     * Finds and displays a Servicetype entity.
     *
     * @Route("/{id}/show", name="servicetype_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_SERVICETYPE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_SERVICETYPE')")
     */
    /* public function showAction($id)
    { 
	
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Servicetype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Servicetype entity.');
        }

        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            // 'delete_form' => $deleteForm->createView(),
        );
	
    } */

    /**
     * Displays a form to edit an existing Servicetype entity.
     *
     * @Route("/{id}/edit", name="servicetype_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_SERVICETYPE') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Servicetype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Servicetype entity.');
        }
        $user = $this->getUser();
        $agency = $user->getAgency();
        if (($agency->getType() != 'MASTER' ) && ($agency != $entity->getAgency())) {
            throw $this->createNotFoundException('No tiene permisos para editar servicios de esta agencia');
        }
        $editForm = $this->createEditForm($entity);
        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'agency' => $agency,             
            // 'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Servicetype entity.
    *
    * @param Servicetype $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Servicetype $entity)
    {
        $form = $this->createForm(new ServicetypeType(), $entity, array(
            'action' => $this->generateUrl('servicetype_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->remove('shippingtype');
        $form->add('shippingtype', null, array('label' => 'Tipo de envío ', 'data'=>$entity->getShippingtype(), 'read_only'=>true, 
        'disabled'=>true));
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Servicetype entity.
     *
     * @Route("/{id}/update", name="servicetype_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Servicetype:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_SERVICETYPE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Servicetype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Servicetype entity.');
        }
        $user = $this->getUser();
        $agency = $user->getAgency();
        if (($agency->getType() != 'MASTER' ) && ($agency != $entity->getAgency())) {
            throw $this->createNotFoundException('No tiene permisos para editar servicios de esta agencia');
        }
        // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('servicetype', array('idag' => $entity->getAgency()->getId())));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'agency' => $agency, 
            // 'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Servicetype entity.
     *
     * @Route("/{id}/delete", name="servicetype_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_SERVICETYPE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_SERVICETYPE')")
     */
    /* public function deleteAction(Request $request, $id)
    {
	
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Servicetype')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Servicetype entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('servicetype'));

    } */

    /**
     * Creates a form to delete a Servicetype entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('servicetype_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
    /**
     * @Route("/services", name="select_services")
     */
    public function ajaxAction(Request $request) {
        //require __DIR__.'/../../../../vendor/autoload.php';
        if (! $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
        // Get the agency ID
        $id = $request->query->get('agency_id');
        // exit(\Doctrine\Common\Util\Debug::dump($id));
        $result = array();
        // Return a list of services
        $repo = $this->getDoctrine()->getManager()->getRepository('NvCargaBundle:Servicetype');
        $services = $repo->findByAgency($id, array('name' => 'asc'));
        foreach ($services as $service) {
            $result[$service->getName()] = $service->getId();
        }
        return new JsonResponse($result);
    }
}
