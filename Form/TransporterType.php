<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TransporterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'Nombre de la Empresa '))
            ->add('phone', null, array('label' => 'Teléfono '))
            ->add('fax', null, array('label' => 'Fax'))
            ->add('contact', null, array('label' => 'Contacto '))
            ->add('zip', null, array('label' => 'Zip '))
            ->add('address', null, array('label' => 'Dirección '))
//            ->add('creationdate')
            ->add('customs_area', null, array('label' => 'Area Aduanera '))
            ->add('city', null, array('label' => 'Ciudad '))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Transporter'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'nvcarga_bundle_transporter';
    }
}
