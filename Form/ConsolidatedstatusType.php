<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConsolidatedstatusType extends AbstractType
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
            ->add('address', null, array('label' => 'Descripción/Dirección '))
//            ->add('creationdate')
            ->add('country', null, array('label' => 'País ', 'required'=> true))
	    ->add('inherited', null, array('label'=> 'labels.inheretedconsol'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Consolidatedstatus'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'consolidatedstatus_type';
    }
}
