<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Carrier;
use NvCarga\Bundle\Form\CarrierType;

/**
 * Carrier controller.
 *
 * @Route("/carrier")
 */
class CarrierController extends Controller
{

    /**
     * Lists all Carrier entities.
     *
     * @Route("/index", name="carrier")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CARRIER') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_CARRIER')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Carrier')->findByMaincompany($this->getUser()->getMaincompany());

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Carrier entity.
     *
     * @Route("/create", name="carrier_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Carrier:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_CARRIER') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Carrier();
        $form = $this->createCreateForm($entity);
        $maincompany = $this->getUser()->getMaincompany();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $carrier = $em->getRepository('NvCargaBundle:Carrier')->findOneBy(['maincompany'=>$maincompany, 'name'=>$entity->getName()]);
            if ($carrier) {
                throw $this->createNotFoundException('Ya existe un carrier con ese nombre');
            }
            $entity->setMaincompany($maincompany);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('carrier'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Carrier entity.
     *
     * @param Carrier $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Carrier $entity)
    {
        $form = $this->createForm(new CarrierType(), $entity, array(
            'action' => $this->generateUrl('carrier_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Carrier entity.
     *
     * @Route("/new", name="carrier_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CARRIER') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Carrier();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Carrier entity.
     *
     * @Route("/{id}/show", name="carrier_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CARRIER') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_CARRIER')")
     */
    /* public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Carrier')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Carrier entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    } */

    /**
     * Displays a form to edit an existing Carrier entity.
     *
     * @Route("/{id}/edit", name="carrier_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_CARRIER') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Carrier')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe ese carrier');
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
    * Creates a form to edit a Carrier entity.
    *
    * @param Carrier $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Carrier $entity)
    {
        $form = $this->createForm(new CarrierType(), $entity, array(
            'action' => $this->generateUrl('carrier_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        if (($entity->getName() == 'Currier') || ($entity->getName() == 'Reempaque en empresa')) {
            $form->remove('name');
            $form->add('name', 'text', array('label' => 'Nombre', 'data'=>$entity->getName(), 'read_only'=>true));
        }
        $form->add('active', 'checkbox', array('label' => false, 'attr'=>array('class'=>'icheck')));
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Carrier entity.
     *
     * @Route("/{id}/update", name="carrier_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Carrier:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_CARRIER') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany = $this->getUser()->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Carrier')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe ese carrier');
        }

        // $deleteForm = $this->createDeleteForm($id);
        $oldname = $entity->getName();
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $carrier = $em->getRepository('NvCargaBundle:Carrier')->findOneBy(['maincompany'=>$maincompany, 'name'=>$entity->getName()]);
            if (($carrier) && ($oldname != $entity->getName())) {
                throw $this->createNotFoundException('Ya existe un carrier con ese nombre');
            }
            $em->flush();
            
            return $this->redirect($this->generateUrl('carrier'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            // 'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Carrier entity.
     *
     * @Route("/{id}/delete", name="carrier_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_CARRIER') or has_role('ROLE_ADMIN')")
     */
    /* public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Carrier')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Carrier entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('carrier'));
    } */

    /**
     * Creates a form to delete a Carrier entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    /* private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('carrier_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    } */
}
