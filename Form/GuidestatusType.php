<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GuidestatusType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'Nombre '))
            ->add('latitude', null, array('label' => 'Latitud '))
            ->add('longitude', null, array('label' => 'Longitud '))
            ->add('address', null, array('label' => 'Dirección '))
//            ->add('creationdate', null, array('label' => 'Nombre '))
            ->add('country', null, array('label' => 'País '))
	    ->add('emailnoti', null, array('label' => 'Envía email '))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Guidestatus'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'guidestatus_type';
    }
}
