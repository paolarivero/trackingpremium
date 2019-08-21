<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Range;

use Doctrine\ORM\EntityManager;
use NvCarga\Bundle\Entity\Maincompany;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;


class BillpayType extends AbstractType
{
    protected $em;
    protected $maincompany;
    function __construct(EntityManager $em, Maincompany $maincompany)
    {
        $this->em = $em;
        $this->maincompany = $maincompany;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $listaccounts = $this->em->getRepository('NvCargaBundle:Account')->findBy(['maincompany' => $this->maincompany, 'isactive'=>true]);
        $listpayments = $this->em->getRepository('NvCargaBundle:Paidtype')->findBy(['maincompany' => $this->maincompany, 'active'=>true]);
        $countries = $this->maincompany->getCountries()->toArray();
        $list = array();
        foreach ($countries as $country) {
            $list[] = $country->getName();
        }
        $listcurrency = $this->em->getRepository('NvCargaBundle:Currency')->findByCountry($list, array('country'=>'ASC'));
        $builder
        ->add('account', 'entity', array('label'=> 'Cuenta ', 'required' => false, 
            'placeholder' => '-- Seleccione la cuenta --',
            'class' => 'NvCargaBundle:Account', 'attr' => array('title'=>'Seleccione la cuenta si es depósito o transferencia'),
            'choices' => $listaccounts,))
        ->add('currency', 'entity', array('label'=> 'Currency ', 'required' => true, 
            'class' => 'NvCargaBundle:Currency', 'attr' => array('title'=>'Seleccione la moneda del pago'),
            'choices' => $listcurrency,))
        ->add('paidtype', 'entity', array('label'=> 'Tipo de pago ', 'required' => true, 
            'placeholder' => '-- Seleccione el tipo de pago --',
            'class' => 'NvCargaBundle:Paidtype',
            'choices' => $listpayments,
            'constraints' => array(new NotBlank(["message" => "Debe seleccionar un tipo de pago"]))))
        ->add('paydate','text', array('label' => 'Fecha del pago ', 'required'=>true))
        ->add('amount','number', array( 'label'=>'Monto pagado ', 'required' => true, 'scale' => 2, 
                'attr' => array('placeholder' => 'Monto pagado'), )) 
         ->add('conversion','number', array( 'label'=>'Valor de Currency en USD ', 'required' => true, 'scale' => 8, 
                'attr' => array('placeholder' => 'Taza de conversión de la moneda'), )) 
        ->add('clock','hidden', array('mapped'=>false))
        ->add('note','text', array('label' => 'Nota '))
        ;
    }
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Billpay'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'billpay_type';
    }
}
