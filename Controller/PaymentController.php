<?php

namespace NvCarga\Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Payment;
use NvCarga\Bundle\Entity\Bill;
use NvCarga\Bundle\Form\PaymentType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\Validator\Constraints\NotBlank;

// For login a user programatically 
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Payment controller.
 *
 * @Route("/payment")
 */
class PaymentController extends Controller
{

    /**
     * Lists all Payment entities.
     *
     * @Route("/", name="payment")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        
        $accounts = $em->getRepository('NvCargaBundle:Account')->findByMaincompany($maincompany);
        $entities = $em->getRepository('NvCargaBundle:Payment')->findByAccount($accounts);

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Lists all Payment entities.
     *
     * @Route("/byverify", name="payment_byverify")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function byverifyAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NvCargaBundle:Payment')->findByVerified(FALSE);

        return array(
            'entities' => $entities,
        );
    }
   /**
     * Lists all Payment entities.
     *
     * @Route("/list", name="payment_list")
     * @Method("GET")
     * @Template("NvCargaBundle:Payment:index.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        // exit(\Doctrine\Common\Util\Debug::dump($user));
        $pobox = $user->getPobox();
        if (!$pobox) {
            throw $this->createNotFoundException('Usted no es cliente...');
        } 
        $customer = $pobox->getCustomer();
        $entities = $em->getRepository('NvCargaBundle:Payment')->findByCustomer($customer);

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Payment entity.
     *
     * @Route("/create", name="payment_create")
     * @Method("POST")
     * @Template("NvCargaBundle:Payment:new.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function createAction(Request $request)
    {
        $entity = new Payment();
        
        $user = $this->getUser();
        // exit(\Doctrine\Common\Util\Debug::dump($user));
        $pobox = $user->getPobox();
        if (!$pobox) {
            throw $this->createNotFoundException('Usted no es cliente...');
        } 
        $customer = $pobox->getCustomer();  
        $entity->setCustomer($customer);
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $guide = $em->getRepository('NvCargaBundle:Payment')->findByGuide($entity->getGuide());
            if ($guide) {
            throw $this->createNotFoundException('Ya existe un pago reportado para esa guía.');
            }
            $entity->setCreationdate(new \DateTime());
            $entity->setPaydate(new \DateTime($entity->getPaydate()));
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('payment_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'nameform' => 'Reportar pago',
        );
    }

    /**
     * Creates a new Payment entity.
     *
     * @Route("/create_public", name="payment_create_public")
     * @Template("NvCargaBundle:Payment:new_public.html.twig")
     */
    public function create_publicAction(Request $request)
    {
        $entity = new Payment();
        $form = $this->createCreateFormPublic($entity);
        $form->handleRequest($request);
        //$guide = $form['guide']->getData();
        // exit(\Doctrine\Common\Util\Debug::dump($guide));

        if ($form->isValid()) {
            // exit(\Doctrine\Common\Util\Debug::dump($guide));
            $id_customer = $form['id_customer']->getData();
                $em = $this->getDoctrine()->getManager();
            $guide = $em->getRepository('NvCargaBundle:Payment')->findByGuide($entity->getGuide());
            if ($guide) {
            throw $this->createNotFoundException('Ya existe un pago reportado para esa guía.');
            }
            $customer = $em->getRepository('NvCargaBundle:Customer')->find($id_customer);
            if (!$customer) {
            throw $this->createNotFoundException('No existe el usuario.');
            }
            $entity->setCustomer($customer);
            $entity->setCreationdate(new \DateTime());
            $entity->setPaydate(new \DateTime($entity->getPaydate()));
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('payment_new_public'));
        } 

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'nameform' =>'Reportar pago',
        );
    }
    /**
     * Creates a form to create a Payment entity.
     *
     * @param Payment $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateFormPublic(Payment $entity)
    {
        $form = $this->createForm(new PaymentType($this->getDoctrine()->getManager()), $entity, array(
            'action' => $this->generateUrl('payment_create_public'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Procesar'));

        return $form;
    }

    /**
     * Creates a form to create a Payment entity.
     *
     * @param Payment $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Payment $entity)
    {
        $form = $this->createForm(new PaymentType($this->getDoctrine()->getManager()), $entity, array(
            'action' => $this->generateUrl('payment_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Procesar'));
        $form->remove('id_customer');
        $form->remove('name_customer');
        $form->remove('last_customer');
        $form->remove('email_customer');

        $form->add('id_customer', 'hidden', array('mapped'=>false, 'data'=> $entity->getCustomer()->getId())); // SOLO SE REQUIERE ESTE ID....
        $form->add('name_customer', 'text', array('label'=> 'Nombre ', 'mapped'=>false, 'data' => $entity->getCustomer()->getName(), 'read_only'=>true));
        $form->add('lastname_customer', 'text', array('label'=> 'Apellido ', 'mapped'=>false,'data' => $entity->getCustomer()->getLastname(), 'read_only'=>true));
        $form->add('email_customer', 'email', array('label'=> 'Email ', 'mapped'=>false, 'data' => $entity->getCustomer()->getEmail(), 'read_only'=>true));

        return $form;
    }
    /**
     * Displays a form to create a new Payment entity.
     *
     * @Route("/new", name="payment_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function newAction()
    {
        $entity = new Payment();
        
        $user = $this->getUser();
        $em = $this->getDoctrine();
        
        //exit(\Doctrine\Common\Util\Debug::dump($guides));
        $pobox = $user->getPobox();
        if (!$pobox) {
            throw $this->createNotFoundException('Usted no es cliente...');
        } 
        $customer = $pobox->getCustomer();
        $entity->setCustomer($customer);
        $form   = $this->createCreateForm($entity);
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'nameform' => 'Reportar pago',
        );
    }
    /**
     * Displays a form to create a new Payment entity.
     *
     * @Route("/new_public", name="payment_new_public")
     * @Method("GET")
     * @Template()
     */
    public function new_publicAction()
    {
        $entity = new Payment();
        $form   = $this->createCreateFormPublic($entity);
        $username = "webuser";
        $em = $this->getDoctrine();
        $repo  = $em->getRepository("NvCargaBundle:User"); //Entity Repository
        $user = $repo->findOneByUsername($username);
        
        if (!$user) {
                throw new UsernameNotFoundException("El usuario " . $username . " no existe");
        } else {
                $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_secured_area', serialize($token));
        }
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'nameform' =>'Reportar pago',
        );
    }
    /**
     * Finds and displays a Payment entity.
     *
     * @Route("/show/{id}", name="payment_show")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $pobox = $user->getPobox();
        if ($pobox) {
            $customer = $pobox->getCustomer();
            $entity = $em->getRepository('NvCargaBundle:Payment')->findBy(array('id' => $id, 'customer' => $customer));
        } else {
            $roles = $user->getRoles();
            if (in_array('ROLE_ADMIN', $roles)) {
                $entity = $em->getRepository('NvCargaBundle:Payment')->find($id);
            } else {
                throw $this->createNotFoundException('No tiene permiso para ver el pago');
            }
        }
        

        if (!$entity) {
            throw $this->createNotFoundException('No existe el pago');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Payment entity.
     *
     * @Route("/{id}/edit", name="payment_edit")
     * @Method("GET")
     * @Template()
     */
