<?php
// src/NvCarga/Bundle/Form/ClassServType.php
namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Range;

class ClassServType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
	
        $builder
            ->add('id','number', array( 'label'=>false, 'disabled' => true, 'read_only'=> true))
            ->add('name','text', array( 'label'=>false, 'disabled' => true, 'read_only'=> true))
            // ->add('description','text', array( 'label'=>false, 'disabled' => true, 'read_only'=> true))
            ->add('measure','text', array( 'label'=>false, 'disabled' => true, 'read_only'=> true ))  
            ->add('price','number', array( 'label'=>false, 'required' => true, 'scale' => 2,  
                    'constraints' => array(new    NotBlank(["message" => "Debe asignar el precio"]), 
                    new Range(["min" => 0, "minMessage" => "El precio mínimo debe ser igual o mayor a {{ limit }}inch",
                    ])))) 
            ->add('amount','integer', array( 'label'=>false, 'required' => false, 'scale' => 2,  
                'constraints' => array(new NotBlank(["message" => "Debe asignar la cantidad"]), 
			       new Range(["min" => 0, "minMessage" => "La cantidad mínima debe ser igual o mayor a {{ limit }}inch",
							]))))
            ->add('total','number', array( 'read_only'=>true, 'label'=>false, 'required' => false, 'scale' => 2,  
                'constraints' => array(new NotBlank(["message" => "Debe asignar el total"]), 
			       new Range(["min" => 0, "minMessage" => "El total mínimo debe ser igual o mayor a {{ limit }}inch",
                        ]))))
	;
     }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
         $resolver->setDefaults(array(
        'data_class' => 'NvCarga\Bundle\Entity\ClassServ',
    ));
	
   }

    /**
     * @return string
     */
    public function getName()
    {
        return 'classserv_type';
    }
}
