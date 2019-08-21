<?php

namespace NvCarga\Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Account;
use NvCarga\Bundle\Form\AccountType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\Validator\Constraints\NotBlank;
/**
 * Account controller.
 *
 * @Route("/account")
 */
class AccountController extends Controller
{

    /**
     * Lists all Account entities.
     *
     * @Route("/", name="account")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser(); 
        $maincompany = $user->getMaincompany();
        if ($user->getAgency()->getType() != "MASTER" ) {
            throw $this->createAccessDeniedException('');
        }
        $entities = $em->getRepository('NvCargaBundle:Account')->findByMaincompany($maincompany);

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Lists all Account entities.
     *
     * @Route("/listpobox", name="account_listpobox")
     * @Method("GET")
     * @Template()
     */
    public function listpoboxAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser(); 
        $maincompany = $user->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Account')->findBy(['isactive'=>TRUE, 'maincompany'=>$maincompany]);

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Account entity.
     *
     * @Route("/create", name="account_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Account:new.html.twig")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $entity = new Account();
        $user = $this->getUser(); 
        $maincompany = $user->getMaincompany();
        $flashBag = $this->get('session')->getFlashBag();
        if ($user->getAgency()->getType() != "MASTER" ) {
            throw $this->createAccessDeniedException('');
        }
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $countaccount = $maincompany->getCountaccounts();
        $plan = $maincompany->getPlan();                  
        if (($plan->getAccounts()) && ($countaccount >= $plan->getMaxaccounts())) {
            $message = 'Ha llegado al número máximo de CUENTAS BANCARIAS permitidas. Para crear más debe actualizar su plan a uno superior.';
            if ($this->isGranted('ROLE_ADMIN')) {
                $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
            }
            $flashBag->add('notice',$message);
            goto next;
            //
            //throw $this->createNotFoundException('La empresa ha alcanzado el número MÁXIMO de CUENTAS BANCARIAS permitidas.');
        }
        
