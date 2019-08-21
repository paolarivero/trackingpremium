<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class BaddressType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
	    ->add('name', null, array('label'=> 'Nombre '))
            ->add('lastname', null, array('label'=> 'Apellido '))							
	    ->add('address', 'textarea', array('label'=> 'Dirección '))
            ->add('phone', 'text', array('label'=> 'Teléfono', 'required' => false))
	    ->add('docid', 'text', array('label'=> 'Documento de identidad', 'required' => false))
        ->add('mobile', 'text', array('label'=> 'Móvil ', 'required' => false))
        //->add('barrio', 'text', array('label'=> 'Sector ', 'required' => false))
	    ->add('zip','text', array('label'=> 'Zip', 'required' => false))
	    ->add('cityid', 'hidden', array('mapped'=>false))
	    ->add('cityname', 'hidden', array('label'=> 'Ciudad ', 'read_only' => true, 'mapped'=>false, 
					'constraints' => array(new NotBlank(["message" => "Escoja una ciudad"]))))  
  	    // ->add('state', 'text', array('label'=> 'Estado ', 'read_only' => true, 'mapped'=>false))
	    //->add('country', 'text', array('label'=> 'País ', 'read_only' => true, 'mapped'=>false)) 
// Para buscar ciudad
	   ->add('selcity', 'hidden', array('label' => ' ', 'mapped'=> false))
        ;
  
           // ->add('customer')
           // ->add('city')
        
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Baddress'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'baddress_type';
    }
}
