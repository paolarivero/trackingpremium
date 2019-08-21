<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Userstatus;
use NvCarga\Bundle\Form\UserstatusType;

/**
 * Userstatus controller.
 *
 * @Route("/userstatus")
 */
class UserstatusController extends Controller
{

    /**
     * Lists all Userstatus entities.
     *
     * @Route("/index", name="userstatus")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_USERSTATUS') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_USERSTATUS')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Userstatus')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Userstatus entity.
     *
     * @Route("/create", name="userstatus_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Userstatus:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_USERSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Userstatus();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
	    $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('userstatus_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Userstatus entity.
     *
     * @param Userstatus $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Userstatus $entity)
    {
        $form = $this->createForm(new UserstatusType(), $entity, array(
            'action' => $this->generateUrl('userstatus_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Userstatus entity.
     *
     * @Route("/new", name="userstatus_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_USERSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Userstatus();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Userstatus entity.
     *
     * @Route("/{id}/show", name="userstatus_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_USERSTATUS') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_USERSTATUS')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Userstatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Userstatus entity.');
        }

        return array(
            'entity'      => $entity,
        );
    }

    /**
     * Displays a form to edit an existing Userstatus entity.
     *
     * @Route("/{id}/edit", name="userstatus_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_USERSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Userstatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Userstatus entity.');
        }

        $editForm = $this->createEditForm($entity);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Userstatus entity.
    *
    * @param Userstatus $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Userstatus $entity)
    {
        $form = $this->createForm(new UserstatusType(), $entity, array(
            'action' => $this->generateUrl('userstatus_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
	$form->remove(name);
	$form->add('name', 'text', array('label' => 'Nombre del status ', 'read_only' => true));
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Userstatus entity.
     *
     * @Route("/{id}/update", name="userstatus_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Userstatus:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_USERSTATUS') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Userstatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Userstatus entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('userstatus_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }
}
