<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WarehouseType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label'=> 'Nombre '))
            ->add('address', 'text', array('label'=> 'Dirección '))
            ->add('zip', 'text', array('label'=> 'Zip '))
//            ->add('creationdate')
            ->add('description', 'text', array('label'=> 'Descripción '))
//            ->add('lastupdate')
//            ->add('city', 'entity', array('label'=> 'Ciudad ', 'class' => 'NvCargaBundle:City'))
            ->add('agency', 'entity', array('label'=> 'Agencia ', 'class' => 'NvCargaBundle:Agency'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Warehouse'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'nvcarga_bundle_warehouse';
    }
}
