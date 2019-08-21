<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TermcondType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', 'textarea', array('label' => 'Contenido '))
            ->add('active', 'checkbox', array('label' => 'Activo ', 'data'=>true, 'attr'=>array('class'=>'icheck')))
            ->add('tableclass', 'choice', array('label' => 'Asociado a ', 
					'choices' => array('Casillero' => 'Pobox', 
							'labels.guide' => 'Guide', 
							'Factura' => 'Bill', 
							'labels.receipt' => 'Receipt', 
							'Servicio Adicional' => 'Adservice', 
							'Alerta' => 'Alert', 
							'labels.consolidated' => 'Consolidated'), 
					'choices_as_values' => true))
            // ->add('lastupdate')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Termcond'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'termcond_type';
    }
}
