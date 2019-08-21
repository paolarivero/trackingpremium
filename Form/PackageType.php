<?php
// src/NvCarga/Bundle/Form/PackageType.php
namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Range;

class PackageType extends AbstractType
{
    protected $carriers;
    function __construct($carriers)
    {
        $this->carriers = $carriers;
    }
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
	$fecha =  new \DateTime();
        $builder
            ->add('arrivedate', 'text', array('label' => 'Fecha  '))
            ->add('tracking','text', array('label'=>'Tracking ', 'required' => false, 'attr' => array('placeholder' => 'Tracking')))
            ->add('reference','textarea', array('label'=>'Referencia ', 'attr' => array('placeholder' => 'Referencia'), 'required' => false))
            ->add('description','text', array('label'=>'Descripción ','attr' => array('placeholder' => 'Descripción'), 'required' => false))
            ->add('quantity','integer', array('label'=>'Cantidad ','empty_data' => '1', 
                'required' => true, 'attr' => array('placeholder' => 'Cantidad'),
                'constraints' => array(new NotBlank(["message" => "Debe asignar la cantidad"]), 
                new Range(["min" => 1,  "minMessage" => "La cantidad mínima debe ser igual o mayor a {{ limit }}"]))))
            ->add('weight','number', array('label'=>'Peso/Lb', 'required' => true, 'scale' => 2, 
                'attr' => array('placeholder' => 'Peso '), 
                'constraints' => array(new NotBlank(["message" => "Debe asignar el peso"]), 
                new Range(["min" => 1, "max" => 2000, "minMessage" => "El peso mínimo debe ser igual o mayor a {{ limit }}lb",
                "maxMessage" => "El peso debe ser menor a {{ limit }}lb"]))))
            ->add('length','number', array('label'=>'Largo " ', 'required' => true, 'scale' => 2, 
                'attr' => array('placeholder' => 'Largo '), 
                'constraints' => array(new NotBlank(["message" => "Debe asignar el largo"]), 
                new Range(["min" => 0.01, "max" => 500, "minMessage" => "El largo mínimo debe ser igual o mayor a {{ limit }}inch",
                "maxMessage" => "El largo debe ser menor a {{ limit }}inch"])))) 
            ->add('width','number', array( 'label'=>'Ancho "', 'required' => true, 'scale' => 2, 
                'attr' => array('placeholder' => 'Ancho '), 'constraints' => array(new NotBlank(["message" => "Debe asignar el ancho"]), new Range(["min" => 0.01, "max" => 500, 
                "minMessage" => "El ancho mínimo debe ser igual o mayor a {{ limit }}inch",
                "maxMessage" => "El ancho debe ser menor a {{ limit }}inch"])))) 
            ->add('height','number', array('label'=>'Alto "', 'required' => true, 'scale' => 2, 
                'attr' => array('placeholder' => 'Alto '), 'constraints' => array(new NotBlank(["message" => "Debe asignar el alto"]), new Range(["min" => 0.01, "max" => 500, "minMessage" => "El alto mínimo debe ser igual o mayor a {{ limit }}inch","maxMessage" => "El alto debe ser menor a {{ limit }}inch"])))) 
            ->add('value','number', array('label'=>'Valor ', 'required' => true, 
                'scale' => 2, 'attr' => array('placeholder' => 'Valor'), 'constraints' => array(new NotBlank(["message" => "Debe asignar el valor"]), new Range(["min" => 0,  
                "minMessage" => "El valor mínimo debe ser igual o mayor a {{ limit }}"])))) 
            ->add('carrier', 'entity', array('label'=>'Carrier ', 'required' => true, 'class'=>'NvCargaBundle:Carrier', 'choices'=>$this->carriers))
            ->add('npack', 'integer', array('label' => 'Bultos', 'required'=>false, 
                    'attr' => array('placeholder'=>'Bultos ')))
            ->add('packtype', 'choice', array('label' => 'Tipo ',
                        'choices' => array('Caja' => 'Caja', 
                        'Sobre' => 'Sobre', 'Bulto' => 'Bulto'),'choices_as_values' => true,))
                        
            ->add('position','text', array('label'=>false, 'mapped'=>false, 'data'=> ' ', 'read_only'=>true, 'attr'=>array('style'=>'width:0px;border:none;outline: none;background-color:transparent;')))
            
                        ;
     }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
         $resolver->setDefaults(array(
        'data_class' => 'NvCarga\Bundle\Entity\Package',
    ));
	
   }

    /**
     * @return string
     */
    public function getName()
    {
        return 'package_type';
    }
}
