<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Guidestatus;
use NvCarga\Bundle\Form\GuidestatusType;

/**
 * Guidestatus controller.
 *
 * @Route("/guidestatus")
 */
class GuidestatusController extends Controller
{

    /**
     * Lists all Guidestatus entities.
     *
     * @Route("/index", name="guidestatus")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDESTATUS') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDESTATUS')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Guidestatus')->findBy([], ['position' => 'ASC']);

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Guidestatus entity.
     *
     * @Route("/create", name="guidestatus_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Guidestatus:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDESTATUS') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Guidestatus();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        
        $status = $em->getRepository('NvCargaBundle:Guidestatus')->createQueryBuilder('c')
                    ->addOrderBy('c.position', 'ASC')
                    ->getQuery()
                    ->getResult(); 

        if ($form->isValid()) {
            $position = $form['thepos']->getData();
            $rstatus = array_reverse($status);
            foreach ($rstatus as $st) {
                if ($st->getPosition() >= $position) {
                    $pos = $st->getPosition();
                    $st->setPosition($pos+1);
                    $em->flush();
                }
            }
            $entity->setPosition($position);
            $entity->setCreationdate(new \DateTime());
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('guidestatus_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Guidestatus entity.
     *
     * @param Guidestatus $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Guidestatus $entity)
    {
        $form = $this->createForm(new GuidestatusType(), $entity, array(
            'action' => $this->generateUrl('guidestatus_create'),
            'method' => 'POST',
        ));
	
        $form->add('thepos', 'hidden', array('mapped'=>false));
        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Guidestatus entity.
     *
     * @Route("/new", name="guidestatus_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDESTATUS') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Guidestatus();
        $form   = $this->createCreateForm($entity);
        $em = $this->getDoctrine()->getManager();
        
        $status = $em->getRepository('NvCargaBundle:Guidestatus')->createQueryBuilder('c')
                    ->orderBy('c.position', 'ASC')
                    ->getQuery()
                    ->getResult();
        return array(
	    'status' => $status,
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Guidestatus entity.
     *
     * @Route("/{id}/show", name="guidestatus_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDESTATUS') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_GUIDESTATUS')")
     */
   /* public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Guidestatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Guidestatus entity.');
        }

        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
           // 'delete_form' => $deleteForm->createView(),
        );
    } */

    /**
     * Displays a form to edit an existing Guidestatus entity.
     *
     * @Route("/{id}/edit", name="guidestatus_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_GUIDESTATUS') or has_role('ROLE_ADMIN')")
     */
    /* public function editAction($id)
    {
	
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Guidestatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Guidestatus entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);
	$status = $em->getRepository('NvCargaBundle:Guidestatus')->createQueryBuilder('c')
			->orderBy('c.position', 'ASC')
    			->getQuery()
			->getResult();

        return array(
	    'status' => $status,
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
	
    } */

    /**
    * Creates a form to edit a Guidestatus entity.
    *
    * @param Guidestatus $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
   /* private function createEditForm(Guidestatus $entity)
    {
	
        $form = $this->createForm(new GuidestatusType(), $entity, array(
            'action' => $this->generateUrl('guidestatus_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
	
    } */

    /**
     * Edits an existing Guidestatus entity.
     *
     * @Route("/{id}/update", name="guidestatus_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Guidestatus:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_GUIDESTATUS') or has_role('ROLE_ADMIN')")
     */
    /* public function updateAction(Request $request, $id)
    { 
	
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Guidestatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Guidestatus entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('guidestatus_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
	
    } */
    /**
     * Deletes a Guidestatus entity.
     *
     * @Route("/{id}/delete", name="guidestatus_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_GUIDESTATUS') or has_role('ROLE_ADMIN')")
     */
   /* public function deleteAction(Request $request, $id)
    { 
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Guidestatus')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Guidestatus entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('guidestatus'));
	
    }*/

    /**
     * Creates a form to delete a Guidestatus entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    /* private function createDeleteForm($id)
    { 
	
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('guidestatus_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
	
    } */
}
