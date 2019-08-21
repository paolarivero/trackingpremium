<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Localcompany;
use NvCarga\Bundle\Form\LocalcompanyType;

/**
 * Localcompany controller.
 *
 * @Route("/localcompany")
 */
class LocalcompanyController extends Controller
{

    /**
     * Lists all Localcompany entities.
     *
     * @Route("/index", name="localcompany")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_LOCALCOMPANY') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_LOCALCOMPANY')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user= $this->getUser();
        $maincompany = $user->getMaincompany();
        
        $entities = $em->getRepository('NvCargaBundle:Localcompany')->findByMaincompany($maincompany);
        $listall = $maincompany->getCountries();
        $countries = array();
        $count = 0;
        foreach ($listall as $country) {
            $countries[$count]['id'] = $country->getName();
            $countries[$count]['name'] = $country->getName();
            $count++;
        }
        return array(
            'entities' => $entities,
            'countries' => $countries,
        );

    }
    /**
     * Creates a new Localcompany entity.
     *
     * @Route("/create", name="localcompany_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Localcompany:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_LOCALCOMPANY') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Localcompany();
        $user= $this->getUser();
        $maincompany = $user->getMaincompany();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $local = $em->getRepository('NvCargaBundle:Localcompany')->findOneBy(array('country' => $entity->getCountry(), 'name' => $entity->getName(), 'maincompany'=>$maincompany));
            if ($local) {
                throw $this->createNotFoundException('Ya existe una empresa local con ese nombre en ese país');
            }
            $entity->setMaincompany($maincompany);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('localcompany'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Localcompany entity.
     *
     * @param Localcompany $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Localcompany $entity)
    {
        $form = $this->createForm(new LocalcompanyType(), $entity, array(
            'action' => $this->generateUrl('localcompany_create'),
            'method' => 'POST',
        ));
        $maincompany = $this->getUser()->getMaincompany();
        $form->add('country',  'entity', array('label' => 'País ', 'class' => 'NvCargaBundle:Country', 'choices'=>$maincompany->getCountries()->toArray() ));
        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Localcompany entity.
     *
     * @Route("/new", name="localcompany_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_LOCALCOMPANY') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Localcompany();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Localcompany entity.
     *
     * @Route("/{id}/show", name="localcompany_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_LOCALCOMPANY') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_LOCALCOMPANY')")
     */
    /* public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Localcompany')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Localcompany entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    } */

    /**
     * Displays a form to edit an existing Localcompany entity.
     *
     * @Route("/{id}/edit", name="localcompany_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_LOCALCOMPANY') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user= $this->getUser();
        $maincompany = $user->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Localcompany')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el transportista local');
        }

        $editForm = $this->createEditForm($entity);
        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
           //'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Localcompany entity.
    *
    * @param Localcompany $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Localcompany $entity)
    {
        $form = $this->createForm(new LocalcompanyType(), $entity, array(
            'action' => $this->generateUrl('localcompany_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        
        $form->remove('country');
        if ($entity->getName() == 'Empresa') {
            $form->remove('name');
            $form->add('name', 'text', array('label' => 'Nombre ', 'data'=>$entity->getName(), 'read_only'=> true));
            $form->add('country',  'entity', array('label' => 'País ', 'class' => 'NvCargaBundle:Country', 'data'=>$entity->getCountry(), 'read_only'=> true, 'disabled'=>true ));
        } else {
            $form->add('country',  'entity', array('label' => 'País ', 'class' => 'NvCargaBundle:Country', 'choices'=>$entity->getMaincompany()->getCountries()->toArray(), 'data'=>$entity->getCountry(), ));
        }
        //
        $form->add('active', 'checkbox', array('label' => false, 'attr'=>array('class'=>'icheck')));
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Localcompany entity.
     *
     * @Route("/{id}/update", name="localcompany_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Localcompany:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_LOCALCOMPANY') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $user= $this->getUser();
        $maincompany = $user->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Localcompany')->findOneBy(['id'=>$id, 'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el transportista local');
        }
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        // $deleteForm = $this->createDeleteForm($id);
        $oldname = $entity->getName();
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            if ($oldname != $entity->getName()) {
                $company = $em->getRepository('NvCargaBundle:Localcompany')->findOneBy(['name'=>$entity->getName(), 'maincompany'=>$maincompany]);
                if ($company) {
                    $this->get('session')->getFlashBag()->add('notice',
                            'Ya tiene un transportista local con ese nombre');
                    goto next;
                }
            }
            $em->flush();
            
            return $this->redirect($this->generateUrl('localcompany'));
        }
        
        next:
        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
           // 'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Localcompany entity.
     *
     * @Route("/{id}/delete", name="localcompany_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_LOCALCOMPANY') or has_role('ROLE_ADMIN')")
     */
    /* public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Localcompany')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Localcompany entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('localcompany'));
    } */

    /**
     * Creates a form to delete a Localcompany entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    /* private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('localcompany_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    } */
}