        $countaccount++;
        $maincompany->setCountaccounts($countaccount);
        
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $id = $form->get("cityid")->getData();
            if (!$id) {
                $this->get('session')->getFlashBag()->add(
                            'notice',
                            'Debe seleccionar una ciudad');
                goto next;
                // throw $this->createNotFoundException('Debe seleccionar una ciudad');
            }
            $em = $this->getDoctrine()->getManager();
            $city = $em->getRepository('NvCargaBundle:City')->find($id);
            if (!$city) {
                $this->get('session')->getFlashBag()->add(
                            'notice',
                            'Error.. La ciuda NO EXISTE');
                goto next;
                //throw $this->createNotFoundException('Error.. La ciuda NO EXISTE');
            }
            $entity->setcity($city);
            $entity->setMaincompany($maincompany);
            $entity->setCreationdate(new \DateTime());
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('account_show', array('id' => $entity->getId())));
        }
        
        next:
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Account entity.
     *
     * @param Account $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     * @Security("has_role('ROLE_ADMIN')")
     */
    private function createCreateForm(Account $entity)
    {
        $form = $this->createForm(new AccountType(), $entity, array(
            'action' => $this->generateUrl('account_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Crear'));
	$form->remove('isactive');
        return $form;
    }

    /**
     * Displays a form to create a new Account entity.
     *
     * @Route("/new", name="account_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction()
    {
        $entity = new Account();
        $form   = $this->createCreateForm($entity);
        $user = $this->getUser(); 
        $maincompany = $user->getMaincompany();
        $em = $this->getDoctrine()->getManager();
        if ($user->getAgency()->getType() != "MASTER" ) {
            throw $this->createAccessDeniedException('');
        }
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $countaccount = $maincompany->getCountaccounts();
        $plan = $maincompany->getPlan();                  
        if (($plan->getAccounts()) && ($countaccount >= $plan->getMaxaccounts())) {
            $message = 'Ha llegado al número máximo de CUENTAS BANCARIAS permitidas. Para crear más debe actualizar su plan a uno superior.';
            if ($this->isGranted('ROLE_ADMIN')) {
                $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
            }
            $flashBag->add('notice',$message);
            //
            //throw $this->createNotFoundException('La empresa ha alcanzado el número MÁXIMO de CUENTAS BANCARIAS permitidas.');
        }
        
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Account entity.
     *
     * @Route("/show/{id}", name="account_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Account')->findOneBy(['id'=>$id,'maincompany'=>$this->getUser()->getMaincompany()]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la cuenta.');
        }

        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
           //  'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Finds and displays a Account entity.
     *
     * @Route("/showpobox/{id}", name="account_showpobox")
     * @Method("GET")
     * @Template()
     */
    public function showpoboxAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Account')->findOneBy(['id'=>$id,'maincompany'=>$this->getUser()->getMaincompany()]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la cuenta.');
        }

        // $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            // 'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Displays a form to edit an existing Account entity.
     *
     * @Route("/edit/{id}", name="account_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser(); 
        $maincompany = $user->getMaincompany();
        if ($user->getAgency()->getType() != "MASTER" ) {
            throw $this->createAccessDeniedException('');
        }

        $entity = $em->getRepository('NvCargaBundle:Account')->findOneBy(['id'=>$id,'maincompany'=>$this->getUser()->getMaincompany()]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la cuenta.');
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
    * Creates a form to edit a Account entity.
    *
    * @param Account $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Account $entity)
    {
        $form = $this->createForm(new AccountType(), $entity, array(
            'action' => $this->generateUrl('account_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

       //  $form->remove('number');
        $form->remove('cityid');
        $form->remove('cityname');

        $city=$entity->getCity();
        $namecity = $city->getName() . ' (' . $city->getState()->getName() . ', ' . $city->getState()->getCountry()->getName() . ')' ;
        
        $form->add('cityid', 'hidden', array('mapped'=>false, 'data'=>$city->getId()));
        $form->add('cityname', 'hidden', array('label'=> 'Ciudad ', 'data'=>$namecity, 'read_only' => true, 'mapped'=>false,));
        //                'constraints' => array(new NotBlank(["message" => "Escoja una ciudad"]))));
        //$form->add('state', 'text', array('label'=> 'Estado ',  'data'=>$city->getState(), 'read_only' => true, 'mapped'=>false));
        //$form->add('country', 'text', array('label'=> 'País ',  'data'=>$city->getState()->getCountry(), 'read_only' => true, 'mapped'=>false));

        // $form->add('number', 'text', array('label'=> 'Número de Cuenta ', 'read_only'=> true));
        $form->add('isactive', 'checkbox', array('label' => 'Cuenta activa', 'attr'=>array('class'=>'icheck')));
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing Account entity.
     *
     * @Route("/update/{id}", name="account_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Account:edit.html.twig")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser(); 
        $maincompany = $user->getMaincompany();
        if ($user->getAgency()->getType() != "MASTER" ) {
            throw $this->createAccessDeniedException('');
        }

        $entity = $em->getRepository('NvCargaBundle:Account')->findOneBy(['id'=>$id,'maincompany'=>$this->getUser()->getMaincompany()]);

        if (!$entity) {
            throw $this->createNotFoundException('No existe la cuenta.');
        }

        // $deleteForm = $this->createDeleteForm($id);
        $flashBag = $this->get('session')->getFlashBag();
        foreach ($flashBag->keys() as $type) {
                $flashBag->set($type, array());
        }
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $cid = $editForm->get("cityid")->getData();
            if (!$id) {
                $this->get('session')->getFlashBag()->add(
                            'notice',
                            'Debe seleccionar una ciudad');
                goto next;
                // throw $this->createNotFoundException('Debe seleccionar una ciudad');
            }
            
            if ($cid != $entity->getCity()->getId()) {
                $em = $this->getDoctrine()->getManager();
                $city = $em->getRepository('NvCargaBundle:City')->find($id);
                if (!$city) {
                    $this->get('session')->getFlashBag()->add(
                            'notice',
                            'Error.. La ciuda NO EXISTE');
                    goto next;
                    //throw $this->createNotFoundException('Error.. La ciuda NO EXISTE');
                }
                $entity->setCity($city);
            }
            $em->flush();

            return $this->redirect($this->generateUrl('account_show', array('id' => $id)));
        } else {
            $this->get('session')->getFlashBag()->add(
                            'notice',
                            'Hay errores en los datos..');
        }
        next:
        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            // 'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Account entity.
     *
     * @Route("/{id}", name="account_delete")
     * @Method("DELETE")
     */
/*
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NvCargaBundle:Account')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Account entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('account'));
    }
*/
    /**
     * Creates a form to delete a Account entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
/*
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('account_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
*/
}
