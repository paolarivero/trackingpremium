<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LabelconfType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('width', null, array('label'=>'Ancho (inch) '))
            ->add('height', null, array('label'=>'Alto (inch) '))
            ->add('clock', 'hidden', array('mapped'=>false));
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Labelconf'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'labelconf_type';
    }
}
