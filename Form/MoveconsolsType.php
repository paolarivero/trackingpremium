<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MoveconsolsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
	//    ->add('consolidated', null, array('label' => 'Consolidado'))
        //    ->add('movdate', null, array('label' => 'Fecha del movimiento', 'data' => $fecha))
	    ->add('movdate','text', array('label' => 'Fecha ',))
            ->add('comment', null, array('label' => 'Comentario'))
            ->add('percentage', null, array('label' => '% de Movimiento ', 'required' => false))
            ->add('status', null, array('label' => 'Status'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Moveconsols'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'moveconsols_type';
    }
}