/*
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Payment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Payment entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
*/
    /**
    * Creates a form to edit a Payment entity.
    *
    * @param Payment $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
/*
    private function createEditForm(Payment $entity)
    {
        $form = $this->createForm(new PaymentType(), $entity, array(
            'action' => $this->generateUrl('payment_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
*/
    /**
     * Edits an existing Payment entity.
     *
     * @Route("/{id}", name="payment_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Payment:edit.html.twig")
     */
/*
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NvCargaBundle:Payment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Payment entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('payment_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
*/
    /**
     * Deletes a Payment entity.
     *
     * @Route("/{id}", name="payment_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $pobox = $user->getPobox();
            if ($pobox) {
                $customer = $pobox->getCustomer();
                $entity = $em->getRepository('NvCargaBundle:Payment')->findOneBy(array('id' => $id, 'customer' => $customer));
            } else {
                throw $this->createNotFoundException('No puede eliminar el reporte de pago de otro cliente');
            }

            if (!$entity) {
                throw $this->createNotFoundException('El pago no existe, o no puede eliminarse');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('payment_list'));
    }

    /**
     * Creates a form to delete a Payment entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('payment_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar pago'))
            ->getForm()
        ;
    }
    /**
     * @Route("/process", name="payment_process")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function processAction(Request $request)
    {
        $list = $request->query->get('paymentlist');
        $paymentlist=json_decode($list);
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        
        $entities = $em->getRepository('NvCargaBundle:Payment')->findById($paymentlist);
        // exit(\Doctrine\Common\Util\Debug::dump($entities));
	
        foreach ($entities as $entity) {
            $entity->setVerified(TRUE);
            $bill = new Bill();
            $hbid = $maincompany->getCountbills();
            
            $plan = $maincompany->getPlan(); 
            if (($plan->getBills()) && ($hbid >= $plan->getMaxbills())) {
                $message = 'Ha llegado al número máximo de FACTURAS permitidas. Para crear más debe actualizar su plan a uno superior.';
                if ($this->isGranted('ROLE_ADMIN')) {
                    $message = $message . '<a href="' .  $this->generateUrl('loginmain') . '" > Actualizar ahora</a>';
                }
                throw $this->createNotFoundException($message);
            }
            $hbid++;
            $maincompany->setCountbills($hbid);
            $nb = str_pad($hbid, 10, '0', STR_PAD_LEFT);
            $bill->setNumber($nb);
            $bill->setCustomer($entity->getCustomer());
            $bill->setCreationdate(new \DateTime());
            $bill->addGuide($entity->getGuide());
            $bill->setCod($entity->getGuide()->getCod());
            $bill->setPaidtype($entity->getGuide()->getPaidtype());
            $em->persist($bill);
            $entity->getGuide()->setBill($bill);
        } 
        $em->flush();
        return $this->redirect($this->generateUrl('payment_byverify'));
    }
}
