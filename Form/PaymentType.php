<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

use Doctrine\ORM\EntityManager;
use NvCarga\Bundle\Entity\Customer;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;


class PaymentType extends AbstractType
{
    protected $em;
    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
	$listaccounts = $this->em->getRepository('NvCargaBundle:Account')->findByIsactive(true);
        $builder
	    ->add('id_customer', 'hidden', array('mapped'=>false, 'data'=> 0)) // SOLO SE REQUIERE ESTE ID....
	    ->add('name_customer', 'text', array('label'=> 'Nombre ', 'mapped'=>false, 
						'constraints' => array(
							new NotBlank(["message" => "El nombre no puede estar vacío"]), 
							new Length(
							["min" => 2, "max" => 30,
							"minMessage" => "El nombre debe tener al menos {{ limit }} caracteres",
							"maxMessage" => "El nombre no puede tener mas de {{ limit }} caracteres"]))))
            ->add('lastname_customer', 'text', array('label'=> 'Apellido ', 'mapped'=>false,
						'constraints' => array(
							new NotBlank(["message" => "El apellido no puede estar vacío"]), 
							new Length(
							["min" => 2, "max" => 30,
							"minMessage" => "El apellido debe tener al menos {{ limit }} caracteres",
							"maxMessage" => "El apellido no puede tener mas de {{ limit }} caracteres"]))))
	    ->add('email_customer', 'email', array('label'=> 'Email ', 'mapped'=>false, 
						'constraints' => array(
							new NotBlank(["message" => "Debe asignar un email"]),
							new Email(["message" => "El email '{{ value }}' no es válido.", 
										"checkMX" => true, "checkHost" => true]))))
            ->add('paydate','text', array('label' => 'Fecha del pago '))
            // ->add('creationdate')
            ->add('amount', null, array('label' => 'Monto pagado ', 'read_only' => true, 'scale' => 2, 
						'rounding_mode' => 0))
            
            ->add('account', 'entity', array('label'=> 'Cuenta ', 'required' => true, 
            'empty_value' => '-- Seleccione la cuenta --',
            'class' => 'NvCargaBundle:Account',
            'choices' => $listaccounts,
	    'constraints' => array(new NotBlank(["message" => "Debe seleccionar una cuenta"]))
            ))
        ;
	$builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }
     protected function addElements(FormInterface $form, Customer $customer= null) {
 
        // States are empty, unless we actually supplied a country
        $guides = array();
        $guidespay = array();
        $payments = $this->em->getRepository('NvCargaBundle:Payment')->findAll();
        foreach ($payments as $pay) {
            $guidespay[] = $pay->getGuide()->getId();
        }
        if ($customer) {
           $guides= $this->em->getRepository('NvCargaBundle:Guide')->createQueryBuilder('g')
                ->where('g.addressee IN (:addressee) OR g.sender = :sender')
                ->setParameters(array('addressee'=> $customer->getBaddress(), 'sender'=> $customer))
                ->orderBy('g.number', 'ASC')
                ->getQuery()
                ->getResult();
                
            foreach ($guides as $guide) {
                if ($key = in_array($guide->getId(), $guidespay)) {
                    unset($guides[$key]);
                }
            }
        }
        $guides = $this->em->getRepository('NvCargaBundle:Guide')->findAll();
        // Add the state element
        $form->add('guide', 'entity', array('label'=> 'labels.guidenumber' ,
            'empty_value' => '-- Seleccione primero el cliente --',
            'class' => 'NvCargaBundle:guide',
            'choices' => $guides,
            'constraints' => array(new NotBlank(["message" => "Debe seleccionar 1"]))
        ));
        // Add submit button again, this time, it's back at the end of the form
 //       $form->add($submit);
    }
    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();
        // Note that the data is not yet hydrated into the entity.
        $customer = $this->em->getRepository('NvCargaBundle:Customer')->findOneByEmail($data['email_customer']);
	// exit(\Doctrine\Common\Util\Debug::dump($customer));

        $this->addElements($form, $customer);
    }
    function onPreSetData(FormEvent $event) {
        $payment = $event->getData();
        $form = $event->getForm();
        // We might have an empty account (when we insert a new account, for instance)
	// exit(\Doctrine\Common\Util\Debug::dump($payment));
	if ($payment->getGuide()) {
		$id_customer= $payment['id_customer'];
		$customer = $this->em->getRepository('NvCargaBundle:Customer')->find($id_customer);
	} else {
		$customer= null;
	} 
        // $customer = $payment->getGuide() ? $payment->getCustomer() : null;
        $this->addElements($form, $customer);
    }   
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Payment'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'payment_type';
    }
}
