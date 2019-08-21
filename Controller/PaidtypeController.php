<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Paidtype;
use NvCarga\Bundle\Form\PaidtypeType;

/**
 * Paidtype controller.
 *
 * @Route("/paidtype")
 */
class PaidtypeController extends Controller
{

    /**
     * Lists all Paidtype entities.
     *
     * @Route("/index", name="paidtype")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_PAIDTYPE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_PAIDTYPE')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        
        $entities = $em->getRepository('NvCargaBundle:Paidtype')->findBy(['maincompany'=>$maincompany, 'deleted'=>false]);

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Paidtype entity.
     *
     * @Route("/create", name="paidtype_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Paidtype:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_PAIDTYPE') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Paidtype();
        $form = $this->createCreateForm($entity);
        $maincompany = $this->getUser()->getMaincompany();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $valname = $em->getRepository('NvCargaBundle:Paidtype')->findOneBy(array('maincompany'=>$maincompany, 'name'=>$entity->getName(), 'deleted'=>false));
            if ($valname) {
                throw $this->createNotFoundException('Ya existe un tipo de pago con ese nombre');
            }
            $oldpaid = $em->getRepository('NvCargaBundle:Paidtype')->findOneBy(array('maincompany'=>$maincompany, 'name'=>$entity->getName(), 'deleted'=>true));
            // exit(\Doctrine\Common\Util\Debug::dump($oldpaid));
            if ($oldpaid) {
                $oldpaid->setDeleted(false);
                $oldpaid->setActive(true);
                $oldpaid->setDescription($entity->getDescription());
                $oldpaid->setMaincompany($maincompany);
                $oldpaid->setCreationdate(new \DateTime());
            } else {
                $entity->setMaincompany($maincompany);
                $em->persist($entity);
            }
            
            $em->flush();

            return $this->redirect($this->generateUrl('paidtype'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Paidtype entity.
     *
     * @param Paidtype $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Paidtype $entity)
    {
        $form = $this->createForm(new PaidtypeType(), $entity, array(
            'action' => $this->generateUrl('paidtype_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Paidtype entity.
     *
     * @Route("/new", name="paidtype_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_PAIDTYPE') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Paidtype();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Paidtype entity.
     *
     * @Route("/{id}/show", name="paidtype_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_PAIDTYPE') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_PAIDTYPE')")
     */
/*
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Paidtype')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el tipo de pago');
        }

        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            // 'delete_form' => $deleteForm->createView(),
        );
    }
*/
    /**
     * Displays a form to edit an existing Paidtype entity.
     *
     * @Route("/{id}/edit", name="paidtype_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_PAIDTYPE') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Paidtype')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el tipo de pago');
        }

        $editForm = $this->createEditForm($entity);
        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            // 'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Paidtype entity.
    *
    * @param Paidtype $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Paidtype $entity)
    {
        $form = $this->createForm(new PaidtypeType(), $entity, array(
            'action' => $this->generateUrl('paidtype_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Paidtype entity.
     *
     * @Route("/{id}/update", name="paidtype_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Paidtype:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_PAIDTYPE') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Paidtype')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el tipo de pago');
        }
        $currentname = $entity->getName();
        // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
	

        if ($editForm->isValid()) {
            $valname = $em->getRepository('NvCargaBundle:Paidtype')->findOneBy(array('maincompany'=>$maincompany, 'name'=>$entity->getName(), 'deleted'=>false));
            if (($valname) && ($currentname != $entity->getName())) {
                throw $this->createNotFoundException('Ya existe un tipo de pago con ese nombre');
            }
            $em->flush();
	    // exit(\Doctrine\Common\Util\Debug::dump($entity));
            return $this->redirect($this->generateUrl('paidtype'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            // 'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Paidtype entity.
     *
     * @Route("/{id}/delete", name="paidtype_delete")
     * @Security("has_role('ROLE_ADMIN_PAIDTYPE') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Paidtype')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);
        if (!$entity) {
        	throw $this->createNotFoundException('No existe el tipo de pago');
        }

        $entity->setDeleted(true);
        $em->flush();

        return $this->redirect($this->generateUrl('paidtype'));
    }

    /**
     * Creates a form to delete a Paidtype entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
/*
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('paidtype_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
*/
}
