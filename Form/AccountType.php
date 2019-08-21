<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

class AccountType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', 'text', array('label'=> 'Número de Cuenta '))
            // ->add('creationdate')
            ->add('rtn', 'text', array('label'=> 'Número de ruta de tránsito (RTN) ', 'required' => false))
            ->add('swift', 'text', array('label'=> 'SWIFT ', 'required' => false))
            ->add('email', 'email', array('label'=> 'Email '))
            ->add('docid', 'text', array('label'=> 'Documento (CI/RIF/Pasaporte) ', 'required' => false))
            ->add('address', 'text', array('label'=> 'Dirección ', 'required' => false))
            ->add('bankname', 'text', array('label'=> 'Nombre del Banco '))
            ->add('holdername', 'text', array('label'=> 'Nombre del titular '))
            // ->add('city')
            // ->add('company')
	    ->add('cityid', 'hidden', array('mapped'=>false))
        ->add('cityname', 'hidden', array('mapped'=>false,)) 
// 					'constraints' => array(new NotBlank(["message" => "Escoja una ciudad"]))))  
//   	    ->add('state', 'text', array('label'=> 'Estado ', 'read_only' => true, 'mapped'=>false))
// 	    ->add('country', 'text', array('label'=> 'País ', 'read_only' => true, 'mapped'=>false))
	    ->add('isactive', 'checkbox', array('label'=> 'Cuenta Activa '))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Account'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'account_type';
    }
}
