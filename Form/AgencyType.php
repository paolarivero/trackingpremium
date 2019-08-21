<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

class AgencyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',null, array('label'=> 'Nombre '))
            ->add('phone', 'text', array('label'=> 'Teléfono '))
            ->add('fax', 'text', array('label'=> 'Fax ', 'required' => false))
            ->add('address', 'textarea', array('label'=> 'Dirección '))
            ->add('email', 'email', array('label'=> 'Email '))
            ->add('webmaster', 'text', array('label'=> 'Webmaster ', 'required' => false))
            ->add('zip',null, array('label'=> 'Zip '))
            ->add('manager', 'text', array('label'=> 'Nombre del Manager'))
            ->add('guidecopies', 'checkbox', array('label'=> 'labels.emailagency', 'required' => false))
            ->add('sharecustomer', 'checkbox', array('label'=> 'Comparte sus clientes ', 'required' => false))
            ->add('poboxs', 'checkbox', array('label'=> 'Maneja Casilleros', 'required' => false))
//            ->add('creationdate')
//            ->add('lastupdate')
//            ->add('status','entity', array('label'=> 'Status Agencia ', 'class' => 'NvCargaBundle:Agencystatus',))
//            ->add('type', 'entity', array('label'=> 'Tipo Agencia ', 'class' => 'NvCargaBundle:Agencytype',))
//            ->add('warehouse', 'entity', array('label'=> 'Bodega ', 'class' => 'NvCargaBundle:Warehouse',))
//            ->add('city', 'entity', array('label'=> 'Ciudad ', 'class' => 'NvCargaBundle:City',))
//            ->add('parent')
            ->add('cityid', 'hidden', array('mapped'=>false))
            ->add('cityname', 'hidden', array('label'=> 'Ciudad ', 'read_only' => true, 'mapped'=>false, 
					'constraints' => array(new NotBlank(["message" => "Escoja una ciudad"]))))  
            //->add('state', 'text', array('label'=> 'Estado ', 'read_only' => true, 'mapped'=>false))
            //->add('country', 'text', array('label'=> 'País ', 'read_only' => true, 'mapped'=>false)) 
// Para buscar ciudad
	   // ->add('namecity', 'text', array('label' => 'Nombre ', 'mapped'=> false, 'required' => false))
	   // ->add('searchcity', 'button', array('label' => 'Buscar'))
	   // ->add('selcity', 'hidden', array('label' => ' ', 'mapped'=> false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Agency'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'agency_type';
    }
}
