<?php

namespace NvCarga\Bundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProfileType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
	//  
        $builder
            ->add('name',null,array('label'=>'Nombre '))
            ->add('description', null, array('label'=>'Descripción '))
            /*
            ->add('admins', 'entity', array('class'=>'NvCargaBundle:Role', 'label'=>' ', 
                    'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('r')
                    ->where('r.name != :rolemain')
                    ->andWhere('r.name LIKE :admin')
                    ->setParameters(['rolemain'=>'ROLE_ADMIN_MAINCOMPANY', 'admin'=> 'ROLE_ADMIN_%'])
                    ->orderBy('r.name', 'ASC');
                    }, 
                    'multiple' => true, 'expanded'=>true))
            
            ->add('views', 'entity', array('class'=>'NvCargaBundle:Role', 'label'=>' ', 
                    'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('r')
                    ->where('r.name LIKE :view')
                    ->setParameters(['view'=> 'ROLE_VIEW_%'])
                    ->orderBy('r.name', 'ASC');
                    }, 
                    'multiple' => true, 'expanded'=>true))
            */
            ->add('adminrole', 'checkbox', array('label'=>'Todos los permisos ', 'mapped' => false,  'required' => false ))
            //->add('auser', 'checkbox', array('label'=>false, 'mapped' => false,  'required' => false))
            //->add('vcustom', 'checkbox', array('label'=>false, 'mapped' => false,  'required' => false ))
            //->add('acustom', 'checkbox', array('label'=>false, 'mapped' => false,  'required' => false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Profile'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'profile_type';
    }
}
