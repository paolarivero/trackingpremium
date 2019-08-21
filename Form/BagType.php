<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BagType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
 //           ->add('number')
 //           ->add('creationdate')
 //           ->add('status')
 //           ->add('agency')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Bag'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'nvcarga_bundle_bag';
    }
}
