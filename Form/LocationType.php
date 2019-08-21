<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LocationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('creationdate')
            ->add('space', null, array('label' => 'Espacio volumétrico '))
            ->add('position', null, array('label' => 'Posición '))
            ->add('comment', null, array('label' => 'Comentario '))
            ->add('warehouse', null, array('label' => 'Bodega '))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Location'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'nvcarga_bundle_location';
    }
}
