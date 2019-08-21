<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Company;
use NvCarga\Bundle\Form\CompanyType;

/**
 * Company controller.
 *
 * @Route("/company")
 */
class CompanyController extends Controller
{

    /**
     * Lists all Company entities.
     *
     * @Route("/index", name="company")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_COMPANY') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_COMPANY')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Company')->findByMaincompany($maincompany);

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Company entity.
     *
     * @Route("/create", name="company_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Company:new.html.twig")
     * @Security("has_role('ROLE_ADMIN_COMPANY') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Company();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $maincompany = $this->getUser()->getMaincompany();
        $countcompany =  $maincompany->getCountcompanies();
        $plan=$maincompany->getPlan();                 
        if (($plan->getCompans()) && ($countcompany >= $plan->getMaxcompanies())){
            $message = 'Ha llegado al número máximo de COMPAÑÍAS LOCALES permitidas. Para crear más debe actualizar su plan a uno superior.';
            if ($this->isGranted('ROLE_ADMIN')) {
                $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
            }
            $flashBag->add('notice',$message);
            goto next;
            //throw $this->createNotFoundException('La empresa ha alcanzado el número MÁXIMO de COMPAÑÍAS LOCALES permitidas.');
        }
        $countcompany++;
        $maincompany->setCountcompanies($countcompany);
        if ($form->isValid()) {
            $entity->setCreationdate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $company = $em->getRepository('NvCargaBundle:Company')->findOneBy(array('maincompany'=>$maincompany,'country'=> $entity->getCountry(), 'name'=> $entity->getName()));
            if ($company) {
                $message='Ya existe una compañía local con el nombre "' .  $entity->getName() . '" en ' . $entity->getCountry();
                $flashBag->add('notice',$message);
                goto next;
            }
            $entity->setMaincompany($maincompany);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('company'));
        }
        next:
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Company entity.
     *
     * @param Company $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Company $entity)
    {
        $form = $this->createForm(new CompanyType(), $entity, array(
            'action' => $this->generateUrl('company_create'),
            'method' => 'POST',
        ));
        $form->remove('country');
        $form->add('submit', 'submit', array('label' => 'Crear'));
        $form->add('country', 'entity', array('label'=> 'País ',
                'empty_value' => '-- Escoja el País --',
                'class' => 'NvCargaBundle:Country',
                'choices' => $this->getUser()->getMaincompany()->getCountries()->toarray())
            );

        return $form;
    }

    /**
     * Displays a form to create a new Company entity.
     *
     * @Route("/new", name="company_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_COMPANY') or has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Company();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $maincompany = $this->getUser()->getMaincompany();
        $countcompany =  $maincompany->getCountcompanies();
        $plan=$maincompany->getPlan();                 
        if (($plan->getCompans()) && ($countcompany >= $plan->getMaxcompanies())){
            $message = 'Ha llegado al número máximo de COMPAÑÍAS LOCALES permitidas. Para crear más debe actualizar su plan a uno superior.';
            if ($this->isGranted('ROLE_ADMIN')) {
                $message = $message . '<a href="' .  $this->generateUrl('loginname') . '" > Actualizar ahora</a>';
            }
            $flashBag->add('notice',$message);
            //throw $this->createNotFoundException('La empresa ha alcanzado el número MÁXIMO de COMPAÑÍAS LOCALES permitidas.');
        }
        $form   = $this->createCreateForm($entity);
        
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Company entity.
     *
     * @Route("/{id}/show", name="company_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_COMPANY') or has_role('ROLE_ADMIN') or has_role('ROLE_VIEW_COMPANY')")
     */
/*
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Company')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Company entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }
*/
    /**
     * Displays a form to edit an existing Company entity.
     *
     * @Route("/{id}/edit", name="company_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_COMPANY') or has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();

        $entity = $em->getRepository('NvCargaBundle:Company')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe esa compañía local');
        }

        $editForm = $this->createEditForm($entity);
//       $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Company entity.
    *
    * @param Company $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Company $entity)
    {
        $form = $this->createForm(new CompanyType(), $entity, array(
            'action' => $this->generateUrl('company_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
	
        $form->remove('country');
        $form->add('country', null, array('label' => 'País ', 'read_only' => true));
        $form->add('submit', 'submit', array('label' => 'Actuailizar'));

        return $form;
    }
    /**
     * Edits an existing Company entity.
     *
     * @Route("/{id}/update", name="company_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Company:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN_COMPANY') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $maincompany = $this->getUser()->getMaincompany();

        $entity = $em->getRepository('NvCargaBundle:Company')->findOneBy(['id'=>$id,'maincompany'=>$maincompany]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Company entity.');
        }

//        $deleteForm = $this->createDeleteForm($id);
        $currentname = $entity->getName();
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $company = $em->getRepository('NvCargaBundle:Company')->findOneBy(array('maincompany'=>$maincompany,'country'=> $entity->getCountry(), 'name'=> $entity->getName()));
            if (($company) && ($currentname != $entity->getName())) {
                throw $this->createNotFoundException('Ya existe una compañía local con el nombre "' .  $entity->getName() . '" en ' . $entity->getCountry());
            }
            $em->flush();

            return $this->redirect($this->generateUrl('company_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Company entity.
     *
     * @Route("/{id}/delete", name="company_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_COMPANY') or has_role('ROLE_ADMIN')")
     */
/*
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Company')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Company entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('company'));
    }
*/
    /**
     * Creates a form to delete a Company entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
/*
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('company_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
*/
}
