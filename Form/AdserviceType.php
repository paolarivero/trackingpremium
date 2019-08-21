<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdserviceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label'=> 'Nombre del servicio ', 'required' => true))
            // ->add('creationdate')
            ->add('measure', 'choice', array('required' => true, 'label'=>'Unidad de medida ',
            'choices' => array('Unit'=>'Unidad', 'Lb' => 'Libras', 'CF'=> 'Pie cúbico',  '%' => 'Porcentaje (%)', 'Miles' => 'Millas', 'N/A' => 'Ninguna')))
            ->add('brand', 'text', array('label'=> 'Marca ', 'required' => false))
            ->add('description', 'text', array('label'=> 'Descripción ', 'required' => false))
            ->add('price', 'number', array('required' => true, 'label' => 'Precio/Porcentaje', 'scale' => 2,  
			'attr' => array('style' => 'width: 7em')))
	    ->add('isactive', null,  array('required' => true, 'label' => 'Activo ', 'data'=>true))
 /*           ->add('services', 'entity', array('required' => false, 'mapped' => false, 'label' => 'Depende de servicios ', 'class' => 'NvCargaBundle:Adservice', 'multiple' => true, 'expanded' => true,  'property' => 'name'))  */
	 ->add('meDependof', null,  array('required' => false, 'label' => 'Depende de servicios ', 
					'multiple' => true, 'expanded' => true,  'property' => 'name'
					));
	
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Adservice'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'adservice_type';
    }
}
