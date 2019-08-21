<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

class UserType extends AbstractType
{
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fileName', 'hidden', array('mapped'=>false))
            ->add('username', 'text', array('label' => 'Username '))
            ->add('email','email', array('label' => 'Email ', 'constraints' => array(
                    new NotBlank(["message" => "Debe asignar un email"]),
                    new Email(["message" => "El email '{{ value }}' no es válido.", 
                        "checkMX" => false, "checkHost" => false]))))
            ->add('name', 'text', array('label' => 'Nombre'))
            ->add('lastname','text', array('label' => 'Apellido '))
            ->add('thepassword', 'repeated', array('required'=>true, 'mapped'=>false,
                'type' => 'password',
                'first_name' => 'pass',
                'second_name' => 'confirm',
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repita el Password'),
                'invalid_message' => 'Los passwords deben ser iguales',
                'constraints' => array(
                    new NotBlank(["message" => "El password no puede estar vacío"]), 
                    new Length(["min" => 5, "max" => 20,
                        "minMessage" => "El password debe tener al menos {{ limit }} caracteres",
                        "maxMessage" => "El password no puede tener mas de {{ limit }} caracteres"]))))
            
//            ->add('salt')
//           ->add('creationdate')
//            ->add('agency', 'entity', array('label' => 'Agencia ', 'class'=> 'NvCargaBundle:Agency'))
//            ->add('status', 'entity', array('label' => 'Status del Usuario ', 'class'=> 'NvCargaBundle:Userstatus'))
//             ->add('user_roles', 'entity', array('label' => 'Roles ', 'class'=> 'NvCargaBundle:Role', 
//                     'query_builder' => function (EntityRepository $er) {
//                     return $er->createQueryBuilder('p')
//                     ->where('p.name != :maincompany')
//                     ->setparameter('maincompany','ROLE_ADMIN_MAINCOMPANY')
//                     ->orderBy('p.name', 'ASC');
//                     }, 'multiple'=>'true', 	'expanded' => true))
            ->add('profile', 'entity',  array('label' => 'Perfil ', 'class'=> 'NvCargaBundle:Profile', 
                    'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                    ->where('p.maincompany = :maincompany')
                    ->setparameter('maincompany',$this->user->getMaincompany())
                    ->orderBy('p.name', 'ASC');
                    }, 'mapped'=>false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'user_type';
    }
}
