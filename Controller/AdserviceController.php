<?php

namespace NvCarga\Bundle\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Adservice;
use NvCarga\Bundle\Form\AdserviceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Adservice controller.
 *
 * @Route("/adservice")
 */
class AdserviceController extends Controller
{

    /**
     * Lists all Adservice entities.
     *
     * @Route("/index", name="adservice")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Adservice')->findByMaincompany($maincompany);

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Adservice entity.
     *
     * @Route("/create", name="adservice_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Adservice:new.html.twig")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Adservice();
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser(); 
        if ($user->getAgency()->getType() != "MASTER" ) {
            throw $this->createAccessDeniedException('');
        }
        $maincompany = $user->getMaincompany();
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $countadserv =  $maincompany->getCountadservices();
        $plan=$maincompany->getPlan();
        if (($plan->getAdservices()) && ($countadserv >= $plan->getMaxadservices())) {
            $message = 'Ha llegado al número máximo de SERVICIOS ADICIONALES permitidos. Para crear más debe actualizar su plan a uno superior.';
            if ($this->isGranted('ROLE_ADMIN')) {
                $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
            }
            $flashBag->add('notice',$message);
            goto next;
            //throw $this->createNotFoundException('La empresa ha alcanzado el número MÁXIMO de SERVICIOS ADICONALES  permitidos.');
        }
        
        $countadserv++;
        $maincompany->setCountadservices($countadserv);
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        
                            
        if ($form->isValid()) {
            
            // $services = $form['services']->getData();
            $services = $entity->getMedependof();
            if ((count($services) > 0) && ($entity->getMeasure() != '%')) {
                throw $this->createNotFoundException('Cuando depende de otros servicios la medida debe ser en PORCENTAJE');
            } 
            foreach ($services as $service) {
                $depend = $service->getMeDependof(); 
                foreach ($depend as $mydepen) {
                    $listdepend = $mydepen->getMeDependof(); 
                    // exit(\Doctrine\Common\Util\Debug::dump($listdepend));
                    if (count($listdepend) > 0) {
                        throw $this->createNotFoundException('No se aceptan dependencias de TERCER ORDEN...');
                    }
                }
            }
            foreach ($services as $service) {
                $depend = $service->getMeDependof(); 
                // $entity->addMeDependof($service);
                $service->addDependofMe($entity);
            }
            // exit(\Doctrine\Common\Util\Debug::dump($services));
            $entity->setCreationdate(new \DateTime());
            $entity->setMaincompany($maincompany);
            $countadserv++;
            $maincompany->setCountadservices($countadserv);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('adservice_show', array('id' => $entity->getId())));
        }
        
        next:
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Adservice entity.
     *
     * @param Adservice $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Adservice $entity)
    {
        $form = $this->createForm(new AdserviceType(), $entity, array(
            'action' => $this->generateUrl('adservice_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));

        return $form;
    }

    /**
     * Displays a form to create a new Adservice entity.
     *
     * @Route("/new", name="adservice_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Adservice();
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser(); 
        $maincompany = $user->getMaincompany();
        if ($user->getAgency()->getType() != "MASTER" ) {
            throw $this->createAccessDeniedException('');
        }
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $countadserv =  $maincompany->getCountadservices();
        $plan=$maincompany->getPlan();
        if (($plan->getAdservices()) && ($countadserv >= $plan->getMaxadservices())) {
            $message = 'Ha llegado al número máximo de SERVICIOS ADICIONALES permitidos. Para crear más debe actualizar su plan a uno superior.';
            if ($this->isGranted('ROLE_ADMIN')) {
                $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
            }
            $flashBag->add('notice',$message);
            //goto next;
            //throw $this->createNotFoundException('La empresa ha alcanzado el número MÁXIMO de SERVICIOS ADICONALES  permitidos.');
        }
        $form   = $this->createCreateForm($entity);
        

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Adservice entity.
     *
     * @Route("/{id}/show", name="adservice_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Adservice')->findOneBy(['id'=>$id,'maincompany'=>$this->getUser()->getMaincompany()]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el servicio adicional');
        }

        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            // 'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Adservice entity.
     *
     * @Route("/{id}/edit", name="adservice_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser(); 
        if ($user->getAgency()->getType() != "MASTER" ) {
            throw $this->createAccessDeniedException('');
        }

        $entity = $em->getRepository('NvCargaBundle:Adservice')->findOneBy(['id'=>$id,'maincompany'=>$this->getUser()->getMaincompany()]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el servicio adicional');
        }

        $editForm = $this->createEditForm($entity);
        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
           //  'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Adservice entity.
    *
    * @param Adservice $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Adservice $entity)
    {
        $form = $this->createForm(new AdserviceType(), $entity, array(
            'action' => $this->generateUrl('adservice_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        $id = $entity->getId();
        $form->add('submit', 'submit', array('label' => 'Actualizar'));
        $form->remove('meDependof');
        $form->add('meDependof', 'entity',  array('required' => false, 'label' => 'Depende de servicios ', 
                        'class' => 'NvCargaBundle:Adservice', 
                        'multiple' => true, 'expanded' => true,  'property' => 'name',
                        'choice_attr' => function($key, $value, $index) use ( $id ) {
                                    $disabled = false;
                                    if ($index == $id) {
                                        $disabled = true;
                                    } // set disabled to true based on the value, key or index of the choice...

                            return $disabled ? ['disabled' => 'disabled'] : [];}));

        return $form;
    }
    /**
     * Edits an existing Adservice entity.
     *
     * @Route("/{id}/update", name="adservice_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Adservice:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser(); 
        if ($user->getAgency()->getType() != "MASTER" ) {
            throw $this->createAccessDeniedException('');
        }
        $entity = $em->getRepository('NvCargaBundle:Adservice')->findOneBy(['id'=>$id,'maincompany'=>$this->getUser()->getMaincompany()]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el servicio adicional');
        }
	
        // $deleteForm = $this->createDeleteForm($id);
        $oldservices = $entity->getMedependof();
        $theolds = array();
        foreach ($oldservices as $oldservice) {
            $theolds[]=$oldservice->getId();	
        } 
        // exit(\Doctrine\Common\Util\Debug::dump($oldservices)); 
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
	
        if ($editForm->isValid()) {
            //$services = $entity->getMedependof();
            $services = $editForm['meDependof']->getData();
            $dependofme = $entity->getDependofMe();
            foreach ($dependofme as $service) {
                // exit(\Doctrine\Common\Util\Debug::dump($dependofme));
                foreach ($services as $newdepend) {
                    if ($newdepend->getId() === $service->getId()) {
                        throw $this->createNotFoundException('El servicio ' . $service->getName() . ' depende de ' . $entity->getName() . '. NO SE PUEDEN CREAR CICLOS DE DEPENDENCIA..');
                    }
                }   
                $secondepend = $service->getDependofMe();
                foreach ($secondepend as $secondservice ) {
                    foreach ($services as $newdepend) {
                        if ($newdepend->getId() === $secondservice->getId()) {
                            throw $this->createNotFoundException('NO SE PUEDEN CREAR CICLOS DE DEPENDENCIA..');
                        }
                    }
                }
            }
        
            foreach ($theolds as $oldservice) {
                $stay = false;
                $thisserv = $em->getRepository('NvCargaBundle:Adservice')->find($oldservice);
                foreach ($services as $service) {
                    if ($service->getId() == $oldservice) {
                        $stay = true;
                    }
                }
                if ($stay == false) {
                    $entity->removeMeDependof($thisserv);
                    $thisserv->removeDependofMe($entity);
                }
            } 
        
            foreach ($services as $service) {
                $stay = false;
                foreach ($theolds as $oldservice) {
                    if ($service->getId() == $oldservice) {
                        $stay = true;
                    }
                }
                if ($stay == false) {
                    $entity->addMeDependof($service);
                    $service->addDependofMe($entity);
                }
            } 
            $em->flush();

            return $this->redirect($this->generateUrl('adservice_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            // 'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Adservice entity.
     *
     * @Route("/{id}/delete", name="adservice_delete")
     * @Method("DELETE")
     */
/*
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Adservice')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Adservice entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('adservice'));
    }
*/
    /**
     * Creates a form to delete a Adservice entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
/*
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('adservice_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
*/
}
