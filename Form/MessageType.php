<?php

namespace NvCarga\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class MessageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           ->add('receiverid', 'hidden', array('mapped'=> false ))
            ->add('receivername', 'text', array('mapped'=> false, 'read_only' => true, 'label' => 'Destinatario '))
            /*
            ->add('goto', 'text', array('label' => 'Destinatario: ', 'mapped'=> false, 'required'=>true, 
                    'constraints' => array(new NotBlank(["message" => "El destinatario no puede estar vacío"]), 
                                new Length(
                                ["min" => 2, "max" => 30,
                                "minMessage" => "El destinatario debe tener al menos {{ limit }} caracteres",
                                "maxMessage" => "El destinatario no puede tener mas de {{ limit }} caracteres"])), 
                                'attr' => array('style' => 'width: 20em')))
                    */
           ->add('subject', 'text', array('label' => 'Asunto: ', 'attr' => array('style' => 'width: 65em')))
           ->add('body', 'textarea', array('label' => 'Contenido: ', 'attr' => array('style' => 'width: 65em; height: 10em')))
//            ->add('creationdate')
//            ->add('isread')
//            ->add('sender')
            
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NvCarga\Bundle\Entity\Message'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'message_type';
    }
}
