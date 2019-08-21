<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AlertType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tracking', null, array('label'=>'Tracking'))
            //->add('creationdate')
            //	    ->add('arrivedate','date', array('label' => 'Fecha de llegada ', 'widget' => 'single_text'))
            ->add('arrivedate','text', array('label' => 'Llega '))
            ->add('file', null , array('label' => 'Anexar Imagen', 'required' => false))
            ->add('insurance', null, array('label' => 'Seguro (Opcional)'))
            ->add('pieces', 'integer', array('label' => 'Piezas ', 'data' => 1))
            ->add('weight', 'number', array('label' => 'Peso ', 'data' => 0.0))
            ->add('description', null, array('label' => 'Descripción '))
            ->add('value', null, array('label' => 'Valor'))
            //->add('show')
            ->add('shippingtype', 'entity', array('label'=>'Envío ', 
                    'required' => true, 'class'=>'NvCargaBundle:Shippingtype'))
            //->add('carrier', 'entity', array('label'=>'Carrier', 
            //        'required' => true, 'class'=>'NvCargaBundle:Carrier'))
            ->add('instructions', null, array('label' => 'Instrucciones '))
//            ->add('pobox')
//            ->add('receipt')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Alert'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'alert_type';
    }
}
