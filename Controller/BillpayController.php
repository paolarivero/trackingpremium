<?php

namespace NvCarga\Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NvCarga\Bundle\Entity\Billpay;
use NvCarga\Bundle\Entity\Bill;
use NvCarga\Bundle\Form\BillpayType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\Validator\Constraints\NotBlank;

// For login a user programatically 
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Billpay controller.
 *
 * @Route("/billpay")
 */
class BillpayController extends Controller
{

    /**
     * Lists all Billpay entities.
     *
     * @Route("/byverify", name="billpay_byverify")
     * @Method("GET")
     * @Template("NvCargaBundle:Billpay:index.html.twig")
     * @Security("has_role('ROLE_ADMIN_BILL') or has_role('ROLE_ADMIN')")
     */
    public function byverifyAction()
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Billpay')->findBy(['maincompany'=> $maincompany, 'verified'=>false, 'refund'=>false]);

        $paidtypes = $em->getRepository('NvCargaBundle:Paidtype')->findBy(array('maincompany'=>$maincompany));
        
        return array(
            'entities' => $entities,
            'paidtypes' => $paidtypes,
            'form_name' => 'Pagos por verificar',
            'verify' => true,
            'refund' => true,
            'cancel' => false,
        );
    }
    /**
     * Lists all Billpay entities.
     *
     * @Route("/refunded", name="billpay_refunded")
     * @Method("GET")
     * @Template("NvCargaBundle:Billpay:index.html.twig")
     * @Security("has_role('ROLE_ADMIN_BILL') or has_role('ROLE_ADMIN')")
     */
    public function refundedAction()
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Billpay')->findBy(['maincompany'=> $maincompany, 'verified'=> true, 'refund'=>true]);

        $paidtypes = $em->getRepository('NvCargaBundle:Paidtype')->findBy(array('maincompany'=>$maincompany));
        
        return array(
            'entities' => $entities,
            'paidtypes' => $paidtypes,
            'form_name' => 'Pagos devueltos',
            'verify' => false,
            'refund' => false,
            'cancel' => false,
        );
    }
    /**
     * Lists all Billpay entities.
     *
     * @Route("/verified", name="billpay_verified")
     * @Method("GET")
     * @Template("NvCargaBundle:Billpay:index.html.twig")
     * @Security("has_role('ROLE_ADMIN_BILL') or has_role('ROLE_ADMIN')")
     */
    public function verifiedAction()
    {
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        $entities = $em->getRepository('NvCargaBundle:Billpay')->findBy(['maincompany'=> $maincompany, 'verified'=>true, 'refund'=>false]);
        $paidtypes = $em->getRepository('NvCargaBundle:Paidtype')->findBy(array('maincompany'=>$maincompany));
        
        return array(
            'entities' => $entities,
            'paidtypes' => $paidtypes,
            'form_name' => 'Pagos verificados',
            'verify' => false,
            'refund' => true,
            'cancel' => false,
        );
    }
   /**
     * Lists all Billpay entities.
     *
     * @Route("/cusver", name="billpay_cusver")
     * @Method("GET")
     * @Template("NvCargaBundle:Billpay:index.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function cusverAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany=$user->getMaincompany();
        // exit(\Doctrine\Common\Util\Debug::dump($user));
        $pobox = $user->getPobox();
        if (!$pobox) {
            throw $this->createNotFoundException('Usted no es cliente...');
        } 
        $customer = $pobox->getCustomer();
        $bills = $em->getRepository('NvCargaBundle:Bill')->findBy(['customer'=>$customer, 'status'=>['COBRADA','PENDIENTE']]);
        $entities = $em->getRepository('NvCargaBundle:Billpay')->findBy(['bill'=>$bills,'verified'=>true]);
        $paidtypes = $em->getRepository('NvCargaBundle:Paidtype')->findBy(array('maincompany'=>$maincompany));
        
        return array(
            'entities' => $entities,
            'paidtypes' => $paidtypes,
            'form_name' => 'Pagos del cliente ' . $customer,
            'verify' => false,
            'refund' => false,
            'cancel' => false,
        );
    }
    /**
     * Lists all Billpay entities.
     *
     * @Route("/cusunver", name="billpay_cusunver")
     * @Method("GET")
     * @Template("NvCargaBundle:Billpay:index.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function cusunverAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany=$user->getMaincompany();
        // exit(\Doctrine\Common\Util\Debug::dump($user));
        $pobox = $user->getPobox();
        if (!$pobox) {
            throw $this->createNotFoundException('Usted no es cliente...');
        } 
        $customer = $pobox->getCustomer();
        $bills = $em->getRepository('NvCargaBundle:Bill')->findBy(['customer'=>$customer, 'status'=>['COBRADA','PENDIENTE']]);
        $entities = $em->getRepository('NvCargaBundle:Billpay')->findBy(['bill'=>$bills,'verified'=>false]);
        $paidtypes = $em->getRepository('NvCargaBundle:Paidtype')->findBy(array('maincompany'=>$maincompany));
        
        return array(
            'entities' => $entities,
            'paidtypes' => $paidtypes,
            'form_name' => 'Pagos del cliente ' . $customer,
            'verify' => false,
            'refund' => false,
            'cancel' => true,
        );
    }
    /**
     * Displays a form to create a new Billpay entity.
     *
     * @Route("/{id}/new", name="billpay_new")
     * @Method("GET")
     * @Template("NvCargaBundle:Billpay:new.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function newAction($id)
    {
        $entity = new Billpay();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $em = $this->getDoctrine()->getManager();
        $bill = $em->getRepository('NvCargaBundle:Bill')->find($id);
        if (!$bill) {
            throw $this->createNotFoundException('No exite la factura ');
        }
        $admin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ADMIN_BILL');
        $pobox = $this->getUser()->getPobox();
        if ($pobox) {
            $customer = $pobox->getCustomer();
            $owner = $customer == $bill->getCustomer();
        } else {
            $owner = false;
        }
        if ((!$admin) && (!$owner)) {
            throw $this->createNotFoundException('No puede hacer pagos a la factura ');
        }
        $entity->setBill($bill);
        $now = new \DateTime();
        $entity->setPaydate($now->format('m/d/Y'));
        $currency = $em->getRepository('NvCargaBundle:Currency')->findOneByCode('USD');
        $entity->setCurrency($currency);
        $entity->setConversion(1);
        $form   = $this->createCreateForm($entity, $id);
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'nameform' => 'Agregar pago',
            'currency_ref' => $currency->getId(),
            'new' => true,
        );
    }

    /**
     * Creates a new Billpay entity.
     *
     * @Route("/{id}/create", name="billpay_create")
     * @Template("NvCargaBundle:Billpay:new.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function createAction(Request $request, $id)
    {
        $entity = new Billpay();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        $em = $this->getDoctrine()->getManager();
        $bill = $em->getRepository('NvCargaBundle:Bill')->find($id);
        if (!$bill) {
            throw $this->createNotFoundException('No exite la factura ');
        }
        $admin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ADMIN_BILL');
        $pobox = $this->getUser()->getPobox();
        if ($pobox) {
            $customer = $pobox->getCustomer();
            $owner = $customer == $bill->getCustomer();
        } else {
            $owner = false;
        }
        if ((!$admin) && (!$owner)) {
            throw $this->createNotFoundException('No puede hacer pagos a la factura ');
        }
        $form   = $this->createCreateForm($entity, $id);
        $entity->setBill($bill);
        $now = new \DateTime();
        $entity->setPaydate($now->format('m/d/Y'));
        $currency = $em->getRepository('NvCargaBundle:Currency')->findOneByCode('USD');
        $entity->setCurrency($currency);
        $entity->setConversion(1);
        $form->handleRequest($request);
        

        if ($form->isValid()) {
            $bill->addPayment($entity);
            $customer = $bill->getCustomer();
            $clock = $form['clock']->getData(); 
            $paydate = $entity->getPaydate();
            $rdate = substr($paydate,0,10) . 'T' . $clock;
            $entity->setPaydate(new \DateTime($rdate));
            
            $entity->setLastupdate($entity->getPaydate());
            if ($user->getPobox()) {
                $entity->setVerified(false);
            } else {
                $entity->setVerified(true);
                $bill->setBalance($bill->getBalance() + $entity->getAmount());
                if ($bill->getBalance() == $bill->getTotal()) {
                    $bill->setStatus('COBRADA');
                }
                if ($entity->getPaidtype()->getName() == 'Saldo del cliente') {
                    $customer->setRefunded($customer->getRefunded() - $entity->getAmount());
                }
            }
            $entity->setMaincompany($maincompany);
            $highest_id = $em->getRepository('NvCargaBundle:Billpay')->createQueryBuilder('r')
                            ->select('MAX(r.id)')
                            ->where('r.maincompany =:thecompany')
                            ->setParameters(['thecompany'=>$maincompany])
                            ->getQuery()
                            ->getSingleScalarResult();
            if ($highest_id) {
                $billpay =  $em->getRepository('NvCargaBundle:Billpay')->find($highest_id);
                $numberhigh = $billpay->getNumber();
                $maxvalue = filter_var($numberhigh, FILTER_SANITIZE_NUMBER_INT);
                $maxvalue = str_replace('-', '', $maxvalue);
                $maxnumber = abs(intval($maxvalue));  
            } else {
                $maxnumber = 0;
            }
            $maxnumber++;
            $number = 'PAYMENT-' . $maincompany->getAcronym() . $maxnumber;
            $entity->setNumber($number);
            
            $em->persist($entity);
            
            $em->flush();
            return $this->redirect($this->generateUrl('bill_show', array('id'=>$id)));
        } 

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'nameform' =>'Agregar pago',
            'currency_ref' => $currency->getId(),
            'new' => false,
        );
    }

    /**
     * Creates a form to create a Billpay entity.
     *
     * @param Billpay $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Billpay $entity, $id)
    {
        $form = $this->createForm(new BillpayType($this->getDoctrine()->getManager(), $this->getUser()->getMaincompany()), $entity, array(
            'action' => $this->generateUrl('billpay_create', array('id'=>$id)),
            'method' => 'POST',
        ));        
        $form->add('submit', 'submit', array('label' => 'Procesar'));


        return $form;
    }
    
    /**
     * @Route("/process", name="billpay_process")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_BILL') or has_role('ROLE_ADMIN')")
     */
    public function processAction(Request $request)
    {
        $list = $request->query->get('paymentlist');
        $paymentlist=json_decode($list);
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        
        $entities = $em->getRepository('NvCargaBundle:Billpay')->findById($paymentlist);
        // exit(\Doctrine\Common\Util\Debug::dump($entities));
	
        foreach ($entities as $entity) {
            $bill = $entity->getBill();
            if ($bill->getStatus() != 'ANULADA') {
                $customer = $bill->getCustomer();
                $saldo = $customer->getRefunded();
                $go = true;
                if ($entity->getPaidtype()->getName() == 'Saldo del cliente') {
                    if ($saldo >= $entity->getAmount()) {
                        $customer->setRefunded($saldo - $entity->getAmount());
                    } else {
                        $go = false;
                    }
                }
                if ($go) {
                    $entity->setVerified(true);
                    $balance = $bill->getBalance();
                    $pago = $entity->getAmount();
                    $deuda = $bill->getTotal() - $balance;
                    $diff =  $deuda - $pago;
                    if ($diff >= 0) {
                        $balance = $balance + $pago;
                        $refund = 0;
                    } else {
                        $balance = $bill->getTotal();
                        $refund = abs($diff);
                    }
                    $bill->setBalance($balance);
                    if ($balance == $bill->getTotal()) {
                        $bill->setStatus('COBRADA');
                    }
                    $customer->setRefunded($customer->getRefunded() + $refund);
                    $em->flush();
                }
            }
        } 
        return $this->redirect($this->generateUrl('billpay_byverify'));
    }
    /**
     * @Route("/refund", name="billpay_refund")
     * @Template()
     * @Security("has_role('ROLE_ADMIN_BILL') or has_role('ROLE_ADMIN')")
     */
    public function refundAction(Request $request)
    {
        $list = $request->query->get('paymentlist');
        $back = $request->query->get('back');
        $paymentlist=json_decode($list);
        $em = $this->getDoctrine()->getManager();
        $maincompany = $this->getUser()->getMaincompany();
        
        $entities = $em->getRepository('NvCargaBundle:Billpay')->findById($paymentlist);
        // exit(\Doctrine\Common\Util\Debug::dump($entities));
	
        foreach ($entities as $entity) {
            $bill = $entity->getBill();
            $customer = $bill->getCustomer();
            if (!$entity->getRefund()) {
                $entity->setRefund(true);
                $customer->setRefunded($customer->getRefunded() + $entity->getAmount());
                if ($entity->getVerified()) {
                    $bill->setBalance($bill->getBalance() - $entity->getAmount());
                    $bill->setStatus('PENDIENTE');
                } else {
                    $entity->setVerified(true);
                }
                $em->flush();
            }
        } 
        return $this->redirect($this->generateUrl($back));
    }
    /**
     * @Route("/cancel", name="billpay_cancel")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function cancelAction(Request $request)
    {
        $list = $request->query->get('paymentlist');
        $paymentlist=json_decode($list);
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $maincompany=$user->getMaincompany();
        $pobox = $user->getPobox();
        if (!$pobox) {
            throw $this->createNotFoundException('Usted no es cliente...');
        } 
        
        $entities = $em->getRepository('NvCargaBundle:Billpay')->findById($paymentlist);
        // exit(\Doctrine\Common\Util\Debug::dump($entities));
	
        foreach ($entities as $entity) {
            $bill=$entity->getBill();
            $bill->removePayment($entity);
            $em->remove($entity);
        } 
        $em->flush();
        return $this->redirect($this->generateUrl('billpay_cusunver'));
    }
    /**
     * Displays a form to edit an existing Payment entity.
     *
     * @Route("/{id}/edit", name="billpay_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NvCargaBundle:Billpay')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el pago a editar');
        }
        $bill = $entity->getBill();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        if (!$bill) {
            throw $this->createNotFoundException('No exite la factura ');
        }
        $admin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ADMIN_BILL');
        $pobox = $user->getPobox();
        if ($pobox) {
            $customer = $pobox->getCustomer();
            $owner = $customer == $bill->getCustomer();
        } else {
            $owner = false;
        }
        if ((!$admin) && (!$owner)) {
            throw $this->createNotFoundException('No puede editar pagos de la factura ');
        }
        $currency = $em->getRepository('NvCargaBundle:Currency')->findOneByCode('USD');
        $entity->setPaydate($entity->getPaydate()->format('m/d/Y'));
        $amount = $entity->getAmount();
        $form   = $this->createEditForm($entity);
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'nameform' => 'Actualizar pago',
            'currency_ref' => $currency->getId(),
            'edition' => true,
            'amount' => $amount,
        );
    }
    /**
     * Creates a form to create a Billpay entity.
     *
     * @param Billpay $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Billpay $entity)
    {
        $form = $this->createForm(new BillpayType($this->getDoctrine()->getManager(), $this->getUser()->getMaincompany()), $entity, array(
            'action' => $this->generateUrl('billpay_update', array('id'=>$entity->getId())),
            'method' => 'PUT',
        ));        
        $form->add('submit', 'submit', array('label' => 'Actualizar'));


        return $form;
    }
    /**
     * Displays a form to edit an existing Payment entity.
     *
     * @Route("/{id}/update", name="billpay_update")
     * @Method("PUT")
     * @Template("NvCargaBundle:Billpay:edit.html.twig")
     */
    public function updateAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NvCargaBundle:Billpay')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el pago a editar');
        }
        $bill = $entity->getBill();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        if (!$bill) {
            throw $this->createNotFoundException('No exite la factura ');
        }
        $admin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ADMIN_BILL');
        $pobox = $user->getPobox();
        if ($pobox) {
            $customer = $pobox->getCustomer();
            $owner = $customer == $bill->getCustomer();
        } else {
            $owner = false;
        }
        if ((!$admin) && (!$owner)) {
            throw $this->createNotFoundException('No puede editar pagos de la factura ');
        }
        $currency = $em->getRepository('NvCargaBundle:Currency')->findOneByCode('USD');
        $oldate = $entity->getPaydate();
        $entity->setPaydate($oldate->format('d/m/Y'));
        $amount = $entity->getAmount();
        $form   = $this->createEditForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $paydate = $entity->getPaydate();
            if ($oldate->format('d/m/Y') != $paydate) {
                $clock = $form['clock']->getData(); 
                $rdate = substr($paydate,0,10) . 'T' . $clock;
                $entity->setPaydate(new \DateTime($rdate));
            } else {
                $entity->setPaydate($oldate);
            }
            $newamount = $entity->getAmount();
            $diff = $newamount - $amount;
            if ($entity->getVerified()) {
                $balance = $bill->getBalance() + $diff;
                $bill->setBalance($balance);
                if ($balance >= $bill->getTotal()) {
                    $bill->setStatus('COBRADA');
                } else {
                    $bill->setStatus('PENDIENTE');
                }
            }
            //exit(\Doctrine\Common\Util\Debug::dump($diff));
            $em->flush();
            if ($pobox) {   
                return $this->redirect($this->generateUrl('billpay_cusunver'));
            } else {
                return $this->redirect($this->generateUrl('billpay_verified'));
            }
        } 
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'nameform' => 'Actualizar pago',
            'currency_ref' => $currency->getId(),
            'edition' => false,
            'amount' => $amount,
        );
    }
    /**
     * Displays a form to edit an existing Payment entity.
     *
     * @Route("/{id}/pdf", name="billpay_pdf")
     * @Method("GET")
     * @Template()
     */
    public function pdfAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NvCargaBundle:Billpay')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No existe el pago a editar');
        }
        $bill = $entity->getBill();
        $user = $this->getUser();
        $maincompany = $user->getMaincompany();
        if (!$bill) {
            throw $this->createNotFoundException('No exite la factura ');
        }
        $admin = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ADMIN_BILL');
        $pobox = $user->getPobox();
        if ($pobox) {
            $customer = $pobox->getCustomer();
            $owner = $customer == $bill->getCustomer();
        } else {
            $owner = false;
        }
        if ((!$admin) && (!$owner)) {
            throw $this->createNotFoundException('No puede generar el PDF del pago');
        }
        $type = $em->getRepository('NvCargaBundle:Agencytype')->findOneByName('MASTER');
        $agency = $em->getRepository('NvCargaBundle:Agency')->findOneBy(['maincompany'=>$maincompany, 'type'=>$type]);
        $viewoption = $this->getView("Billpay", "print");
        $html = $this->renderView($viewoption, array('entity' => $entity, 'agency'=>$agency));
        $number = $entity->getNumber(); 
        $filename = sprintf('billpay-%s.pdf', transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $number));
        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
                'orientation' => 'portrait',
                'margin-top'    => 10,
                'margin-right'  => 12,
                'margin-bottom' => 10,
                'margin-left'   => 12, 
                'enable-javascript' => true, 
                'javascript-delay' => 1000, 
                'no-stop-slow-scripts' => true, 
                'no-background' => false, 
                'lowquality' => false,
                'encoding' => 'utf-8',
                'images' => true,
                'cookie' => array(),
                'page-size' => 'A4',
                'dpi' => 300,
                'image-dpi' => 300,
                'enable-external-links' => true,
                'enable-internal-links' => true
            )),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
            ]
        );

    }
    public function getView($table, $type) {
        $option = null;
        $maincompany = $this->getUser()->getMaincompany();
        //exit(\Doctrine\Common\Util\Debug::dump($maincompany->getFormat()));
        if ($maincompany->getFormat()) {
            $option = $maincompany->getFormat()->getBillpayprint();
        }
        $viewoption = 'NvCargaBundle:' . $table . ':' . $type . $option . '.html.twig';
        if (!$this->get('templating')->exists($viewoption) ) {
            $viewoption = 'NvCargaBundle:' . $table . ':' . $type . '.html.twig';
        }
        return $viewoption;
    }
}
