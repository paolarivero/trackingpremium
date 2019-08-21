<?php

namespace NvCarga\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Profile;
use NvCarga\Bundle\Form\ProfileType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

/**
 * Profile controller.
 *
 * @Route("/profile")
 */
class ProfileController extends Controller
{

    /**
     * Lists all Profile entities.
     *
     * @Route("/index", name="profile")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        
        $entities = $em->getRepository('NvCargaBundle:Profile')->findByMaincompany($maincompany);

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Profile entity.
     *
     * @Route("/create", name="profile_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Profile:new.html.twig")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Profile();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $newprofile = $em->getRepository('NvCargaBundle:Profile')->findOneBy(['maincompany' =>$maincompany, 'name'=>$entity->getName()]);
            if ($newprofile) {
                $this->get('session')->getFlashBag()->add('notice',
                            'Ya existe un perfil con el nombre ' . $entity->getName());
                goto next;
                 
            }
            //exit(\Doctrine\Common\Util\Debug::dump(count($entity->getAdmins())));
            $adminrole = $form['adminrole']->getData();
            if ($adminrole) {
                $profiles = $em->getRepository('NvCargaBundle:Profile')->findByMaincompany($maincompany);
                $radmin = $em->getRepository('NvCargaBundle:Role')->findOneByName('ROLE_ADMIN');
                foreach ($profiles as $profile) {
                    if (in_array($radmin, $profile->getRoles()->toArray())) {
                        $this->get('session')->getFlashBag()->add('notice',
                            'Ya existe un perfil llamado "' . $profile->getName() . '" que tiene TODOS LOS PERMISOS.');
                        goto next;
                    }
                }
                $entity->addRole($radmin);
            } else {
                $rolesadmin = $entity->getAdmins();
                $rolesview = $entity->getViews();
                if ((count($rolesadmin) == 0) && (count($rolesview) == 0)) {
                    $this->get('session')->getFlashBag()->add('notice',
                            'Debe asignar al MENOS un ROLE al perfil.');
                    goto next;
                }
                foreach ($rolesadmin as $role) {
                    $entity->addRole($role);
                }
                foreach ($rolesview as $role) {
                    $entity->addRole($role);
                }
            }
            $roleuser = $em->getRepository('NvCargaBundle:Role')->findOneByName('ROLE_USER');
            $entity->addRole($roleuser);
            $entity->setMaincompany($maincompany);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('profile_show', array('id' => $entity->getId())));
        }
        next: 
        
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Profile entity.
     *
     * @param Profile $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Profile $entity)
    {
        $form = $this->createForm(new ProfileType(), $entity, array(
            'action' => $this->generateUrl('profile_create'),
            'method' => 'POST',
        ));
        
        $em = $this->getDoctrine()->getManager();
        $rolesadmis = $em->getRepository('NvCargaBundle:Role')->createQueryBuilder('r')
                    ->where('r.name != :rolemain')
                    ->andWhere('r.name LIKE :admin')
                    ->setParameters(['rolemain'=>'ROLE_ADMIN_MAINCOMPANY', 'admin'=> 'ROLE_ADMIN_%'])
                    ->orderBy('r.name', 'ASC')
                    ->getQuery()
                    ->getResult();
       
        $rolesviews = $em->getRepository('NvCargaBundle:Role')->createQueryBuilder('r')
                    ->where('r.name LIKE :view')
                    ->setParameters(['view'=> 'ROLE_VIEW_%'])
                    ->orderBy('r.name', 'ASC')
                    ->getQuery()
                    ->getResult();
        
        $form->add('admins', 'entity', array('label'=>' ', 'class' => 'NvCargaBundle:Role', 
                    'choices'   => $rolesadmis,
                    'multiple' => true, 'expanded'=>true,
                    /*
                    'choice_label' => function ($category) {
                        //return 'translation[en].' . $category->getDisplayName();
                        return 'Administrador de ' . '%'. $category->getDisplayName() . '%';
                    },
                    */
                    'choice_translation_domain' => false,
                    /*
                    'choice_translation_domain' => 'messages',
                    */
                    /*
                    'choice_attr' => function($val, $key, $index) {
                        // adds a class like attending_yes, attending_no, etc
                        return ['class' => 'icheck'];}
                    */
                    ));
        $form->add('views', 'entity', array('label'=>' ', 'class' => 'NvCargaBundle:Role', 
                    'choices'   => $rolesviews,
                    'multiple' => true, 'expanded'=>true));
        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Profile entity.
     *
     * @Route("/new", name="profile_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Profile();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Profile entity.
     *
     * @Route("/{id}/show", name="profile_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Profile')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('NO existe el PERFIL');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Profile entity.
     *
     * @Route("/{id}/edit", name="profile_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Profile')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('NO existe el PERFIL');
        }
        
        foreach ($entity->getRoles() as $role) {
            if ($role->getName() != 'ROLE_ADMIN') {
                $pos = strpos($role->getName(), 'ROLE_ADMIN_');
                if ($pos === false) {
                    $entity->addView($role);
                } else {
                    $entity->addAdmin($role);
                }
            } 
        }
        
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Profile entity.
    *
    * @param Profile $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Profile $entity)
    {
        $form = $this->createForm(new ProfileType(), $entity, array(
            'action' => $this->generateUrl('profile_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        
        $em = $this->getDoctrine()->getManager();
        $rolesadmis = $em->getRepository('NvCargaBundle:Role')->createQueryBuilder('r')
                    ->where('r.name != :rolemain')
                    ->andWhere('r.name LIKE :admin')
                    ->setParameters(['rolemain'=>'ROLE_ADMIN_MAINCOMPANY', 'admin'=> 'ROLE_ADMIN_%'])
                    ->orderBy('r.name', 'ASC')
                    ->getQuery()
                    ->getResult();
       
        $rolesviews = $em->getRepository('NvCargaBundle:Role')->createQueryBuilder('r')
                    ->where('r.name LIKE :view')
                    ->setParameters(['view'=> 'ROLE_VIEW_%'])
                    ->orderBy('r.name', 'ASC')
                    ->getQuery()
                    ->getResult();
        
                    
        $form->add('admins', 'entity', array('label'=>' ', 'class' => 'NvCargaBundle:Role', 
                    'choices'   => $rolesadmis,
                    'multiple' => true, 'expanded'=>true, 
                    /*
                    'choice_attr' => function($val, $key, $index) {
                        // adds a class like attending_yes, attending_no, etc
                        return ['class' => 'icheck'];}
                    */
                    ));
        $form->add('views', 'entity', array('label'=>' ', 'class' => 'NvCargaBundle:Role', 
                    'choices'   => $rolesviews,
                    'multiple' => true, 'expanded'=>true));
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Profile entity.
     *
     * @Route("/{id}/update", name="profile_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Profile:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $entity = $em->getRepository('NvCargaBundle:Profile')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Profile entity.');
        }
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $name = $entity->getName();
        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            foreach ($entity->getRoles() as $role) {
                $entity->removeRole($role);
            }
            $newprofile = $em->getRepository('NvCargaBundle:Profile')->findOneBy(['maincompany' =>$maincompany, 'name'=>$entity->getName()]);
            if (($newprofile) && ($name != $entity->getName())) {
                 throw $this->createNotFoundException('Ya existe un  perfil con el nombre ' . $entity->getName()); 
            }
            $adminrole = $editForm['adminrole']->getData();
            if ($adminrole) {
                $profiles = $em->getRepository('NvCargaBundle:Profile')->findByMaincompany($maincompany);
                $radmin = $em->getRepository('NvCargaBundle:Role')->findOneByName('ROLE_ADMIN');
                foreach ($profiles as $profile) {
                    if (in_array($radmin, $profile->getRoles()->toArray())) {
                        $this->get('session')->getFlashBag()->add('notice',
                            'Ya existe un perfil llamado "' . $profile->getName() . '" que tiene TODOS LOS PERMISOS.');
                        goto next;
                    }
                }
                $entity->addRole($radmin);
            } else {
                //exit(\Doctrine\Common\Util\Debug::dump(count($entity->getAdmins())));
                $rolesadmin = $entity->getAdmins();
                $rolesview = $entity->getViews();
                if ((count($rolesadmin) == 0) && (count($rolesview) == 0)) {
                    $this->get('session')->getFlashBag()->add('notice',
                            'Debe asignar al MENOS un ROLE al perfil.');
                    goto next;
                }
                foreach ($rolesadmin as $role) {
                    $entity->addRole($role);
                }
                foreach ($rolesview as $role) {
                    $entity->addRole($role);
                }
            }
            $em->flush();

            return $this->redirect($this->generateUrl('profile_show', array('id' => $id)));
        }

        next:
        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Profile entity.
     *
     * @Route("/{id}/delete", name="profile_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Profile')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('NO existe el PERFIL.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('profile'));
    }

    /**
     * Creates a form to delete a Profile entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('profile_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
