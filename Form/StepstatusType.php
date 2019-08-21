<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StepstatusType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label'=>'Nombre '))
            ->add('percentage', 'text', array('label'=>'Porcentage '))
            ->add('description', 'text', array('label'=>'Descripción '))
 //           ->add('creationdate')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Stepstatus'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'stepstatus_type';
    }
}
