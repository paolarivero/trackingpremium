<?php

namespace NvCarga\Bundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

class CustomerType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label'=> 'Nombre '))
            ->add('lastname', null, array('label'=> 'Apellido '))							
            ->add('address', 'textarea', array('label'=> 'Dirección ', 'mapped'=>false, 
                    'constraints' => array(
                        new NotBlank(["message" => "La dirección no puede estar vacía"]), 
                        new Length(
                        ["min" => 4, "max" => 120,
                        "minMessage" => "La dirección debe tener al menos {{ limit }} caracteres",
                        "maxMessage" => "La dirección no puede tener mas de {{ limit }} caracteres"]))))
            ->add('phone', 'text', array('mapped'=> false,'label'=> 'Teléfono', 'required' => false))
            ->add('docid', 'text', array('mapped'=> false,'label'=> 'Documento de identidad', 'required' => false))
            ->add('mobile', 'text', array('mapped'=> false,'label'=> 'Móvil ', 'required' => false))
            //->add('barrio', 'text', array('mapped'=> false,'label'=> 'Sector ', 'required' => false))
            ->add('email', 'email', array('label'=> 'Email '))
            ->add('zip','text', array('mapped'=> false,'label'=> 'Zip', 'required' => false))
            ->add('cityid', 'hidden', array('mapped'=>false))
            ->add('cityname', 'hidden', array('label'=> 'Ciudad ', 'read_only' => true, 'mapped'=>false, 
                    'constraints' => array(new NotBlank(["message" => "Escoja una ciudad"]))))  
            // ->add('state', 'text', array('label'=> 'Estado ', 'read_only' => true, 'mapped'=>false))
            //->add('country', 'text', array('label'=> 'País ', 'read_only' => true, 'mapped'=>false)) 
            // Para buscar ciudad
            //->add('namecity', 'text', array('label' => 'Nombre ', 'mapped'=> false, 'required' => false))
            //->add('searchcity', 'button', array('label' => 'Buscar'))
            ->add('selcity', 'hidden', array('label' => ' ', 'mapped'=> false))
            ->add('type', 'entity', array('label' => 'Tipo de Cliente ', 
                    'data_class'=> 'NvCarga\Bundle\Entity\Customertype', 
                    'class'=> 'NvCargaBundle:Customertype', 'expanded' => true,
                ))
            //->add('creationdate') , 'disabled'=>true
            //->add('status', 'entity', array('label'=> 'Status ', 'class' => 'NvCargaBundle:Customerstatus'))
            //->add('type',  'entity', array('label'=> 'Tipo de cliente ', 'class' => 'NvCargaBundle:Customertype'))               
            //->add('pobox')
                ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Customer'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'customer_type';
    }
}
