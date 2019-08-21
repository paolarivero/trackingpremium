<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MoveguidesType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('movdate','text', array('label' => 'Fecha '))
            ->add('track',null,array('label'=> 'Número de track '))
            ->add('comment',null,array('label'=> 'Comentario '))
            ->add('percentage',null,array('label'=> '% de movimiento ', 'required'=>false))
            ->add('status',null,array('label'=> 'labels.statusguide'))
            // ->add('company', 'entity', array('label'=> 'Compañía local ', 'class' => 'NvCargaBundle:Localcompany'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Moveguides'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'moveguides_type';
    }
}
